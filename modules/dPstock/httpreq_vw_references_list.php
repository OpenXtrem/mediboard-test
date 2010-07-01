<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage dPstock
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */
 
CCanDo::checkRead();

$category_id  = CValue::getOrSession('category_id');
$societe_id   = CValue::getOrSession('societe_id');
$keywords     = CValue::getOrSession('keywords');
$reference_id = CValue::getOrSession('reference_id');
$mode         = CValue::get('mode');
$start        = CValue::get('start', 0);
$letter       = CValue::get('letter', "%");
$show_all     = CValue::get('show_all');

// Don't user getOrSession as we don't want to get it from session
CValue::setSession("show_all", $show_all);
CValue::setSession("letter", $letter);

$where = array();
if ($category_id) {
  $where['product.category_id'] = " = $category_id";
}
if ($societe_id) {
  $where['product_reference.societe_id'] = " = $societe_id";
}
if ($keywords) {
  $where[] = "product_reference.code LIKE '%$keywords%' OR 
              product.code LIKE '%$keywords%' OR 
              product.name LIKE '%$keywords%' OR 
              product.description LIKE '%$keywords%'";
}
if (!$show_all) {
  $where[] = "product_reference.cancelled = '0' OR product_reference.cancelled IS NULL";
}
$where["product.name"] = ($letter === "#" ? "RLIKE '^[^A-Z]'" : "LIKE '$letter%'");

$orderby = 'product.name ASC';

$leftjoin = array();
$leftjoin['product'] = 'product.product_id = product_reference.product_id';

$reference = new CProductReference();
$total = $reference->countList($where, null, null, null, $leftjoin);
$list_references = $reference->loadList($where, $orderby, intval($start).",".CAppUI::conf("dPstock CProductReference pagination_size"), null, $leftjoin);
foreach($list_references as $ref) {
  $ref->loadRefsFwd();
  $ref->_ref_product->loadRefStock();
}

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('list_references', $list_references);
$smarty->assign('total', $total);
$smarty->assign('mode', $mode);
$smarty->assign('start', $start);
$smarty->assign('letter', $letter);
$smarty->assign('reference_id', $reference_id);


$smarty->display('inc_references_list.tpl');
