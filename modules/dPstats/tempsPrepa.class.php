<?php /* $Id: $ */

/**
 *	@package Mediboard
 *	@subpackage dPmateriel
 *	@version $Revision: $
 *  @author Sébastien Fillonneau
 */

/**
 * The CTempsPrepa class
 */
class CTempsPrepa extends CMbObject {
  // DB Table key
  var $temps_prepa_id = null;
  
  // DB Fields
  var $chir_id     = null;
  var $nb_prepa    = null;
  var $nb_plages   = null;
  var $duree_moy   = null;
  var $duree_ecart = null;
  
  // Object References
  var $_ref_praticien = null;

  
  function CTempsPrepa() {
    $this->CMbObject("temps_prepa", "temps_prepa_id");
    
    $this->loadRefModule(basename(dirname(__FILE__)));

    static $props = array (
      "temps_prepa_id" => "ref",
      "chir_id"        => "ref",
      "nb_plage"       => "num|pos",
      "nb_prepa"       => "num|pos",
      "duree_moy"      => "time",
      "duree_ecart"    => "time"
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
  
  function loadRefsFwd(){ 
    $this->_ref_praticien = new CMediusers;
    $this->_ref_praticien->load($this->chir_id);

  }
}
?>