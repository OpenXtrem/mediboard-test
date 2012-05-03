<script type="text/javascript">
  Main.add(function() {
    var form = getForm("newNaissance");
    var url = new Url("system", "ajax_seek_autocomplete");
    url.addParam("object_class", "CMediusers");
    url.addParam('show_view', true);
    url.addParam("input_field", "_prat_autocomplete");
    url.autoComplete(form.elements._prat_autocomplete, null, {
      minChars: 2,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field,selected) {
        $V(field.form['praticien_id'], selected.getAttribute('id').split('-')[2]);
      },
      callback:  function(input, queryString) {
        if (form._only_pediatres.checked) {
          queryString += "&ljoin[spec_cpam]=spec_cpam.spec_cpam_id  = users_mediboard.spec_cpam_id";
          queryString += "&where[spec_cpam.text]=PEDIATRE";
        }
        return queryString;
      }
    });
  });
</script>

<form name="newNaissance" method="post" action="?"
  onsubmit="return onSubmitFormAjax(this, {onComplete: function() { Control.Modal.close(); Naissance.reloadNaissances('{{$operation_id}}'); }})">
  <input type="hidden" name="m" value="maternite" />
  <input type="hidden" name="del" value="0" />
  {{mb_field object=$naissance field=sejour_maman_id hidden=true}}
  {{mb_field object=$naissance field=operation_id hidden=true}}
  <input type="hidden" name="praticien_id" value="{{$sejour->praticien_id}}" />
  
  {{if $provisoire}}
    <input type="hidden" name="dosql" value="do_dossier_provisoire_aed" />
  {{else}}
    <input type="hidden" name="dosql" value="do_create_naissance_aed" />
  {{/if}}
  {{if $callback}}
    <input type="hidden" name="callback" value="{{$callback}}" />
  {{/if}}
  
  {{if $naissance}}
    {{mb_key object=$naissance}}
  {{/if}}
 
  {{if $parturiente}} 
    {{mb_key object=$parturiente}}
  {{/if}}

  {{if $constantes}} 
    {{mb_key object=$constantes}}
  {{/if}}
  
  <table class="form">
    <tr>
      <th class="category" colspan="2">
        Informations sur la naissance
      </th>
    </tr>
    <tr>
      <th>
        {{mb_label object=$patient field="sexe"}}
      </th>
      <td>
        {{mb_field object=$patient field="sexe" emptyLabel="CPatient.sexe."}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$patient field="naissance"}}
      </th>
      <td>
        {{mb_field object=$patient field="naissance" form="newNaissance" register="true"}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$patient field="nom"}}
      </th>
      <td>
        {{mb_field object=$patient field="nom"}}
      </td>
    </tr>
    
    {{if !$provisoire}}
      <tr>
        <th>
          {{mb_label object=$patient field="prenom"}}
        </th>
        <td>
          {{mb_field object=$patient field="prenom"}}
        </td>
      </tr>
      <tr>
        <th>
         {{mb_label object=$naissance field="hors_etab"}}
        </th>
        <td>
          {{mb_field object=$naissance field="hors_etab"}}
        </td>
      </tr>
      <tr>
        <th>
         {{mb_label object=$naissance field="heure"}}
        </th>
        <td>
          {{mb_field object=$naissance field="heure" form="newNaissance" register="true"}}
        </td>
      </tr>
    {{/if}}
    <tr>
      <th>
       {{mb_label object=$naissance field="rang"}}
      </th>
      <td>
        {{mb_field object=$naissance field="rang" size="2" increment="true" form="newNaissance" step="1"}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$sejour field=praticien_id}}
      </th>
      <td>
        <input type="text" name="_prat_autocomplete" value="{{$sejour->_ref_praticien}}" />
        <label>
          <input type="checkbox" name="_only_pediatres" checked="checked" />
          {{tr}}CNaissance-only_pediatres{{/tr}}
        </label>
      </td>
    </tr>
    {{if !$provisoire}}
      <tr>
        <th class="category" colspan="2">{{tr}}CConstantesMedicales{{/tr}}</th>
      </tr>
      <tr>
        <th>
         {{mb_label object=$constantes field=poids}}
        </th>
        <td>
          {{mb_field object=$constantes field=poids size="3"}} {{$list_constantes.poids.unit}}
        </td>
      </tr>
      <tr>
        <th>
         {{mb_label object=$constantes field=taille}}
        </th>
        <td>
          {{mb_field object=$constantes field=taille size="3"}} {{$list_constantes.taille.unit}}
        </td>
      </tr>
      <tr>
        <th>
         {{mb_label object=$constantes field=perimetre_cranien}}
        </th>
        <td>
          {{mb_field object=$constantes field=perimetre_cranien size="3"}} {{$list_constantes.perimetre_cranien.unit}}
        </td>
      </tr>
    {{/if}}
    <tr>
      <td class="button" colspan="2">
        {{if $naissance->_id}}
          <button type="submit" class="submit">{{tr}}Modify{{/tr}}</button>
          <button type="button" class="trash" onclick="Naissance.confirmDeletion(this.form)">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button type="submit" class="submit singleclick">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>