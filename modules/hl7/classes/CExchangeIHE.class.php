<?php

/**
 * Exchange IHE
 *  
 * @category IHE
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
 */

/**
 * Class CExchangeIHE 
 * Exchange IHE
 */

class CExchangeIHE extends CExchangeTabular {
  /**
   * @var array
   */
  static $messages = array(
    "PAM"    => "CPAM",
    "PAM_FR" => "CPAMFR",
    "DEC"    => "CDEC",
    "SWF"    => "CSWF",
    "PDQ"    => "CPDQ"
  );
  
  // DB Table key
  /**
   * @var null
   */
  var $exchange_ihe_id = null;

  /**
   * @var null
   */
  var $code            = null;
  
  /**
   * @var CHL7v2Message
   */
  var $_message_object = null;

  /**
   * Initialize object specification
   *
   * @return CMbObjectSpec the spec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->loggable = false;
    $spec->table = 'exchange_ihe';
    $spec->key   = 'exchange_ihe_id';
    
    return $spec;
  }

  /**
   * Get properties specifications as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    
    $props["sender_class"]  = "enum list|CSenderFTP|CSenderSOAP|CSenderMLLP|CSenderFileSystem show|0";
    
    $props["receiver_id"]   = "ref class|CReceiverIHE"; 
    $props["object_class"]  = "enum list|CPatient|CSejour|COperation|CAffectation|COperation|CConsultation show|0";
    $props["code"]          = "str";
    
    $props["_message"]      = "er7";
    $props["_acquittement"] = "er7";

    return $props;
  }

  /**
   * Handle exchange
   *
   * @return null|string|void
   */
  function handle() {
    return COperatorIHE::event($this);
  }

  /**
   * Get exchange IHE families
   *
   * @return array Families
   */
  function getFamily() {
    return self::$messages;
  }

  /**
   * Check if data is well formed
   *
   * @param string        $data  Data
   * @param CInteropActor $actor Actor
   *
   * @return bool|void
   */
  function isWellFormed($data, CInteropActor $actor = null) {
    try {
      $sender = ($actor ? $actor : $this->loadRefSender());
      $strict = $this->getConfigs($sender->_guid)->strict_segment_terminator;
      
      return CHL7v2Message::isWellFormed($data, $strict);
    }
    catch (Exception $e) {
      return false;
    }
  }

  /**
   * Get HL7 config for one actor
   *
   * @param string $actor_guid Actor GUID
   *
   * @return CHL7Config|void
   */
  function getConfigs($actor_guid) {
    list($sender_class, $sender_id) = explode("-", $actor_guid);
    
    $sender_hl7_config = new CHL7Config();
    $sender_hl7_config->sender_class = $sender_class;
    $sender_hl7_config->sender_id    = $sender_id;
    $sender_hl7_config->loadMatchingObject();
    
    return $this->_configs_format = $sender_hl7_config;
  }

  /**
   * Check if data is understood
   *
   * @param string        $data  Data
   * @param CInteropActor $actor Actor
   *
   * @return bool|void
   */
  function understand($data, CInteropActor $actor = null) {
    if (!$this->isWellFormed($data, $actor)) {
      return false;
    }

    $hl7_message = $this->parseMessage($data, false, $actor);

    $hl7_message_evt = "CHL7Event$hl7_message->event_name";

    if ($hl7_message->i18n_code) {
      $hl7_message_evt = $hl7_message_evt."_".$hl7_message->i18n_code;
    }
    
    foreach ($this->getFamily() as $_message) {
      $message_class = new $_message;
      $evenements = $message_class->getEvenements();
      if (in_array($hl7_message_evt, $evenements)) {
        if (!$hl7_message->i18n_code) {
          $this->_family_message_class = $_message;
          $this->_family_message       = CHL7Event::getEventVersion($hl7_message->version, $hl7_message->event_name);
        }
        else {
          $this->_family_message_class = $_message;
          $this->_family_message       = CHL7Event::getEventVersion($hl7_message->version, $hl7_message->getI18NEventName());
        }

        return true;
      }
    }
  }

