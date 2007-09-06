<?php

/**
* @package Mediboard
* @subpackage dPcabinet
* @version $Revision: $
* @author Alexis Granger
*/

class CBanque extends CMbObject {
  // DB Table key
  var $banque_id = null;

  // DB fields
  var $nom         = null;
  var $description = null;
  
  function CBanque() {
    $this->CMbObject("banque", "banque_id");
    
    $this->loadRefModule(basename(dirname(__FILE__)));
  }
  
  function getSpecs() {
    return array (
      "nom"         => "notNull str",
      "description" => "str"
    );
  }
  
  function getSeeks() {
    return array (
      "description" => "like"
    );
  }
  
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }

}

?>