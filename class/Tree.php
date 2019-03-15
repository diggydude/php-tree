<?php

  require_once(__DIR__ . '/Node.php');

  class Tree
  {

    const OP_EQUAL            = "equ";
    const OP_NOT_EQUAL        = "neq";
    const OP_GREATER_THAN     = "grt";
    const OP_GREATER_OR_EQUAL = "gte";
    const OP_LESS_THAN        = "let";
    const OP_LESS_OR_EQUAL    = "lte";
    const OP_BETWEEN          = "btw";
    const OP_IN_SET           = "ins";
    const OP_STARTS_WITH      = "stw";
    const OP_ENDS_WITH        = "enw";
    const OP_CONTAINS_SUBSTR  = "css";
    const OP_MATCHES_REGEX    = "mrx";

    protected

      $root,
      $nodes;

    /*

      The $store parameter is a two-dimensional array. Each
      sub-array should contain an "id" element and a "parentId"
      element to build the Tree correctly. Each sub-array may
      contain any other arbitrary data. An ideal $store would
      be a database query resultset having key column names
      aliased to "id" and "parentId".

      Alternately, you can call the importStore() method to populate
      the Tree using stand-in fields for "id" and "parentId".

    */

    public function __construct($store = array())
    {
      $this->clear();
      if (is_string($store)) {
        $store = unserialize($store);
      }
      if (!is_array($store)) {
        $store = array();
      }
      usort($store, function($a, $b) {return ($a['id'] == $b['id']) ? 0 : (($a['id'] < $b['id']) ? -1 : 1);});
      foreach ($store as $value) {
        if (!array_key_exists($value['id'], $this->nodes)) {
          $node = $this->createNode($value);
          $this->nodes[$node->nodeId] = $node;
          $this->nodes[$node->parentId]->appendChild($node);
        }
      }
    } // __construct

    public static function fromString($serialized)
    {
      $store = unserialize($serialized);
      return new Tree($store);
    } // fromString

    public static function load($filename)
    {
      $data = file_get_contents($filename);
      return self::fromString($data);
    } // load

    public function saveAs($filename)
    {
      file_put_contents($filename, (string) $this);
    } // saveAs

    /*

      importStore() populates a Tree from a store that doesn't have
      "id" and "parentId" fields. The specified fields are used
      instead of "id" and "parentId".

      You can combine multiple stores into one Tree by repeatedly
      calling importStore() with different store arguments. To empty
      the Tree for reuse, call clear() between imports or other
      modifications.

    */

    public function importStore($idField, $parentIdField, $store)
    {
      foreach ($store as &$value) {
        if (is_object($value)) {
          $value = get_object_vars($value);
        }
        if ($idField != "id") {
          $value['id'] = $value[$idField];
        }
        if ($parentIdField != "parentId") {
          $value['parentId'] = $value[$parentIdField];
        }
      }
      usort($store, function($a, $b) {return ($a['id'] == $b['id']) ? 0 : (($a['id'] < $b['id']) ? -1 : 1);});
      foreach ($store as $value) {
        if (!array_key_exists($value['id'], $this->nodes)) {
          $node = $this->createNode($value);
          $this->nodes[$node->nodeId] = $node;
          $this->nodes[$node->parentId]->appendChild($node);
        }
      }
    } // importStore

    public function clear()
    {
      $this->nodes    = array();
      $this->root     = $this->createNode(
                          (object) array(
                            'value'    => null,
                            'id'       => 0,
                            'parentId' => null
                          )
                        );
      $this->nodes[0] = $this->root;
    } // clear

    public function createNode($value)
    {
      $node = new Node($value, $this);
      $this->nodes[$node->nodeId] = $node;
      return $node;
    } // createNode

    public function getNodeById($nodeId)
    {
      return (array_key_exists($nodeId, $this->nodes)) ? $this->nodes[$nodeId] : null;
    } // getNodeById

    public function deleteNode($nodeId)
    {
      if (array_key_exists($nodeId, $this->nodes)) {
        unset($this->nodes[$nodeId]);
      }
    } // deleteNode

    public function getChild($index)
    {
      return $this->root->getChild($index);
    } // getChild

    public function appendChild(Node $node)
    {
      $this->root->appendChild($node);
    } // appendChild

    public function removeChild($index)
    {
      return $this->root->removeChild($index);
    } // removeChild

    /*

      search() combines the functionality of find() and graph()
      to render search results as a nested HTML unordered list.
      The graph contains all Nodes in the ancestry paths from the
      root Node to each matching Node. (See Node::getLimb())

    */

    public function search($field, $operator, $value, $template)
    {
      $results = $this->find($field, $operator, $value);
      $store   = array();
      foreach ($results as $node) {
        $store[$node->nodeId] = get_object_vars($node->value);
        $ancestors = $node->getAncestors();
        foreach ($ancestors as $ancestor) {
          if (!array_key_exists($ancestor->nodeId, $store)) {
            $store[$ancestor->nodeId] = get_object_vars($ancestor->value);
          }
        }
      }
      $view = new Tree($store);
      return $view->graph($template);
    } // search

    /*

      find() returns an array of all Nodes in the Tree
      matching the search criteria.

    */

    public function find($field, $operator, $value)
    {
      $results = array();
      switch ($operator) {
        case self::OP_EQUAL:
          foreach ($this->nodes as $node) {
            if (property_exists($node->value, $field)) {
              if ($node->value->$field == $value) {
                $results[] = $node;
              }
            }
          }
          break;
        case self::OP_NOT_EQUAL:
          foreach ($this->nodes as $node) {
            if (property_exists($node->value, $field)) {
              if ($node->value->$field != $value) {
                $results[] = $node;
              }
            }
          }
          break;
        case self::OP_GREATER_THAN:
          foreach ($this->nodes as $node) {
            if (property_exists($node->value, $field)) {
              if ($node->value->$field > $value) {
                $results[] = $node;
              }
            }
          }
          break;
        case self::OP_GREATER_OR_EQUAL:
          foreach ($this->nodes as $node) {
            if (property_exists($node->value, $field)) {
              if ($node->value->$field >= $value) {
                $results[] = $node;
              }
            }
          }
          break;
        case self::OP_LESS_THAN:
          foreach ($this->nodes as $node) {
            if (property_exists($node->value, $field)) {
              if ($node->value->$field < $value) {
                $results[] = $node;
              }
            }
          }
          break;
        case self::OP_LESS_OR_EQUAL:
          foreach ($this->nodes as $node) {
            if (property_exists($node->value, $field)) {
              if ($node->value->$field <= $value) {
                $results[] = $node;
              }
            }
          }
          break;
        case self::OP_BETWEEN:
          foreach ($this->nodes as $node) {
            if (property_exists($node->value, $field)) {
              if (($node->value->$field >= $value[0]) && ($node->value->$field <= $value[1])) {
                $results[] = $node;
              }
            }
          }
          break;
        case self::OP_IN_SET:
          foreach ($this->nodes as $node) {
            if (property_exists($node->value, $field)) {
              if (in_array($node->value->$field, $value)) {
                $results[] = $node;
              }
            }
          }
          break;
        case self::OP_STARTS_WITH:
          foreach ($this->nodes as $node) {
            if (property_exists($node->value, $field)) {
              if (stripos($node->value->$field, $value) === 0) {
                $results[] = $node;
              }
            }
          }
          break;
        case self::OP_ENDS_WITH:
          foreach ($this->nodes as $node) {
            if (property_exists($node->value, $field)) {
              if (strripos($node->value->$field, $value) == (strlen($node->value->$field) - strlen($value))) {
                $results[] = $node;
              }
            }
          }
          break;
        case self::OP_CONTAINS_SUBSTR:
          foreach ($this->nodes as $node) {
            if (property_exists($node->value, $field)) {
              if (stripos($node->value->$field, $value) !== false) {
                $results[] = $node;
              }
            }
          }
          break;
        case self::OP_MATCHES_REGEX:
          foreach ($this->nodes as $node) {
            if (property_exists($node->value, $field)) {
              $regex = "%" . str_replace("%", "\\%", $value) . "%";
              if (preg_match($regex, $node->value->$field)) {
                $results[] = $node;
              }
            }
          }
          break;
        default:
          throw new Exception(__METHOD__ . ' > Unsupported search operator.');
      }
      return $results;
    } // find

    /*

      graph() renders the Tree as a nested HTML unordered list using
      the supplied ASP-style template to display properties of each
      Node. Each list item element has the CSS class "tree-node" for
      styling.

      This method may be called on the Trees returned by
      Node::getBranch(), Node::getLimb(), and Node::getStem() to
      render various types of subtrees.

    */

    public function graph($template)
    {
      $html = "<ul>";
      foreach ($this->root->childNodes as $node) {
        $html .= self::_graphSubtree($node, $template);
      }
      $html .= "</ul>";
      return $html;
    } // graph

    /*

      _graphSubtree() and _renderHtml() are static helper functions called
      by graph() to render the HTML list. User code needn't call them
      directly.

    */

    protected static function _graphSubtree($root, $template)
    {
      $html = "<li class=\"tree-node\">" . self::_renderHtml($root, $template);
      if (count($root->childNodes) == 0) {
        $html .= "</li>";
        return $html;
      }
      $html .= "<ul>";
      foreach ($root->childNodes as $node) {
        $html .= self::_graphSubtree($node, $template);
      }
      $html .= "</ul></li>";
      return $html;
    } // _graphSubtree

    protected static function _renderHtml($node, $template)
    {
      preg_match_all('/<%((?!%>).*?)%>/', $template, $matches);
      $tags  = $matches[0];
      $props = $matches[1];
      $html  = $template;
      for ($i = 0; $i < count($props); $i++) {
        $prop = $props[$i];
        if (property_exists($node->value, $prop)) {
          $html = str_replace($tags[$i], $node->value->$prop, $html);
        }
      }
      return $html;
    } // _renderHtml

    public function toArray()
    {
      $values = array();
      foreach ($this->nodes as $node) {
        if ($node->nodeId === 0) {
          continue;
        }
        $values[] = get_object_vars($node->value);
      }
      return $values;
    } // toArray

    public function __toString()
    {
      $values = $this->toArray();
      return serialize($values);
    } // __toString

    public function __get($prop)
    {
      switch ($prop) {
        case "tree":
          return $this;
        case "value":
          return $this->toArray();
        case "nodeId":
        case "parentId":
        case "childNodes":
          return $this->root->$prop;
        default:
          return (property_exists($this, $prop)) ? $this->$prop : null;
      }
    } // __get

  } // Tree

?>