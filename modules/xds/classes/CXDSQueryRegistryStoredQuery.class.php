<?php

/**
 * $Id$
 *  
 * @category XDS
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision$
 * @link     http://www.mediboard.org
 */
 
/**
 * Description
 */
class CXDSQueryRegistryStoredQuery {

  /**
   * FindDocuments               : urn:uuid:14d4debf-8f97-4251-9a74-a90016b0af0d
   * FindSubmissionSets          : urn:uuid:f26abbcb-ac74-4422-8a30-edb644bbc1a9
   * FindFolders                 : urn:uuid:958f3006-baad-4929-a4de-ff1114824431
   * GetAll                      : urn:uuid:10b545ea-725c-446d-9b95-8aeb444eddf3
   * GetDocuments                : urn:uuid:5c4f972b-d56b-40ac-a5fc-c8ca9b40b9d4
   * GetFolders                  : urn:uuid:5737b14c-8a1a-4539-b659-e03a34a5e1e4
   * GetAssociations             : urn:uuid:a7ae438b-4bc2-4642-93e9-be891f7bb155
   * GetDocumentsAndAssociations : urn:uuid:bab9529a-4a10-40b3-a01f-f68a615d247a
   * GetSubmissionSets           : urn:uuid:51224314-5390-4169-9b91-b1980040715a
   * GetSubmissionSetAndContents : urn:uuid:e8e3cb2c-e39c-46b9-99e4-c12f57260b83
   * GetFolderAndContents        : urn:uuid:b909a503-523d-4517-8acf-8e5834dfc4c7
   * GetFoldersForDocument       : urn:uuid:10cae35a-c7f9-4cf5-b61e-fc3278ffb578
   * GetRelatedDocuments         : urn:uuid:d90e5407-b356-4d91-a89f-873917b4b0e6
   * FindDocumentsByReferenceId  : urn:uuid:12941a89-e02e-4be5-967c-ce4bfc8fe492
   */
  public $query;
  public $returnComposedObjects;
  public $returnType;
  public $values = array();
  public $document_item;
  public $patient;


  static $multivalued_field = array(
    "\$XDSDocumentEntryClassCode", "\$XDSDocumentEntryTypeCode", "\$XDSDocumentEntryPracticeSettingCode",
    "\$XDSDocumentEntryHealthcareFacilityTypeCode", "\$XDSDocumentEntryEventCodeList", "\$XDSDocumentEntryConfidentialityCode",
    "\$XDSDocumentEntryAuthorPerson", "\$XDSDocumentEntryFormatCode", "\$XDSDocumentEntryStatus", "\$XDSDocumentEntryType",
    "\$XDSSubmissionSetSourceId", "\$XDSSubmissionSetContentType", "\$XDSSubmissionSetStatus", "\$XDSDocumentEntryEntryUUID",
    "\$XDSDocumentEntryUniqueId", "\$uuid",
  );

  /**
   * set the EntryId query of the mediboard document
   *
   * @param CDocumentItem    $object   Mediboard document
   * @param CInteropReceiver $receiver Receiver
   *
   * @return void
   */
  function setEntryIDbyDocument($object, $receiver) {
    $oid = CMbOID::getOIDFromClass($object, $receiver);
    $oid = "$oid.$object->_id.$object->version";

    $this->document_item = $object;
    //Pour l'authentification indirecte, les valeurs ne doivent pas �tre chang�es
    $this->returnComposedObjects = "false";
    $this->returnType            = "ObjectRef";
    //GetDocuments
    $this->query                 = "urn:uuid:5c4f972b-d56b-40ac-a5fc-c8ca9b40b9d4";
    $this->values = array(
      "\$XDSDocumentEntryUniqueId" => array("$oid")
    );
  }

  /**
   * Set the value for the queries with UUID
   *
   * @param String   $query       Query
   * @param CPatient $patient     Patient
   * @param String[] $uuid_array  UUID to search
   * @param String   $return_type Type return(ObjectRef/LeafClass)
   *
   * @return void
   */
  function setQueryUUID($query, $patient, $uuid_array, $return_type) {
    $this->returnType            = $return_type;
    $this->returnComposedObjects = "false";
    $this->patient               = $patient;
    $this->query                 = $query;
    $field = "\$uuid";
    if ($query == "urn:uuid:5c4f972b-d56b-40ac-a5fc-c8ca9b40b9d4") {
      $field = "\$XDSDocumentEntryEntryUUID";
    }
    $this->values[$field]      = $uuid_array;
  }

  /**
   * Create the XDS query
   *
   * @return string
   */
  function createQuery() {
    $xml = new CXDSXmlDocument();
    $message = $xml->createQueryElement($xml, "AdhocQueryRequest");
    $response_option = $xml->createQueryElement($message, "ResponseOption");
    $xml->addAttribute($response_option, "returnComposedObjects", $this->returnComposedObjects);
    $xml->addAttribute($response_option, "returnType", $this->returnType);
    $adhocQuery = $xml->createRimRoot("AdhocQuery", null, $message);
    $xml->addAttribute($adhocQuery, "id", $this->query);

    foreach ($this->values as $_name => $_values) {
      //And statement
      if (is_array($_values)) {
        foreach ($_values as $_value) {
          $data = self::formatData($_value, $_name);
          $slot = new CXDSSlot("$_name", array($data));
          $xml->importDOMDocument($adhocQuery, $slot->toXML());
        }
      }
      //OR Statement
      else {
        $parts = explode("|", $_values);
        $value = "";
        foreach ($parts as $_part) {
          $data = self::formatData($_part, $_name, true);
          $value .= "$data,";
        }
        $value = rtrim($value, ",");
        $slot = new CXDSSlot("$_name", array("($value)"));
        $xml->importDOMDocument($adhocQuery, $slot->toXML());
      }
    }

    return $xml->saveXML();
  }

  /**
   * Format the data
   *
   * @param String $value        Value to format
   * @param String $_param       Name of the param
   * @param bool   $bypass_multi Bypass the check multi
   *
   * @return string
   */
  static function formatData($value, $_param, $bypass_multi=false) {
    /**
     * ?Children??s Hospital? ? a single quote is inserted in a string by specifying two single quotes
     */
    if (strpos($value, "'") !== false) {
      $value = substr_replace($value, "'", strpos($value, "'"), 0);
    }
    //?urn:oasis:names:tc:ebxml-regrep:StatusType:Approved? - in single quotes for strings.
    if (!is_numeric($value)) {
      $value = "'$value'";
    }

    //Format for multiple values is(value, value, value, ?) OR(value)if only one value is to be specified.
    if (!$bypass_multi && in_array($_param, self::$multivalued_field)) {
      $value = "($value)";
    }

    return $value;
  }
}