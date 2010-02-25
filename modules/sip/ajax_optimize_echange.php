<?php /* $Id $ */

/**
 * @package Mediboard
 * @subpackage sip
 * @version $Revision: 7816 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

global $can;

$can->needsAdmin();

$do_optimize= CValue::get("do_optimize");

// Filtre sur les enregistrements
$itemEchangeHprim = new CEchangeHprim;

// Requ�tes
$where = array();
$where["compressed"] = "= '1'";

if (!$do_optimize) {
  $count = $itemEchangeHprim->countList($where);
  CAppUI::stepAjax($count." �changes HPRIM � optimiser");
  return;
}

$order = "date_production DESC";

// R�cup�ration de la liste des echanges HPRIM
$echangesHprim = $itemEchangeHprim->loadList($where, $order, "0, 1000");
$count  = 0;
foreach($echangesHprim as $_echange_hprim) {  
  // Affectation de l'object_id et object_class
  $_echange_hprim->getObjectIdClass();
  if (!$_echange_hprim->object_class || !$_echange_hprim->object_id || $_echange_hprim->store()) {
    $_echange_hprim->delete();
    CAppUI::stepAjax("#$_echange_hprim->_id : Suppression de l'�change HPRIM", UI_MSG_WARNING);
    continue;
  }
  
  $domGetEvenement = CHPrimXMLEvenementsPatients::getHPrimXMLEvenementsPatients($_echange_hprim->message);
  $domGetEvenement->loadXML(utf8_decode($_echange_hprim->message));
  $domGetEvenement->formatOutput = false;
  $_echange_hprim->message = utf8_encode($domGetEvenement->saveXML()); 
 
  $domGetAcquittement = new CHPrimXMLAcquittementsPatients();
  $domGetAcquittement->loadXML(utf8_decode($_echange_hprim->acquittement));
  $domGetAcquittement->formatOutput = false;
  $_echange_hprim->acquittement = utf8_encode($domGetAcquittement->saveXML()); 
  
  if ($msg = $_echange_hprim->store()) {
    CAppUI::stepAjax("Impossible d'ajouter le whiteSpace sur le message et/ou l'acquittement XML", UI_MSG_WARNING);
    continue;
  }
  
  $_echange_hprim->compressed = 1;
  if ($msg = $_echange_hprim->store()) {
    CAppUI::stepAjax("#$_echange_hprim->_id : Impossible de sauvegarder l'�change HPRIM", UI_MSG_WARNING);
    CAppUI::stepAjax($msg, UI_MSG_WARNING);
    continue;
  }
  $count++;
}
if ($count == 0) {
  echo "<script type='text/javascript'>stop=true;</script>";
}
CAppUI::stepAjax($count. " �changes HPRIM optimis�s et sauvegard�s");
?>