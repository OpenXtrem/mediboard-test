<?php /* $Id: message.class.php 8208 2010-03-04 19:14:03Z lryo $ */

/**
 * @package Mediboard
 * @subpackage system
 * @version $Revision: 8208 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

/**
 * View sender source class. 
 * @abstract Encapsulate an FTP source for view sending purposes only
 */
class CViewSenderSource extends CMbObject {
  // DB Table key
  var $source_id = null; 
  
  // DB fields
  var $name      = null;
  var $libelle   = null;
  var $group_id  = null;
  var $actif     = null;
  var $archive   = null;
  
  // Form fields
  var $_type_echange   = null;
  var $_ref_source_ftp = null;
  var $_reachable      = null;
  
  // Distant refs
  var $_ref_senders = null;
      
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "view_sender_source";
    $spec->key   = "source_id";
    $spec->uniques["name"] = array("name");
    return $spec;
  }

  function getProps() {
    $props = parent::getProps();
    $props["name"]     = "str notNull";
    $props["libelle"]  = "str";
    $props["group_id"] = "ref notNull class|CGroups autocomplete|text";
    $props["actif"]    = "bool notNull";
    $props["archive"]  = "bool notNull";
    
    $props["_reachable"] = "bool";
    return $props;
  }
  
  function getBackProps() {
    return parent::getBackProps() + array(
      "senders_link" => "CSourceToViewSender source_id",
    );
  }
  
  function updateFormFields() {
    parent::updateFormFields();
    
    $this->_type_echange = $this->_class;
    $this->_view         = $this->name . ($this->libelle ? " - $this->libelle" : "");
  }

  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", 1);
  }
  
  function loadRefSourceFTP() {
    return $this->_ref_source_ftp = CExchangeSource::get("$this->_guid", "ftp", true, $this->_type_echange);
  }
  
  function loadRefSenders() {
    $senders_link = $this->loadBackRefs("senders_link");
    return $this->_ref_senders = CMbObject::massLoadFwdRef($senders_link, "sender_id");
  }
}

?>