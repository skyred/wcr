<?php

/**
 * @file
 * Contains \Drupal\wcr\Render\MainContent\BlockRenderer.
 */

namespace Drupal\wcr\Render\MainContent;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;

use Drupal\wcr\Plugin\RenderArrayFormatterManager;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\MainContent\MainContentRendererInterface;


use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Cache\Cache;

use Drupal\wcr\Service\Utilities;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * Default main content renderer for page partials.
 */
class BlockRenderer implements MainContentRendererInterface {

  protected $pluginManager;

  protected $urlParameterPrefix = '_wcr_';

  /**
   * WebComponentRenderer constructor.
   *
   * @param \Drupal\wcr\Plugin\RenderArrayFormatterManager $render_array_formatter_manager
   */
  public function __construct(RenderArrayFormatterManager $render_array_formatter_manager) {
    $this->pluginManager = $render_array_formatter_manager;

  }

  /**
   * {@inheritdoc}
   */
  public function renderResponse(array $main_content, Request $request, RouteMatchInterface $route_match) {
    // Process URL parameters.
    $mode = $request->get($this->urlParameterPrefix . "mode");

    $plugins = $this->pluginManager->getDefinitions();

    if (!isset($plugins[$mode])) {
      // Plugin not found.
      throw new PluginNotFoundException($mode, sprintf('The "%s" format does not exist.', $mode));
    }
    $instance = $this->pluginManager->createInstance($mode);
    // @todo Generate title.
    $response = $instance->generateResponse($main_content, [
                                            'request' => $request,
                                            'route_match' => $route_match,
                                            'title' => '']);

    return $response;
  }



}
