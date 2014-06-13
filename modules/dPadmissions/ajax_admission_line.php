<?php 

/**
 * $Id$
 *  
 * @category Admissions
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision$
 * @link     http://www.mediboard.org
 */

CCanDo::checkRead();

$sejour_id = CValue::get("sejour_id");
$date      = CValue::getOrSession("date");

$date_actuelle = CMbDT::dateTime("00:00:00");
$date_demain   = CMbDT::dateTime("00:00:00", "+ 1 day");

$date_min = CMbDT::dateTime("00:00:00", $date);
$date_max = CMbDT::dateTime("23:59:59", $date);

$sejour = new CSejour();
$sejour->load($sejour_id);

$sejour->loadRefPraticien();
$patient = $sejour->loadRefPatient();

$patient->loadIPP();

// Dossier m�dical
$dossier_medical = $patient->loadRefDossierMedical(false);

// Chargement du num�ro de dossier
$sejour->loadNDA();

// Chargement des notes sur le s�jourw
$sejour->loadRefsNotes();

// Chargement des modes d'entr�e
$sejour->loadRefEtablissementProvenance();

// Chargement de l'affectation
$affectation = $sejour->loadRefFirstAffectation();

// Chargement des interventions
$whereOperations = array("annulee" => "= '0'");
$operations = $sejour->loadRefsOperations($whereOperations);

// Chargement optimis�e des prestations
CSejour::massCountPrestationSouhaitees(array($sejour));

foreach ($operations as $operation) {
  $operation->loadRefsActes();
  $dossier_anesth = $operation->loadRefsConsultAnesth();
  $consultation = $dossier_anesth->loadRefConsultation();
  $consultation->loadRefPlageConsult();
  $dossier_anesth->_date_consult = $consultation->_date;
}

if ($sejour->type == 'ambu' && CAppUI::conf('dPadmissions CSejour entree_pre_op_ambu', CGroups::loadCurrent())) {
  $curr_operation = $sejour->loadRefCurrOperation($date);
  $curr_operation->loadRefPlageOp();
  $sejour->entree_prevue = CMbDT::subTime($curr_operation->presence_preop, CMbDT::time($curr_operation->_datetime));
}

if (CAppUI::conf("dPadmissions show_deficience")) {
  CDossierMedical::massCountAntecedentsByType(array($dossier_medical), "deficience");
}

$list_mode_entree = array();
if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_entree")) {
  $mode_entree = new CModeEntreeSejour();
  $where = array(
    "actif" => "= '1'",
  );
  $list_mode_entree = $mode_entree->loadGroupList($where);
}

$smarty = new CSmartyDP();

$smarty->assign("_sejour"         , $sejour);
$smarty->assign("date_min"        , $date_min);
$smarty->assign("date_max"        , $date_max);
$smarty->assign("date_actuelle"   , $date_actuelle);
$smarty->assign("date_demain"     , $date_demain);
$smarty->assign("list_mode_entree", $list_mode_entree);
$smarty->assign("prestations"     , CPrestation::loadCurrentList());
$smarty->assign("canAdmissions"   , CModule::getCanDo("dPadmissions"));
$smarty->assign("canPatients"     , CModule::getCanDo("dPpatients"));
$smarty->assign("canPlanningOp"   , CModule::getCanDo("dPplanningOp"));

$smarty->display("inc_vw_admission_line.tpl");