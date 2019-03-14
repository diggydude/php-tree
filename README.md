# php-tree
A simple data tree structure with search, graphing, and flat file storage functions.

This is not a BTree or B+ Tree or any other kind of high-performance n-ary tree. It has more in common with the HTML Document Object Model (DOM).

There are no iterators. Instead, the Tree and Node classes have methods useful for graphing search results and various types of subtrees.

See examples.php and the comments at the following class methods for more details:

Tree::__construct()

Tree::importStore()

Tree::search()

Tree::find()

Tree::graph()

Node::__construct()

Node::getBranch()

Node::getLimb()

Node::getStem()
