<table>
  <tr>
    <th>{{mb_label object=$operation field="exec_tarif"}}</th>
    <td>
      <!-- Formulaire date d'�x�cution de tarif -->
      <form name="editExecTarif" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
        {{mb_key object=$operation}}
        {{mb_class object=$operation}}
        {{mb_field object=$operation field="exec_tarif" form="editExecTarif" register=true onchange="this.form.onsubmit();"}}
      </form>
    </td>
    <th><label for="_tarif_id">Tarif</label></th>
    <td>
      <form name="selectTarif" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: reloadActes.curry({{$operation->_id}}, {{$operation->chir_id}})});">
        {{mb_class object=$operation}}
        {{mb_key object=$operation}}
        <input type="hidden" name="_bind_tarif" value="1"/>
        <input type="hidden" name="_delete_actes" value="0"/>
        <input type="hidden" name="_datetime" value="{{$operation->_datetime}}">

        <select name="_tarif_id" class="str" onchange="this.form.onsubmit();">
          <option value="" selected="selected">&mdash; {{tr}}Choose{{/tr}}</option>
          {{if $tarifs.user|@count}}
            <optgroup label="Tarifs praticien">
              {{foreach from=$tarifs.user item=_tarif}}
                <option value="{{$_tarif->_id}}" {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
              {{/foreach}}
            </optgroup>
          {{/if}}
          {{if $tarifs.func|@count}}
            <optgroup label="Tarifs cabinet">
              {{foreach from=$tarifs.func item=_tarif}}
                <option value="{{$_tarif->_id}}" {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
              {{/foreach}}
            </optgroup>
          {{/if}}
          {{if $conf.dPcabinet.Tarifs.show_tarifs_etab && $tarifs.group|@count}}
            <optgroup label="Tarifs �tablissement">
              {{foreach from=$tarifs.group item=_tarif}}
                <option value="{{$_tarif->_id}}" {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
              {{/foreach}}
            </optgroup>
          {{/if}}
        </select>
      </form>
    </td>
  </tr>
</table>