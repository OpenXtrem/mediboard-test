<?php

/**
 * $Id$
 *  
 * @category Messagerie
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision$
 * @link     http://www.mediboard.org
 */
 
/**
 * Description
 */
class CUserMessageDest extends CMbObject {
  /**
   * @var integer Primary key
   */
  public $usermessage_dest_id;

  public $user_message_id;
  public $to_user_id;            //destinataire
  public $from_user_id;

  public $datetime_read;
  public $datetime_sent;     // if !sent => draft
  public $archived;
  public $starred;

  public $_ref_message;
  public $_ref_user_to;
  public $_ref_user_from;


  /**
   * Initialize the class specifications
   *
   * @return CMbFieldSpec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "usermessage_dest";
    $spec->key    = "usermessage_dest_id";
    return $spec;
  }

  
  /**
   * Get collections specifications
   *
   * @return array
   */
  function getBackProps() {
    $backProps = parent::getBackProps();
    return $backProps;
  }

  /**
   * Load unread messages
   *
   * @param null $user_id user to load, null = current
   *
   * @return CUserMessageDest[]
   */
  static function loadNewMessages($user_id = null) {
    $dests = array();

    if (CModule::getActive("messagerie")) {
      $user = CMediusers::get($user_id);
      $dest = new self();
      $where = array();
      $where["to_user_id"] = " = '$user->_id'";
      $where["datetime_sent"] = " IS NOT NULL";
      $where["datetime_read"] = " IS NULL";
      /** @var CUserMessageDest[] $dests */
      $dests = $dest->loadList($where);
      foreach ($dests as $_dest) {
        $_dest->loadRefFwd();
      }
    }
    return $dests;
  }
  
  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["user_message_id"]   = "ref class|CUserMessage notNull cascade";
    $props["to_user_id"]        = "ref class|CMediusers notNull";
    $props["from_user_id"]      = "ref class|CMediusers notNull";
    $props["datetime_read"]     = "dateTime";
    $props["datetime_sent"]     = "dateTime";
    $props["archived"]          = "bool default|0";
    $props["starred"]           = "bool default|0";
    return $props;
  }

  /**
   * load the message
   *
   * @param bool $cache use cache
   *
   * @return CUserMessage
   */
  function loadRefMessage($cache = true) {
    return $this->_ref_message = $this->loadFwdRef("user_message_id", $cache);
  }

  /**
   * load the user TO
   *
   * @return CMediusers|null
   */
  function loadRefTo() {
    return $this->_ref_user_to = $this->loadFwdRef("to_user_id", true);
  }
  /**
   * load the user FROM
   *
   * @return CMediusers|null
   */
  function loadRefFrom() {
    return $this->_ref_user_from = $this->loadFwdRef("from_user_id", true);
  }


  /**
   * load the main refs
   *
   * @return null
   */
  function loadRefFwd() {
    $this->loadRefMessage();
    $this->loadRefFrom()->loadRefFunction();
    $this->loadRefTo()->loadRefFunction();
  }
}
