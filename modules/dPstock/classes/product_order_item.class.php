<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage dPstock
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

class CProductOrderItem extends CMbObject {
  // DB Table key
  var $order_item_id      = null;

  // DB Fields
  var $reference_id       = null;
  var $order_id           = null;
  var $quantity           = null;
  var $unit_price         = null; // In the case the reference price changes

  // Object References
  //    Single
  /**
   * @var CProductOrder
   */
  var $_ref_order         = null;
  /**
   * @var CProductReference
   */
  var $_ref_reference     = null;
  
  //    Multiple
  var $_ref_receptions    = null;

  // Form fields
  var $_price             = null;
  var $_cond_price        = null;
  var $_date_received     = null;
  var $_quantity_received = null;
  var $_unit_quantity     = null;
  var $_is_unit_quantity  = null;

  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'product_order_item';
    $spec->key   = 'order_item_id';
    return $spec;
  }

  function getProps() {
    $specs = parent::getProps();
    $specs['reference_id']       = 'ref notNull class|CProductReference';
    $specs['order_id']           = 'ref class|CProductOrder'; // can be null because of gifts
    $specs['quantity']           = 'num notNull pos';
    $specs['unit_price']         = 'currency precise';
    $specs['_cond_price']        = 'currency';
    $specs['_price']             = 'currency';
    $specs['_quantity_received'] = 'num';
    $specs['_unit_quantity']     = 'num';
    return $specs;
  }

  function receive($quantity, $code = null) {
    if ($this->_id) {
      $reception = new CProductOrderItemReception();
      $reception->order_item_id = $this->_id;
      $reception->quantity = $quantity;
      $reception->date = mbDateTime();
      $reception->code = $code;
      return $reception->store();
    } else {
      return "$this->_class_name::receive failed : order_item must be stored before";
    }
  }
  
  function isReceived() {
    $this->updateReceived();
  	return $this->_quantity_received >= $this->quantity;
  }
  
  function getStock() {
    $this->loadReference();
    $this->loadOrder();
    
    $stock = new CProductStockGroup();
    $stock->group_id = $this->_ref_order->group_id;
    $stock->product_id = $this->_ref_reference->product_id;
    $stock->loadMatchingObject();
    return $stock;
  }
  
  function updateReceived() {
    $this->loadRefsReceptions();
    
    $quantity = 0;
    foreach ($this->_ref_receptions as $reception) {
      $quantity += $reception->quantity;
    }
    $this->_quantity_received = $quantity;
  }
  
  function updateFormFields() {
    parent::updateFormFields();
    $this->updateReceived();
    $this->getUnitQuantity();
    
    $this->_view = $this->_ref_reference->_view;
    $this->_price = $this->unit_price * $this->quantity;
    $this->_cond_price = $this->_unit_quantity ? $this->_price / $this->_unit_quantity : 0;
  }
  
  function getUnitQuantity(){
    $this->completeField("quantity");
    $this->loadReference();
    return $this->_unit_quantity = $this->quantity * $this->_ref_reference->_unit_quantity;
  }
  
  function updateDBFields() {
    parent::updateDBFields();
    
    if ($this->_unit_quantity) {
      $this->completeField("reference_id");
      $this->loadReference();
      $this->quantity = $this->_unit_quantity / $this->_ref_reference->_unit_quantity;
    }
  }
  
  function loadReference() {
    return $this->_ref_reference = $this->loadFwdRef("reference_id", true);
  }
  
  function loadOrder($cache = true) {
    $this->completeField("order_id");
    return $this->_ref_order = $this->loadFwdRef("order_id", $cache);
  }  
  
  function loadRefsReceptions() {
    return $this->_ref_receptions = $this->loadBackRefs('receptions', 'date DESC');
  }

  function loadRefsFwd() {
    parent::loadRefsFwd();

    $this->loadReference();
    $this->loadOrder();
  }
  
  function getBackProps() {
    $backProps = parent::getBackProps();
    $backProps['receptions'] = 'CProductOrderItemReception order_item_id';
    return $backProps;
  }
  
  function loadRefsBack() {
    $this->loadRefsReceptions();
  }

  function store() {
    $this->loadRefsFwd();
    
    if($this->order_id && $this->reference_id && !$this->_id) {
      $where = array(
        'order_id'     => "= '$this->order_id'",
        'reference_id' => "= '$this->reference_id'",
      );

      if (isset($this->_is_unit_quantity)) {
        $this->quantity /= ($this->_ref_reference->quantity * $this->_ref_reference->_ref_product->quantity);
      }
      
      $duplicateKey = new CProductOrderItem();
      if($duplicateKey->loadObject($where)) {
        $duplicateKey->loadRefsFwd();
        $this->_id = $duplicateKey->_id;
        $this->quantity += $duplicateKey->quantity;
        $this->unit_price = $duplicateKey->unit_price;
      } else {
        $this->unit_price = $this->_ref_reference->price;
      }
    }
    
    if (!$this->_id && ($stock = $this->getStock())) {
      $stock->loadRefOrders();
      if ($stock->_zone_future > 2) {
        CAppUI::setMsg("Attention : le stock optimum risque d'�tre d�pass�", UI_MSG_WARNING);
      }
    }
    
    return parent::store();
  }
  
  function delete(){
    $order = $this->loadOrder(false);
    
    if ($msg = parent::delete()){
      return $msg;
    }
    
    if ($order->countBackRefs("order_items") == 0) {
      return $order->delete();
    }
  }
}
?>