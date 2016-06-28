<?php

/**
 * @file
 * Contains \Drupal\wcr\Render\MainContent\ComponentsPageRenderer.
 */

namespace Drupal\wcr\Render\MainContent;

use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\wcr\BlockList;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Display\ContextAwareVariantInterface;
use Drupal\Core\Display\PageVariantInterface;
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
 */
class ComponentsPageRenderer implements MainContentRendererInterface {

  protected $blocks;
  protected $pageAttachments;
  protected $htmlRenderer;
  protected $page;
  protected $renderer;
  protected $displayVariantManager;
  protected $eventDispatcher;
  protected $renderCache;
  protected $elementName;
  protected $htmlResponseAttachmentsProcessor;
  protected $titleResolver;
  /**
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  private $title_resolver;

  /**
   * ComponentsPageRenderer constructor.
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   * @param \Drupal\Core\Render\MainContent\MainContentRendererInterface $html_renderer
   * @param \Drupal\Component\Plugin\PluginManagerInterface $display_variant_manager
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   * @param \Drupal\Core\Render\RenderCacheInterface $render_cache
   * @param \Drupal\Core\Render\AttachmentsResponseProcessorInterface $html_response_attachments_processor
   */
  public function __construct(TitleResolverInterface $title_resolver,
                              MainContentRendererInterface $html_renderer,
                              PluginManagerInterface $display_variant_manager,
                              EventDispatcherInterface $event_dispatcher,
                              RenderCacheInterface $render_cache,
                              AttachmentsResponseProcessorInterface $html_response_attachments_processor) {
    $this->renderer = \Drupal::service('renderer');
    $this->htmlRenderer = $html_renderer;
    $this->elementName = '';
    $this->displayVariantManager = $display_variant_manager;
    $this->eventDispatcher = $event_dispatcher;
    $this->renderCache = $render_cache;
    $this->htmlResponseAttachmentsProcessor = $html_response_attachments_processor;
    $this->blocks = [];
    $this->titleResolver = $title_resolver;
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
    // Determine the title: use the title provided by the main content if any,
    // otherwise get it from the routing information.
    $get_title = function (array $main_content) use ($request, $route_match) {
      return isset($main_content['#title']) ? $main_content['#title'] : $this->titleResolver->getTitle($request, $route_match->getRouteObject());
    };
    // If the _controller result already is #type => page,
    // we have no work to do: The "main content" already is an entire "page"
    // (see html.html.twig).
    if (isset($main_content['#type']) && $main_content['#type'] === 'page') {
      $page = $main_content;
      $title = $get_title($page);
    }
    // Otherwise, render it as the main content of a #type => page, by selecting
    // page display variant to do that and building that page display variant.
    else {

      $variant_id = 'components_display';

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

      $title = $get_title($main_content);

      // Instantiate the page display, and give it the main content.
      $page_display = $this->displayVariantManager->createInstance($variant_id);
      if (!$page_display instanceof PageVariantInterface) {
        throw new \LogicException('Cannot render the main content for this page because the provided display variant does not implement PageVariantInterface.');
      }
      $page_display
        ->setMainContent($main_content)
        ->setTitle($title);
      // Some display variants need to be passed an array of contexts with
      // values because they can't get all their contexts globally. For example,
      // in Page Manager, you can create a Page which has a specific static
      // context (e.g. a context that refers to the Node with nid 6), if any
      // such contexts were added to the $event, pass them to the $page_display.
      if ($page_display instanceof ContextAwareVariantInterface) {
     //   $page_display->setContexts($event->getContexts());
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
    $this->htmlRenderer->invokePageAttachmentHooks($page);

    return [$page, $title];
  }


  /**
   * {@inheritdoc}
   */
  public function renderResponse(array $main_content, Request $request, RouteMatchInterface $route_match) {
    // Process URL parameters.

    list($this->page, $title) = $this->preparePage($main_content, $request, $route_match);


    $blockList = new BlockList($this->page);
    $blockList->setTitle($title);
    $blockList->setJsAssets(array_merge($this->page['#attached']['scripts'], $this->page['#attached']['scripts_bottom']));

    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);
    $response->headers->set('Content-Type', 'application/json');
    $response->setContent($blockList->toJson());
    return $response;

  }
  
}
