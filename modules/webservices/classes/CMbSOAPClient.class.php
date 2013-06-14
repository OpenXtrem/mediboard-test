<?php

/**
 * CMbSOAPClient
 *  
 * @category Webservices
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
 */
if (!class_exists("SoapClient")) {
  return;
}

/**
 * The CMbSOAPClient class
 */
class CMbSOAPClient extends SoapClient {
  public $wsdl_url;
  public $type_echange_soap;
  public $soap_client_error = false;
  public $flatten;
  public $loggable;
  public $encoding;

  /**
   * The constructor
   *
   * @param string  $rooturl    The URL of the wsdl file
   * @param string  $type       The type of exchange
   * @param array   $options    An array of options
   * @param boolean $loggable   True if you want to log all the exchanges with the web service
   * @param string  $local_cert Path of the certifacte
   * @param string  $passphrase Pass phrase for the certificate
   * @param bool    $safe_mode  Safe mode
   *
   * @throws CMbException
   *
   * @return CMbSOAPClient
   */
  function __construct($rooturl, $type = null, $options = array(), $loggable = null, $local_cert = null, $passphrase = null, $safe_mode = 0) {
    $this->wsdl_url = $rooturl;

    if ($loggable) {
      $this->loggable = $loggable;
    }
    
    if ($type) {
      $this->type_echange_soap = $type;
    }

    if (!$safe_mode) {
      if (!$html = file_get_contents($this->wsdl_url)) {
        $this->soap_client_error = true;
        throw new CMbException("CSourceSOAP-unable-to-parse-url", $this->wsdl_url);
      }

      if (strpos($html, "<?xml") === false) {
        $this->soap_client_error = true;
        throw new CMbException("CSourceSOAP-wsdl-invalid");
      }
    }
    
    // Ajout des options personnalis�es
    $options = array_merge($options, array("connexion_timeout" => CAppUI::conf("webservices connection_timeout")));
    if (CAppUI::conf("webservices trace")) {
      $options = array_merge($options, array("trace" => true));
    }
    if ($local_cert) {
      $options = array_merge($options, array("local_cert" => $local_cert));
    }
    if ($passphrase) {
      $options = array_merge($options, array("passphrase" => $passphrase));
    }

    parent::__construct($this->wsdl_url, $options);
  }

  /** Calls a SOAP function
   *
   * @param string $function_name   The name of the SOAP function to call
   * @param array  $arguments       An array of the arguments to pass to the function
   * @param array  $options         An associative array of options to pass to the client
   * @param mixed  $input_headers   An array of headers to be sent along with the SOAP request
   * @param array  &$output_headers If supplied, this array will be filled with the headers from the SOAP response
   *
   * @throws Exception|SoapFault
   *
   * @return mixed SOAP functions may return one, or multiple values
   */
  public function call($function_name, $arguments, $options = null, $input_headers = null, &$output_headers = null) {
    try {
      return parent::__soapCall($function_name, $arguments, $options, $input_headers, $output_headers);
    } 
    catch(SoapFault $fault) {
      throw $fault;
    }
  }
  
  /**
   * Set the request and the response of the exchange and return it
   * 
   * @param CEchangeSOAP $exchange The exchangeSOAP
   * 
   * @return null
   */
  public function getTrace(CEchangeSOAP $exchange) {
    $exchange->trace                 = true;
    $exchange->last_request_headers  = $this->__getLastRequestHeaders();
    $exchange->last_request          = $this->__getLastRequest();
    $exchange->last_response_headers = $this->__getLastResponseHeaders();
    $exchange->last_response         = $this->__getLastResponse();
  }
  
  /**
   * Defines headers to be sent along with the SOAP requests
   * 
   * @param array $soapheaders The headers to be set
   * 
   * @return boolean True on success or False on failure 
   */
  public function setHeaders($soapheaders) {
    $this->__setSoapHeaders($soapheaders);
  }

  /**
   * Check service availability
   *
   * @throws CMbException
   *
   * @return void
   */
  public function checkServiceAvailability() {
    $xml = file_get_contents($this->wsdl_url);

    $dom = new CMbXMLDocument();
    $dom->loadXML($xml);

    $xpath = new CMbXPath($dom);
    $xpath->registerNamespace("wsdl", "http://schemas.xmlsoap.org/wsdl/");
    $xpath->registerNamespace("soap", "http://schemas.xmlsoap.org/wsdl/soap/");

    $service_nodes = $xpath->query("//wsdl:service");
    foreach ($service_nodes as $_service_node) {
      $service_name = $_service_node->getAttribute("name");

      $port_nodes = $xpath->query("wsdl:port", $_service_node);
      foreach ($port_nodes as $_port_node) {
        $address = $xpath->queryAttributNode("soap:address", $_port_node, "location");

        // Url exist
        $url_exist = url_exists($address);

        if (!$url_exist) {
          throw new CMbException("Service '$service_name' injoignable � l'adresse : <em>$address</em>");
        }
      }
    }
  }
}