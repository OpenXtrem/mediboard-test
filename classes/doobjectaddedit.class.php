<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage classes
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

class CDoObjectAddEdit {
  var $className           = null;
  var $objectKeyGetVarName = null;
  var $createMsg           = null;
  var $modifyMsg           = null;
  var $deleteMsg           = null;
  var $refTab              = null;
  var $redirect            = null;
  var $redirectStore       = null;
  var $redirectError       = null;
  var $redirectDelete      = null;
  var $isNotNew            = null;
  var $ajax                = null;
  var $callBack            = null;
  var $suppressHeaders     = null;
  var $_obj                = null;
  var $_objBefore          = null;
  var $_logIt              = null;

  function CDoObjectAddEdit($className, $objectKeyGetVarName = null) {
    global $m;

    $this->className           = $className;
    $this->postRedirect        = null;
    $this->redirect            = "m={$m}";
    $this->redirectStore       = null;
    $this->redirectError       = null;
    $this->redirectDelete      = null;

    $this->createMsg           = CAppUI::tr("$className-msg-create");
    $this->modifyMsg           = CAppUI::tr("$className-msg-modify");
    $this->deleteMsg           = CAppUI::tr("$className-msg-delete");
    
    $this->refTab              =& $_POST;

    $this->_logIt              = true;
    $this->_obj                = new $this->className();
    $this->_objBefore          = new $this->className();
    
    $this->objectKeyGetVarName = $objectKeyGetVarName ? $objectKeyGetVarName : $this->_obj->_spec->key;
  }

  function doBind() {
    $this->ajax            = CMbArray::extract($this->refTab, "ajax");
    $this->suppressHeaders = CMbArray::extract($this->refTab, "suppressHeaders");
    $this->callBack        = CMbArray::extract($this->refTab, "callback");
    $this->postRedirect    = CMbArray::extract($this->refTab, "postRedirect");
    if($this->postRedirect) {
      $this->redirect = $this->postRedirect;
    }
    
    // Object binding
    $this->_obj->bind($this->refTab);
    
    $this->_objBefore->load($this->_obj->_id);
  }

  function doDelete() {
    if ($this->_obj->_purge) {
      set_time_limit(120);
      if ($msg = $this->_obj->purge()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR );
        if ($this->redirectError) {
          $this->redirect =& $this->redirectError;
        }
      }
      else {
        CValue::setSession($this->objectKeyGetVarName);
        CAppUI::setMsg(CAppUI::tr("msg-purge"), UI_MSG_ALERT);
        if ($this->redirectDelete) {
          $this->redirect =& $this->redirectDelete;
        }
      }
      return;
    }
    
    if ($msg = $this->_obj->delete()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
      if ($this->redirectError) {
        $this->redirect =& $this->redirectError;
      }
    } 
    else {
      CValue::setSession($this->objectKeyGetVarName);
      CAppUI::setMsg($this->deleteMsg, UI_MSG_ALERT);
      if ($this->redirectDelete) {
        $this->redirect =& $this->redirectDelete;
      }
    }
  }

  function doStore() {
    if ($msg = $this->_obj->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR );
      if ($this->redirectError) {
        $this->redirect =& $this->redirectError;
      }
    } 
    else {
      $id = $this->objectKeyGetVarName;
      CValue::setSession($id, $this->_obj->$id);
      $this->isNotNew = @$this->refTab[$this->objectKeyGetVarName];
      CAppUI::setMsg($this->isNotNew ? $this->modifyMsg : $this->createMsg, UI_MSG_OK);
      if ($this->redirectStore) {
        $this->redirect =& $this->redirectStore;
      }
    }
  }

  function doRedirect() {
    if ($this->redirect === null) {
      return;
    }
    
    // Cas ajax
    if ($this->ajax) {
      $idName = $this->objectKeyGetVarName;
      $idValue = $this->_obj->$idName;
      $callBack = $this->callBack;
      echo CAppUI::getMsg();
      if ($callBack) {
        echo "\n<script type='text/javascript'>$callBack($idValue);</script>";
      }
      CApp::rip();
    }

    // Cas normal
    CAppUI::redirect($this->redirect);
    
  }

  function doIt() {
    $this->doBind();
    if (intval(CValue::read($this->refTab, 'del'))) {
      $this->doDelete();
    } else {
      $this->doStore();
    }
    $this->doRedirect();
  }

  /**
   * Sets a error messages and redirects
   * @param string $msg 
   */
  function errorRedirect($msg) {
	  CAppUI::setMsg($msg, UI_MSG_ERROR);
	  $this->redirect =& $this->redirectError;
	  $this->doRedirect();
  }
}
