<?php /* $Id: print_etiquettes.php $ */

/**
 * @package Mediboard
 * @subpackage dPurgences
 * @version $Revision: $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

// Chargement du rpu
$rpu_id = CValue::get("rpu_id");
$rpu = new CRPU();
$rpu->load($rpu_id);
$rpu->loadRefSejour();

// Chargement du patient
$rpu->_ref_sejour->loadRefPatient();

// R�cup�ration des valeurs des champs;
$fields = $rpu->_ref_sejour->completeLabelFields();
$fields = array_merge($fields, $rpu->_ref_sejour->_ref_patient->completeLabelFields());

// Chargement des mod�les d'�tiquettes
$modele_etiquette = new CModeleEtiquette;
$where = array();
$where['object_class'] = " = 'CRPU'";

if (count($modeles_etiquettes = $modele_etiquette->loadList($where))) {
	// TODO: faire une modale pour proposer les mod�les d'�tiquettes
	$first_modele = reset($modeles_etiquettes);
	$first_modele->replaceFields($fields);
	$first_modele->printEtiquettes();
}
?>