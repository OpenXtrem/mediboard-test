<?php /* $Id: $ */

/**
 * @package Mediboard
 * @subpackage dmi
 * @version $Revision: $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

CCanDo::checkRead();

$date = CValue::get("date");
$type = CValue::get("type");

CValue::setSession("date", $date);
CValue::setSession("type", $type);

$date_min = $date;
$date_max = mbDate("+1 DAY", mbDate());

$dmi_line = new CPrescriptionLineDMI();

$where = array();
$where["date"] = "BETWEEN '$date_min' AND '$date_max'";

if ($type) {
  $where["type"] = " = '$type'";
}

$list_lines = $dmi_line->loadList($where, "date");

$lines_by_context = array();
$contexts = array();

foreach($list_lines as $_dmi) {
  $_dmi->loadRefsFwd();
  $_dmi->_ref_prescription->loadRefPatient();
  $_dmi->_ref_product->loadBackRefs("references");
  $_dmi->_ref_product->loadRefStock();
  $_dmi->_ref_operation->loadRefsFwd();
  
  $product_order = new CProductOrder;
  $product_order->setObject($_dmi->_ref_operation);
  $orders = $product_order->loadMatchingList();
  
  foreach($orders as $_order_id => $_order) {
    $_order->loadRefsOrderItems();
    foreach($_order->_ref_order_items as $_order_item) {
      $_order_item->loadReference();
      if ($_order_item->_ref_reference->product_id != $_dmi->product_id) {
        unset($orders[$_order_id]);
        break;
      }
    }
  }
  
  $_dmi->_orders = $orders;
    
  foreach($_dmi->_ref_product->_back["references"] as $_reference) {
  	$_reference->loadRefSociete();
  }
  
  if (!isset($lines_by_context[$_dmi->_ref_prescription->_guid])) {
    $lines_by_context[$_dmi->_ref_prescription->_guid] = array();
    $contexts[$_dmi->_ref_prescription->_guid] = $_dmi->_ref_prescription;
  }
  $lines_by_context[$_dmi->_ref_prescription->_guid][$_dmi->_id] = $_dmi;
}

// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("lines_by_context", $lines_by_context);
$smarty->assign("contexts"        , $contexts);
$smarty->display("inc_list_commandes.tpl");
