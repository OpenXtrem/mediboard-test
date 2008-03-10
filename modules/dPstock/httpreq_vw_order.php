<?php /* $Id: $ */

/**
 *	@package Mediboard
 *	@subpackage dPstock
 *	@version $Revision: $
 *  @author Fabien M�nager
 */
 
global $AppUI, $can, $m;

$can->needsRead();

$order_id  = mbGetValueFromGet('order_id');

// Loads the expected Order
$order = new CProductOrder();
$order->load($order_id);
$order->loadRefsBack();

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('order', $order);

$smarty->display('inc_vw_order.tpl');
?>
