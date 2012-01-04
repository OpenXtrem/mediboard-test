{{* $Id$ *}}

<script type="text/javascript">
  // Explicit form preparation for Ajax loading
  prepareForm(document.editPrescriptionItem);
</script>

<form name="editPrescriptionItem" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
<input type="hidden" name="m" value="dPlabo" />
<input type="hidden" name="dosql" value="do_prescription_examen_aed" />
<input type="hidden" name="prescription_labo_examen_id" value="{{$prescriptionItem->_id}}" />
<input type="hidden" name="del" value="0" />

{{if !$prescriptionItem->_id}}
<table class="form">
  <tr>
    <th class="title">Veuillez s�lectioner une analyse</th>
  </tr>
</table>
{{else}}
{{assign var="prescription" value=$prescriptionItem->_ref_prescription_labo}}
{{assign var="examen" value=$prescriptionItem->_ref_examen_labo}}
{{assign var="patient" value=$prescription->_ref_patient}}
<table class="form">
  <tr>
    <th class="title modify" colspan="2">
      Saisie du r�sultat
    </th>
  </tr>
  <tr>
    <th>{{tr}}CPatient{{/tr}}</th>
    <td>{{mb_value object=$patient field="_view"}}</td>
  </tr>

  <tr>
    <th>{{tr}}CExamenLabo{{/tr}}</th>
    <td>{{mb_value object=$examen field="_view"}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="type"}}</th>
    <td>
      {{mb_value object=$examen field="type"}}
      {{if $examen->type == "num"}}
      : {{$examen->unite}}
      ({{$examen->min}} &ndash; {{$examen->max}})
      {{/if}}
    </td>
  </tr>
  {{if $prescription->_status >= $prescription|const:"VALIDEE"}}
  <tr>
    <th>{{mb_label object=$prescriptionItem field="date"}}</th>
    <td>{{mb_value object=$prescriptionItem field="date" form="editPrescriptionItem" register=true}}</td>
  </tr>

  {{if !$examen->_external}}
  <tr>
    <th>{{mb_label object=$prescriptionItem field="resultat"}}</th>
    <td>{{mb_value object=$prescriptionItem field="resultat" prop=$examen->type}}</td>
  </tr>
  {{/if}}
  <tr>
    <th>{{mb_label object=$prescriptionItem field="commentaire"}}</th>
    <td>{{mb_value object=$prescriptionItem field="commentaire"}}</td>
  </tr>
  {{elseif $prescription->_status >= $prescription|const:"VEROUILLEE"}}
  <tr>
    <th>{{mb_label object=$prescriptionItem field="date"}}</th>
    <td>{{mb_field object=$prescriptionItem field="date" form="editPrescriptionItem"}}</td>
  </tr>

  {{if !$examen->_external}}
  <tr>
    <th>{{mb_label object=$prescriptionItem field="resultat"}}</th>
    <td>{{mb_field object=$prescriptionItem field="resultat" prop=$examen->type}}</td>
  </tr>
  {{/if}}
  <tr>
    <th>
      {{mb_label object=$prescriptionItem field="commentaire"}}
    </th>
    <td>
      {{mb_field object=$prescriptionItem field="commentaire" form="editPrescriptionItem"
        aidesaisie="validateOnBlur: 0"}}
    </td>
  </tr>
  <tr>
    <td colspan="2" class="button">
      <button type="button" class="submit" onclick="submitFormAjax(this.form, 'systemMsg', { onComplete: function() { Prescription.select(); Prescription.Examen.edit({{$prescriptionItem->_id}}); } });">
        Valider
      </button>
    </td>
  </tr>

  {{else}}

  {{if !$examen->_external}}
	<!-- Non v�rouill�e -->
	<tr>
    <td class="text" colspan="2">
      <div class="big-info">
        Merci de <strong>v�rouiller la prescription</strong> pour pourvoir saisir les r�sultats.
      </div>
    </td>
  </tr>
  {{/if}}
	  
  {{/if}}
  
</table>

{{/if}}
</form>