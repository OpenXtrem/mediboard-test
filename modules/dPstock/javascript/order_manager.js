/** Submit order function 
 *  Used to submit an order : new or edit order
 *  @param oForm The form containing all the info concerning the order to submit
 *  @param options Options used to execute functions after the submit : {refreshLists, close}
 */
function submitOrder (oForm, options) {
  submitFormAjax(oForm, 'systemMsg',{
    onComplete: function() {
      if (options.close && window.opener) {
        window.close();
      } else {
        refreshOrder($F(oForm.order_id), options);
      }
    }
  });
}

/** Submit order item function
 *  Used to submit an order item : new or edit order item
 *  @param oForm The form containing all the info concerning the order item to submit
 *  @param options Options used to execute functions after the submit : {refreshLists, close}
 */
function submitOrderItem (oForm, options) {
  if (options && options.noAjax) {
    oForm.submit();
  } else {
    submitFormAjax(oForm, 'systemMsg',{
      onComplete: function() {
        refreshOrder($F(oForm.order_id), options); 
        refreshOrderItem($F(oForm.order_item_id));
      }
    });
  }
}

/** The refresh order function
 *  Used to refresh the view of an order
*/
function refreshOrder(order_id, options) {
  if (options.refreshLists) {
    refreshLists();
  }
  url = new Url;
  url.setModuleAction("dPstock","httpreq_vw_order");
  url.addParam("order_id", order_id);
  url.requestUpdate("order-"+order_id, { waitingText: null } );
}

function refreshOrderItem(order_item_id) {
  url = new Url;
  url.setModuleAction("dPstock", "httpreq_vw_order_item");
  url.addParam("order_item_id", order_item_id);
  url.requestUpdate("order-item-"+order_item_id, { waitingText: null } );
}

function confirmOrder() {
  return confirm("Etes-vous sur de vouloir passer cette commande ?");
}

function confirmLock() {
  return confirm("Etes-vous sur de vouloir verrouiller cette commande et la passer � l'�tat de valid�e ?");
}

function refreshListOrders(type, keywords) {
  url = new Url;
  url.setModuleAction("dPstock","httpreq_vw_orders_list");
  url.addParam("type", type);
  url.addParam("keywords", keywords);
  url.requestUpdate("list-orders-"+type, { waitingText: null } );
}

function refreshLists(keywords) {
  if (!window.opener) {
    refreshListOrders("waiting",   keywords);
    refreshListOrders("locked",    keywords);
    refreshListOrders("pending",   keywords);
    refreshListOrders("received",  keywords);
    refreshListOrders("cancelled", keywords);
  } else {
    window.opener.refreshLists();
  }
}

function popupOrder(oForm, width, height) {
  width = width?width:500;
  height = height?height:500;
  
  url = new Url();
  url.setModuleAction("dPstock", "vw_aed_order");
  url.addParam("order_id", $F(oForm.order_id));
  url.addParam("_autofill", oForm._autofill != undefined);
  url.popup(width, height, "Edition/visualisation commande");
}