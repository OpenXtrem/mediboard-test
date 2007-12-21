<script type="text/javascript">

Antecedent = {
  remove: function(oForm, onComplete) {
    var oOptions = {
      typeName: 'cet antécédent',
      ajax: 1,
      target: 'systemMsg'
    };
    
    var oOptionsAjax = {
      onComplete: onComplete
    };
    
    confirmDeletion(oForm, oOptions, oOptionsAjax);
  }
}

Traitement = {
  remove: function(oForm, onComplete) {
    var oOptions = {
      typeName: 'ce traitement',
      ajax: 1,
      target: 'systemMsg'
    };
    
    var oOptionsAjax = {
      onComplete: onComplete
    };
    
    confirmDeletion(oForm, oOptions, oOptionsAjax);
  }
}

</script>


  {{include file="inc_consult_anesth/inc_list_addiction.tpl}}    

<strong>Antécédents du patient</strong>
<ul>
{{if $patient->_ref_dossier_medical->_ref_antecedents}}
  {{foreach from=$patient->_ref_dossier_medical->_ref_antecedents key=curr_type item=list_antecedent}}
  {{if $list_antecedent|@count}}
  <li>
    {{tr}}CAntecedent.type.{{$curr_type}}{{/tr}}
    {{foreach from=$list_antecedent item=curr_antecedent}}
    <ul>
      <li>
        <form name="delAntFrm-{{$curr_antecedent->_id}}" action="?m=dPcabinet" method="post">

        <input type="hidden" name="m" value="dPpatients" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="dosql" value="do_antecedent_aed" />
        <input type="hidden" name="antecedent_id" value="{{$curr_antecedent->_id}}" />
        
        <button class="trash notext" type="button" onclick="Antecedent.remove(this.form, reloadDossierMedicalPatient)">
          {{tr}}delete{{/tr}}
        </button> 
        {{if $_is_anesth && $sejour->_id}}
        <button class="add notext" type="button" onclick="copyAntecedent({{$curr_antecedent->_id}})">
          {{tr}}add{{/tr}}
        </button>
        {{/if}}         
        {{if $curr_antecedent->date}}
          {{$curr_antecedent->date|date_format:"%d/%m/%Y"}} :
        {{/if}}
         <a href="#" onmouseover="ObjectTooltip.create(this, { mode: 'objectViewHistory', params: { object_class: 'CAntecedent', object_id: {{$curr_antecedent->_id}} } })">
          {{$curr_antecedent->rques}}
         </a>
      </form>
      </li>
    </ul>
    {{/foreach}}
  </li>
  {{/if}}
  {{/foreach}}
{{else}}
  <li><em>Pas d'antécédents</em></li>
{{/if}}
</ul>
<strong>Traitements du patient</strong>
<ul>
  {{foreach from=$patient->_ref_dossier_medical->_ref_traitements item=curr_trmt}}
  <li>
    <form name="delTrmtFrm-{{$curr_trmt->_id}}" action="?m=dPcabinet" method="post">
    <input type="hidden" name="m" value="dPpatients" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="dosql" value="do_traitement_aed" />
    <input type="hidden" name="traitement_id" value="{{$curr_trmt->traitement_id}}" />
    <button class="trash notext" type="button" onclick="Traitement.remove(this.form, reloadDossierMedicalPatient)">
      {{tr}}delete{{/tr}}
    </button>
    {{if $_is_anesth && $sejour->_id}}
    <button class="add notext" type="button" onclick="copyTraitement({{$curr_trmt->traitement_id}})">
      {{tr}}add{{/tr}}
    </button>
    {{/if}}
    {{if $curr_trmt->fin}}
      Du {{$curr_trmt->debut|date_format:"%d/%m/%Y"}} au {{$curr_trmt->fin|date_format:"%d/%m/%Y"}} :
    {{elseif $curr_trmt->debut}}
      Depuis le {{$curr_trmt->debut|date_format:"%d/%m/%Y"}} :
    {{/if}}
      <a href="#" onmouseover="ObjectTooltip.create(this, { mode: 'objectViewHistory', params: { object_class: 'CTraitement', object_id: {{$curr_trmt->_id}} } })">
       {{$curr_trmt->traitement}}
      </a>
    </form>
  </li>
  {{foreachelse}}
  <li><em>Pas de traitements</em></li>
  {{/foreach}}
</ul>
<strong>Diagnostics du patient</strong>
<ul>
  {{foreach from=$patient->_ref_dossier_medical->_ext_codes_cim item=curr_code}}
  <li>
    <button class="trash notext" type="button" onclick="oCimField.remove('{{$curr_code->code}}')">
      {{tr}}delete{{/tr}}
    </button>
    {{if $_is_anesth && $sejour->_id}}
    <button class="add notext" type="button" onclick="oCimAnesthField.add('{{$curr_code->code}}')">
      {{tr}}add{{/tr}}
    </button>
    {{/if}}
    {{$curr_code->code}}: {{$curr_code->libelle}}
  </li>
  {{foreachelse}}
  <li><em>Pas de diagnostic</em></li>
  {{/foreach}}
</ul>

<!-- Gestion des diagnostics pour le dossier medical du patient -->
<form name="editDiagFrm" action="?m=dPcabinet" method="post">
  <input type="hidden" name="m" value="dPpatients" />
  <input type="hidden" name="tab" value="edit_consultation" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
  <input type="hidden" name="object_id" value="{{$patient->_id}}" />
  <input type="hidden" name="object_class" value="CPatient" />
  <input type="hidden" name="codes_cim" value="{{$patient->_ref_dossier_medical->codes_cim}}" />
</form>

<script type="text/javascript">
oCimField = new TokenField(document.editDiagFrm.codes_cim, { 
  confirm  : 'Voulez-vous réellement supprimer ce diagnostic ?',
  onChange : updateTokenCim10
} ); 
</script>      