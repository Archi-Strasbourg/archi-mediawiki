<?php
namespace ArchiRecentChanges;

use CategoryBreadcrumb\CategoryBreadcrumb;

use Linker;

use Title;


$title = $_POST['title'];

if ($title->getNamespace() == NS_ADDRESS_NEWS) {
    $title = Title::newFromText($title->getText(), NS_ADDRESS);
}
$parenttree = $title->getParentCategoryTree();
CategoryBreadcrumb::checkParentCategory($parenttree);
CategoryBreadcrumb::checkTree($parenttree);
$flatTree = CategoryBreadcrumb::getFlatTree($parenttree);
$return = '';
$categories = array_reverse($flatTree);
if (isset($categories[0])) {
    $catTitle = Title::newFromText($categories[0]);
    $return .= Linker::link($catTitle, htmlspecialchars($catTitle->getText()));
    if (isset($categories[1])) {
        $catTitle = Title::newFromText($categories[1]);
        $return .= ' > ' . Linker::link($catTitle, htmlspecialchars($catTitle->getText()));
    }
}

echo $return;
?>