<?php /* $Id: $ */

/**
* @package Mediboard
* @subpackage dPplanningOp
* @version $Revision: $
* @author Thomas Despoix
*/

class CNaissance extends CMbObject {
  // DB Table key
  var $naissance_id = null;

  // DB References
  var $operation_id = null;

  // DB Fields
  var $nom_enfant      = null;
  var $prenom_enfant   = null;
  var $date_prevue     = null;
  var $date_reelle     = null;
  var $debut_grossesse = null;
      
  // DB References
  var $_ref_operation = null;
  
  function CNaissance() {
    $this->CMbObject("naissance","naissance_id");
    
    $this->loadRefModule(basename(dirname(__FILE__)));

    static $props = array (
      "operation_id"    => "ref|notNull",
      "nom_enfant"      => "str|notNull|confidential",
      "prenom_enfant"   => "str",
      "date_prevue"     => "date",
      "date_reelle"     => "dateTime",
      "debut_grossesse" => "date",
    );
    $this->_props =& $props;

    static $seek = array (
      "nom"    => "likeBegin",
      "prenom" => "likeBegin",
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
  
  function loadRefsFwd() {
    $this->_ref_operation = new COperation;
    $this->_ref_operation->load($this->operation_id);
  }
}
?>