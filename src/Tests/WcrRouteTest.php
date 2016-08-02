<?php

namespace Drupal\wcr\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * @group wcr
 */
class WcrRouteTest extends WcrTestBase  {

  public function testFrontpageBlockList() {
    $this->drupalLogin($this->user);
    $this->drupalGet('', [
                                 'query' => [
                                    '_wrapper_format' =>'drupal_wcr',
                                    '_wcr_mode' => 'list',
                                 ],
                                ]);
    $this->assertResponse(200);
   // $this->assertRaw('<table>', 'A table is present in the output.');


  }
}