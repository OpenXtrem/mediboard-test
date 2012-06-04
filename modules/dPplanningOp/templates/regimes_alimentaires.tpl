{{mb_default var=prefix value=modal}}

<button 
  type="button" 
  class="new" 
  onclick="Modal.open('{{$prefix}}-regimes', { closeOnClick: $('{{$prefix}}-regimes').down('button.tick') } );"
>
  R�gime alimentaire
</button>

<table id="{{$prefix}}-regimes" style="display: none;">
  <tr>
    <th class="category" colspan="2">R�gimes alimentaires</th>
  </tr>
  {{assign var=fields value="-"|explode:"hormone_croissance-repas_sans_sel-repas_sans_porc-repas_diabete-repas_sans_residu"}}
  {{foreach from=$fields item=_field}}
  <tr>
    <th>{{mb_label object=$sejour field=$_field}}</th>
    <td>{{mb_field object=$sejour field=$_field onchange="Value.synchronize(this, 'editSejour');"}}</td>
  </tr>
  {{/foreach}}
  <tr>
    <td class="button" colspan="2">
      <button class="tick" type="button">{{tr}}Validate{{/tr}}</button>
    </td>
  </tr>
</table>
