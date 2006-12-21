<script language="Javascript" type="text/javascript">
function verifIntubDifficileAndSave(oForm){
  if(oForm.mallampati[2].checked || oForm.mallampati[3].checked
    || oForm.bouche[0].checked || oForm.bouche[1].checked
    || oForm.distThyro[0].checked){
  
    // Avertissement d'intubatino difficile
    $('divAlertIntubDiff').style.visibility = "visible";
  }else{
    $('divAlertIntubDiff').style.visibility = "hidden";
  }
  submitFormAjax(oForm, 'systemMsg')
}
</script>
<form name="editFrmIntubation" action="?m=dPcabinet" method="post">
<input type="hidden" name="m" value="{{$m}}" />
<input type="hidden" name="del" value="0" />
<input type="hidden" name="dosql" value="do_consult_anesth_aed" />
<input type="hidden" name="consultation_anesth_id" value="{{$consult_anesth->consultation_anesth_id}}" />
<table class="form">
  <tr>
    <th colspan="6" class="category">Condition d'intubation</th>
  </tr>
  <tr>
    {{foreach from=$consult_anesth->_enumsTrans.mallampati|smarty:nodefaults key=curr_mallampati item=trans_mallampati}}
    <td rowspan="4" class="button">
      <label for="mallampati_{{$curr_mallampati}}" title="Mallampati de {{$trans_mallampati}}">
        <img src="images/pictures/{{$curr_mallampati}}.gif" alt="{{$trans_mallampati}}" />
        <br />
        <input type="radio" name="mallampati" value="{{$curr_mallampati}}" {{if $consult_anesth->mallampati == $curr_mallampati}}checked="checked"{{/if}} onclick="verifIntubDifficileAndSave(this.form);" />
        {{$trans_mallampati}}
      </label>
    </td>
    {{/foreach}}

    <th><label for="bouche_m20" title="Ouverture de la bouche">Ouverture de la bouche</label></th>
    <td>
      {{html_radios name="bouche" options=$consult_anesth->_enumsTrans.bouche selected=$consult_anesth->bouche separator="<br />" onclick="verifIntubDifficileAndSave(this.form);"}}
    </td>
  </tr>
  
  <tr>
    <th><label for="distThyro_m65" title="Distance thyro-mentonni�re">Distance thyro-mentonni�re</label></th>
    <td>
      {{html_radios name="distThyro" options=$consult_anesth->_enumsTrans.distThyro selected=$consult_anesth->distThyro separator="<br />" onclick="verifIntubDifficileAndSave(this.form);"}}
    </td>
  </tr>

  <tr>
    <th><label for="etatBucco" title="Etat bucco-dentaire">Etat bucco-dentaire</label></th>
    <td>
      <select name="_helpers_etatBucco" size="1" onchange="pasteHelperContent(this);this.form.etatBucco.onchange();">
        <option value="">&mdash; Choisir une aide</option>
        {{html_options options=$consult_anesth->_aides.etatBucco}}
      </select>
      <button class="new notext" title="Ajouter une aide � la saisie" type="button" onclick="addHelp('CConsultAnesth', this.form.etatBucco)"></button><br />
      <textarea name="etatBucco" onchange="submitFormAjax(this.form, 'systemMsg')" title="{{$consult_anesth->_props.etatBucco}}">{{$consult_anesth->etatBucco}}</textarea>
    </td>
  </tr>
  
  <tr>
    <th><label for="conclusion" title="Remarques et Conclusion sur les conditions d'intubation">Remarques / Conclusion</label></th>
    <td>
      <select name="_helpers_conclusion" size="1" onchange="pasteHelperContent(this);this.form.conclusion.onchange();">
        <option value="">&mdash; Choisir une aide</option>
        {{html_options options=$consult_anesth->_aides.conclusion}}
      </select>
      <button class="new notext" title="Ajouter une aide � la saisie" type="button" onclick="addHelp('CConsultAnesth', this.form.conclusion)"></button><br />
      <textarea name="conclusion" onchange="submitFormAjax(this.form, 'systemMsg')" title="{{$consult_anesth->_props.conclusion}}">{{$consult_anesth->conclusion}}</textarea>
    </td>
  </tr>
  <tr>
    <td colspan="6" class="button">
      <div id="divAlertIntubDiff" style="float:right;color:#F00;{{if !$consult_anesth->_intub_difficile}}visibility:hidden;{{/if}}"><strong>Intubation Difficile Pr�visible</strong></div>
    </td>
  </tr>
</table>
</form>