<?php

/**
 * Format RPC Literal
 *  
 * @category Webservices
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
 */

/**
 * Class CWSDLRPCLiteral 
 * Format RPC Literal
 */
class CWSDLRPCLiteral extends CWSDLRPC {
  function addTypes() {
    $definitions = $this->documentElement;
    $partie2 = $this->createComment("partie 2 : Types");
    $definitions->appendChild($partie2);
    $types = $this->addElement($definitions, "types", null, "http://schemas.xmlsoap.org/wsdl/");
    
    $xsd = $this->addElement($types, "xsd:schema", null, "http://www.w3.org/2001/XMLSchema");
    $this->addAttribute($xsd, "elementFormDefault", "qualified");
    $this->addAttribute($xsd, "xmlns", "http://www.w3.org/2001/XMLSchema");
    $this->addAttribute($xsd, "targetNamespace", "http://soap.mediboard.org/wsdl/");
    
    // Foreach method to describe
    foreach ($this->_soap_handler->getParamSpecs() as $_method => $_paramSpec) {
      // MethodRequest element
      // Foreach parameters
      foreach ($_paramSpec["parameters"] as $_param => $_type) {
        $child_element = $this->addElement($xsd, "element", null, "http://www.w3.org/2001/XMLSchema");
        $this->addAttribute($child_element, "name", $_method."-".$_param); 
        $this->addAttribute($child_element, "type", "xsd:".$this->xsd[$_type]);
        
        $this->addDocumentation($child_element, CAppUI::tr(get_class($this->_soap_handler)."-".$_method."-".$_param));
        
        if ($_type == "array") {
          $complexType = $this->addElement($child_element, "complexType", null, "http://www.w3.org/2001/XMLSchema");
          $this->addAttribute($complexType, "name", "parameters");
          
          $sequence = $this->addElement($complexType, "sequence", null, "http://www.w3.org/2001/XMLSchema");
          // Foreach array parameters
          foreach ($_param as $_paramName => $_type) {
            $child_element = $this->addElement($sequence, "element", null, "http://www.w3.org/2001/XMLSchema");
            $this->addAttribute($child_element, "name", $_paramName); 
            $this->addAttribute($child_element, "type", "xsd:".$this->xsd[$_type]);
            
            $this->addDocumentation($child_element, CAppUI::tr(get_class($this->_soap_handler)."-".$_method."-".$_paramName));
          }
        }
      }
      
      // MethodResponse element
      // Foreach returns
      foreach ($_paramSpec["return"] as $_return => $_type) {
        $child_element = $this->addElement($xsd, "element", null, "http://www.w3.org/2001/XMLSchema");
        $this->addAttribute($child_element, "name", $_method."-".$_return);
        $this->addAttribute($child_element, "type", "xsd:".$this->xsd[$_type]);
        
        $this->addDocumentation($child_element, CAppUI::tr(get_class($this->_soap_handler)."-".$_method."-".$_return));
        
        if ($_type == "array") {
          $complexType = $this->addElement($child_element, "complexType", null, "http://www.w3.org/2001/XMLSchema");
          $this->addAttribute($complexType, "name", "parameters");
          
          $sequence = $this->addElement($complexType, "sequence", null, "http://www.w3.org/2001/XMLSchema");
          // Foreach array parameters
          foreach ($_param as $_paramName => $_type) {
            $child_element = $this->addElement($sequence, "element", null, "http://www.w3.org/2001/XMLSchema");
            $this->addAttribute($child_element, "name", $_paramName); 
            $this->addAttribute($child_element, "type", "xsd:".$this->xsd[$_type]);
            
            $this->addDocumentation($child_element, CAppUI::tr(get_class($this->_soap_handler)."-".$_method."-".$_paramName));
          }
        }
      }
    }
    
    // Traitement final
    $this->purgeEmptyElements();
  }
  
  function addMessage() {
    $definitions = $this->documentElement;
    $partie3 = $this->createComment("partie 3 : Message");
    $definitions->appendChild($partie3);
    
    foreach($this->_soap_handler->getParamSpecs() as $_method => $_paramSpec) {
      $message = $this->addElement($definitions, "message", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($message, "name", $_method."Request");
      
      foreach ($_paramSpec['parameters'] as $_oneParam => $_paramType) {
        $part = $this->addElement($message, "part", null, "http://schemas.xmlsoap.org/wsdl/");
        $this->addAttribute($part, "name", $_oneParam);
        $this->addAttribute($part, "element", "typens:".$_method."-".$_oneParam);
      }
      
      $message = $this->addElement($definitions, "message", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($message, "name", $_method."Response");
      
      foreach ($_paramSpec['return'] as $_oneParam => $_paramType) {
        $part = $this->addElement($message, "part", null, "http://schemas.xmlsoap.org/wsdl/");
        $this->addAttribute($part, "name", $_oneParam);
        $this->addAttribute($part, "element", "typens:".$_method."-".$_oneParam);
      }
    }
  }
  
  function addPortType() {
    $definitions = $this->documentElement;
    $partie4 = $this->createComment("partie 4 : Port Type");
    $definitions->appendChild($partie4);
    
    $portType = $this->addElement($definitions, "portType", null, "http://schemas.xmlsoap.org/wsdl/");
    $this->addAttribute($portType, "name", "MediboardPort");
    
    foreach($this->_soap_handler->getParamSpecs() as $_method => $_paramSpec) {
      $partie5 = $this->createComment("partie 5 : Operation");
      $portType->appendChild($partie5);
      $operation = $this->addElement($portType, "operation", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($operation, "name", $_method);
      
      $input = $this->addElement($operation, "input", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($input, "message", "typens:".$_method."Request");
      
      $output = $this->addElement($operation, "output", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($output, "message", "typens:".$_method."Response");
    }
  }
  
  function addBinding() {
    $definitions = $this->documentElement;
    $partie6 = $this->createComment("partie 6 : Binding");
    $definitions->appendChild($partie6);
    
    $binding = $this->addElement($definitions, "binding", null, "http://schemas.xmlsoap.org/wsdl/");
    $this->addAttribute($binding, "name", "MediboardBinding");
    $this->addAttribute($binding, "type", "typens:MediboardPort");
    
    $soap = $this->addElement($binding, "soap:binding", null, "http://schemas.xmlsoap.org/wsdl/soap/");
    $this->addAttribute($soap, "style", "rpc");
    $this->addAttribute($soap, "transport", "http://schemas.xmlsoap.org/soap/http");

    foreach($this->_soap_handler->getParamSpecs() as $_method => $_paramSpec) {
      $operation = $this->addElement($binding, "operation", null, "http://schemas.xmlsoap.org/wsdl/");
      
      $this->addAttribute($operation, "name", $_method);
      
      $soapoperation = $this->addElement($operation, "soap:operation", null, "http://schemas.xmlsoap.org/wsdl/soap/");
      $this->addAttribute($soapoperation, "soapAction", "");
      
      $input = $this->addElement($operation, "input", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($input, "name", $_method."Request");
      
      $soapbody = $this->addElement($input, "soap:body", null, "http://schemas.xmlsoap.org/wsdl/soap/");
      $this->addAttribute($soapbody, "use", "literal");
      
      $output = $this->addElement($operation, "output", null, "http://schemas.xmlsoap.org/wsdl/");
      $this->addAttribute($output, "name", $_method."Response");
      
      $soapbody = $this->addElement($output, "soap:body", null, "http://schemas.xmlsoap.org/wsdl/soap/");
      $this->addAttribute($soapbody, "use", "literal");
    }
  }
}
