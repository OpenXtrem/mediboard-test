{{if $_suivi instanceof CObservationMedicale}}
  {{if @$show_patient}}
  <td><strong>{{$_suivi->_ref_sejour->_ref_patient}}</strong></td>
  <td class="text">{{$_suivi->_ref_sejour->_ref_last_affectation->_ref_lit->_view}}</td>
  {{/if}}
  <td><strong>{{tr}}{{$_suivi->_class}}{{/tr}}</strong></td>
  <td>
    <strong>
      <div class="mediuser" style="border-color: #{{$_suivi->_ref_user->_ref_function->color}};">
        {{$_suivi->_ref_user}}
      </div>
    </strong>
  </td>
  <td  style="text-align: center">
    <strong>
      {{mb_ditto name=date value=$_suivi->date|date_format:$conf.date}}
    </strong>
  </td>
  <td>{{$_suivi->date|date_format:$conf.time}}</td>
  <td class="text" colspan="2"
    {{if $_suivi->degre == "high"}} style="background-color: #faa" {{/if}} 
    {{if $_suivi->degre == "info"}} style="background-color: #aaf" {{/if}}>
    <div>
	    <strong>{{mb_value object=$_suivi field=text}}</strong>
    </div>
  </td>
	<td>
  	{{if !$readonly && $_suivi->_canEdit}}
      <form name="Del-{{$_suivi->_guid}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        <input type="hidden" name="dosql" value="do_observation_aed" />
        <input type="hidden" name="del" value="1" />
        <input type="hidden" name="m" value="dPhospi" />
        <input type="hidden" name="observation_medicale_id" value="{{$_suivi->_id}}" />
        <input type="hidden" name="sejour_id" value="{{$_suivi->sejour_id}}" />
        <button type="button" class="trash notext" onclick="submitSuivi(this.form, 1)">{{tr}}Delete{{/tr}}</button>
      </form>
  	  <button type="button" class="edit notext" onclick="addObservation(null, null, '{{$_suivi->_id}}');"></button>
    {{/if}}
  </td>
{{/if}}

{{if $_suivi instanceof CConstantesMedicales}}
  <td>{{tr}}{{$_suivi->_class}}{{/tr}}</td>
  <td>
    {{$_suivi->_ref_user->_view}}
  </td>
  <td style="text-align: center">
     {{mb_ditto name=date value=$_suivi->datetime|date_format:$conf.date}}
  </td>
  <td>{{$_suivi->datetime|date_format:$conf.time}}</td>
  <td colspan="2" class="text">
    {{foreach from=$params key=_key item=_field name="const"}}
      {{if $_suivi->$_key != null && $_key|substr:0:1 != "_"}}
        {{mb_title object=$_suivi field=$_key}} :
        {{if array_key_exists("formfields", $_field)}}
          {{mb_value object=$_suivi field=$_field.formfields.0 size="2" }} / 
          {{mb_value object=$_suivi field=$_field.formfields.1 size="2" }}
        {{else}}
          {{mb_value object=$_suivi field=$_key}}
        {{/if}} {{$_field.unit}},
      {{/if}}
    {{/foreach}}
  </td>
  <td></td>
{{/if}}

{{if $_suivi instanceof CPrescriptionLineElement || $_suivi instanceof CPrescriptionLineComment}}
  <td><strong>Prescription</strong></td>
	<td>
		<strong>
      <div class="mediuser" style="border-color: #{{$_suivi->_ref_praticien->_ref_function->color}};">
        {{mb_value object=$_suivi field="praticien_id"}}
      </div>
    </strong>
	</td>
  <td style="text-align: center">
  	{{mb_ditto name=date value=$_suivi->debut|date_format:$conf.date}}
	</td>
	<td>{{mb_value object=$_suivi field="time_debut"}}</td>
  <td colspan="2">
  	{{if !$readonly}}
		  <button type="button" class="tick" onclick="addTransmissionAdm('{{$_suivi->_id}}','{{$_suivi->_class}}');" style="float: right;">R�aliser ({{$_suivi->_count.transmissions}})</button>
		{{/if}}
		
		{{if $_suivi instanceof CPrescriptionLineElement}}
		<strong>{{$_suivi->_view}}</strong>
		{{/if}}
    {{mb_value object=$_suivi field="commentaire"}}
	</td>
	<td>
    {{if !$readonly && $_suivi->_canEdit}}
      <form name="Del-{{$_suivi->_guid}}" action="?" method="post">
        <input type="hidden" name="m" value="dPprescription" />
        {{if $_suivi instanceof CPrescriptionLineElement}}
          <input type="hidden" name="dosql" value="do_prescription_line_element_aed" />
        {{else}}
          <input type="hidden" name="dosql" value="do_prescription_line_comment_aed" />
        {{/if}}
        <input type="hidden" name="del" value="1" />
        {{mb_key object=$_suivi}}
        <input type="hidden" name="sejour_id" value="{{$_suivi->_ref_prescription->object_id}}" />
        <button type="button" class="trash notext" onclick="submitSuivi(this.form, 1);"></button>
      </form>
      <button type="button" class="edit notext"
        onclick="addPrescription('{{$_suivi->_ref_prescription->object_id}}', '{{$app->user_id}}', '{{$_suivi->_id}}', '{{$_suivi->_class}}');">{{tr}}Edit{{/tr}}</button>
    {{/if}}
    </td>
  {{/if}}

