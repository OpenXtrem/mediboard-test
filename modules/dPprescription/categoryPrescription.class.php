<?php /* $Id: $ */

/**
 *	@package Mediboard
 *	@subpackage dPprescription
 *	@version $Revision: $
 *  @author Alexis Granger
 */

/**
 * The CPrescription class
 */
class CCategoryPrescription extends CMbObject {
  // DB Table key
  var $category_prescription_id = null;
  
  // DB Fields
  var $chapitre    = null;
  var $nom         = null;
  var $description = null;
  var $header      = null;
  var $group_id    = null;
  
  // BackRefs
  var $_ref_elements_prescription = null;
  
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'category_prescription';
    $spec->key   = 'category_prescription_id';
    return $spec;
  }
  
  function getSpecs() {
  	$specs = parent::getSpecs();
    $specs["chapitre"]    = "notNull enum list|anapath|biologie|imagerie|consult|kine|soin|dm|dmi";
    $specs["nom"]         = "notNull str";
    $specs["description"] = "text";
    $specs["header"]      = "text";
    $specs["group_id"]    = "ref class|CGroups";
    return $specs;
  }
  
  function getBackRefs() {
    $backRefs = parent::getBackRefs();
    $backRefs["elements_prescription"]   = "CElementPrescription category_prescription_id";
    $backRefs["executants_prescription"] = "CExecutantPrescriptionLine category_prescription_id";
    $backRefs["functions_category"]      = "CFunctionCategoryPrescription category_prescription_id";
    $backRefs["comments_prescription"]   = "CPrescriptionLineComment category_prescription_id";
    return $backRefs;
  }     
  
  function updateFormFields(){
  	parent::updateFormFields();
  	$this->_view = $this->nom;
  }
  
  function loadElementsPrescription() {
    $this->_ref_elements_prescription = $this->loadBackRefs("elements_prescription","libelle");
  }
  
  /**
   * Charge toutes les categories tri�es par chapitre
   * @param $chapitre string Permet de restreindre � un seul chapitre
   * @param $group string 'no_group'     => non associ�es � une clinique
   * 											'current_group => associ�es � la clinique courante
   *                      'current'      => no_group OR current_group 
   *                      'all'          => toutes les categories
   * 								 int	 group_id => group selectionn�
   * @return array[CCategoryPrescription] Les cat�gories
   */
  static function loadCategoriesByChap($chapitre = null, $group="all") {
    global $g;
    
		$categorie = new CCategoryPrescription;
		$where = array();

		if($chapitre){
		  $where["chapitre"] = " = '$chapitre'";
		}
    
		// Permet de filtrer les categories
    if($group != 'all'){
      if(is_numeric($group)){
        $where["group_id"] = " = '$group'";
      }
      if($group == 'no_group'){
        $where["group_id"] = "IS NULL";
      }
      if($group == 'current_group'){
        $where["group_id"] = " = '$g'";
      }
      if($group == 'current'){
        $where[] = "group_id = '$g' OR group_id IS NULL"; 
      }
    }
		
		// Initialisation des chapitres
		$chapitres = explode("|", $categorie->_specs["chapitre"]->list);
		
		$categories_par_chapitre = array();
		foreach ($chapitres as $chapitre) {
		  $categories_par_chapitre[$chapitre] = array();
		}
		
		// Chargement et classement par chapitre
    $order = "nom";
    $categories = $categorie->loadList($where, $order);
    foreach ($categories as &$categorie) {
		  $categories_par_chapitre[$categorie->chapitre]["$categorie->_id"] =& $categorie;
		}
  	ksort($categories_par_chapitre);
  	return $categories_par_chapitre;
  }
}

?>