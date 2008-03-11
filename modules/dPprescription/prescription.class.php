<?php /* $Id: $ */

/**
 *	@package Mediboard
 *	@subpackage dPprescription
 *	@version $Revision: $
 *  @author Romain Ollivier
 */

/**
 * The CPrescription class
 */
class CPrescription extends CMbObject {
  // DB Table key
  var $prescription_id = null;
  
  // DB Fields
  var $praticien_id = null;
  var $object_class = null;
  var $object_id    = null;
  var $libelle      = null;
  
  // Object References
  var $_ref_object  = null;
  var $_ref_patient = null;
  
  // BackRefs
  var $_ref_prescription_lines                = null;
  var $_ref_prescription_lines_element        = null;
  var $_ref_prescription_lines_element_by_cat = null;
  var $_ref_prescription_lines_comment        = null;
  
  function CPrescription() {
    $this->CMbObject("prescription", "prescription_id");
    
    $this->loadRefModule(basename(dirname(__FILE__)));
  }
  
  function getBackRefs() {
    $backRefs = parent::getBackRefs();
    $backRefs["prescription_line_medicament"] = "CPrescriptionLineMedicament prescription_id";
    return $backRefs;
  }
  
  function getSpecs() {
  	$specsParent = parent::getSpecs();
    $specs = array (
      "praticien_id"  => "notNull ref class|CMediusers",
      "object_id"     => "ref class|CCodable meta|object_class",
      "object_class"  => "notNull enum list|CSejour|CConsultation",
      "libelle"       => "str"
    );
    return array_merge($specsParent, $specs);
  }
  
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefsFwd();
    $this->_view = "Prescription du Dr. ".$this->_ref_praticien->_view." : ".$this->_ref_object->_view;
    if($this->libelle){
    	$this->_view .= "($this->libelle)";
    }
  
