<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage dPprescription
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

$adm = CValue::post("adm");
$list_administrations = array();
$mode_dossier = CValue::get("mode_dossier");
$refresh_popup = CValue::get("refresh_popup", "0");

$adm = json_decode(stripslashes(utf8_encode($adm)), true);

$sejour = new CSejour();
$date_sel = null;
$tabs_refresh = array();
$sejour_id = null;
$nb_patients = 1;

if (count($adm) > 0) {
	foreach ($adm as $ad) {
		$ad['quantite']    =  is_numeric($ad['quantite']) ? $ad['quantite'] : '';
		$ad['prise_id']    =  is_numeric($ad['key_tab'])  ? $ad['key_tab'] : '';
		$ad['unite_prise'] = !is_numeric($ad['key_tab'])  ? utf8_decode($ad['key_tab']) : '';
		
		$ad['key_tab'] = str_replace('/', '-', $ad['key_tab']);
		
		$date = mbDate($ad['dateTime']);
		$time = mbTime($ad['dateTime']);
    
		// Un peu d'initialisation lourde ...
	  if (!isset($list_administrations[$ad['line_id']])) {
	    $list_administrations[$ad['line_id']] = array();
	  }
	  if (!isset($list_administrations[$ad['line_id']][$ad['key_tab']])) {
	    $list_administrations[$ad['line_id']][$ad['key_tab']] = array();
	  }
	  if (!isset($list_administrations[$ad['line_id']][$ad['key_tab']][$date])) {
	    $list_administrations[$ad['line_id']][$ad['key_tab']][$date] = array();
	  }
	  if (!isset($list_administrations[$ad['line_id']][$ad['key_tab']][$date][$time])) {
	    $list_administrations[$ad['line_id']][$ad['key_tab']][$date][$time] = array();
	  }

	  $curr_adm = &$list_administrations[$ad['line_id']][$ad['key_tab']][$date][$time];
	
		// Si une prise est specifi�e (pas de moment unitaire), on charge la prise pour stocker l'unite de prise
		$curr_adm['unite_prise'] = $ad['unite_prise'];
		if ($ad['prise_id']) {
		  $prise = new CPrisePosologie();
		  $prise->load($ad['prise_id']);
		  $curr_adm['unite_prise'] = $prise->unite_prise;
		}
		
		// Chargement de la ligne
		$line = new $ad['object_class'];
		$line->load($ad['line_id']);

		// Recherche des chapitres a rafraichir apres la creation des administrations
		switch($line->_class){
		  case 'CPrescriptionLineMedicament':
		    if($line->_is_injectable){
		      $tabs_refresh["inj"] = "inj";
		    }
		    else if ($line->inscription) {
		      $tabs_refresh["inscription"] = "inscription";
		    }
		    else {
		      $tabs_refresh["med"] = "med";
		    }
		    break;
		  case 'CPrescriptionLineMix':
		    $tabs_refresh["perf"] = "perf";
		    break;
		  case 'CPrescriptionLineElement':
		    if ($line->inscription) {
		      $tabs_refresh["inscription"] = "inscription";
		    }
		    else {
  		    $chapitre = $line->_ref_element_prescription->_ref_category_prescription->chapitre;
  		    $tabs_refresh[$chapitre] = $chapitre;
		    }
		}
		
		if($line instanceof CPrescriptionLineMedicament){
		  $line->_ref_produit->loadConditionnement();
		  $line->loadRefProduitPrescription();
		}
		
		if (CAppUI::conf("pharmacie ask_stock_location_administration")) {
		  $line->loadRefsProductsStocks();
		}
		
		$curr_adm['line'] = $line;
		$curr_adm['prise'] = new CPrisePosologie();
		$curr_adm['prise']->quantite = $ad['quantite'];
		$curr_adm['prise_id'] = $ad['prise_id'];
		$curr_adm['dateTime'] = "$date $time";
		$curr_adm['notToday'] = ($date != mbDate());
		
		if (!$date_sel)  $date_sel  = isset($ad['date_sel']) ? $ad['date_sel'] : null;
		
		$line->loadRefPrescription();
		$line->_ref_prescription->loadRefObject();
		
		// Si plusieurs patients, ne pas afficher le nom du premier patient trouv� 
		if ($sejour_id != null && $sejour_id != $line->_ref_prescription->_ref_object->_id) {
		  $nb_patients ++;
		}
		
	  if (!$sejour->_id) {
			$sejour = $line->_ref_prescription->_ref_object;
			$sejour->loadRefPatient();
			$sejour->_ref_patient->loadRefsAffectations();
			$sejour->_ref_patient->_ref_curr_affectation->loadView();
			$sejour_id = $sejour->_id;
		}
	}
}

$user_id = CAppUI::$user->_id;
$transmission = new CTransmissionMedicale();

if (count($adm) > 0) {
  $transmission->sejour_id = $sejour->_id;
  $transmission->user_id = $user_id;
  $tranmission->object_class = get_class($line);
}

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("administrations", $list_administrations);
$smarty->assign("date_sel"       , $date_sel);
$smarty->assign("sejour"        , $sejour);
$smarty->assign("transmission"  , $transmission);
$smarty->assign("mode_dossier"  , $mode_dossier);
$smarty->assign("tabs_refresh"  , $tabs_refresh);
$smarty->assign("user_id"       , $user_id);
$smarty->assign("refresh_popup" , $refresh_popup);
$smarty->assign("nb_patients"   , $nb_patients);
$smarty->assign("new_adm"       , new CAdministration);
$smarty->display("inc_vw_add_multiple_administrations.tpl");

?>