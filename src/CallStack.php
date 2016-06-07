<?php
/**
 * Created by PhpStorm.
 * User: ztl8702
 * Date: 7/06/16
 * Time: 12:35 PM
 */

namespace Drupal\wcr;


class CallStack {
  private $count;
  private $stack;


  public function __construct() {
    $this->count = 0;
    $this->stack = array();

  }

  public function append($element) {
    $this->count ++;
    $this->stack[]=$element;
  }

  public function pop() {
    array_pop($this->stack);
    $this->count --;
  }

  public function getLast() {
    return $this->stack[$this->count-1];
  }

  public function getCount() {
    return $this->count;
  }
}