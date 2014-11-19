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
 * Class CHL7v2ReceiveOrderMessage
 * Order message, message XML HL7
 */
class CHL7v2ReceiveOrderMessage extends CHL7v2MessageXML {
  static $event_codes = array ("O01");

  /**
   * Get contents
   *
   * @return array
   */
  function getContentNodes() {
    $data = parent::getContentNodes();

    $pv1 = $this->queryNode("PV1", null, $data, true);

    $data["admitIdentifiers"] = $this->getAdmitIdentifiers($pv1, $this->_ref_sender);

    $ORDER = $this->queryNodes("ORM_O01.ORDER", null, $varnull, true);
    foreach ($ORDER as $_ORM_O01_ORDER) {
      // ORC
      $this->queryNode("ORC", $_ORM_O01_ORDER, $data, true);
    }

    $ORDER_DETAIL          = $this->queryNode("ORM_O01.ORDER_DETAIL", null, $varnull, true);
    $ORDER_DETAIL_SEGMENTS = $this->queryNode("ORM_O01.ORDER_DETAIL_SEGMENTS", $ORDER_DETAIL, $varnull, true);

    // OBR
    $this->queryNode("OBR", $ORDER_DETAIL_SEGMENTS, $data, true);

    return $data;
  }

  /**
   * Handle receive order message
   *
   * @param CHL7v2ReceiveOrderMessageResponse $ack     Acknowledgment
   * @param CPatient                          $patient Person
   * @param array                             $data    Data
   *
   * @return string|void
   */
  function handle(CHL7v2ReceiveOrderMessageResponse $ack, CPatient $patient, $data) {
    $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
    $sender         = $exchange_hl7v2->_ref_sender;
    $sender->loadConfigValues();

    $this->_ref_sender = $sender;
    $patientPI = CValue::read($data['personIdentifiers'], "PI");

    if (!$patientPI) {
      return $exchange_hl7v2->setORRError($ack, "E007");
    }

    $IPP = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $patientPI);
    // Patient non retrouv� par son IPP
    if (!$IPP->_id) {
      return $exchange_hl7v2->setORRError($ack, "E105");
    }
    $patient->load($IPP->object_id);

    $venueAN = $this->getVenueAN($sender, $data);

    $NDA = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $venueAN);
    // S�jour non retrouv� par son NDA
    if (!$NDA->_id) {
      return $exchange_hl7v2->setORRError($ack, "E205");
    }
    $sejour = new CSejour();
    $sejour->load($NDA->object_id);

    // Common order - ORC
    $orc           = $data["ORC"];
    $obr           = $data["OBR"];
    $event_request = $this->getEventRequest($orc);
    $consultation  = new CConsultation();

    $placer_id = $this->getPlacerNumber($orc);
    $filler_id = $this->getFillerNumber($orc);

    switch ($event_request) {
      // new order
      case "SN":
        $datetime = $this->getDate($orc);
        $orc12    = $this->getDoctorNode($orc, $data);
        $mediuser = new CMediusers();

        $medisuer_id = $this->getDoctor($orc12, $mediuser);
        if (!$medisuer_id) {
          return $exchange_hl7v2->setORRError($ack, "E801");
        }
        $consultation->createByDatetime($datetime, $medisuer_id, $patient->_id);

        if (!$consultation->_id) {
          return $exchange_hl7v2->setORRError($ack, "E802");
        }

        $idex        = new CIdSante400();
        $idex->id400 = $filler_id;
        $idex->tag   = $sender->_tag_consultation;
        $idex->setObject($consultation);
        $idex->store();
        break;
      //Modification
      case "SC":
        $consultation->load($placer_id);
        $status_code = $this->getStatusCode($orc);
        switch ($status_code) {
          case "CM":
            $status = CConsultation::TERMINE;
            break;
          case "OD":
            $status = CConsultation::PLANIFIE;
            break;
          case "IP":
            $status = CConsultation::EN_COURS;
            break;
          default:
            return $exchange_hl7v2->setORRError($ack, "E803");
        }
        $consultation->chrono = $status;

        if ($msg = $consultation->store()) {
          return $exchange_hl7v2->setORRError($ack, "E804", $msg);
        }

        $obr4        = $this->getExamen("OBR.4", $obr, $data);

        //Identifiant de l'�l�ment de prescription
        $examen_id   = $this->getExamenID($obr4);
        $examen_name = $this->getExamenName($obr4);

        //todo g�rer avec l'�l�ment de prescription

        break;
      // cancel order request
      case "OC":
        $consultation->annule = "1";
        if ($msg = $consultation->store()) {
          return $exchange_hl7v2->setORRError($ack, "E804", $msg);
        }

        $idex = CIdSante400::getMatchFor($consultation, $sender->_tag_consultation);
        $idex->id400 = "trash_$idex->id400";
        if ($msg = $idex->store()) {
          return $exchange_hl7v2->setORRError($ack, "E805", $msg);
        }
        break;
      default:
        return $exchange_hl7v2->setORRError($ack, "E205");
    }

    return $exchange_hl7v2->setORRSuccess($ack);
  }

  /**
   * Get event request (Order Control)
   *
   * @param DOMNode $node ORC node
   *
   * @return string
   */
  function getEventRequest(DOMNode $node) {
    return $this->queryTextNode("ORC.1", $node);
  }

  /**
   * Get the placer number
   *
   * @param DOMNode $node ORC node
   *
   * @return string
   */
  function getPlacerNumber($node) {
    $placer = "ORC.2/EI.1";
    if ($this->_ref_sender->_configs["change_filler_placer"]) {
      $placer = "ORC.3/EI.1";
    }
    return $this->queryTextNode($placer, $node);
  }

  /**
   * Get the filler number
   *
   * @param DOMNode $node ORC node
   *
   * @return string
   */
  function getFillerNumber($node) {
    $filler = "ORC.3/EI.1";
    if ($this->_ref_sender->_configs["change_filler_placer"]) {
      $filler = "ORC.2/EI.1";
    }
    return $this->queryTextNode($filler, $node);
  }

  /**
   * Get the status code
   *
   * @param DOMNode $node ORC node
   *
   * @return string
   */
  function getStatusCode($node) {
    return $this->queryTextNode("ORC.5", $node);
  }

  /**
   * Get the date
   *
   * @param DOMNode $node ORC node
   *
   * @return string
   */
  function getDate($node) {
    return $this->queryTextNode("ORC.9/TS.1", $node);
  }

  /**
   * Get the doctor information
   *
   * @param DOMNode $node ORC node
   * @param array   $data array of data
   *
   * @return string
   */
  function getDoctorNode($node, $data) {
    return $this->queryNode("ORC.12", $node, $data);
  }

  /**
   * Get the examen
   *
   * @param DOMNode $node ORC node
   * @param array   $data array of data
   *
   * @return string
   */
  function getExamen($node, $data) {
    return $this->queryNode("OBR.4", $node, $data);
  }

  /**
   * Get the examen identifiant
   *
   * @param DOMNode $node ORC node
   *
   * @return string
   */
  function getExamenID($node) {
    return $this->queryTextNode("CE.1", $node);
  }

  /**
   * Get the examen name
   *
   * @param DOMNode $node ORC node
   *
   * @return string
   */
  function getExamenName($node) {
    return $this->queryTextNode("CE.2", $node);
  }
}
