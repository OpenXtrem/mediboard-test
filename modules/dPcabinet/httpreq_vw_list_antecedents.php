<?php
/**
 * $Id$
 *
 * @package    Mediboard
 * @subpackage Cabinet
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 */

CCanDo::check();

$patient_id     = CValue::getOrSession("patient_id", 0);
$_is_anesth     = CValue::getOrSession("_is_anesth", null);
$sejour_id      = CValue::getOrSession("sejour_id");
$sort_by_date   = CValue::getOrSession("sort_by_date");

$patient = new CPatient();
$patient->load($patient_id);

// Chargement du dossier medical du patient
$patient->loadRefDossierMedical();
$dossier_medical =& $patient->_ref_dossier_medical;
$dossier_medical->needsRead();

$sejour = new CSejour();
$sejour->load($sejour_id);

// Incoh�rence possible du s�jour en session, auquel cas on r�initialise le s�jour
if ($patient_id != $sejour->patient_id) {
  $sejour = new CSejour();
  $sejour_id = null;
}


$prescription_sejour = $sejour->loadRefPrescriptionSejour();
if ($prescription_sejour) {
  $prescription_sejour->countLinesTP();
}

// Chargements des antecedents et traitements du dossier_medical
if ($dossier_medical->_id) {
  $dossier_medical->loadRefsAntecedents(true, $sort_by_date); // On doit charger TOUS les antecedents, meme les annul�s (argument true)
  $dossier_medical->loadRefsTraitements(true);
  $dossier_medical->countAntecedents();
  $dossier_medical->countTraitements();

  $prescription = $dossier_medical->loadRefPrescription();

  foreach ($dossier_medical->_all_antecedents as $_antecedent) {
    $_antecedent->loadLogs();
  }
  foreach ($dossier_medical->_ref_traitements as $_traitement) {
    $_traitement->loadLogs();
  }
  
  if ($prescription && is_array($prescription->_ref_prescription_lines)) {
    foreach ($prescription->_ref_prescription_lines as $_line) {
      $_line->loadRefsPrises();
      if ($_line->fin && $_line->fin <= CMbDT::date()) {
        $_line->_stopped = true;
        $dossier_medical->_count_cancelled_traitements++;
      }
    }
  }
}

$user = CAppUI::$user;
$user->isPraticien();

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("sejour"    , $sejour);
$smarty->assign("patient"   , $patient);
$smarty->assign("_is_anesth", $_is_anesth);

$smarty->assign("user", $user);
$smarty->assign("sort_by_date", $sort_by_date);


$smarty->display("inc_list_ant.tpl");
