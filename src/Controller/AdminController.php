<?php

namespace Drupal\wcr\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\wcr\Plugin\RenderArrayFormatterManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Admin page controller
 */
class AdminController extends ControllerBase {


  protected $pluginManager;

  public function __construct() {
    $this->pluginManager = \Drupal::service('plugin.manager.views.render_array_formatter');
  }

  public function pluginList() {

    $plugins = $this->pluginManager->getDefinitions();

    $header = array(
      'Name',
      'ID',
      'Description',
      'Provider module',
    );
    $rows = [];

    foreach ($plugins as $item) {

      $rows[] = array(
        'data' => array(
          // Cells.
          $item['name'],
          $item['id'],
          $item['description'],
          $item['provider'],
        ),
      );
    }

    $build['plugins_table'] = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => array('id' => 'admin-wcr-plugins', 'class' => array('admin-wcr-plugins')),
      '#empty' => $this->t('No plugins available.'),
    );

    return $build;

  }


}
