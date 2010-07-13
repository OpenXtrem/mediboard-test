<?php /* $Id: compteRendu.class.php 9309 2010-06-28 16:17:19Z flaviencrochard $ */
  
/**
 * @package Mediboard
 * @subpackage system
 * @version $Revision: 8779 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

class CContentXML extends CMbObject {
  // DB Table key
  var $content_id = null;
  
  // DB Fields
  var $content   = null;
  var $import_id = null;

  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'content_xml';
    $spec->key   = 'content_id';
    return $spec;
  }
  
  function getProps() { 
    $specs = parent::getProps();
    $specs["content"]   = "xml";
    $specs["import_id"] = "ref class|CMbObject";
    
    return $specs;
  }
}
