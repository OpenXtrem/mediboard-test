<?php

/**
* @package Mediboard
* @subpackage dPlabo
* @version $Revision: $
* @author Romain Ollivier
*/

class CPackItemExamenLabo extends CMbObject {
  // DB Table key
  var $pack_item_examen_labo_id = null;
  
  // DB references
  var $pack_examens_labo_id = null;
  var $examen_labo_id       = null;
  
  // Forward references
  var $_ref_pack_examens_labo = null;
  var $_ref_examen_labo       = null;
  
  function CPackItemExamenLabo() {
    $this->CMbObject("pack_item_examen_labo", "pack_item_examen_labo_id");
    
    $this->loadRefModule(basename(dirname(__FILE__)));
  }
  
  function getSpecs() {
    return array (
      "pack_examens_labo_id" => "ref class|CPackExamensLabo notNull",
      "examen_labo_id"       => "ref class|CExamenLabo notNull"
    );
  }
  
  function updateFormFields() {
    $this->loadRefsFwd();
    $this->_shortview = $this->_ref_examen_labo->_shortview;
    $this->_view      = $this->_ref_examen_labo->_view;
  }
  
  function loadRefPack() {
    $this->_ref_pack_examens_labo = new CPackExamensLabo;
    $this->_ref_pack_examens_labo->load($this->pack_examens_labo_id);
  }

  function loadRefExamen() {
    $this->_ref_examen_labo = new CExamenLabo;
    $this->_ref_examen_labo->load($this->examen_labo_id);
  }
  
  function loadRefsFwd() {
    $this->loadRefPack();
    $this->loadRefExamen();
  }
}

?>