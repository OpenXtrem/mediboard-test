<?php
/**
 * $Id$
 * 
 * @package    Mediboard
 * @subpackage stock
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version    $Revision$
 */

class CProductOrder extends CMbMetaObject {
  public $order_id;

  // DB Fields
  public $date_ordered;
  public $comments;
  public $societe_id;
  public $group_id;
  public $address_class;
  public $address_id;
  public $locked;
  public $order_number;
  public $bill_number;
  public $cancelled;
  public $deleted;
  public $received;

  /** @var CProductOrderItem[] */
  public $_ref_order_items;

  /** @var CProductReception[] */
  public $_ref_receptions;

  /** @var CSociete */
  public $_ref_societe;

  /** @var CGroups*/
  public $_ref_group;

  /** @var CGroups|CFunctions|CBlocOperatoire */
  public $_ref_address;

  // Form fields
  public $_total;
  public $_total_tva;
  public $_status;
  public $_count_received;
  public $_count_renewed;
  public $_date_received;
  public $_received;
  public $_partial;
  public $_customer_code;
  public $_context_bl;
  public $_septic;
  public $_has_lot_numbers = false;
  
  // actions
  public $_order;
  public $_receive;
  public $_autofill;
  public $_redo;
  public $_reset;
  
  static $_return_form_label = "Bon de retour";
  
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'product_order';
    $spec->key   = 'order_id';
    return $spec;
  }

  function getBackProps() {
    $backProps = parent::getBackProps();
    $backProps['order_items'] = 'CProductOrderItem order_id';
    return $backProps;
  }

  function getProps() {
    $props = parent::getProps();
    $props['date_ordered']    = 'dateTime seekable';
    $props['order_number']    = 'str maxLength|64 seekable protected';
    $props['bill_number']     = 'str maxLength|64 protected';
    $props['societe_id']      = 'ref notNull class|CSociete seekable autocomplete|name';
    $props['group_id']        = 'ref notNull class|CGroups show|0';
    $props['address_class']   = 'enum notNull list|CGroups|CFunctions|CBlocOperatoire';
    $props['address_id']      = 'ref notNull class|CMbObject meta|address_class';
    $props['comments']        = 'text';
    $props['locked']          = 'bool show|0';
    $props['cancelled']       = 'bool show|0';
    $props['deleted']         = 'bool show|0';
    $props['received']        = 'bool';
    $props['object_id']       = 'ref class|CMbObject meta|object_class';
    $props['object_class']    = 'enum list|COperation show|0'; // only COperation for now
    
    $props['_total']          = 'currency show|1';
    $props['_total_tva']      = 'currency show|1';
    $props['_status']         = 'enum list|opened|locked|ordered|received|cancelled show|1';
    $props['_count_received'] = 'num pos';
    $props['_date_received']  = 'dateTime';
    $props['_received']       = 'bool';
    $props['_partial']        = 'bool';
    $props['_customer_code']  = 'str show|1';
    
    $props['_order']          = 'bool';
    $props['_receive']        = 'bool';
    $props['_autofill']       = 'bool';
    $props['_redo']           = 'bool';
    $props['_reset']          = 'bool';
    return $props;
  }

  /**
   * Counts this received product's items
   *
   * @return int
   */
  function countReceivedItems() {
    $this->loadRefsOrderItems();
    $count = 0;
    
    foreach ($this->_ref_order_items as $item) {
      if ($item->isReceived()) {
        $count++;
      }
    }
    return $this->_count_received = $count;
  }

  /**
   * @return int
   */
  function countRenewedItems(){
    $this->loadRefsOrderItems();
    $count = 0;
    
    foreach ($this->_ref_order_items as $item) {
      if ($item->renewal) {
        $count++;
      }
    }
    return $this->_count_renewed = $count;
  }

  /**
   * @return bool
   */
  function containsRenewalLines() {
    $this->loadRefsOrderItems();
    
    foreach ($this->_ref_order_items as $_item) {
      if ($_item->renewal) {
        return true;
      }
    }

    return false;
  }

  /**
   * Marks every order's items as received
   *
   * @return string|null
   */
  function receive() {
    $this->loadRefsOrderItems();

    // we mark all the items as received
    foreach ($this->_ref_order_items as $item) {
      if (!$item->isReceived()) {
        $reception = new CProductOrderItemReception();
        $reception->quantity = $item->quantity - $item->_quantity_received;
        $reception->order_item_id = $item->_id;
        $reception->date = CMbDT::dateTime();
        if ($msg = $reception->store()) {
          return $msg;
        }
      }
    }
  }
  
  /**
   * Fills the order in function of the stocks and future stocks
   */
  function autofill() {
    $this->updateFormFields();
    $this->completeField('societe_id');
    
    // if the order has not been ordered yet
    // and not partially received
    // and not totally received
    // and not cancelled
    // and not deleted
    if (!$this->date_ordered && !$this->_received && !$this->cancelled && !$this->deleted) {
      
      // we empty the order
      foreach ($this->_ref_order_items as $item) {
        $item->delete();
      }
    }
    
    // we retrieve all the stocks
    $stock = new CProductStockGroup();
    $list_stocks = $stock->loadList();
    
    // for every stock
    foreach ($list_stocks as $stock) {
      $stock->loadRefsFwd();
      
      // if the stock is in the "red" or "orange" zone
      if ($stock->_zone_future < 2) {
        $current_stock = $stock->quantity;
        $expected_stock = $stock->getOptimumQuantity();
        
        if ($current_stock < $expected_stock) {
          // we get the best reference for this product
          $where = array(
            'product_id' => " = '{$stock->_ref_product->_id}'",
            'societe_id' => " = '$this->societe_id'",
          );
          $orderby = 'price ASC';
          $best_reference = new CProductReference();
          
          if ($best_reference->loadObject($where, $orderby) && $best_reference->quantity > 0) {
            // we store the new order item in the current order
            $order_item = new CProductOrderItem();
            $order_item->order_id = $this->_id;
            $order_item->quantity = $expected_stock - $current_stock;
            $order_item->reference_id = $best_reference->_id;
            $order_item->unit_price = $best_reference->price;
            $order_item->store();
          }
        }
      }
    }
  }
  
  /**
   * Fills a new order with the same articles
   */
  function redo() {
    $this->load();
    $order = new CProductOrder();
    $order->societe_id   = $this->societe_id;
    $order->group_id     = $this->group_id;
    $order->locked       = 0;
    $order->cancelled    = 0;
    $order->order_number = uniqid(rand());
    $order->store();
    $order->order_number = $order->getUniqueNumber();
    $order->store();
    
    $this->loadRefsOrderItems();
    foreach ($this->_ref_order_items as $item) {
      $item->loadRefs();
      $new_item = new CProductOrderItem();
      $new_item->reference_id = $item->reference_id;
      $new_item->order_id = $order->order_id;
      $new_item->quantity = $item->quantity;
      $new_item->unit_price = $item->_ref_reference->price;
      $new_item->store();
    }
  }
  
  function reset() {
    $this->load();
    $this->date_ordered = '';
    $this->locked = 0;
    $this->cancelled = 0;
    
    $this->loadRefsOrderItems();
    foreach ($this->_ref_order_items as $item) {
      foreach ($item->_ref_receptions as $reception) {
        $reception->delete();
      }
    }
  }

  /**
   * Search a product
   *
   * @param string  $type     The type of orders we are looking for [waiting|locked|pending|received|cancelled]
   * @param string  $keywords [optional]
   * @param integer $limit    = 30 [optional]
   * @param array   $where    Where additionnal
   *
   * @return array The list of orders
   */
  function search($type, $keywords = "", $limit = 30, $where = array()) {
    global $g;

    $leftjoin = array();
    $leftjoin['product_order_item'] = 'product_order.order_id = product_order_item.order_id';
    $leftjoin['product_order_item_reception'] = 'product_order_item.order_item_id = product_order_item_reception.order_item_id';
    $leftjoin['product_reference'] = 'product_order_item.reference_id = product_reference.reference_id';
    $leftjoin['product'] = 'product_reference.product_id = product.product_id';
    
    // if keywords have been provided
    if ($keywords) {
      $societe = new CSociete();
      $where_or = array();

      // we seek among the societes
      $where_societe_or = array();
      foreach ($societe->getSeekables() as $field => $spec) {
        $where_societe_or[] = "societe.$field LIKE '%$keywords%'";
      }
      $where_societe[] = implode(' OR ', $where_societe_or);
      
      // we seek among the orders
      foreach ($this->getSeekables() as $field => $spec) {
        $where_or[] = "product_order.$field LIKE '%$keywords%'";
      }
      $where_or[] = 'product_order.societe_id ' . CSQLDataSource::prepareIn(array_keys($societe->loadList($where_societe)));
      $where[] = implode(' OR ', $where_or);
    }
    
    $orderby = 'product_order.date_ordered DESC, product_order_item_reception.date DESC';
    $where['product_order.deleted']      = " = 0";
    $where['product_order.cancelled']    = " = 0";
    $where['product_order.locked']       = " = 0";
    $where['product_order.date_ordered'] = "IS NULL";
    $where['product_order.received']     = " != '1'";
    
    // Exclude return orders (Bon de retour)
    $where['product_order.comments']     = $this->_spec->ds->prepare("!= % OR product_order.comments IS NULL", CProductOrder::$_return_form_label);
    
    switch ($type) {
      case 'waiting':
        break;
      case 'locked':
        $where['product_order.locked']       = " = 1";
        break;
      case 'pending':
        $where['product_order.locked']       = " = 1";
        $where['product_order.date_ordered'] = "IS NOT NULL";
        break;
      case 'received':
        $where['product_order.locked']       = " = 1";
        $where['product_order.date_ordered'] = "IS NOT NULL";
        $where['product_order.received']     = " = '1'";
        break;
      default:
      case 'cancelled':
        $where['product_order.cancelled']    = " = 1";
        unset($where['product_order.locked']);
        unset($where['product_order.received']);
        unset($where['product_order.date_ordered']);
        break;
    }
    
    $where['product_order.group_id'] = " = '".CProductStockGroup::getHostGroup()."'";
    
    $old_limit = $limit;
    
    if ($type === 'pending') {
      $limit = 200;
    }
    
    $groupby = "product_order.order_id";
    $orders_list  = $this->loadList($where, $orderby, $limit, $groupby, $leftjoin);
    
    // bons de facturation seulement
    if ($type === 'pending') {
      foreach ($orders_list as $_id => $_order) {
        if (!$_order->containsRenewalLines()) {
          unset($orders_list[$_id]);
        }
      }
      $this->_search_count = count($orders_list);
      
      $orders_list = CRequest::artificialLimit($orders_list, $old_limit);
    }
    else {
      $this->_search_count = count($this->countMultipleList($where, null, $groupby, $leftjoin));
    }
    
    /*if ($type === 'pending') {
      $list = array();
      foreach ($orders_list as $_order) {
        if ($_order->countReceivedItems() < $_order->countBackRefs("order_items")) {
          $list[] = $_order;
        }
      }
      $orders_list = $list;
    }
    
    else if ($type === 'received') {
      $list = array();
      foreach ($orders_list as $_order) {
        if ($_order->countReceivedItems() >= $_order->countBackRefs("order_items")) {
          $list[] = $_order;
        }
      }
      $orders_list = $list;
    }*/
    
    foreach ($orders_list as $_order) {
      $_order->loadRefsFwd();
    }
    
    return $orders_list;
  }
  
  function getUniqueNumber() {
    $format     = CAppUI::conf('dPstock CProductOrder order_number_format');
    $contextual = CAppUI::conf('dPstock CProductOrder order_number_contextual');
    
    if (strpos($format, '%id') === false) {
      $format .= '%id';
    }
    
    $format = str_replace('%id', str_pad($this->_id ? $this->_id : 0, 4, '0', STR_PAD_LEFT), $format);
    $number = CMbDT::transform(null, null, $format);
    
    if ($contextual) {
      $this->completeField("object_class");
      $bl = ($this->object_class === "COperation") || $this->_context_bl;
      $number = ($bl ? "BL" : "PH") . $number;
    }
 
    return $number;
  }
  
  function getReceptions(){
    if (!$this->_id) {
      return $this->_ref_receptions = array();
    }
      
    $rec = new CProductReception;
    return $this->_ref_receptions = $rec->findFromOrder($this->_id);
  }

  function updateFormFields() {
    parent::updateFormFields();
    
    $this->completeField("received");
    
    if (!$this->comments) {
      $group = CGroups::loadCurrent();
      if ($group->pharmacie_id) {
        $this->comments = $group->loadRefPharmacie()->soustitre;
      }
    }
    
    // Total
    $items_count = $this->countBackRefs("order_items");
    $this->updateTotal();
    $this->loadRefsFwd();
    
    // Status
    $this->_status = "opened";
    if ($this->locked) {
      $this->_status = "locked";
    }
    if ($this->date_ordered) {
      $this->_status = "ordered";
    }
    if ($this->received) {
      $this->_status = "received";
    }
    if ($this->cancelled) {
      $this->_status = "cancelled";
    }
    
    // View
    $this->_view  = "$this->order_number - ";
    $this->_view .= $this->societe_id ? $this->_ref_societe->_view : "";
    
    /*
    $this->_view .= " - $items_count article".(($items_count > 1) ? 's' : '');
    if ($this->_total !== null) {
      $this->_view .= ", total = $this->_total ".CAppUI::conf("currency_symbol");
    }*/
    
    $customer_code = $this->societe_id ? $this->_ref_societe->customer_code : null;
    if (!$customer_code) {
      $customer_code = "-";
    }
    $this->_customer_code = $customer_code;
  }
  
  function updateTotal(){
    $this->_total = 0;
    $this->_total_tva = 0;
    $this->loadRefsOrderItems();
    foreach ($this->_ref_order_items as $item) {
      $item->updateFormFields();
      $this->_total += $item->_price;
      $this->_total_tva += $item->_price + ($item->_price * $item->tva / 100);
    }
  }
  
  function updateCounts(){
    $this->countReceivedItems(); // makes loadRefsOrderItems
    $this->countRenewedItems(); // makes loadRefsOrderItems
    
    // we guess the reception date by geeting the latest reception item's date
    foreach ($this->_ref_order_items as $item) {
      $item->loadRefsReceptions();
      $rec = reset($item->_ref_receptions);
      $this->_date_received = $rec ? $rec->date : null;
    }
    
    // if no reception item, we get the last log for the "received" field
    if (!$this->_date_received && $this->received) {
      $log = $this->loadLastLogForField("received");
      if ($log && $log->_id) {
        $this->_date_received = $log->date;
      }
    }
    
    $items_count = count($this->_ref_order_items);

    $this->_received = $this->received || ($items_count >= $this->_count_received);
    $this->_partial = !$this->_received && ($this->_count_received > 0);
  }

  /**
   * @param bool $force
   *
   * @return CProductOrderItem[]
   */
  function loadRefsOrderItems($force = false) {
    if ($this->_ref_order_items && !$force) {
      return $this->_ref_order_items;
    }
    
    $ljoin = array(
      "product_reference" => "product_reference.reference_id = product_order_item.reference_id",
      "product"           => "product.product_id = product_reference.product_id",
    );

    $order = "renewal, product.classe_comptable, product.code";

    return $this->_ref_order_items = $this->loadBackRefs('order_items', $order, null, null, $ljoin);
  }

  /**
   * @return CGroups|CFunctions|CBlocOperatoire
   */
  function loadRefAddress(){
    $this->_ref_address = $this->loadFwdRef("address_id", true);

    if ($this->address_class == "CFunctions" || $this->address_class == "CBlocOperatoire") {
      $this->_ref_address->loadRefGroup();
    }

    return $this->_ref_address;
  }
  
  function updatePlainFields() {
    $this->updateFormFields();
    
    if ($this->_autofill) {
      $this->_autofill = null;
      $this->autofill();
    }
    
    if ($this->_order && !$this->date_ordered) {
      if (count($this->_ref_order_items) != 0) {
        $this->date_ordered = CMbDT::dateTime();
      }
      $this->_order = null;
    }
    
    // If the flag _receive is true, and if not every item has been received, we mark all them as received
    if ($this->_receive && !$this->_received) {
      $this->_receive = null;
      $this->receive();
    }
    
    if ($this->_redo) {
      $this->_redo = null;
      $this->redo();
    }
    
    if ($this->_reset) {
      $this->_reset = null;
      $this->reset();
    }
  }
  
  function store () {
    if (!$this->_id && $this->object_class && $this->object_id && empty($this->comments)) {
      $this->loadTargetObject();
      if ($this->object_class == "COperation") {
        if ($this->_septic) {
          $this->comments = "D�st�rilis�";
        }
        else {
          $this->_ref_object->loadRefSejour();
          $this->_ref_object->_ref_sejour->loadNDA();
          $num_dos = $this->_ref_object->_ref_sejour->_NDA;
          $this->comments = "Num�ro de s�jour: $num_dos";
        }
      }
    }

    if (!$this->_id && !$this->address_id) {
      $group = $this->loadRefGroup();
      if ($group->pharmacie_id) {
        $this->address_class = "CFunctions";
        $this->address_id    = $group->pharmacie_id;
      }
      else {
        $this->address_class = "CGroups";
        $this->address_id    = $this->group_id;
      }
    }
    
    // gestion des bons de commandes n'ayant pas de lignes renouvelables
    $this->completeField("object_id", "object_class", "comments");
    if (
        $this->_order &&
        ($this->object_id || strpos(self::$_return_form_label, $this->comments) === 0) &&
        $this->countRenewedItems() == 0
    ) {
      $this->received = 1;
    }
    
    if (!$this->_id && empty($this->order_number)) {
      $this->order_number = uniqid(rand());
      if ($msg = parent::store()) {
        return $msg;
      }
      $this->order_number = $this->getUniqueNumber();
    }
    
    return parent::store();
  }

  function loadRefsBack(){
    $this->loadRefsOrderItems();
  }

  function loadRefsFwd(){
    parent::loadRefsFwd();

    $this->loadRefGroup();
    $this->loadRefSociete();
  }

  /**
   * @param bool $cache
   *
   * @return CGroups
   */
  function loadRefGroup($cache = true){
    return $this->_ref_group = $this->loadFwdRef("group_id", $cache);
  }

  /**
   * @param bool $cache
   *
   * @return CSociete
   */
  function loadRefSociete($cache = true) {
    return $this->_ref_societe = $this->loadFwdRef("societe_id", $cache);
  }
  
  function delete() {
    $items_count = $this->countBackRefs("order_items");
    
    if ($items_count == 0 || !$this->date_ordered) {
      return parent::delete();
    }

    if ($this->date_ordered && !$this->_received) {
      // TODO: here : cancel order !!
      return parent::delete();
    }

    return "This order cannot be deleted";
  }
  
  function loadView(){
    parent::loadView();
    
    foreach ($this->_ref_order_items as $_item) {
      if ($_item->lot_id) {
        $_item->loadRefLot();
        $this->_has_lot_numbers = true;
      }
    }
  }

  /**
   * Get the order's label
   *
   * @return string
   */
  function getLabel(){
    if ($this->object_id) {
      return "Bon de commande / Facturation";
    }
    
    if (strpos($this->comments, self::$_return_form_label) === 0) {
      return "Bon de retour";
    }
    
    return "Bon de commande";
  }

  /**
   * @param int $permType
   *
   * @return bool
   */
  function getPerm($permType) {
    $this->loadRefsOrderItems();

    foreach ($this->_ref_order_items as $item) {
      if (!$item->getPerm($permType)) {
        return false;
      }
    }

    return true;
  }
}
