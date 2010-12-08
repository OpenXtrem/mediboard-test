<script type="text/javascript">
Main.add(function () {
  var form = getForm("edit_reference");
  updateUnitQuantity(form.quantity, "equivalent_quantity");
  updateUnitQuantity(form.mdq, "equivalent_quantity_mdq");
});

function updateUnitQuantity(element, view) {
  $(view).update('('+(element.value * element.form._unit_quantity.value)+' '+element.form._unit_title.value+')');
}

function updatePrice(type, form) {
  var value = form[type].value,
      quantity = form.quantity.value || 1,
      product_quantity = "{{$reference->_ref_product->quantity}}" || 0;
  
  switch (type) {
    case "price": 
      $V(form._cond_price, (value/quantity).toFixed(4), false);
      $V(form._unit_price, (value/quantity/product_quantity).toFixed(4), false);
    break;
    
    case "_cond_price":
      $V(form.price, (value*quantity).toFixed(2), false);
      $V(form._unit_price, (value/product_quantity).toFixed(4), false);
    break;
    
    case "_unit_price":
      $V(form.price, (value*quantity*product_quantity).toFixed(2), false);
      $V(form._cond_price, (value*product_quantity).toFixed(4), false);
    break;
  }
}
</script>

<a class="button new" href="?m={{$m}}&amp;tab=vw_idx_reference&amp;reference_id=0">{{tr}}CProductReference-title-create{{/tr}}</a>
<form name="edit_reference" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
<input type="hidden" name="dosql" value="do_reference_aed" />
<input type="hidden" name="reference_id" value="{{$reference->_id}}" />
<input type="hidden" name="del" value="0" />
<input type="hidden" name="_unit_quantity" value="{{$reference->_ref_product->_unit_quantity}}" onchange="updateUnitQuantity(this.form.quantity, 'equivalent_quantity')" />
<input type="hidden" name="_unit_title" value="{{$reference->_ref_product->_unit_title}}" />

<table class="form">
  {{mb_include module=system template=inc_form_table_header object=$reference}}
	
  {{if $reference->cancelled == 1}}
  <tr>
    <th class="category cancelled" colspan="10">
      {{mb_label object=$reference field=cancelled}}
    </th>
  </tr>
  {{/if}}
	
  <tr>
    <th>{{mb_label object=$reference field="societe_id"}}</th>
    <td>
      {{mb_field object=$reference field=societe_id form="edit_reference" autocomplete="true,1,50,false,true" 
                 style="width: 15em;"}}
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$reference field="product_id"}}</th>
    <td>
      <input type="hidden" name="product_id" value="{{$reference->product_id}}" class="{{$reference->_props.product_id}}" />
      <input type="text" name="product_name" value="{{$reference->_ref_product->name}}" size="40" readonly="readonly" ondblclick="ProductSelector.init()" />
      <button class="search notext" type="button" onclick="ProductSelector.init()">{{tr}}Search{{/tr}}</button>
      <button class="edit notext" type="button" onclick="location.href='?m=dPstock&amp;tab=vw_idx_product&amp;product_id='+this.form.product_id.value">{{tr}}Edit{{/tr}}</button>
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$reference field="code"}}</th>
    <td>{{mb_field object=$reference field="code"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$reference field="supplier_code"}}</th>
    <td>{{mb_field object=$reference field="supplier_code"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$reference field="quantity"}}</th>
    <td>
      {{mb_field object=$reference field="quantity" increment=1 form=edit_reference min=1 size=4 onchange="updateUnitQuantity(this, 'equivalent_quantity'); updatePrice('price', this.form);if($('CReference-quantity')) $('CReference-quantity').update(this.value);"}}
      <input type="text" name="packaging" readonly="readonly" value="{{$reference->_ref_product->packaging}}" style="border: none; background: transparent; width: 5em; color: inherit;" onchange="this.form.packaging_2.value=this.value"/>
      <span id="equivalent_quantity"></span>
    </td>
  </tr>
  
  <tr {{if !$conf.dPstock.CProductReference.use_mdq}}style="display: none"{{/if}}>
    <th>{{mb_label object=$reference field="mdq"}}</th>
    <td>{{mb_field object=$reference field="mdq" increment=1 form=edit_reference min=1 size=4 onchange="updateUnitQuantity(this, 'equivalent_quantity_mdq')"}}
      <input type="text" name="packaging_2" readonly="readonly" value="{{$reference->_ref_product->packaging}}" style="border: none; background: transparent; width: 5em; color: inherit;"/>
      <span id="equivalent_quantity_mdq"></span>
    </td>
  </tr>
  
  {{assign var=sub_quantity value=$reference->_ref_product->quantity}}
  <tr>
    <th>{{mb_label object=$reference field="price"}}</th>
    <td>
      {{mb_field object=$reference field="price" increment=1 form=edit_reference min=0 size=4 
                 onchange="updatePrice('price', this.form)"}}
      (par {{if $reference->_ref_product->packaging}}
        <span id="CReference-quantity">{{$reference->quantity}}</span> {{$reference->_ref_product->packaging}}{{else}}r�f�rence{{/if}})
    </td>
  </tr>
  
  <tr {{if !$conf.dPstock.CProductReference.show_cond_price}}style="display: none"{{/if}}>
    <th>{{mb_label object=$reference field="_cond_price"}}</th>
    <td>
      {{mb_field object=$reference field="_cond_price" increment=1 form=edit_reference min=0 size=4 
                 onchange="updatePrice('_cond_price', this.form)"}}
      (par {{$reference->_ref_product->packaging|ternary:$reference->_ref_product->packaging:"conditionnement"}})
    </td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$reference field="_unit_price"}}</th>
    <td>
      {{mb_field object=$reference field="_unit_price" increment=1 form=edit_reference min=0 size=4 
                 onchange="updatePrice('_unit_price', this.form)"}}
      (par {{$reference->_ref_product->item_title|ternary:$reference->_ref_product->item_title:"unit� de d�livrance"}})
    </td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$reference field="tva"}}</th>
    <td>{{mb_field object=$reference field="tva" increment=1 form=edit_reference decimals=1 min=0 size=2}}</td>
  </tr>
  <tr>
    <td class="button" colspan="4">
      {{if $reference->_id}}
      <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
			
      {{mb_field object=$reference field=cancelled hidden=1}}
      <script type="text/javascript">
        function confirmCancel(element) {
          var form = element.form;
          var element = form.cancelled;
          
          // Cancel 
          if ($V(element) != "1") {
            if (confirm("Voulez-vous vraiment archiver cette r�f�rence ?")) {
              $V(element, "1");
              form.submit();
              return;   
            }
          }
              
          // Restore
          if ($V(element) == "1") {
            if (confirm("Voulez-vous vraiment r�tablir cette r�f�rence ?")) {
              $V(element, "0");
              form.submit();
              return;
            }
          }
        }
        
      </script>
      
      <button class="{{$reference->cancelled|ternary:"change":"cancel"}}" type="button" onclick="confirmCancel(this);">
        {{tr}}{{$reference->cancelled|ternary:"Restore":"Archive"}}{{/tr}}
      </button>
						
      <button type="button" class="trash" onclick="confirmDeletion(this.form,{typeName:'',objName:'{{$reference->_view|smarty:nodefaults|JSAttribute}}'})">
        {{tr}}Delete{{/tr}}
      </button>
      {{else}}
      <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
      {{/if}}
    </td>
  </tr>        
</table>
</form>
