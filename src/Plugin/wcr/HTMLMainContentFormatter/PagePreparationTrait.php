<?php

namespace Drupal\wcr\Plugin\wcr\HTMLMainContentFormatter;

use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\PageDisplayVariantSelectionEvent;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RenderEvents;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Display\ContextAwareVariantInterface;
use Drupal\Core\Display\PageVariantInterface;
use Drupal\Component\Utility\Crypt;

trait PagePreparationTrait {

  protected $page;
  protected $pageAttachments;
  protected $eventDispatcher;
  protected $titleResolver;
  protected $htmlRenderer;
  protected $displayVariantManager;
  protected $renderCache;
  protected $renderer;
  protected $htmlResponseAttachmentsProcessor;

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
      return isset($main_content['#title']) ? $main_content['#title'] : $this->getTitleResolver()->getTitle($request, $route_match->getRouteObject());
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
      // Select the page display variant to be used to render this main content,
      // default to the built-in "simple page".
      $event = new PageDisplayVariantSelectionEvent('block_page', $route_match);
      $this->getEventDispatcher()->dispatch(RenderEvents::SELECT_PAGE_DISPLAY_VARIANT, $event);
      $variant_id = $event->getPluginId();

      // We must render the main content now already, because it might provide a
      // title. We set its $is_root_call parameter to FALSE, to ensure
      // placeholders are not yet replaced. This is essentially "pre-rendering"
      // the main content, the "full rendering" will happen in
      // ::renderResponse().
      // @todo Remove this once https://www.drupal.org/node/2359901 lands.
      if (!empty($main_content)) {
        $this->getRenderer()->executeInRenderContext(new RenderContext(), function() use (&$main_content) {
          if (isset($main_content['#cache']['keys'])) {
            // Retain #title, otherwise, dynamically generated titles would be
            // missing for controllers whose entire returned render array is
            // render cached.
            $main_content['#cache_properties'][] = '#title';
          }
          return $this->getRenderer()->render($main_content, FALSE);
        });
        $main_content = $this->getRenderCache()->getCacheableRenderArray($main_content) + [
            '#title' => isset($main_content['#title']) ? $main_content['#title'] : NULL
          ];
      }

      $title = $get_title($main_content);

      // Instantiate the page display, and give it the main content.
      $page_display = $this->getDisplayVariantManager()->createInstance($variant_id);
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
    $this->getHtmlRenderer()->invokePageAttachmentHooks($page);

    return $page;
  }

  /**
   * Process the attachments for a render array.
   *
   * @param array $html_attachments
   * @return array
   */
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
      $response = $this->getHtmlResponseAttachmentsProcessor()->processAttachments($response);
      $result[$type] = $response->getContent();
    }
    return $result;
  }

  protected function getDisplayVariantManager() {
    if (!isset($this->displayVariantManager)) {
      $this->displayVariantManager = \Drupal::service("plugin.manager.display_variant");
    }
    return $this->displayVariantManager;
  }

  protected function getHtmlRenderer() {
    if (!isset($this->htmlRenderer)) {
      $this->htmlRenderer = \Drupal::service("main_content_renderer.html");
    }
    return $this->htmlRenderer;
  }

  protected function getTitleResolver() {
    if (!isset($this->titleResolver)) {
      $this->titleResolver = \Drupal::service("title_resolver");
    }
    return $this->titleResolver;
  }

  protected function getEventDispatcher() {
    if (!isset($this->eventDispatcher)) {
      $this->eventDispatcher = \Drupal::service("event_dispatcher");
    }
    return $this->eventDispatcher;
  }

  protected function getRenderCache() {
    if (!isset($this->renderCache)) {
      $this->renderCache = \Drupal::service("render_cache");
    }
    return $this->renderCache;
  }

  protected function getRenderer() {
    if (!isset($this->renderer)) {
      $this->renderer = \Drupal::service("renderer");
    }
    return $this->renderer;
  }

  protected function getHtmlResponseAttachmentsProcessor() {
    if (!isset($this->htmlResponseAttachmentsProcessor)) {
      $this->htmlResponseAttachmentsProcessor = \Drupal::service("html_response.attachments_processor");
    }
    return $this->htmlResponseAttachmentsProcessor;
  }


}