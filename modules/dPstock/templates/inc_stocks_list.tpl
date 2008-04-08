<table class="tbl">
  <tr>
    <th>{{tr}}CProductStock-product_id{{/tr}}</th>
    <th>{{tr}}CProductStock-quantity{{/tr}}</th>
    <th>{{tr}}CProductStock-bargraph{{/tr}}</th>
  </tr>
  
<!-- Stocks list -->
{{foreach from=$list_stocks item=curr_stock}}
  <tr>
    <td><a href="?m={{$m}}&amp;tab=vw_idx_stock&amp;stock_id={{$curr_stock->_id}}" title="{{tr}}CProductStock.modify{{/tr}}">{{$curr_stock->_ref_product->_view}}</a></td>
    <td>{{$curr_stock->quantity}}</td>
    <td>{{include file="inc_bargraph.tpl" stock=$curr_stock}}</td>
  </tr>
{{foreachelse}}
  <tr>
    <td colspan="3">{{tr}}CProductStock.none{{/tr}}</td>
  </tr>
{{/foreach}}
</table>
