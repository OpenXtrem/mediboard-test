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
$where["compressed"] = "= '0'";
$where["acquittement"] = "IS NOT NULL ";

if (!$do_optimize) {
  $count = $itemEchangeHprim->countList($where);
  
  CAppUI::stepAjax($count." �changes HPRIM � optimiser");
} else {
  
  
  // R�cup�ration de la liste des echanges HPRIM
  $listEchangeHprim = $itemEchangeHprim->loadList($where, null, "0, 1000");
  $count  = 0;
  foreach($listEchangeHprim as $_echange_hprim) {
    $errors = 0;
    
    // Affectation de l'object_id et object_class
    $_echange_hprim->getObjectIdClass();
    if (!$errors) {
      if ($msg = $_echange_hprim->store()) {
        CAppUI::stepAjax("#$_echange_hprim->_id : Impossible � sauvegarder l'�change HPRIM", UI_MSG_WARNING);
        CAppUI::stepAjax($msg, UI_MSG_WARNING);
      } else {
        $count++;
      }
    }
    
    // Compression
    if (!$_echange_hprim->message = gzcompress($_echange_hprim->message)) {
      $errors++;
      CAppUI::stepAjax("Compression du message impossible", UI_MSG_WARNING);
    }
    if (!$_echange_hprim->acquittement = gzcompress($_echange_hprim->acquittement)) {
      $errors++;
      CAppUI::stepAjax("Compression de l'acquittement impossible", UI_MSG_WARNING);
    }
    if (!$errors) {
      $_echange_hprim->compressed = 1;
      if ($msg = $_echange_hprim->store()) {
        $errors++;
        CAppUI::stepAjax("#$_echange_hprim->_id : Impossible � compresser l'�change HPRIM", UI_MSG_WARNING);
        CAppUI::stepAjax($msg, UI_MSG_WARNING);
      }
    }
  }
  if ($count == 0) {
    echo "<script type='text/javascript'>stop=true;</script>";
  }
  CAppUI::stepAjax($count. " �changes HPRIM optimis� et sauvegard�");
}
?>