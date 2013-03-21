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
 * Class CHL7v2LinkUnlink
 * Link/Unlink patients, message XML HL7
 */
class CHL7v2LinkUnlink extends CHL7v2MessageXML {
  static $event_codes = "A24 A37";

  /**
   * Get contents
   *
   * @return array
   */
  function getContentNodes() {
    $data  = array();

    $exchange_ihe = $this->_ref_exchange_ihe;
    $sender       = $exchange_ihe->_ref_sender;
    $sender->loadConfigValues();

    $this->_ref_sender = $sender;

    $this->queryNode("EVN", null, $data, true);

    $sub_data = array();
    foreach ($this->queryNodes("PID") as $_PID) {
      $sub_data["DOMElement"]        = $_PID;
      $sub_data["personIdentifiers"] = $this->getPersonIdentifiers("PID.3", $_PID, $sender);

      $data["PID"][] = $sub_data;
    }

    return $data;
  }

  /**
   * Handle link/unlink patients message
   *
   * @param CHL7Acknowledgment $ack     Acknowledgment
   * @param CPatient           $patient Person
   * @param array              $data    Data
   *
   * @return string|void
   */
  function handle(CHL7Acknowledgment $ack, CPatient $patient, $data) {
    $exchange_ihe = $this->_ref_exchange_ihe;
    $sender       = $exchange_ihe->_ref_sender;
    $sender->loadConfigValues();

    $this->_ref_sender = $sender;

    if (count($data["PID"]) != 2) {
      return $exchange_ihe->setAckAR($ack, "E500", null, $patient);
    }

    foreach ($data["PID"] as $_PID) {
      $patientPI = CValue::read($_PID['personIdentifiers'], "PI");

      // Acquittement d'erreur : identifiants PI non fournis
      if (!$patientPI) {
        return $exchange_ihe->setAckAR($ack, "E100", null, $patient);
      }
    }

    $patient_1_PI = CValue::read($data["PID"][0]['personIdentifiers'], "PI");
    $patient_2_PI = CValue::read($data["PID"][1]['personIdentifiers'], "PI");

    $patient_1 = new CPatient();
    $patient_1->_IPP = $patient_1_PI;
    $patient_1->loadFromIPP($sender->group_id);
    // PI non connu (non fourni ou non retrouv�)
    if (!$patient_1->_id) {
      return $exchange_ihe->setAckAR($ack, "E501", null, $patient_1);
    }

    $patient_2 = new CPatient();
    $patient_2->_IPP = $patient_2_PI;
    $patient_2->loadFromIPP($sender->group_id);
    // PI non connu (non fourni ou non retrouv�)
    if (!$patient_2->_id) {
      return $exchange_ihe->setAckAR($ack, "E501", null, $patient_2);
    }

    $function_handle = "handle$exchange_ihe->code";
    
    if (!method_exists($this, $function_handle)) {
      return $exchange_ihe->setAckAR($ack, "E006", null, $patient);
    }
    
    return $this->$function_handle($ack, $patient_1, $patient_2, $data);
  }

  /**
   * Handle event A24 - Link two patients
   *
   * @param CHL7Acknowledgment $ack       Acknowledgment
   * @param CPatient           $patient_1 Person
   * @param CPatient           $patient_2 Person
   * @param array              $data      Data
   *
   * @return string
   */
  function handleA24(CHL7Acknowledgment $ack, CPatient $patient_1, CPatient $patient_2, $data) {
    $exchange_ihe = $this->_ref_exchange_ihe;

    // Association des deux patients
    $patient_1->patient_link_id = $patient_2->_id;
    if ($msg = $patient_1->store()) {
      return $exchange_ihe->setAckAR($ack, "E502", $msg, $patient_1);
    }

    return $exchange_ihe->setAckAA($ack, "I501", null, $patient_1);
  }

  /**
   * Handle event A37 - Unlink two previously linked patients
   *
   * @param CHL7Acknowledgment $ack       Acknowledgment
   * @param CPatient           $patient_1 Person
   * @param CPatient           $patient_2 Person
   * @param array              $data      Data
   *
   * @return string
   */
  function handleA37(CHL7Acknowledgment $ack, CPatient $patient_1, CPatient $patient_2, $data) {
    $exchange_ihe = $this->_ref_exchange_ihe;

    $exchange_ihe = $this->_ref_exchange_ihe;

    // Association des deux patients
    $patient_1->patient_link_id = "";
    if ($msg = $patient_1->store()) {
      return $exchange_ihe->setAckAR($ack, "E503", $msg, $patient_1);
    }

    return $exchange_ihe->setAckAA($ack, "I502", null, $patient_1);
  }
}