  /**
   * Get exchange errors
   *
   * @return bool|void
   */
  function getErrors() {
  }

  /**
   * Get Message
   *
   * @return CHL7v2Message|void
   */
  function getMessage() {
    if ($this->_message !== null) {
      $hl7_message = $this->parseMessage($this->_message);
      
      $this->_doc_errors_msg   = !$hl7_message->isOK(CHL7v2Error::E_ERROR);
      $this->_doc_warnings_msg = !$hl7_message->isOK(CHL7v2Error::E_WARNING);
      
      $this->_message_object = $hl7_message;

      return $hl7_message;
    }
  }

  /**
   * Parse HL7 message
   *
   * @param string $string     Data
   * @param bool   $parse_body Parse only header ?
   * @param null   $actor      Actor
   *
   * @return CHL7v2Message
   */
  function parseMessage($string, $parse_body = true, $actor = null) {
    $hl7_message = new CHL7v2Message();
    
    if (!$this->_id && $actor) {
      $this->sender_id    = $actor->_id;
      $this->sender_class = $actor->_class;
    }
    
    if ($this->sender_id) {
      $this->loadRefSender();
      $this->getConfigs($this->_ref_sender->_guid);
      $hl7_message->strict_segment_terminator = ($this->_configs_format->strict_segment_terminator == 1);
    }

    $hl7_message->parse($string, $parse_body);
    
    return $hl7_message;
  }

  /**
   * Get HL7 acquittement
   *
   * @return CHL7v2Message|void
   */
  function getACK() {
    if ($this->_acquittement === null) {
      return;
    }

    $hl7_ack = new CHL7v2Message();
    $hl7_ack->parse($this->_acquittement);

    $this->_doc_errors_ack   = !$hl7_ack->isOK(CHL7v2Error::E_ERROR);
    $this->_doc_warnings_ack = !$hl7_ack->isOK(CHL7v2Error::E_WARNING);

    return $hl7_ack;
  }

  /**
   * Get message encoding
   *
   * @return string|void
   */
  function getEncoding(){
    return $this->_message_object->getEncoding();
  }

  /**
   * Populate exchange
   *
   * @param CExchangeDataFormat $data_format Data format
   * @param CHL7Event           $event       Event HL7
   *
   * @return string|void
   */
  function populateExchange(CExchangeDataFormat $data_format, CHL7Event $event) {
    $this->group_id        = $data_format->group_id;
    $this->sender_id       = $data_format->sender_id;
    $this->sender_class    = $data_format->sender_class;
    $this->version         = $event->message->extension ? $event->message->extension : $event->message->version;
    $this->nom_fichier     = ""; 
    $this->type            = $event->profil;
    $this->sous_type       = $event->transaction;
    $this->code            = $event->code;
    $this->_message        = $data_format->_message;
  }

  /**
   * Populate error exchange
   *
   * @param CHL7Acknowledgment $ack   Acknowledgment
   * @param CHL7Event          $event Event HL7
   *
   * @return string|void
   */
  function populateErrorExchange(CHL7Acknowledgment $ack = null, CHL7Event $event = null) {
    if ($ack) {
      $msgAck = $ack->event_ack->msg_hl7;
      $this->_acquittement       = $ack->event_ack->msg_hl7;;
      /* @todo Comment g�rer ces informations ? */
      $this->statut_acquittement = $ack->ack_code;
      $this->acquittement_valide = $ack->event_ack->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;
    }
    else {
      $this->message_valide      = $event->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;
      $this->date_production     = mbDateTime();
      $this->date_echange        = mbDateTime();
    }

    $this->store();
  }

