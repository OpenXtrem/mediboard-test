<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPpatients
* @version $Revision$
* @author Romain Ollivier
*/

class CTraitement extends CMbObject {
  // DB Table key
  var $traitement_id = null;

  // DB References
  var $object_id    = null;
  var $object_class = null;

  // DB fields
  var $debut      = null;
  var $fin        = null;
  var $traitement = null;
  
  // Object References
  var $_ref_object = null;

  function CTraitement() {
    $this->CMbObject("traitement", "traitement_id");
    
    $this->loadRefModule(basename(dirname(__FILE__)));
  }

  function getSpecs() {
    return array (
      "object_id"    => "notNull ref",
      "object_class" => "notNull enum list|CPatient|CConsultAnesth",
      "debut"        => "date",
      "fin"          => "date moreEquals|debut",
      "traitement"   => "text"
    );
  }
  
  function getSeeks() {
    return array (
      "traitement" => "like"
    );
  }
  
  function loadRefsFwd() {
    // Objet
    if (class_exists($this->object_class)) {
      $this->_ref_object = new $this->object_class;
      if ($this->object_id)
        $this->_ref_object->load($this->object_id);
    } else {
      trigger_error("Enable to create instance of '$this->object_class' class", E_USER_ERROR);
    }
  }
}

?>