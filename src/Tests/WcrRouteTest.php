<?php

namespace Drupal\wcr\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * @group wcr
 */
class WcrRouteTest extends WebTestBase {
  private $user;

  public function setUp() {
    parent::setUp();
    $this->user = $this->drupalCreateUser(array('access content'));
    printf("debug %s",  print_r($this->user, true));
  }

  public function testFrontpageBlockList() {
    //$this->user = $this->drupalCreateUser(array('administer content'));
    //$this->drupalLogin($this->user);
    $out = $this->drupalGet('', [
                                 'query' => [
                                    '_wrapper_format' =>'drupal_wcr',
                                    '_wcr_mode' => 'list',
                                 ],
                                ]);

    printf("%s", $out);

    $this->assertResponse(200);

  }
}