<?php /* $Id: $ */

/**
 *	@package Mediboard
 *	@subpackage dPstock
 *	@version $Revision: $
 *  @author Fabien M�nager
 */
 
global $can;
$can->needsEdit();

$order_id = mbGetValueFromGetOrSession('order_id');

// Loads the expected Order
$order = new CProductOrder();
if ($order_id) {
  $order->load($order_id);
  $order->updateFormFields();
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('order', $order);
$smarty->display('vw_idx_order_manager.tpl');

?>