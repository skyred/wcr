<?php
/**
 * @file
 * Contains Drupal/wcr/CallStack.
 *
 * This class, when used as a service, serves as a tracker of the call stack
 * in the rendering process.
 *
 * Currently, every call to the doRender() function will results in a record
 * in this "CallStack". Other functions could be added later.
 *
 * The class also tracks the relationship between each function call by
 * building a "tree" structure of function calls.
 */

namespace Drupal\wcr;


class CallStack {
  /**
   * Variables for the stack
   */
  private $count;
  private $stack;


  /**
   * Variables for the tree structure (to be converted to OOP)
   */
  private $treeNodes;
  private $treeChildren;
  private $treeParent;
  private $treeCount;
  private $treeNow;

  /**
   * CallStack constructor.
   */
  public function __construct() {
    $this->count = 0;
    $this->stack = array();

    $this->treeNodes = array();
    $this->treeParent = array();
    $this->treeChildren = array();
    $this->treeCount = 0;
    $this->treeNow = -1;
  }

  /**
   * Append a new record in the callstack and the tree.
   * @param $element
   */
  public function append($element) {
    $this->count ++;
    $this->stack[] = $element;

    // Add this element in the tree
    $this->treeCount++;
    $this->treeNodes[$this->treeCount - 1] = $element;
    $this->treeParent[$this->treeCount - 1] = $this->treeNow;
    if ( $this->treeNow != -1) {
      if (!isset($this->treeChildren[$this->treeNow])){
        $this->treeChildren[$this->treeNow] = array();
      }
      $this->treeChildren[$this->treeNow][] = $this->treeCount - 1;
    }
    // Move tree pointer to current function call (tracking relationship)
    $this->treeNow = $this->treeCount - 1;
  }

  public function pop() {
    // Pop out the top item in the stack
    array_pop($this->stack);
    $this->count --;

    // Restore tree pointer to its parent node
    $this->treeNow = $this->treeParent[$this->treeNow];
  }

  public function getLast() {
    return $this->stack[$this->count - 1];
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

  /**
   * Print a tree structure.
   *
   * @param $treeNodeID
   * @return array
   */
  public function printTree($treeNodeID) {
    $currentNode = $this->treeNodes[$treeNodeID];
    $currentNode['children'] = [];
    foreach ($this->treeChildren[$treeNodeID] as $child) {
      $currentNode['children'][] = $this->printTree($child);
    }
    return $currentNode;
  }

  /**
   * Get the total number of tree nodes.
   *
   * @return int
   */
  public function getTreeCount()
  {
    return $this->treeCount;
  }
}
