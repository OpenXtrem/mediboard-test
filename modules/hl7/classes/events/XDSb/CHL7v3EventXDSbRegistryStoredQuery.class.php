<?php

/**
 * $Id$
 *  
 * @category HL7
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision$
 * @link     http://www.mediboard.org
 */
 
/**
 * CHL7v3EventXDSRegistryStoredQuery
 * Registry stored query
 */
class CHL7v3EventXDSbRegistryStoredQuery extends CHL7v3EventXDSb implements CHL7EventXDSbRegistryStoredQuery {
  /** @var string */
  public $interaction_id = "RegistryStoredQuery";

  /**
   * Build ProvideAndRegisterDocumentSetRequest event
   *
   * @param CXDSQueryRegistryStoredQuery $object compte rendu
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($object) {
    //R�cup�ration de l'objet mediboard concern� par la requ�te � effectuer(pr�sence du loadLastLog dans le parent)
    $mb_object = $object->document_item ? $object->document_item : $object->patient;

    parent::build($mb_object);

    $this->message = $object->createQuery();

    $this->updateExchange(false);
  }

  /**
   * @see parent::getAcknowledgment
   */
  function getAcknowledgment() {

    $dom = new CMbXMLDocument("UTF-8");
    $dom->loadXMLSafe($this->ack_data);

    $xpath = new CMbXPath($dom);
    $xpath->registerNamespace("rs", "urn:oasis:names:tc:ebxml-regrep:xsd:rs:3.0");
    $xpath->registerNamespace("rim", "urn:oasis:names:tc:ebxml-regrep:xsd:rim:3.0");
    $status = $xpath->queryAttributNode(".", null, "status");

    if ($status === "urn:oasis:names:tc:ebxml-regrep:ResponseStatusType:Failure") {
      $nodes = $xpath->query("//rs:RegistryErrorList/rs:RegistryError");
      $ack = array();
      foreach ($nodes as $_node) {
        $ack[] = array("status"  => $xpath->queryAttributNode(".", $_node, "codeContext"),
                       "context" => $xpath->queryAttributNode(".", $_node, "errorCode")
        );
      }
    }
    else {
      $nodes = $xpath->query("//rim:RegistryObjectList/rim:ObjectRef");
      $ack = array();
      foreach ($nodes as $_node) {
        $ack[] = array("status"  => $xpath->queryAttributNode(".", $_node, "id"),
                       "context" => "");
      }
    }

    return $ack;
  }
}
