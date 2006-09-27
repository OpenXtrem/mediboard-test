<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPcompteRendu
* @version $Revision$
* @author Romain Ollivier
*/

class CCompteRendu extends CMbObject {
  // DB Table key
  var $compte_rendu_id = null;

  // DB References
  var $chir_id     = null; // not null when is a template associated to a user
  var $function_id = null; // not null when is a template associated to a function
  var $object_id   = null; // null when is a template, not null when a document

  // DB fields
  var $nom    = null;
  var $source = null;
  var $type   = null;
  var $valide = null;
  
  /// Form fields
  var $_is_document      = false;
  var $_is_modele        = false;
  var $_object_className = null;
  
  // Referenced objects
  var $_ref_chir     = null;
  var $_ref_function = null;
  var $_ref_object   = null;

  function CCompteRendu() {
    $this->CMbObject("compte_rendu", "compte_rendu_id");
    
    $this->loadRefModule(basename(dirname(__FILE__)));

    static $props = array (
      "chir_id"     => "ref|xor|function_id",
      "function_id" => "ref",
      "object_id"   => "ref",
      "nom"         => "str|notNull",
      "source"      => "html",
      "type"        => "enum|patient|consultAnesth|operation|hospitalisation|consultation|notNull"
    );
    $this->_props =& $props;

    static $seek = array (
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


  function loadModeles($where = null, $order = null, $limit = null, $group = null, $leftjoin = null) {
    if (!isset($where["object_id"])) {
      $where["object_id"] = "IS NULL";
    }

    return parent::loadList($where, $order, $limit, $group, $leftjoin);
  }

  function loadDocuments($where = null, $order = null, $limit = null, $group = null, $leftjoin = null) {
    if (!isset($where["object_id"])) {
      $where["object_id"] = "IS NOT NULL";
    }
    
    return parent::loadList($where, $order, $limit, $group, $leftjoin);
  }
  
  function updateFormFields() {
    parent::updateFormFields();
    switch($this->type) {
      case "patient" :
        $this->_object_className = "CPatient";
        break;
      case "consultation" :
        $this->_object_className = "CConsultation";
        break;
      case "consultAnesth" :
        $this->_object_className = "CConsultAnesth";
        break;
      case "operation" :
        $this->_object_className = "COperation";
        break;
      case "hospitalisation" :
        $this->_object_className = "COperation";
        break;
      case "autre" :
        $this->_object_className = "COperation";
    }
    if($this->object_id == null)
      $this->_view = "Mod�le : ";
    else
      $this->_view = "Document : ";
    $this->_view .= $this->nom;
  }


  function loadRefsFwd() {

    // Objet
    $this->_ref_object = new $this->_object_className;
    if($this->object_id)
      $this->_ref_object->load($this->object_id);
      $this->_ref_object->loadRefsFwd();

    // Chirurgien
    $this->_ref_chir = new CMediusers;
    if($this->chir_id) {
      $this->_ref_chir->load($this->chir_id);
    } elseif($this->object_id) {
      switch($this->_object_className) {
        case "CConsultation" :
          $this->_ref_chir->load($this->_ref_object->_ref_plageconsult->chir_id);
          break;
        case "CConsultAnesth" :
          $this->_ref_object->_ref_consultation->loadRefsFwd();
          $this->_ref_chir->load($this->_ref_object->_ref_consultation->_ref_plageconsult->chir_id);
          break;
        case "COperation" :
          $this->_ref_chir->load($this->_ref_object->chir_id);
          break;
      }
    }

    // Fonction
    $this->_ref_function = new CFunctions;
    if($this->function_id)
      $this->_ref_function->load($this->function_id);
  }
  
  function getPerm($permType) {
    if(!($this->_ref_chir || $this->_ref_function) || !$this->_ref_object) {
      $this->loadRefsFwd();
    }
    if($this->_ref_chir->_id) {
      $can = $this->_ref_chir->getPerm($permType);
    } else {
      $can = $this->_ref_function->getPerm($permType);
    }
    if($this->_ref_object->_id) {
      $can = $can && $this->_ref_object->getPerm($permType);
    }
    return $can;
  }
}

?>