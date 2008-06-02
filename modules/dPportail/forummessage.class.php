<?php /* $Id: $ */

/**
 * @package Mediboard
 * @subpackage dPportail
 * @version $Revision: $
 * @author Fabien	
 */

/**
 * The CForumMessage class
 */
  
class CForumMessage extends CMbObject {
    // DB Fields
    var $body               = null;
    var $date               = null;
    var $user_id            = null;
    var $forum_thread_id    = null;
    
    // References
    var $_ref_forum_thread  = null;
    var $_ref_user          = null;

    function CForumMessage() {
        $this->CMbObject('forum_message', 'forum_message_id'); 
        $this->loadRefModule(basename(dirname(__FILE__)));
    }

    function getSpecs() {
        return array (
            'body'            => 'notNull html',
            'date'            => 'notNull dateTime',
            'user_id'         => 'notNull ref class|CMediusers',
            'forum_thread_id' => 'notNull ref class|CForumThread'
        );
    }

    function updateFormFields() {
        parent::updateFormFields();
        $this->_view = substr($this->body, 0, 20) . '...';
    }
    
    function loadRefsFwd(){
        $this->_ref_user = new CMediusers();
        $this->_ref_user->load($this->user_id);
        $this->_ref_user->loadRefFunction();
        $this->_ref_user->loadRefDiscipline();
        
        $this->_ref_forum_thread = new CForumThread();
        $this->_ref_forum_thread->load($this->forum_thread_id);
    }
    
}
?>
