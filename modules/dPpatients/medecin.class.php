<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPpatients
* @version $Revision$
* @author Romain Ollivier
*/

/**
 * The CMedecin Class
 */
class CMedecin extends CMbObject {
  // DB Table key
	var $medecin_id = null;

  // DB Fields
	var $nom             = null;
  var $prenom          = null;
  var $jeunefille      = null;
	var $adresse         = null;
	var $ville           = null;
	var $cp              = null;
	var $tel             = null;
	var $fax             = null;
	var $email           = null;
  var $disciplines     = null;
  var $orientations    = null;
  var $complementaires = null;

  // Form fields
	var $_tel1 = null;
	var $_tel2 = null;
	var $_tel3 = null;
	var $_tel4 = null;
	var $_tel5 = null;
	var $_fax1 = null;
	var $_fax2 = null;
	var $_fax3 = null;
	var $_fax4 = null;
	var $_fax5 = null;

  // Object References
  var $_ref_patients = null;

	function CMedecin() {
		$this->CMbObject("medecin", "medecin_id");
    
    $this->loadRefModule(basename(dirname(__FILE__)));

    static $props = array (
      "nom"             => "str|notNull|confidential",
      "prenom"          => "str|confidential",
      "jeunefille"      => "str|confidential",
      "adresse"         => "str|confidential",
      "ville"           => "str|confidential",
      "cp"              => "num|maxLength|5|confidential",
      "tel"             => "num|length|10|confidential",
      "fax"             => "num|length|10|confidential",
      "email"           => "str|confidential",
      "disciplines"     => "str|confidential",
      "orientations"    => "str|confidential",
      "complementaires" => "str|confidential"
    );
    $this->_props =& $props;

    static $seek = array (
      "nom"         => "likeBegin",
      "prenom"      => "likeBegin",
      "ville"       => "like",
      "disciplines" => "like"
    );
    $this->_seek =& $seek;

    static $enums = null;
    if (!$enums) {
      $enums = $this->getEnums();
    }
    
    $this->_enums =& $enums;
    
    static $enumsTrans = null;
    if (!$enumsTrans) {
      $enumsTrans = $this->getEnumsTrans();
    }
    
    $this->_enumsTrans =& $enumsTrans;
	}
  
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = "$this->nom $this->prenom";
    
    $this->_tel1 = substr($this->tel, 0, 2);
    $this->_tel2 = substr($this->tel, 2, 2);
    $this->_tel3 = substr($this->tel, 4, 2);
    $this->_tel4 = substr($this->tel, 6, 2);
    $this->_tel5 = substr($this->tel, 8, 2);

    $this->_fax1 = substr($this->fax, 0, 2);
    $this->_fax2 = substr($this->fax, 2, 2);
    $this->_fax3 = substr($this->fax, 4, 2);
    $this->_fax4 = substr($this->fax, 6, 2);
    $this->_fax5 = substr($this->fax, 8, 2);
  }
  
  function updateDBFields() {
    if ($this->_tel1 !== null) {
      $this->tel = 
        $this->_tel1 .
        $this->_tel2 .
        $this->_tel3 .
        $this->_tel4 .
        $this->_tel5;
    }
    
    if ($this->_fax1 !== null) {
      $this->fax = 
        $this->_fax1 .
        $this->_fax2 .
        $this->_fax3 .
        $this->_fax4 .
        $this->_fax5;
    }
  }

	function check() {
    // Data checking
    $msg = null;

    if (!strlen($this->nom)) {
      $msg .= "Nom invalide: '$this->nom'<br />";
    }

    if (!strlen($this->prenom)) {
      $msg .= "Nom invalide: '$this->prenom'<br />";
    }
        
    return $msg . parent::check();
	}
	
  function canDelete(&$msg, $oid = null) {
    $tables[] = array (
      "label"     => "patient(s)", 
      "name"      => "patients", 
      "idfield"   => "patient_id", 
      "joinfield" => "medecin_traitant"
    );
    
    return parent::canDelete( $msg, $oid, $tables );
  }
  
  function loadRefs() {
    // Backward references
    $obj = new CPatient();
    $this->_ref_patients = $obj->loadList("medecin_traitant = '$this->medecin_id'");
  }
  
  function getExactSiblings() {
  	$where = array();
  	$where["medecin_id"] = db_prepare("!= %", $this->medecin_id);
  	$where["nom"]        = db_prepare("= %", $this->nom);
  	$where["prenom"]     = db_prepare("= %", $this->prenom);
    $where["cp"] = $this->cp == null ? "IS NULL" : db_prepare("= %", $this->cp);
      
  	$siblings = new CMedecin;
  	$siblings = $siblings->loadList($where);
  	return $siblings;
  }
}
?>