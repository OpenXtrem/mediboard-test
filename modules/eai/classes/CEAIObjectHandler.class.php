<?php /* $Id $ */

/**
 * @package Mediboard
 * @subpackage sip
 * @version $Revision: 12588 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

class CEAIObjectHandler extends CMbObjectHandler {
  static $handled               = array ();
  var $_eai_initiateur_group_id = null;

  static function isHandled(CMbObject $mbObject) {
    return in_array($mbObject->_class, self::$handled);
  }
  
  function sendFormatAction($action, CMbObject $mbObject) {
    if (!$action) {
      return;
    }
    
    // Parcours des receivers actifs
    $receiver = new CInteropReceiver(); 
    $receivers = $receiver->getObjects();
    foreach ($receivers as $_receivers) {
      if (!$_receivers) {
        continue;
      }
      foreach ($_receivers as $_receiver) { 
        if (!$format_object_handler_classname = $_receiver->getFormatObjectHandler($this)) {
          continue;
        }
        
        $_receiver->loadConfigValues();
        $_receiver->loadRefsMessagesSupported();
        // Destinataire non actif on envoi pas
        if (!$_receiver->actif) {
          continue;
        }
        
        // Affectation du receiver � l'objet
        $mbObject->_receiver = $_receiver;
        
        // R�cup�re le handler du format
        $format_object_handler = new $format_object_handler_classname;
        // Envoi l'action au handler du format
        try {
          $format_object_handler->$action($mbObject);
        } 
        catch (Exception $e) {
          CAppUI::setMsg($e->getMessage(), UI_MSG_ERROR);
        }
      }
    }
  }
  
  function onBeforeStore(CMbObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }
    
    if (isset($mbObject->_eai_initiateur_group_id)) {
      $this->_eai_initiateur_group_id = $mbObject->_eai_initiateur_group_id;
    }
  }
  
  function onAfterStore(CMbObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }
    
    $mbObject->_eai_initiateur_group_id = $this->_eai_initiateur_group_id;
    
    if (!$mbObject->_ref_last_log) {
      return false;
    }
    
    // Cas d'une fusion
    if ($mbObject->_merging) {
      return false;
    }
    if ($mbObject->_forwardRefMerging) {
      return false;
    }
    
    return true;
  }

  function onBeforeMerge(CMbObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }
    
    if (!$mbObject->_merging) {
      return false;
    }
    
    return true;
  }
  
  function onAfterMerge(CMbObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }
    
    if (!$mbObject->_merging) {
      return false;
    }
    
    return true;
  }
  
  function onBeforeDelete(CMbObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }
    
    return true;
  }
  
  function onAfterDelete(CMbObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }
    
    return true;
  }
}
?>