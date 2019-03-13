<?php

  require_once(__DIR__ . '/../func/randomString.php');

  class Node
  {

    protected

      $tree,
      $nodeId,
      $parentId,
      $value,
      $childNodes;

    /*

      Don't call the Node constructor directly. Instead,
      call Tree::createNode() for correct operation.

    */

    public function __construct($value, $tree)
    {
      if (is_array($value)) {
        $value = (object) $value;
      }
      $this->tree       = $tree;
      $this->nodeId     = null;
      $this->parentId   = 0;
      $this->value      = $value;
      $this->childNodes = array();
      if (property_exists($value, 'id')) {
        $this->nodeId = $value->id;
      }
      if (property_exists($value, 'parentId')) {
        $this->parentId = $value->parentId;
      }
      if ($this->nodeId === null) {
        $this->nodeId = randomString(32);
      }
    } // __construct

    public function setValue($value)
    {
      $oldValue    = $this->value;
      $this->value = $value;
      return $oldValue;
    } // setValue

    public function getParent()
    {
      return $this->tree->getNodeById($this->parentId);
    } // getParent

    public function getChild($index)
    {
      return ($index < count($this->childNodes)) ? $this->childNodes[$index] : null;
    } // getChild

    public function appendChild(Node $node)
    {
      $node->parentId = $this->nodeId;
      $this->childNodes[] = $node;
    } // appendChild

    public function removeChild($index)
    {
      $node = $this->getChild($index);
      if ($node !== null) {
        unset($this->childNodes[$index]);
        $this->childNodes = array_values($this->childNodes);
      }
      return $node;
    } // removeChild

    public function getAncestors()
    {
      $results = array();
      if ($this->parentId === 0) {
        return $results;
      }
      $parentNode = $this->getParent();
      $results[] = $parentNode;
      $results = array_merge($results, $parentNode->getAncestors());
      return $results;
    } // getAncestors

    public function getDescendants()
    {
      $results = array();
      if (count($this->childNodes) == 0) {
        return $results;
      }
      $children = $this->childNodes;
      $results  = $children;
      foreach ($children as $child) {
        $results = array_merge($results, $child->getDescendants());
      }
      return $results;
    } // getDescendants

    /*

      getBranch() returns a Tree having this Node's topmost ancestor
      as its sole child and containing all of that top-level Node's
      descendants.

    */

    public function getBranch()
    {
      $nodes = $this->getAncestors();
      $nodes = array($nodes[count($nodes) - 1]);
      $nodes = array_merge($nodes, $nodes[count($nodes) - 1]->getDescendants());
      $store = array();
      foreach ($nodes as $node) {
        $store[] = get_object_vars($node->value);
      }
      return new Tree($store);
    } // getBranch

    /*

      getLimb() returns a Tree having this Node's topmost ancestor as
      its sole child. It contains a single Node at each level,
      descending from the top-level ancestor through all of this Node's
      ancestors to this Node.

    */

    public function getLimb()
    {
      $nodes = $this->getAncestors();
      $store = array();
      foreach ($nodes as $node) {
        $store[] = get_object_vars($node->value);
      }
      $store[] = get_object_vars($this->value);
      return new Tree($store);
    } // getLimb

    /*

      getStem() return a Tree having this Node as its sole child
      and containing all of this Node's descendants.

    */

    public function getStem()
    {
      $nodes = $this->getDescendants();
      $store = array();
      foreach ($nodes as $node) {
        $store[] = get_object_vars($node->value);
      }
      $data = get_object_vars($this->value);
      $data['parentId'] = 0;
      $store[] = $data;
      $stem = new Tree($store);
      $data['parentId'] = $this->parentId;
      $stem->getNodeById($this->nodeId)->setValue((object) $data);
      $stem->getNodeById($this->nodeId)->parentId = $this->parentId;
      return $stem;
    } // getStem

    public function __get($prop)
    {
      return (property_exists($this, $prop)) ? $this->$prop : null;
    } // __get

  } // Node

?>