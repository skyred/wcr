<?php

/**
 * @file
 * Contains \Drupal\wcr\Render\MainContent\PartialRenderer.
 */

namespace Drupal\wcr\Render\MainContent;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Display\ContextAwareVariantInterface;
use Drupal\Core\Render\AttachmentsResponseProcessorInterface;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\MainContent\MainContentRendererInterface;
use Drupal\Core\Render\PageDisplayVariantSelectionEvent;
use Drupal\Core\Render\RenderCacheInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RenderEvents;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Cache\Cache;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * Default main content renderer for page partials.
 *
 */
class BlockRenderer implements MainContentRendererInterface {



  protected $blocks;
  protected $page_attachments;
  protected $page;
  protected $renderer;
  protected $displayVariantManager;
  protected $eventDispatcher;
  protected $renderCache;
  protected $htmlResponseAttachmentsProcessor;

  /**
   * WebComponentRenderer constructor.
   * @param \Drupal\wcr\Render\MainContent\PluginManagerInterface $display_variant_manager
   * @param \Drupal\wcr\Render\MainContent\EventDispatcherInterface $event_dispatcher
   * @param \Drupal\wcr\Render\MainContent\RenderCacheInterface $render_cache
   * @param \Drupal\Core\Render\AttachmentsResponseProcessorInterface $html_response_attachments_processor
   * @internal param \Drupal\Core\Render\MainContent\MainContentRendererInterface $html_renderer
   */
  public function __construct(PluginManagerInterface $display_variant_manager,
                              EventDispatcherInterface $event_dispatcher,
                              RenderCacheInterface $render_cache,
                              AttachmentsResponseProcessorInterface $html_response_attachments_processor) {
    $this->renderer = \Drupal::service('renderer');
    $this->displayVariantManager = $display_variant_manager;
    $this->eventDispatcher = $event_dispatcher;
    $this->renderCache = $render_cache;
    $this->htmlResponseAttachmentsProcessor = $html_response_attachments_processor;
    $this->blocks = [];
  }

