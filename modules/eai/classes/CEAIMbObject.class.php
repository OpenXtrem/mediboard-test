<?php

/**
 * MbObject utilities EAI
 *  
 * @category EAI
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
 */

/**
 * Class CEAIMbObject
 * MbObject utilities EAI
 */

class CEAIMbObject {
  static function getModifiedFields(CMbObject $object) {
    $modified_fields = "";

    if ($object->_ref_current_log && is_array($object->_ref_current_log->_fields)) {
      $fields = $object->_ref_current_log->_fields;
      
      if (count($fields) > 1) {
        $modified_fields = $fields[0];
      }
      foreach ($fields as $_field) {
        $modified_fields .= "$_field, ";
      }
    } 
    
    return $modified_fields;
  }
  
  
  static function getComment(CMbObject $object, CMbObject $otherObject = null) {
    $modified_fields = self::getModifiedFields($object);
    
    if (!$object->_ref_current_log) {
      return "";
    }

    if ($object instanceof CPatient) {
      switch ($object->_ref_current_log->type) {
         // Enregistrement du patient
        case "create" :
          $comment = "Le patient a �t� cr�� dans Mediboard avec l'IC $object->_id.";
          break;
         // Modification du patient
        case "store" :
          $comment = "Le patient avec l'IC '$object->_id' dans Mediboard a �t� modifi�.";
          $comment .= ($modified_fields) ? "Les champs mis � jour sont les suivants : $modified_fields." : null;
          break;
        // Fusion des patients
        case "merge" : 
          $comment  = "Le patient avec l'IC '$object->_id' a �t� fusionn� avec le patient dont l'IC est '$otherObject->_id'.";
          break;
        default :
          $comment = "";
      }     
      
      return $comment;
    }
    
    if ($object instanceof CSejour) {
      switch ($object->_ref_current_log->type) {
         // Enregistrement du s�jour
        case "create" :
          $comment = "Le s�jour a �t� cr�� dans Mediboard avec l'IC $object->_id.";
          break;
         // Modification du s�jour
        case "store" :
          $comment = "Le s�jour avec l'IC '$object->_id' dans Mediboard a �t� modifi�.";
          $comment .= ($modified_fields) ? "Les champs mis � jour sont les suivants : $modified_fields." : null;
          break;
        // Fusion des s�jours
        case "merge" : 
          $comment  = "Le s�jour avec l'IC '$object->_id' a �t� fusionn� avec le s�jour dont l'IC est '$otherObject->_id'.";
        default :
          $comment = "";
      }     
      
      return $comment;
    }
    
    if ($object instanceof CIdSante400) {
      if (!$object->_id) {
        return "";
      }
      
      return "L'IPP/NDA cr�� est : $object->id400";
    }
  }
}

?>