{{if $_suivi instanceof CConsultation}}
  <tr>
    <td>
      {{if $_suivi->_ref_consult_anesth->_id}}
        <strong>{{tr}}CConsultAnesth{{/tr}}</strong>
      {{else}}
        <strong>{{tr}}CConsultation{{/tr}}</strong>
      {{/if}}
    </td>
    <td>
      <strong>
        <div class="mediuser" style="border-color: #{{$_suivi->_ref_praticien->_ref_function->color}};">
          {{mb_value object=$_suivi field="_praticien_id"}}
        </div>
      </strong>
    </td>
    <td style="text-align: center">
      {{mb_ditto name=date value=$_suivi->_datetime|date_format:$conf.date}}
    </td>
    <td>{{$_suivi->_datetime|date_format:$conf.time}}</td>
    <td></td>
    <td>
      {{if $_suivi->_ref_consult_anesth->_id}}
        {{$_suivi->rques}}
      {{else}}
        {{$_suivi->conclusion}}
      {{/if}}
    </td>
    <td>
      {{if !$readonly && $_suivi->_canEdit}}
        <button type="button" class="edit notext" onclick="modalConsult('{{$_suivi->_id}}')"></button>
      {{/if}}
    </td>
  </tr>
{{/if}}

{{* Tableau de transmissions *}}
{{if $_suivi|is_array}}
  {{assign var=nb_trans value=$_suivi|@count}}
  
  {{if @$show_patient}}
    <td>{{$_suivi[0]->_ref_sejour->_ref_patient}}</td>
    <td class="text">{{$_suivi[0]->_ref_sejour->_ref_last_affectation->_ref_lit->_view}}</td>
  {{/if}}
  
  <td class="narrow">{{tr}}{{$_suivi[0]->_class}}{{/tr}}</td>
  <td class="narrow">{{$_suivi[0]->_ref_user}}</td>
  <td style="text-align: center;" class="narrow">
    {{mb_ditto name=date value=$_suivi[0]->date|date_format:$conf.date}}
  </td>
  <td class="narrow">{{$_suivi[0]->date|date_format:$conf.time}}</td>
  <td class="text" style="height: 22px;">
    {{if $_suivi[0]->object_id && $_suivi[0]->object_class}}
      {{assign var=classes value=' '|explode:"CPrescriptionLineMedicament CPrescriptionLineElement CAdministration CPrescriptionLineMix"}}
      {{if in_array($_suivi[0]->object_class, $classes)}}
        <span
         title="{{$_suivi[0]->_ref_object->_view}} {{if $_suivi[0]->_ref_object instanceof CPrescriptionLineElement && $_suivi[0]->_ref_object->commentaire}}({{$_suivi[0]->_ref_object->commentaire}}){{/if}}"
          style="float: left; border: 2px solid #800; width: 5px; height: 11px; margin-right: 3px;">
        </span>
      {{/if}}
      
      <a href="#1" onclick="if (window.addTransmission) { addTransmission('{{$_suivi[0]->sejour_id}}', '{{$app->user_id}}', null, '{{$_suivi[0]->object_id}}', '{{$_suivi[0]->object_class}}'); }">
      
      {{if !in_array($_suivi[0]->object_class, $classes)}}
        {{$_suivi[0]->_ref_object->_view}}
      {{/if}}
      {{if $_suivi[0]->object_class == "CPrescriptionLineMedicament"}}
      [{{$_suivi[0]->_ref_object->_ref_produit->_ref_ATC_2_libelle}}]
      {{/if}}
      
      {{if $_suivi[0]->object_class == "CPrescriptionLineElement"}}
      [{{$_suivi[0]->_ref_object->_ref_element_prescription->_ref_category_prescription->_view}}]
      {{/if}}
      
      {{if $_suivi[0]->object_class == "CAdministration"}}
        {{if $_suivi[0]->_ref_object->object_class == "CPrescriptionLineMedicament"}}
          [{{$_suivi[0]->_ref_object->_ref_object->_ref_produit->_ref_ATC_2_libelle}}]
        {{/if}}
        
        {{if $_suivi[0]->_ref_object->object_class == "CPrescriptionLineElement"}}
          [{{$_suivi[0]->_ref_object->_ref_object->_ref_element_prescription->_ref_category_prescription->_view}}]
        {{/if}}
      {{/if}}
        </a>
      
    {{/if}}
    {{if $_suivi[0]->libelle_ATC}}
      <a href="#1" onclick="if (window.addTransmission) { addTransmission('{{$_suivi[0]->sejour_id}}', '{{$_suivi[0]->user_id}}', null, null, null, '{{$_suivi[0]->libelle_ATC|smarty:nodefaults|JSAttribute}}'); }">{{$_suivi[0]->libelle_ATC}}</a>
    {{/if}}
  </td>
  <td class="text" {{if $_suivi[0]->degre == "high"}} style="background-color: #faa" {{/if}}>
    {{foreach from=$_suivi item=_trans}}
      <strong>
        {{if $_trans->type == "data"}}
          D: 
        {{elseif $_trans->type == "action"}}
          A: 
        {{else}}
          R: 
        {{/if}}
      </strong>
      {{$_trans->text}} <br />
    {{/foreach}}
  </td>
  
  <td style="white-space: nowrap;">
    {{if !$readonly && $_suivi[0]->_canEdit}}
      
      <form name="Del-{{$_suivi[0]->_guid}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this);">
        {{if $_suivi|@count == 1}}
          <input type="hidden" name="dosql" value="do_transmission_aed" />
        {{else}}
          <input type="hidden" name="dosql" value="do_multi_transmission_aed" />
        {{/if}}
        <input type="hidden" name="del" value="1" />
        <input type="hidden" name="m" value="dPhospi" />
        {{if $_suivi|@count == 1}}
          <input type="hidden" name="transmission_medicale_id" value="{{$_suivi[0]->_id}}" />
        {{/if}}
        {{if $_suivi|@count >= 2}}
          <input type="hidden" name="{{$_suivi[0]->type}}_id" value="{{$_suivi[0]->_id}}" />
          <input type="hidden" name="{{$_suivi[1]->type}}_id" value="{{$_suivi[1]->_id}}" />
        {{/if}}
        {{if $_suivi|@count == 3}}
          <input type="hidden" name="{{$_suivi[2]->type}}_id" value="{{$_suivi[2]->_id}}" />
        {{/if}}
        <input type="hidden" name="sejour_id" value="{{$_suivi[0]->sejour_id}}" />
        <button type="button" class="trash notext"
         onclick="confirmDeletion(this.form,
          {typeName:'la/les transmission(s)',
            ajax: true,
            callback: function() { submitSuivi(getForm('Del-{{$_suivi[0]->_guid}}'), 1); } })">{{tr}}Delete{{/tr}}</button>
      </form>
      {{if $_suivi|@count == 1}}
        <button type="button" class="edit notext" onclick="addTransmission('{{$_suivi[0]->sejour_id}}', null, '{{$_suivi[0]->_id}}', null, null, null, 1)"></button>
      {{elseif $_suivi|@count == 2}}
        <button type="button" class="edit notext" onclick="addTransmission('{{$_suivi[0]->sejour_id}}', null, { {{$_suivi[0]->type}}_id: '{{$_suivi[0]->_id}}', {{$_suivi[1]->type}}_id: '{{$_suivi[1]->_id}}' }, null, null, null, 1)"></button>
      {{else}}
        <button type="button" class="edit notext" onclick="addTransmission('{{$_suivi[0]->sejour_id}}', null, { {{$_suivi[0]->type}}_id: '{{$_suivi[0]->_id}}', {{$_suivi[1]->type}}_id: '{{$_suivi[1]->_id}}', {{$_suivi[2]->type}}_id: '{{$_suivi[2]->_id}}' }, null, null, null, 1)"></button>
      {{/if}}
    {{/if}}
  </td>
  
{{/if}}