  /**
   * Prepares the HTML body: wraps the main content in #type 'page'.
   * //TODO: Remove this once HTMLRenderer is reusable.
   *
   * @param array $main_content
   *   The render array representing the main content.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object, for context.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match, for context.
   *
   * @return array
   *   An array with two values:
   *   0. A #type 'page' render array.
   *   1. The page title.
   *
   * @throws \LogicException
   *   If the selected display variant does not implement PageVariantInterface.
   */
  public function preparePage(array $main_content, Request $request, RouteMatchInterface $route_match) {

    // If the _controller result already is #type => page,
    // we have no work to do: The "main content" already is an entire "page"
    // (see html.html.twig).
    if (isset($main_content['#type']) && $main_content['#type'] === 'page') {
      $page = $main_content;
      $title = "";
    }
    // Otherwise, render it as the main content of a #type => page, by selecting
    // page display variant to do that and building that page display variant.
    else {
      // Select the page display variant to be used to render this main content,
      // default to the built-in "simple page".
      $event = new PageDisplayVariantSelectionEvent('simple_page', $route_match);
      $this->eventDispatcher->dispatch(RenderEvents::SELECT_PAGE_DISPLAY_VARIANT, $event);
      $variant_id = $event->getPluginId();

      // We must render the main content now already, because it might provide a
      // title. We set its $is_root_call parameter to FALSE, to ensure
      // placeholders are not yet replaced. This is essentially "pre-rendering"
      // the main content, the "full rendering" will happen in
      // ::renderResponse().
      // @todo Remove this once https://www.drupal.org/node/2359901 lands.
      if (!empty($main_content)) {
        $this->renderer->executeInRenderContext(new RenderContext(), function() use (&$main_content) {
          if (isset($main_content['#cache']['keys'])) {
            // Retain #title, otherwise, dynamically generated titles would be
            // missing for controllers whose entire returned render array is
            // render cached.
            $main_content['#cache_properties'][] = '#title';
          }
          return $this->renderer->render($main_content, FALSE);
        });
        $main_content = $this->renderCache->getCacheableRenderArray($main_content) + [
            '#title' => isset($main_content['#title']) ? $main_content['#title'] : NULL
          ];
      }

      $title = "";

      // Instantiate the page display, and give it the main content.
      $page_display = $this->displayVariantManager->createInstance($variant_id);
      if (!$page_display instanceof PageVariantInterface) {
        throw new \LogicException('Cannot render the main content for this page because the provided display variant does not implement PageVariantInterface.');
      }
      $page_display
        ->setMainContent($main_content)
        ->setTitle($title)
        ->addCacheableDependency($event)
        ->setConfiguration($event->getPluginConfiguration());
      // Some display variants need to be passed an array of contexts with
      // values because they can't get all their contexts globally. For example,
      // in Page Manager, you can create a Page which has a specific static
      // context (e.g. a context that refers to the Node with nid 6), if any
      // such contexts were added to the $event, pass them to the $page_display.
      if ($page_display instanceof ContextAwareVariantInterface) {
        $page_display->setContexts($event->getContexts());
      }

      // Generate a #type => page render array using the page display variant,
      // the page display will build the content for the various page regions.
      $page = array(
        '#type' => 'page',
      );
      $page += $page_display->build();
    }

    // $page is now fully built. Find all non-empty page regions, and add a
    // theme wrapper function that allows them to be consistently themed.
    $regions = \Drupal::theme()->getActiveTheme()->getRegions();
    foreach ($regions as $region) {
      if (!empty($page[$region])) {
        $page[$region]['#theme_wrappers'][] = 'region';
        $page[$region]['#region'] = $region;
      }
    }

    // Allow hooks to add attachments to $page['#attached'].
    $this->invokePageAttachmentHooks($page);

    return $page;
  }

  protected function prepareBlocks(array $main_content, Request $request, RouteMatchInterface $route_match) {
    // TODO: remove reliance on HTMLRenderer.
    $this->page = $this->preparePage($main_content, $request, $route_match);

    // Iterate through all blocks.
    $regions = \Drupal::theme()->getActiveTheme()->getRegions();
    foreach ($regions as $region) {
      if (!empty($this->page[$region])) {
        // Non-empty region, iterate the blocks inside it.
        foreach ($this->page[$region] as $key => $child) {
          if (substr($key, 0, 1) != '#') {
            $this->blocks[$region .'/' .$key] = array (
              "id" => $region .'/' .$key,
              "render_array" => $child,
            );
          }
        }

      }
    }
    // Render each block
    foreach ($this->blocks as &$block) {
      $block['markup'] = $this->renderer->renderRoot($block['render_array']);
    }

    $this->renderer->renderRoot($this->page);
    // Save the full assets of the page.
    $this->page_attachments = $this->page['#attached'];
  }

  /**
   * {@inheritdoc}
   */
  public function renderResponse(array $main_content, Request $request, RouteMatchInterface $route_match) {
    // Process URL parameters.
    $block_requested = $request->get("block");
    $is_REST = $request->getContentType() == "json";
    // Prepare.
    $this->prepareBlocks($main_content, $request, $route_match);

    if (empty($block_requested)) {
      // List Mode
      if ($is_REST) {
        return $this->renderBlockListREST();
      } else {
        return $this->renderBlockList();
      }
    } else {
      // Render Mode
      if ($is_REST) {
        return $this->renderBlockREST($this->blocks[$block_requested]);
      } else {
        return $this->renderBlock($this->blocks[$block_requested]);
      }
    }
  }

  protected function renderBlockList() {
    //  Use a Symfony response object to have complete control over the response.
    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);

    $debug = "";
    $keys = array_keys($this->blocks);
    foreach ($keys as $key) {
      $debug = $debug. $key . '<br /> ';
    }

