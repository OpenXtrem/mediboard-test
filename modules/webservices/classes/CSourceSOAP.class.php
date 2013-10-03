<?php

/**
 * Source SOAP
 *
 * @category Webservices
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  SVN: $Id:$
 * @link     http://www.mediboard.org
 */

/**
 * Class CSourceSOAP
 * Source SOAP
 */
class CSourceSOAP extends CExchangeSource {
  // DB Table key
  public $source_soap_id;
  
  // DB Fields
  public $wsdl_external;
  public $wsdl_mode;
  public $evenement_name;
  public $single_parameter;
  public $encoding;
  public $stream_context;
  public $type_soap;
  public $local_cert;
  public $passphrase;
  public $iv_passphrase;
  public $safe_mode;
  public $return_raw;
  public $soap_version;
  public $xop_mode;
  public $use_tunnel;

  // Options de contexte SSL
  public $verify_peer;
  public $cafile;

  /** @var CSOAPClient */
  protected $_soap_client;

  public $_headerbody = array();

  /**
   * Initialize object specification
   *
   * @return CMbObjectSpec the spec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'source_soap';
    $spec->key   = 'source_soap_id';
    return $spec;
  }

  /**
   * Get properties specifications as strings
   *
   * @see parent::getProps()
   *
   * @return array
   */
  function getProps() {
    $specs = parent::getProps();

    $specs["wsdl_external"]    = "str";
    $specs["wsdl_mode"]        = "bool default|1";
    $specs["evenement_name"]   = "str";
    $specs["single_parameter"] = "str";
    $specs["encoding"]         = "enum list|UTF-8|ISO-8859-1|ISO-8859-15 default|UTF-8";
    $specs["type_soap"]        = "enum list|CMbSOAPClient|CNuSOAPClient default|CMbSOAPClient notNull";
    $specs["iv_passphrase"]    = "str show|0 loggable|0";
    $specs["safe_mode"]        = "bool default|0";
    $specs["return_raw"]       = "bool default|0";
    $specs["soap_version"]     = "enum list|SOAP_1_1|SOAP_1_2 default|SOAP_1_1 notNull";
    $specs["xop_mode"]         = "bool default|0";
    $specs["use_tunnel"]       = "bool default|0";

    $specs["local_cert"]       = "str";
    $specs["passphrase"]       = "password show|0 loggable|0";

    $specs["verify_peer"]      = "bool default|0";
    $specs["cafile"]           = "str";

    $specs["stream_context"]   = "str";

    return $specs;
  }

  /**
   * Calls a SOAP function
   *
   * @param string $function  Function name
   * @param array  $arguments Arguments
   *
   * @return void
   */
  function __call($function, $arguments) { 
    $this->setData(reset($arguments));
    $this->send($function);
  }

  /**
   * Encrypt fields
   *
   * @return void
   */
  function updateEncryptedFields(){
    if ($this->passphrase === "") {
      $this->passphrase = null;
    }
    else {
      if (!empty($this->passphrase)) {
        $this->passphrase = $this->encryptString($this->passphrase, "iv_passphrase");
      }
    }
  }

  /**
   * Set SOAP header
   *
   * @param string $namespace      The namespace of the SOAP header element.
   * @param string $name           The name of the SoapHeader object
   * @param array  $data           A SOAP header's content. It can be a PHP value or a SoapVar object
   * @param bool   $mustUnderstand Value must understand
   * @param null   $actor          Value of the actor attribute of the SOAP header element
   *
   * @return void
   */
  function setHeaders($namespace, $name, $data, $mustUnderstand = false, $actor = null) {
    if ($actor) {
      $this->_headerbody[] = new SoapHeader($namespace, $name, $data, $mustUnderstand, $actor);
    }
    else {
      $this->_headerbody[] = new SoapHeader($namespace, $name, $data);
    }
  }

  function getSoapClient(){
    return $this->_soap_client;
  }

