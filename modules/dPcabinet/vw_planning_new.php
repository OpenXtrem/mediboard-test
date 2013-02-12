<?php 
/**
 * $Id$
 * 
 * @package    Mediboard
 * @subpackage dPcabinet
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 */

CCanDo::checkRead();

// L'utilisateur est-il praticien ?
$chir = null;
$mediuser = CMediusers::get();
if ($mediuser->isPraticien()) {
  $chir = $mediuser->createUser();
}

// Praticien selectionn�
$chirSel = CValue::getOrSession("chirSel", $chir ? $chir->user_id : null);

// Liste des consultations a avancer si desistement
$now = mbDate();
$where = array(
  "plageconsult.date" => " > '$now'",
  "consultation.si_desistement" => "= '1'",
);
$where[] = "plageconsult.chir_id = '$chirSel' OR plageconsult.remplacant_id = '$chirSel' OR plageconsult.pour_compte_id = '$chirSel'";
$ljoin = array(
  "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id",
);

$consultation_desist = new CConsultation();
$count_si_desistement = $consultation_desist->countList($where, null, $ljoin);

// Liste des chirurgiens
$user = new CMediusers();
$listChir = CAppUI::pref("pratOnlyForConsult", 1) ?
  $user->loadPraticiens(PERM_EDIT) :
  $user->loadProfessionnelDeSante(PERM_EDIT);

// P�riode
$today = mbDate();

$debut = CValue::getOrSession("debut", $today);

$debut = mbDate("last sunday", $debut);
$fin   = mbDate("next sunday", $debut);
$debut = mbDate("+1 day", $debut);

$prev = mbDate("-1 week", $debut);
$next = mbDate("+1 week", $debut);

$smarty = new CSmartyDP();

$smarty->assign("listChirs", $listChir);
$smarty->assign("today"    , $today);
$smarty->assign("debut"    , $debut);
$smarty->assign("fin"      , $fin);
$smarty->assign("prev"     , $prev);
$smarty->assign("next"     , $next);
$smarty->assign("chirSel"  , $chirSel);
$smarty->assign("count_si_desistement", $count_si_desistement);
$smarty->display("vw_planning_new.tpl");
