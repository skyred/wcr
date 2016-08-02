<?php

namespace Drupal\wcr\Tests;

use Drupal\simpletest\WebTestBase;


class WcrTestBase extends WebTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('block', 'node', 'ctools', 'wcr');

  protected $user;

  public function setUp() {
    parent::setUp();
    $this->user = $this->drupalCreateUser(array('access content'));
  }

}