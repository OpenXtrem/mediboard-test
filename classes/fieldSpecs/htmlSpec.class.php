<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage classes
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

CAppUI::requireSystemClass("mbFieldSpec");

class CHtmlSpec extends CMbFieldSpec {

  function getSpecType() {
    return("html");
  }
  
  function checkProperty($object){
    $fieldName = $this->fieldName;
    $propValue = $object->$fieldName;
    
    // Root node surrounding
    $source = utf8_encode("<div>$propValue</div>");
    
    // Entity purge
    $source = preg_replace("/&\w+;/i", "", $source);
    
    // Escape warnings, returns false if really invalid
    if (!@DOMDocument::loadXML($source)) {
      return "Le document HTML est mal form�, ou la requ�te n'a pas pu se terminer.";
    }
    return null;
  }
  
  function sample(&$object, $consistent = true){
    parent::sample($object, $consistent);
    $fieldName = $this->fieldName;
    $propValue =& $object->$fieldName;
    
    $propValue = "Document confidentiel";
  }
  
  function getFormHtmlElement($object, $params, $value, $className){
    return $this->getFormElementTextarea($object, $params, $value, $className);
  }
  
  function getDBSpec(){
    return "MEDIUMTEXT";
  }
}

?>