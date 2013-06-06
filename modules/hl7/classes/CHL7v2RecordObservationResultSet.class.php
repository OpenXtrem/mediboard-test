<?php
/**
 * $Id$
 * 
 * @package    Mediboard
 * @subpackage hl7
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version    $Revision$
 */

/**
 * Class CHL7v2RecordObservationResultSet 
 * Record observation result set, message XML
 */
class CHL7v2RecordObservationResultSet extends CHL7v2MessageXML {
  static $event_codes = array("R01");

  public $codes = array();

  /**
   * Get data nodes
   *
   * @return array Get nodes
   */
  function getContentNodes() {
    $data = $patient_results = array();
    
    $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
    $sender         = $exchange_hl7v2->_ref_sender;
    $sender->loadConfigValues();
    
    $patient_results = $this->queryNodes("ORU_R01.PATIENT_RESULT", null, $varnull, true);
    
    foreach ($patient_results as $_patient_result) {
      // Patient
      $oru_patient = $this->queryNode("ORU_R01.PATIENT", $_patient_result, $varnull);
      $PID = $this->queryNode("PID", $oru_patient, $data, true);
      $data["personIdentifiers"] = $this->getPersonIdentifiers("PID.3", $PID, $sender);
      
      // Venue
      $oru_visit = $this->queryNode("ORU_R01.VISIT", $oru_patient, $varnull);
      $PV1 = $this->queryNode("PV1", $oru_visit, $data, true);
      if ($PV1) {
        $data["admitIdentifiers"] = $this->getAdmitIdentifiers($PV1, $sender);
      }
      
      // Observations
      $order_observations = $this->queryNodes("ORU_R01.ORDER_OBSERVATION", $_patient_result, $varnull);
      $data["observations"] = array();
      foreach ($order_observations as $_order_observation) {
        $tmp = array();
        // OBR
        $this->queryNode("OBR", $_order_observation, $tmp, true);
        
        // OBXs
        $oru_observations = $this->queryNodes("ORU_R01.OBSERVATION", $_order_observation, $varnull);
        foreach ($oru_observations as $_oru_observation) {
          $this->queryNodes("OBX", $_oru_observation, $tmp, true);
        }
        
        $data["observations"][] = $tmp;
      }
    }
    
    return $data;
  }

  /**
   * Handle event
   *
   * @param CHL7Acknowledgment $ack     Acknowledgement
   * @param CPatient           $patient Person
   * @param array              $data    Nodes data
   *
   * @return null|string
   */
  function handle(CHL7Acknowledgment $ack, CPatient $patient, $data) {
    // Traitement du message des erreurs
    $comment = "";
    $codes   = array();
    $object  = null;
    
    $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
    $exchange_hl7v2->_ref_sender->loadConfigValues();
    $sender         = $exchange_hl7v2->_ref_sender;

    $patientPI = CValue::read($data['personIdentifiers'], "PI");
    $venueAN   = CValue::read($data['personIdentifiers'], "AN");

    if (!$patientPI) {
      return $exchange_hl7v2->setAckAR($ack, "E007", null, $patient);
    }
   
    $IPP = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $patientPI);
    // Patient non retrouv� par son IPP
    if (!$IPP->_id) {
      return $exchange_hl7v2->setAckAR($ack, "E105", null, $patient);
    }
    $patient->load($IPP->object_id);

    // R�cup�ration des observations
    foreach ($data["observations"] as $_observation) {
      // R�cup�ration de la date du relev�
      $observation_dt = $this->getOBRObservationDateTime($_observation["OBR"]);

      $NDA = null;
      if ($venueAN) {
        $NDA = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $venueAN);
      }

      // S�jour non retrouv� par son NDA
      if ($NDA && $NDA->_id) {
        /** @var CSejour $sejour */
        $sejour = $NDA->loadTargetObject();
      }
      else {
        $where = array(
          "patient_id" => "= '$patient->_id'",
          "annule"     => "= '0'",
        );
        $sejours = CSejour::loadListForDate(CMbDT::date($observation_dt), $where, null, 1);
        $sejour = reset($sejours);

        if (!$sejour) {
          return $exchange_hl7v2->setAckAR($ack, "E205", null);
        }
      }

