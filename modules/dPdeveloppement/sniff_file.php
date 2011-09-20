<?php /* $Id: form_tester.php 6402 2009-06-08 07:53:07Z phenxdesign $ */

/**
* @package Mediboard
* @subpackage dPdeveloppement
* @version $Revision: 6402 $
* @author Fabien M�nager
*/

CCanDo::checkRead();

if (!class_exists("CMbCodeSniffer")) {
  CAppUI::stepMessage(UI_MSG_WARNING, "CMbCodeSniffer-error-PEAR_needed");
  return;
}

$file = CValue::get("file");
$file = str_replace(":", "/", $file);

$sniffer = new CMbCodeSniffer;
$sniffer->process($file);
$report = $sniffer->makeReportPath($file);
$errors = reset($sniffer->getFilesErrors());
$alerts = $sniffer->getFlattenAlerts();

// Cuz sniffer changes work dir but restores it at destruction
// Be aware that unset() won't call __destruct() anyhow
$sniffer->__destruct();
  
// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("file", $file);
$smarty->assign("alerts", $alerts);
$smarty->assign("errors", $errors);
$smarty->display("sniff_file.tpl");

?>