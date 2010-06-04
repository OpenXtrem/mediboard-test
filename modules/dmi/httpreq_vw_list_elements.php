<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage dmi
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

global $can, $g;
$can->needsRead();

$category_class = CValue::getOrSession("category_class");
$page           = intval(CValue::get("page", 0));

// Recuperation des categories
$category = new $category_class;
$category->group_id = $g;
$categories = $category->loadMatchingList();

// Chargement de tous les dmis
foreach ($categories as &$_category) {
  $_category->loadRefsElements(/*null, "$page, 30"*/);
  foreach ($_category->_ref_elements as &$_element) {
  	$_element->loadExtProduct();
  	$_element->_ext_product->loadRefsFwd();
  }
}

switch($category_class){
  case 'CDMICategory':
    $object_class = 'CDMI';
    break;
  case 'CCategoryDM':
    $object_class = 'CDM';
    break; 
}

// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("categories", $categories);
$smarty->assign("category_class", $category_class);
$smarty->assign("object_class", $object_class);
$smarty->display("inc_list_elements.tpl");
?>