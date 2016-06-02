<?php

/**
 * @file
 * Contains \Drupal\wcr\Render\MainContent\WebComponentRenderer.
 */

namespace Drupal\wcr\Render\MainContent;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Render\AttachmentsResponseProcessorInterface;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\MainContent\MainContentRendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * Default main content renderer for Web Component (HTML Import) requests.
 */
class WebComponentRenderer implements MainContentRendererInterface {


  /**
   * The HTML response attachments processor service.
   *
   * @var \Drupal\Core\Render\AttachmentsResponseProcessorInterface
   */
  protected $htmlRenderer;

  /**
   * WebComponentRenderer constructor.
   * @param MainContentRendererInterface $html_renderer
     */
  public function __construct(MainContentRendererInterface $html_renderer) {
    $this->htmlRenderer = $html_renderer;
    $this->renderer = \Drupal::service('recursive_renderer');
  }


  /**
   * {@inheritdoc}
   */
  public function renderResponse(array $main_content, Request $request, RouteMatchInterface $route_match) {
/*    if (!$this->validatePreconditions($request)) {
      throw new PreconditionFailedHttpException();
    }*/

    list($page, $title) = $this->htmlRenderer->prepare($main_content, $request, $route_match);

    //  a Symfony response object

    $partials_required= $request->get("templates");

    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);

    \kint($this->renderer->renderRoot($page['content']['polymer_page_title']));

    \kint(BubbleableMetadata::createFromRenderArray($page['content']['polymer_page_title']));

    $response->headers->set('Content-Type', 'text/html');

    $response->setContent("OK");

  /*  $regions = \Drupal::theme()->getActiveTheme()->getRegions();
    // Start with page-level HTML <head> attachments and cacheability.
    $metadata = BubbleableMetadata::createFromRenderArray($page);
    foreach ($regions as $region) {
      if (!empty($page[$region])) {
        // @todo Future improvement: only render a region if it is actually
        // going to change. This would yield an even bigger benefit. The benefit
        // today is less data on the wire and particularly fewer things to
        // render in the browser. But we still render everything on the server.
        // This is sufficient for a prototype, but that would yield even better
        // performance.
        $this->renderer->renderRoot($page[$region]);
        $region_metadata = BubbleableMetadata::createFromRenderArray($page[$region]);
        if ($this->refreshlessPageState->hasChanged($region_metadata, $request)) {
          $response->addCommand(new RefreshlessUpdateRegionCommand($region, \Drupal::service('render_cache')->getCacheableRenderArray($page[$region])));
        }

        $metadata = $metadata->merge($region_metadata);
      }
    }

    // Collect all attachments that affect the HTML <head>, render those into
    // HTML and send the appropriate AJAX command. (Note that we do this for
    // all content, including unchanged regions, because we don't know where
    // each tag in the requesting page's <head> bubbled up from, i.e. from which
    // region.)
    $html_head_attachments = array_intersect_key($metadata->getAttachments(), array_flip(static::$htmlHeadAttachmentTypes));
    if (!empty($html_head_attachments)) {
      $response->addCommand(new RefreshlessUpdateHtmlHeadCommand($this->renderTitle($title), $this->renderHtmlHead($html_head_attachments)));
    }

    // Send updated Refreshless page state.
    $response->addAttachments(['drupalSettings' => ['refreshlessPageState' => $this->refreshlessPageState->build($metadata)]]);*/

    return $response;
  }



}
