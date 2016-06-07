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
  private $treeNodes;
  private $treeChildren;
  private $treeParent;
  private $treeCount;
  private $treeNow;

  public function __construct() {
    $this->count = 0;
    $this->stack = array();


    $this->treeNodes = array();
    $this->treeParent = array();
    $this->treeChildren = array();
    $this->treeCount = 0;
    $this->treeNow = -1;
  }

  public function append($element) {
    $this->count ++;
    $this->stack[] = $element;

    $this->treeCount++;
    $this->treeNodes[$this->treeCount-1]=$element;
    $this->treeParent[$this->treeCount-1] = $this->treeNow;
    if ( $this->treeNow != -1) {
      if (!isset($this->treeChildren[$this->treeNow])){
        $this->treeChildren[$this->treeNow]=array();
      }
      $this->treeChildren[$this->treeNow][] = $this->treeCount-1;
    }
    $this->treeNow=$this->treeCount-1;
  }

  public function pop() {
    array_pop($this->stack);
    $this->count --;

    $this->treeNow = $this->treeParent[$this->treeNow];
  }

  public function getLast() {
    return $this->stack[$this->count-1];
  }

  public function getCount() {
    return $this->count;
  }

  public function printStack() {
    $str = "";
    foreach ($this->stack as $item) {
      $str = $str . '\n' . $item['func'];
    }
    return $str;
  }


  public function printTree($treeNodeID) {
    $currentNode = $this->treeNodes[$treeNodeID];
    $currentNode['children']=[];
    foreach ($this->treeChildren[$treeNodeID] as $child) {
      $currentNode['children'][] = $this->printTree($child);
    }
    return $currentNode;
  }

  public function getTreeCount()
  {
    return $this->treeCount;
  }
}