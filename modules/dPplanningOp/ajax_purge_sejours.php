<?php /* $Id: $ */

/**
 * @package Mediboard
 * @subpackage dPpatients
 * @version $Revision: 7138 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

CCanDo::Admin();

$sejour = new CSejour();

// Supression de patients
$suppr    = 0;
$error    = 0;
$qte      = CValue::get("qte", 1);
$date_min = CValue::get("date_min", mbDate());
$date_min = $date_min ? $date_min : mbDate();
$where = array("entree" => ">= '$date_min 00:00:00'");
$listSejours = $sejour->loadList($where, null, $qte);

foreach($listSejours as $_sejour) {
  CAppUI::setMsg($_sejour->_view, UI_MSG_OK);
  if($msg = $_sejour->purge()) {
    CAppUI::setMsg($msg, UI_MSG_ALERT);
    $error++;
    continue;
  }
  CAppUI::setMsg("s�jour supprim�", UI_MSG_OK);
  $suppr++;
}

// Nombre de patients
$nb_sejours = $sejour->countList($where);

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("resultsMsg" , CAppUI::getMsg());
$smarty->assign("suppr"      , $suppr);
$smarty->assign("error"      , $error);
$smarty->assign("nb_sejours" , $nb_sejours);

$smarty->display("inc_purge_sejours.tpl");

 
?>