  /**
   * Populate ACK exchange
   *
   * @param CHL7Acknowledgment $ack      Acknowledgment
   * @param CMbObject          $mbObject Object
   *
   * @return string
   */
  function populateExchangeACK(CHL7Acknowledgment $ack, $mbObject = null) {
    $msgAck = $ack->event_ack->msg_hl7;

    $this->statut_acquittement = $ack->ack_code;
    $this->acquittement_valide = $ack->event_ack->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;

    if ($mbObject && $mbObject->_id) {
      $this->setObjectIdClass($mbObject);
      $this->setIdPermanent($mbObject);
    }

    $this->_acquittement = $msgAck;
    $this->date_echange = mbDateTime();
    $this->store();
    
    return $msgAck;
  }

  /**
   * Generate 'Application Accept' acknowledgment
   *
   * @param CHL7Acknowledgment $ack            Acknowledgment
   * @param array              $mb_error_codes Mediboard errors codes
   * @param null               $comments       Comments
   * @param CMbObject          $mbObject       Object
   *
   * @return string
   */
  function setAckAA(CHL7Acknowledgment $ack, $mb_error_codes, $comments = null, CMbObject $mbObject = null) {
    $ack->generateAcknowledgment("AA", $mb_error_codes, "0", "I", $comments, $mbObject);
        
    return $this->populateExchangeACK($ack, $mbObject);
  }

  /**
   * Generate 'Application Reject' acknowledgment
   *
   * @param CHL7Acknowledgment $ack            Acknowledgment
   * @param array              $mb_error_codes Mediboard errors codes
   * @param string             $comments       Comments
   * @param CMbObject          $mbObject       Object
   *
   * @return string
   */
  function setAckAR(CHL7Acknowledgment $ack, $mb_error_codes, $comments = null, CMbObject $mbObject = null) {
    $ack->generateAcknowledgment("AR", $mb_error_codes, "207", "E", $comments, $mbObject);

    return $this->populateExchangeACK($ack, $mbObject);               
  }

  /**
   * Generate 'Patient Demographics Response' acknowledgment
   *
   * @param CHL7v2PatientDemographicsAndVisitResponse $ack        Acknowledgment
   * @param array                                     $objects    Objects
   * @param string                                    $QPD8_error QPD-8 that contained the unrecognized domain
   *
   * @return string
   */
  function setPDRAA(CHL7v2PatientDemographicsAndVisitResponse $ack, $objects = array(), $QPD8_error = null) {
    $ack->generateAcknowledgment("AA", "0", "I", $objects);

    return $this->populateExchangeACK($ack);
  }

  /**
   * Generate 'Patient Demographics Response' acknowledgment
   *
   * @param CHL7v2PatientDemographicsAndVisitResponse $ack        Acknowledgment
   * @param array                                     $objects    Objects
   * @param string                                    $QPD8_error QPD-8 that contained the unrecognized domain
   *
   * @return string
   */
  function setPDRAE(CHL7v2PatientDemographicsAndVisitResponse $ack, $objects = null, $QPD8_error = null) {
    $ack->generateAcknowledgment("AE", "204", "E", null, $QPD8_error);

    return $this->populateExchangeACK($ack);
  }

  /**
   * Get exchange observation
   *
   * @param bool $display_errors Display errors ?
   *
   * @return array|void
   */
  function getObservations($display_errors = false) {
    if ($this->_acquittement) {
      $acq = $this->_acquittement;
      
      $this->_observations = array();
      
      if (strpos($acq, "UNICODE") !== false) {
        $acq = utf8_decode($acq);
      }
      
      // quick regex
      // ERR|~~~207^0^0^E201||207|E|code^libelle|||commentaire
      $pattern = "/ERR\|[^\|]*\|[^\|]*\|[^\|]*\|([^\|]*)\|([^\^]+)\^([^\|]+)\|[^\|]*\|[^\|]*\|([^\r\n\|]*)/ms";
      if (preg_match_all($pattern, $acq, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
          if ($match[1] == "E") {
            $this->_observations[$match[2]] = array(
              "code"        => $match[2],
              "libelle"     => $match[3],
              "commentaire" => strip_tags($match[4]),
            );
          }
        }

        return $this->_observations;
      }
    }
  }

  /**
   * Load view
   *
   * @return array|void
   */
  function loadView() {
    parent::loadView();
    
    $this->getObservations();
  }
}