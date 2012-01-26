<?php /* $Id:$ */

/**
 * @package Mediboard
 * @subpackage hl7
 * @version $Revision: 10041 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

class CHL7v2Field extends CHL7v2Entity {
  /**
   * @var CHL7v2Segment
   */
  var $owner_segment = null;
  
  var $name          = null;
  var $datatype      = null;
  var $length        = null;
  var $table         = null;
  var $description   = null;
  var $required      = null;
  var $unbounded     = null;
  var $items         = array();
    
  var $meta_spec     = null;
  
  private $_ts_fixed = false;
  
  function __construct(CHL7v2Segment $segment, $spec) {
    parent::__construct($segment);
    
    $this->owner_segment = $segment;
    $this->name     = (string)$spec->name;
    $this->datatype = (string)$spec->datatype;
    $this->length   = (int)$spec->attributes()->length;
    $this->table    = (int)$spec->attributes()->table;
    
    $this->meta_spec = $spec;
    
    if ($this->datatype == "TS") {
      //$this->datatype = "DTM";
    }
    $this->description = (string)$spec->description;
    $this->required    = $spec->isRequired();
    $this->unbounded   = $spec->isUnbounded();
  }
  
  function _toXML(DOMNode $node, $hl7_datatypes) {
    $doc = $node->ownerDocument;
    
    foreach($this->items as $_item) {
      $_item->_toXML($node, $hl7_datatypes);
    }
  }
  
  function parse($data) {
    parent::parse($data);
    
    if ($this->required && ($this->data === "" || $this->data === null) /* === $message->nullValue*/) { // nullValue ("") or null ??
      $this->error(CHL7v2Exception::FIELD_EMPTY, null, $this);
    }
    
    $message = $this->getMessage();
    
    $items = CHL7v2::split($message->repetitionSeparator, $this->data, $this->keep());
    
    /* // Ce test ne semble pas etre valide, car meme si maxOccurs n'est pas unbounded, on en trouve souvent plusieurs occurences 
    if (!$this->unbounded && count($items) > 1) {
      mbTrace($this);
      $this->error(CHL7v2Exception::TOO_MANY_FIELD_ITEMS, $this->name, $this);
    }*/
    
    $this->items = array();
    
    foreach($items as $i => $components) {
      $_field_item = new CHL7v2FieldItem($this, $this->meta_spec, $i);
      $_field_item->parse($components);
      
      $this->items[] = $_field_item;
    }
    
    $this->validate();
  }
  
  function fill($items) {
    if (!isset($items)) {
      return;
    }
    
    if (!is_array($items)) {
      $items = trim($items);
      $items = array($items);
    }
    
    $this->items = array();
    
    foreach($items as $i => $data) {
      $_field_item = new CHL7v2FieldItem($this, $this->meta_spec, $i);
      $_field_item->fill($data);
      
      $this->items[] = $_field_item;
    }
  }
  
  function validate() {
    foreach($this->items as $item) {
      $item->validate();
    }
  }
  
  function getSpecs(){
    $specs = $this->getSchema(self::PREFIX_COMPOSITE_NAME, $this->datatype, $this->getMessage()->extension);
    
    // The timestamp case, where Time contains TimeStamp data
    /*if (!$this->_ts_fixed && $this->datatype === "TS") {
      $specs->elements->field[0]->datatype = "DTM";
    }
    
    $this->_ts_fixed = true;*/
    
    return $specs;
  }
  
  function getVersion(){
    return $this->owner_segment->getVersion();
  }
  
  /**
   * @return CHL7v2Segment
   */
  function getSegment(){
    return $this->owner_segment;
  }
  
  /**
   * @return CHL7v2Message
   */
  function getMessage(){
    return $this->owner_segment->getMessage();
  }
  
  function getPath($separator = ".", $with_name = false){
    if ($with_name) {
      return array($this->name);
    }
    
    $self_pos = explode($separator, $this->name);
    return array((int)$self_pos[1]);
  }
  
  function getTypeTitle(){
    $str = $this->datatype;
    
    if ($this->length) {
      $str .= "[$this->length]";
    }
    
    return $str;
  }
  
  function __toString(){
    $rs = $this->getMessage()->repetitionSeparator;
    
    if (CHL7v2Message::$decorateToString) {
      $rs = "<span class='rs'>$rs</span>";
    }
    
    if (empty($this->items)) {
      $item = new CHL7v2FieldItem($this, $this->meta_spec, 0);
      $items = array($item);
    }
    else {
      $items = $this->items;
    }
    
    $str = implode($rs, $items);
    
    if (CHL7v2Message::$decorateToString) {
      $str = "<span class='entity field' id='entity-er7-$this->id'>$str</span>";
    }
    
    return $str;
  }
}
