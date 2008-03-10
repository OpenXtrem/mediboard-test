<?php /* $Id: $ */

/**
 *	@package Mediboard
 *	@subpackage dPstock
 *	@version $Revision: $
 *  @author Fabien M�nager
 */

global $AppUI, $can, $m;

$can->needsRead();

$stock_id    = mbGetValueFromGetOrSession('stock_id');
$product_id  = mbGetValueFromGetOrSession('product_id');
$category_id = mbGetValueFromGetOrSession('category_id');

// Loads the stock in function of the stock ID or the product ID
$stock = new CProductStock();
if ($stock_id) {
  $stock->stock_id = $stock_id;
  $stock->loadMatchingObject();
  $stock->loadRefsFwd();
  $stock->_ref_product->loadRefsFwd();
} else if($product_id) {
  $stock->product_id = $product_id;
  $product = new CProduct();
  $product->load($product_id);
  $stock->_ref_product = $product;
} else $stock->loadRefsFwd();

// Loads the required Category and the complete list
$category = new CProductCategory();
$list_categories = $category->loadList(null, 'name');
if (!$category_id) {
  $category = $stock->_ref_product->_ref_category;
} else {
  $category->category_id = $category_id;
  $category->loadMatchingObject();
}

if ($category) {
  $category->loadRefs();
  
  // Loads the products list
  foreach($category->_ref_products as $prod) {
    $prod->loadRefs();
  }
} else $category = new CProductCategory();

// Retrieving the Groups list
$group = new CGroups();
$list_groups = $group->loadList();

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign('stock',           $stock);
$smarty->assign('category',        $category);
$smarty->assign('list_groups',     $list_groups);
$smarty->assign('list_categories', $list_categories);

$smarty->display('vw_idx_stock.tpl');

?>