    $response->headers->set('Content-Type', 'text/html');
    $response->setContent($debug);
    return $response;
  }

  protected function renderBlockListREST() {
    //  Use a Symfony response object to have complete control over the response.
    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);
    $keys = array_keys($this->blocks);
    $response->headers->set('Content-Type', 'application/json');
    $response->setContent(\json_encode($keys));
    return $response;
  }

  protected function renderBlock($block_to_render) {
    if (!empty($block_to_render)) {
      $render_array = $block_to_render['render_array'];

      // Merge the assets.
      $render_array = $this->renderer->mergeBubbleableMetadata($render_array, $this->page_attachments);

      // Use a custom wrapper instead of `html` theme hook.
      $html = [
        '#type' => 'wcrhtml',
        'page' => $render_array,
        '#attached'=> $this->page_attachments,
      ];
      $html = $this->renderer->mergeBubbleableMetadata($html, $render_array["#cache"]);
      // Add url to cache context, to prevent query arguments being ignored.
      $html['#cache']['contexts'] = Cache::mergeContexts($html['#cache']['contexts'], [ "url" ]); //url.query_args

      $html ['#cache']['tags'][] = 'rendered';

      //
      $this->renderer->renderRoot($html);
      $response = new HtmlResponse($html, 200, [
        'Content-Type' => 'text/html; charset=UTF-8',
      ]);
      return $response;
    }
    else {
      $response = new Response();
      $response->setContent("Block not found.");
      $response->setStatusCode(Response::HTTP_NOT_FOUND);
      return $response;
    }
  }

  protected function renderBlockREST($block_to_render) {
    if (!empty($block_to_render)) {
      $render_array = $block_to_render['render_array'];

      // Merge the assets.
      $render_array = $this->renderer->mergeBubbleableMetadata($render_array, $this->page_attachments);

      // Use a custom wrapper instead of `html` theme hook.
      $html = [
        '#type' => 'bodyonly',
        'page' => $render_array,
      ];
      $html = $this->renderer->mergeBubbleableMetadata($html, $render_array["#cache"]);
      // Add url to cache context, to prevent query arguments being ignored.
      $html['#cache']['contexts'] = Cache::mergeContexts($html['#cache']['contexts'], [ "url" ]); //url.query_args

      $html ['#cache']['tags'][] = 'rendered';


   //   $html_head_attachments = array_intersect_key($this->page_attachments, array_flip(static::$htmlHeadAttachmentTypes));
     // if (!empty($html_head_attachments)) {
        $head = $this->renderAttachments($this->page_attachments);
      //
      $this->renderer->renderRoot($html);
      $response = new AjaxResponse([
        "content" => $html["#markup"],
        "attachments" => $head,
      ], 200, [
        'Content-Type' => 'application/json; charset=UTF-8',
        'Access-Control-Allow-Origin' => '*',
      ]);
      return $response;
    }
    else {
      $response = new Response();
      $response->setContent("Block not found.");
      $response->setStatusCode(Response::HTTP_NOT_FOUND);
      return $response;
    }
  }

  protected function renderAttachments(array $html_attachments) {
    // @See template_preprocess_html().
    $types = [
      'styles' => 'css',
      'scripts' => 'js',
      'scripts_bottom' => 'js-bottom',
      'head' => 'head',
    ];
    $placeholder_token = Crypt::randomBytesBase64(55);
    $result = [];
    foreach ($types as $type => $placeholder_name) {
      $placeholder = '<' . $placeholder_name . '-placeholder token="' . $placeholder_token . '">';
      $html_attachments['html_response_attachment_placeholders'][$type] = $placeholder;
      $response = new HtmlResponse();
      $response->setContent($placeholder);
      $response->setAttachments($html_attachments);
      $response = $this->htmlResponseAttachmentsProcessor->processAttachments($response);
      $result[$type] = $response->getContent();
    }
    return $result;
  }
}