  /**
   * Send SOAP event
   *
   * @param string $evenement_name Event name
   * @param bool   $flatten        Flat args
   *
   * @return bool|void
   * @throws CMbException
   */
  function send($evenement_name = null, $flatten = false) {
    if (!$this->_id) {
      throw new CMbException("CSourceSOAP-no-source", $this->name);
    }
    
    if (!$evenement_name) {
      $evenement_name = $this->evenement_name;
    }
    
    if (!$evenement_name) {
      throw new CMbException("CSourceSOAP-no-evenement", $this->name);
    }   
    
    if ($this->single_parameter) {
      $this->_data = array("$this->single_parameter" => $this->_data);
    }
    
    if (!$this->_data) {
      $this->_data = array();
    }
    
    $options = array(
      "encoding" => $this->encoding
    );

    if ($this->return_raw) {
      $options["return_raw"] = true;
    }

    if ($this->xop_mode) {
      $options["xop_mode"] = true;
    }

    if ($this->use_tunnel) {
      $options["use_tunnel"] = true;
    }
    
    $soap_client = new CSOAPClient($this->type_soap);
    $this->_soap_client = $soap_client;

    $password   = $this->getPassword();
    $passphrase = $this->getPassword($this->passphrase, "iv_passphrase");

    $soap_client->make(
      $this->host, $this->user, $password, $this->type_echange, $options, null,
      $this->stream_context, $this->local_cert, $passphrase, $this->safe_mode,
      $this->verify_peer, $this->cafile, $this->wsdl_external
    );
    
    if ($soap_client->client->soap_client_error) {
      throw new CMbException("CSourceSOAP-unreachable-source", $this->name);
    }
    
    // Applatissement du tableau $arguments qui contient un �l�ment vide array([0] => ...) ?
    $soap_client->client->flatten  = $flatten;
    
    // D�finit un ent-�te � utiliser dans les requ�tes ?
    if ($this->_headerbody) {
      $soap_client->setHeaders($this->_headerbody);
    }
   
    // Aucun log � produire ? 
    $soap_client->client->loggable = $this->loggable;

    $this->_acquittement = $soap_client->call($evenement_name, $this->_data);

    if (is_object($this->_acquittement)) {
      $acquittement = (array) $this->_acquittement;
      if (count($acquittement) == 1) {
        $this->_acquittement = reset($acquittement);
      } 
    }
    
    return true;
  }

  /**
   * If source is reachable
   *
   * @return bool|void
   */
  function isReachableSource() {
    $check_option["local_cert"] = $this->local_cert;
    $check_option["ca_cert"]    = $this->cafile;
    $check_option["passphrase"] = $this->getPassword($this->passphrase, "iv_passphrase");
    $check_option["username"]   = $this->user;
    $check_option["password"]   = $this->getPassword();

    if (!$this->safe_mode) {
      if (!CHTTPClient::checkUrl($this->host, $check_option)) {
        $this->_reachable = 0;
        $this->_message   = CAppUI::tr("CSourceSOAP-unreachable-source", $this->host);

        return false;
      }
    }

    return true;
  }

  /**
   * If is authentificate
   *
   * @return bool|void
   */
  function isAuthentificate() {
    $options = array(
      "encoding" => $this->encoding
    );

    try {
      $soap_client = new CSOAPClient($this->type_soap);

      $password   = $this->getPassword();
      $soap_client->make(
        $this->host, $this->user, $password, $this->type_echange, $options,
        null, null, $this->local_cert, $this->passphrase, false, $this->verify_peer,
        $this->cafile, $this->wsdl_external
      );

      $soap_client->checkServiceAvailability();
    }
    catch (Exception $e) {
      $this->_reachable = 1;
      $this->_message   = $e->getMessage();
      return false;
    }

    return true;
  }

  /**
   * Get response time
   *
   * @return int
   */
  function getResponseTime() {
    return $this->_response_time = url_response_time($this->host, 80);
  }
}