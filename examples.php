<?php

  ini_set('display_errors', 1);
  require_once(__DIR__ . '/class/Tree.php');

  // Create a data store
  $store = array(
             array(
               'id'       => 1,
               'parentId' => 0,
               'uri'      => '#',
               'text'     => 'HTML Tutorials'
             ),
             array(
               'id'       => 2,
               'parentId' => 1,
               'uri'      => 'https://html.com/',
               'text'     => 'HTML.com: Study HTML and Learn to Code With Our Step-By-Step Guide'
             ),
             array(
               'id'       => 3,
               'parentId' => 1,
               'uri'      => 'https://www.w3schools.com/html/',
               'text'     => 'HTML5 Tutorial'
             ),
             array(
               'id'       => 4,
               'parentId' => 1,
               'uri'      => 'https://www.htmldog.com/guides/html/beginner/',
               'text'     => 'HTML Beginner Tutorial'
             ),
             array(
               'id'       => 5,
               'parentId' => 0,
               'uri'      => '#',
               'text'     => 'CSS Tutorials'
             ),
             array(
               'id'       => 6,
               'parentId' => 5,
               'uri'      => 'https://www.w3schools.com/Css/',
               'text'     => 'CSS Tutorial'
             ),
             array(
               'id'       => 7,
               'parentId' => 5,
               'uri'      => 'https://www.tutorialspoint.com/css/css3_tutorial.htm',
               'text'     => 'CSS3 - Tutorial'
             ),
             array(
               'id'       => 8,
               'parentId' => 5,
               'uri'      => 'https://www.lynda.com/CSS-training-tutorials/447-0.html',
               'text'     => 'CSS Training and Tutorials'
             ),
             array(
               'id'       => 9,
               'parentId' => 0,
               'uri'      => '#',
               'text'     => 'JavaScript Tutorials'
             ),
             array(
               'id'       => 10,
               'parentId' => 9,
               'uri'      => 'https://javascript.info/',
               'text'     => 'The Modern Javascript Tutorial'
             ),
             array(
               'id'       => 11,
               'parentId' => 9,
               'uri'      => 'https://www.codecademy.com/learn/introduction-to-javascript',
               'text'     => 'Introduction To JavaScript'
             ),
             array(
               'id'       => 12,
               'parentId' => 9,
               'uri'      => 'https://www.javascript.com/try',
               'text'     => 'Start learning JavaScript with our free real time tutorial'
             ),
             array(
               'id'       => 13,
               'parentId' => 0,
               'uri'      => '#',
               'text'     => 'PHP Tutorials'
             ),
             array(
               'id'       => 14,
               'parentId' => 13,
               'uri'      => 'https://www.tutorialrepublic.com/php-tutorial/',
               'text'     => 'PHP Tutorial - An Ultimate Guide for Beginners'
             ),
             array(
               'id'       => 15,
               'parentId' => 13,
               'uri'      => 'https://www.guru99.com/php-tutorials.html',
               'text'     => 'PHP Tutorial for Beginners: Learn in 7 Days'
             ),
             array(
               'id'       => 16,
               'parentId' => 13,
               'uri'      => 'https://www.sololearn.com/Course/PHP/',
               'text'     => 'PHP Tutorial | SoloLearn: Learn to code for FREE!'
             )
          );

  // Create new Tree from store data
  $tree = new Tree($store);

  // Save the Tree data in a file
  $tree->saveAs('./links.dat');

  // Create a new Tree from the saved file
  unset($tree);
  $tree = Tree::load('./links.dat');
  $template = "<a href=\"<%uri%>\" target=\"_blank\"><%text%></a>";
  $view1 = $tree->graph($template);

  // Find all Nodes whose link text contain "Beginner" and graph the search results
  $results = $tree->search('text', Tree::OP_CONTAINS_SUBSTR, 'Beginner', $template);

  // Get the branch of the Tree containing Node with nodeId of 15
  $branch = $tree->getNodeById(15)->getBranch();
  $view2 = $branch->graph($template);

  // Get the limb of the Tree containing Node with nodeId of 10
  $limb = $tree->getNodeById(10)->getLimb();
  $view3 = $limb->graph($template);

  // Get the stem based on Node with nodeId of 13
  $stem = $tree->getNodeById(13)->getStem();
  $view4 = $stem->graph($template);

?>
<html>
  <head>
    <title>PHP-Tree Examples</title>
    <style type="text/css">
      body {font-family: sans-serif;}
    </style>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/treeflex@2.0.1/dist/css/treeflex.css" />
  </head>
  <body>
    <h1>PHP-Tree Examples</h1>
    <h2>Example 1: Full Tree</h2>
    <p>Create a new Tree:</p>
    <?php echo $view1; ?>
    <h2>Example 2: Search Results</h2>
    <p>Search the Tree for Nodes having "Beginner" in their link text:</p>
    <?php echo $results; ?>
    <h2>Example 3: Branch</h2>
    <p>Get the branch of the Tree containing Node with nodeId of 15:</p>
    <?php echo $view2; ?>
    <h2>Example 4: Limb</h2>
    <p>Get the limb of the Tree containing Node with nodeId of 10:</p>
    <?php echo $view3; ?>
    <h2>Example 5: Stem</h2>
    <p>Get the stem based on Node with nodeId of 13:</p>
    <?php echo $view4; ?>
    <h2>Example 6: Modify the Tree</h2>
    <p>Add a top-level Node and re-parent the existing top-level Nodes:</p>
    <?php

      $top = $tree->createNode(
               (object) array(
                 'id'       => 17,
                 'parentId' => 0,
                 'uri'      => '#',
                 'text'     => 'Tutorials'
               )
             );
      $tree->appendChild($top);
      while (count($tree->childNodes) > 1) {
        $child = $tree->removeChild(0);
        $top->appendChild($child);
      }
      echo $tree->graph($template);

    ?>
    <h2>Example 7: Fun with Templates!</h2>
    <p>You can use CSS to style the HTML output so it'll work with some client-side "TreeView" scripts. Here, we use
       <a href="https://www.cssscript.com/semantic-hierarchy-tree-treeflex/" target="_blank">Treeflex</a>
       to turn Example 6 into an organizational chart. (This also works with the JavaScript version
       of this script.)
    </p>
    <div class="tf-tree">
    <?php

      $template = "<span class=\"tf-nc\"><a href=\"<%uri%>\" target=\"_blank\"><%text%></a></span>";
      echo $tree->graph($template);

    ?>
    </div>
    </script>
  </body>
</html>