      // R�cup�ration de l'op�ration courante � la date du relev�
      $operation = $sejour->getCurrOperation($observation_dt);

      if (!$operation->_id) {
        return $exchange_hl7v2->setAckAR($ack, "E301", null, $operation);
      }

      foreach ($_observation["OBX"] as $_OBX) {
        // OBX.2 : Value type
        $value_type = $this->getOBXValueType($_OBX);

        switch ($value_type) {
          // Reference Pointer to External Report
          case "RP" :
            if (!$this->getReferencePointerToExternalReport($_OBX, $operation)) {
              return $exchange_hl7v2->setAckAR($ack, $this->codes, null, $operation);
            }

            break;

          // Encapsulated PDF
          case "ED" :
            if (!$this->getEncapsulatedPDF($_OBX, $patient, $operation)) {
              return $exchange_hl7v2->setAckAR($ack, $this->codes, null, $operation);
            }

            break;

          // Pulse Generator and Lead Observation Results
          case "ST" :  case "CWE" :  case "DTM" :  case "NM" :  case "SN" :
            if (!$this->getPulseGeneratorAndLeadObservationResults($_OBX, $patient, $operation)) {
              return $exchange_hl7v2->setAckAR($ack, $this->codes, null, $operation);
            }

            break;

          // Not supported
          default :
            return $exchange_hl7v2->setAckAR($ack, "E302", null, $operation);
        }
      }
    }
    
    return $exchange_hl7v2->setAckAA($ack, $this->codes, $comment, $object);
  }

  /**
   * Get observation date time
   *
   * @param DOMNode $node DOM node
   *
   * @return string
   */
  function getOBRObservationDateTime(DOMNode $node) {
    return $this->queryTextNode("OBR.7", $node);
  }

  /**
   * Get value type
   *
   * @param DOMNode $node DOM node
   *
   * @return string
   */
  function getOBXValueType(DOMNode $node) {
    return $this->queryTextNode("OBX.2", $node);
  }

  /**
   * Get observation date time
   *
   * @param DOMNode $node DOM node
   *
   * @return string
   */
  function getOBXObservationDateTime(DOMNode $node) {
    return $this->queryTextNode("OBX.14/TS.1", $node);
  }

  /**
   * Get result status
   *
   * @param DOMNode $node DOM node
   *
   * @return string
   */
  function getOBXResultStatus(DOMNode $node) {
    return $this->queryTextNode("OBX.11", $node);
  }

  /**
   * Get observation date time
   *
   * @param DOMNode            $node   DOM node
   * @param CObservationResult $result Result
   *
   * @return string
   */
  function mappingObservationResult(DOMNode $node, CObservationResult $result) {
    // OBX-3: Observation Identifier
    $this->getObservationIdentifier($node, $result);
    
    // OBX-6: Units
    $this->getUnits($node, $result);
    
    // OBX-5: Observation Value (Varies)
    $result->value = $this->getObservationValue($node);
    
    // OBX-11: Observation Result Status
    $result->status =$this->getObservationResultStatus($node);
  }

  /**
   * Get observation identifier
   *
   * @param DOMNode            $node   DOM node
   * @param CObservationResult $result Result
   *
   * @return string
   */
  function getObservationIdentifier(DOMNode $node, CObservationResult $result) {
    $identifier    = $this->queryTextNode("OBX.3/CE.1", $node);
    $text          = $this->queryTextNode("OBX.3/CE.2", $node);
    $coding_system = $this->queryTextNode("OBX.3/CE.3", $node);
    
    $value_type = new CObservationValueType();
    $result->value_type_id = $value_type->loadMatch($identifier, $coding_system, $text);
  }

  /**
   * Get unit
   *
   * @param DOMNode            $node   DOM node
   * @param CObservationResult $result Result
   *
   * @return string
   */
  function getUnits(DOMNode $node, CObservationResult $result) {
    $identifier    = $this->queryTextNode("OBX.6/CE.1", $node);
    $text          = $this->queryTextNode("OBX.6/CE.2", $node);
    $coding_system = $this->queryTextNode("OBX.6/CE.3", $node);
    
    $unit_type = new CObservationValueUnit();
    $result->unit_id = $unit_type->loadMatch($identifier, $coding_system, $text);
  }

  /**
   * Get observation value
   *
   * @param DOMNode $node DOM node
   *
   * @return string
   */
  function getObservationValue(DOMNode $node) {
    return $this->queryTextNode("OBX.5", $node);
  }

  /**
   * Get observation result status
   *
   * @param DOMNode $node DOM node
   *
   * @return string
   */
  function getObservationResultStatus(DOMNode $node) {
    return $this->queryTextNode("OBX.11", $node);
  }

  /**
   * OBX Segment pulse generator and lead observation results
   *
   * @param DOMNode    $OBX       DOM node
   * @param CPatient   $patient   Person
   * @param COperation $operation Op�ration
   *
   * @return bool
   */
  function getPulseGeneratorAndLeadObservationResults(DOMNode $OBX, CPatient $patient, COperation $operation) {
    $result_set = new CObservationResultSet();

    $dateTimeOBX = $this->getOBXObservationDateTime($OBX);
    if ($dateTimeOBX) {
      $result_set->patient_id    = $patient->_id;
      $result_set->context_class = "COperation";
      $result_set->context_id    = $operation->_id;
      $result_set->datetime      = CMbDT::dateTime($dateTimeOBX);
      if ($msg = $result_set->store()) {
        $this->codes[] = "E302";
      }
    }

    // Traiter le cas o� ce sont des param�tres sans r�sultat utilisable
    if ($this->getOBXResultStatus($OBX) === "X") {
      return true;
    }

    $result = new CObservationResult();
    $result->observation_result_set_id = $result_set->_id;
    $this->mappingObservationResult($OBX, $result);

    /* @todo � voir si on envoi un message d'erreur ou si on continu ... */
    if ($msg = $result->store()) {
      $this->codes[] = "E304";
    }

    return true;
  }

  /**
   * OBX Segment with encapsulated PDF
   *
   * @return bool
   */
  function getEncapsulatedPDF() {

  }

  /**
   * OBX Segment with reference pointer to external report
   *
   * @param DOMNode    $OBX       DOM node
   * @param COperation $operation Op�ration
   *
   * @return bool
   */
  function getReferencePointerToExternalReport(DOMNode $OBX, COperation $operation) {
    $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
    $sender         = $exchange_hl7v2->_ref_sender;

    // Chargement de la source associ�e � l'exp�diteur
    /** @var CInteropSender $sender_link */
    $sender_link = reset($sender->loadRefsObjectLinks())->_ref_object;

    // Aucun exp�diteur permettant de r�cup�rer les fichiers
    if (!$sender_link->_id) {
      $this->codes[] = "E340";

      return false;
    }

    $authorized_sources = array(
      "CSenderFileSystem",
      "CSenderFTP"
    );

    // L'exp�diteur n'est pas prise en charge pour la r�ception de fichiers
    if (!CMbArray::in($sender_link->_class, $authorized_sources)) {
      $this->codes[] = "E341";

      return false;
    }

    $sender_link->loadRefsExchangesSources();
    // Aucune source permettant de r�cup�rer les fichiers
    if (!$sender_link->_id) {
      $this->codes[] = "E342";

      return false;
    }

    $source = $sender_link->_ref_exchanges_sources[0];

    $filename = $this->getObservationValue($OBX);
    $path     = $filename;

    if ($source instanceof CSourceFileSystem) {
      $path = $source->getFullPath()."/$path";
    }

    $content = $source->getData("$path");

    // Gestion du CFile
    $file = new CFile();
    $file->setObject($operation);
    $file->file_name = $filename;
    $file->file_type = "application/pdf";
    $file->loadMatchingObject();

    $file->file_date = "now";
    $file->file_size = strlen($content);

    $file->fillFields();
    $file->updateFormFields();

    $file->putContent($content);

    if ($msg = $file->store()) {
      $this->codes[] = "E343";
    }

    $this->codes[] = "I340";

    return true;
  }
}