    if(!$this->object_id){
    	$this->_view = "Protocole: ".$this->libelle;
    }
  }
  
  function loadRefsFwd() {
    $this->_ref_praticien = new CMediusers();
    $this->_ref_praticien->load($this->praticien_id);
    $this->_ref_object = new $this->object_class();
    $this->_ref_object->load($this->object_id);
    $this->_ref_patient = new CPatient();
    $this->_ref_patient->load($this->_ref_object->patient_id);
  }
  
  // Chargement des lignes de prescription
  function loadRefsLines() {
    $line = new CPrescriptionLineMedicament();
    $where = array("prescription_id" => "= $this->_id");
    $order = "prescription_line_id";
    $this->_ref_prescription_lines = $line->loadList($where, $order);
  }
  
  
  //--------------
  // Chargement des lignes de medicaments (medicaments + commentaires)
  //--------------
  function loadRefsLinesMedComments(){
    // Chargement des lignes de medicaments
  	$this->loadRefsLines();
  	// Chargement des lignes de commentaire du medicament
  	$this->loadRefsLinesComment("medicament");
  	
  	// Initialisation du tableau de fusion
  	$this->_ref_lines_med_comments["med"] = array();
  	$this->_ref_lines_med_comments["comment"] = array();
  	
  	foreach($this->_ref_prescription_lines as &$line_med){
  		$this->_ref_lines_med_comments["med"][] = $line_med;
  	}
  	if(isset($this->_ref_prescription_lines_comment["medicament"]["comment"])){
      foreach($this->_ref_prescription_lines_comment["medicament"]["comment"] as &$comment_med){
  	  	$this->_ref_lines_med_comments["comment"][] = $comment_med;
  	  }
  	}
  }
  
  // Chargement des lignes d'element
  function loadRefsLinesElement(){
  	$line = new CPrescriptionLineElement();
    $where = array("prescription_id" => "= $this->_id");
    $order = "prescription_line_element_id";
    $this->_ref_prescription_lines_element = $line->loadList($where, $order);
    foreach ($this->_ref_prescription_lines_element as &$line_element){
    	$line_element->loadRefElement();
    	$line_element->_ref_element_prescription->loadRefCategory();
    }
  }
  
  
  // Chargement des lignes d'elements par cat�gorie
  function loadRefsLinesElementByCat(){
  	$this->loadRefsLinesElement();
  	$this->_ref_prescription_lines_element_by_cat = array();
  	
  	foreach($this->_ref_prescription_lines_element as $line){
  		$category = new CCategoryPrescription();
  		$category->load($line->_ref_element_prescription->category_prescription_id);
  	  $this->_ref_prescription_lines_element_by_cat[$category->chapitre]["element"][] = $line;	
  	}
  	ksort($this->_ref_prescription_lines_element_by_cat);
  }
  
  
  // Chargement des lignes de commentaires
  function loadRefsLinesComment($chapitre = null){
  	$this->_ref_prescription_lines_comment = array();
    
  	$this->_ref_prescription_lines_comment["dmi"] = array();
  	$this->_ref_prescription_lines_comment["anapath"] = array();
  	$this->_ref_prescription_lines_comment["biologie"] = array();
  	$this->_ref_prescription_lines_comment["imagerie"] = array();
  	$this->_ref_prescription_lines_comment["consult"] = array();
  	$this->_ref_prescription_lines_comment["kine"] = array();
  	$this->_ref_prescription_lines_comment["soin"] = array();
  	
  	
  	$commentaires = array();
  	$line_comment = new CPrescriptionLineComment();
  	$line_comment->prescription_id = $this->_id;
  	if($chapitre){
  		$line_comment->chapitre = $chapitre;
  	}
  	$commentaires = $line_comment->loadMatchingList();
  	foreach($commentaires as $_line_comment){
  		  $this->_ref_prescription_lines_comment[$_line_comment->chapitre]["comment"][] = $_line_comment;
    }		
  }
  
  
  //-------------  
  // Chargement des lignes d'elements (Elements + commentaires)
  //-------------
  function loadRefsLinesElementsComments(){
  	$this->loadRefsLinesElementByCat();
  	$this->loadRefsLinesComment();
  	
  	// Suppression des ligne de medicaments
  	unset($this->_ref_prescription_lines_comment["medicament"]);
  	
  	// Fusion des tableaux d'element et de commentaire 
  	$this->_ref_lines_elements_comments = array_merge_recursive($this->_ref_prescription_lines_element_by_cat, $this->_ref_prescription_lines_comment);
    foreach($this->_ref_lines_elements_comments as &$chapitre){
    	if(!array_key_exists("comment", $chapitre)){
    		$chapitre["comment"] = array();
    	}
      if(!array_key_exists("element", $chapitre)){
    		$chapitre["element"] = array();
    	}
    }
  }
  
  
  // Chargement des favoris de prescription pour un praticien donn�
  static function getFavorisPraticien($praticien_id){
  	$favoris = array();
  	$listFavoris = array();
  	$listFavoris["medicament"] = array();
  	$listFavoris["dmi"] = array();
  	$listFavoris["imagerie"] = array();
  	$listFavoris["consult"] = array();
  	$listFavoris["kine"] = array();
  	$listFavoris["soin"] = array();
  	$listFavoris["anapath"] = array();
  	$listFavoris["biologie"] = array();
  	
  	$favoris["medicament"] = CBcbProduit::getFavoris($praticien_id);
	  $favoris["dmi"] = CElementPrescription::getFavoris($praticien_id, "dmi");
	  $favoris["anapath"] = CElementPrescription::getFavoris($praticien_id, "anapath");
	  $favoris["biologie"] = CElementPrescription::getFavoris($praticien_id, "biologie");
	  $favoris["imagerie"] = CElementPrescription::getFavoris($praticien_id, "imagerie");
	  $favoris["consult"] = CElementPrescription::getFavoris($praticien_id, "consult");
	  $favoris["kine"] = CElementPrescription::getFavoris($praticien_id, "kine");
	  $favoris["soin"] = CElementPrescription::getFavoris($praticien_id, "soin");
	  
	  foreach($favoris as $key => $typeFavoris) {
	  	foreach($typeFavoris as $curr_fav){
	  		if($key == "medicament"){
	  		  $produit = new CBcbProduit();
	        $produit->load($curr_fav["code_cip"]);
	        $listFavoris["medicament"][] = $produit;
	  		} else {
	  			$element = new CElementPrescription();
	  			$element->load($curr_fav["element_prescription_id"]);
	  			$listFavoris[$key][] = $element;
	  		}
	  	}
	  }
	  return $listFavoris;  	
  }
}

?>