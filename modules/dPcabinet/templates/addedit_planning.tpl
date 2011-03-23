<!-- $Id$ -->

{{mb_script module="dPpatients" script="pat_selector"}}
{{mb_script module="dPcabinet" script="plage_selector"}}
{{mb_script module="dPcompteRendu" script="document"}}
{{mb_script module="dPcompteRendu" script="modele_selector"}}
{{mb_script module="dPcabinet" script="file"}}
{{if $consult->_id}}
  {{mb_ternary var=object_consult test=$consult->_is_anesth value=$consult->_ref_consult_anesth other=$consult}}
  {{mb_include module="dPfiles" template="yoplet_uploader" object=$object_consult}}
{{/if}}
{{assign var=attach_consult_sejour value=$conf.dPcabinet.CConsultation.attach_consult_sejour}}
<script type="text/javascript">

Medecin = {
  form: null,
  edit : function() {
    this.form = document.forms.editFrm;
    var url = new Url("dPpatients", "vw_medecins");
    url.popup(700, 450, "Medecin");
  },
  
  set: function(id, view) {
    $('_adresse_par_prat').show().update('Autres : '+view);
    $V(this.form.adresse_par_prat_id, id);
    $V(this.form._correspondants_medicaux, '', false);
  }
};

function refreshListCategorie(praticien_id){
  var url = new Url("dPcabinet", "httpreq_view_list_categorie");
  url.addParam("praticien_id", praticien_id);
  url.requestUpdate("listCategorie");
}

function changePause(){
  var oForm = document.editFrm;
  if(oForm._pause.checked){
    oForm.patient_id.value = "";
    oForm._pat_name.value = "";
    $("viewPatient").hide();
    $("infoPat").update("");
  }else{
    $("viewPatient").show();
  }
}

function requestInfoPat() {
  var oForm = document.editFrm;
  if(!oForm.patient_id.value){
    return false;
  }
  var url = new Url("dPpatients", "httpreq_get_last_refs");
  url.addElement(oForm.patient_id);
  url.addElement(oForm.consultation_id);
  url.requestUpdate("infoPat");
}

function ClearRDV(){
  var oForm = document.editFrm;
  $V(oForm.plageconsult_id, "", true);
  $V(oForm._date, "");
  $V(oForm.heure, "");
}

function annuleConsult(oForm, etat) {
  if(etat) {
    if(confirm("Voulez-vous vraiment annuler cette consultation ?")) {
      oForm.chrono.value = {{$consult|const:'TERMINE'}};
    } else {
      return;
    }
  } else {
    if(confirm("Voulez-vous vraiment r�tablir cette consultation ?")) {
      oForm.chrono.value = {{$consult|const:'PLANIFIE'}};
    } else {
      return;
    }
  }
  oForm.annule.value = etat;
  if(checkForm(oForm)) {
    oForm.submit();
  }
}

function checkFormRDV(oForm){
  if(!oForm._pause.checked && oForm.patient_id.value == ""){
    alert("Veuillez s�lectionner un patient");
    PatSelector.init();
    return false;
  }else{
    var infoPat = $('infoPat');
    var operations = infoPat.select('input[name=_operation_id]');
    var checkedOperation = operations.find(function (o) {return o.checked});
    if (checkedOperation) {
      oForm._operation_id.value = checkedOperation.value;
    }
    return checkForm(oForm);
  }
}

function printForm() {
  var url = new Url("dPcabinet", "view_consultation"); 
  url.addElement(document.editFrm.consultation_id);
  url.popup(700, 500, "printConsult");
  return;
}

function printDocument(iDocument_id) {
	var oForm = document.editFrm;
  if (iDocument_id.value != 0) {
    var url = new Url("dPcompteRendu", "edit_compte_rendu");
    url.addElement(oForm.consultation_id, "object_id");
    url.addElement(iDocument_id, "modele_id");
    url.popup(700, 600, "Document");
    return true;
  }
  return false;
}

checkCorrespondantMedical = function(){
  form = getForm("editFrm");
  var url = new Url("dPplanningOp", "ajax_check_correspondant_medical");
  url.addParam("patient_id", $V(form.patient_id));
  url.addParam("object_id" , $V(form.consultation_id));
  url.addParam("object_class", '{{$consult->_class_name}}');
  url.requestUpdate("correspondant_medical");
}

Main.add(function () {
  var oForm = document.editFrm;

  requestInfoPat();

  {{if $plageConsult->_id && !$consult->_id}}
  oForm.plageconsult_id.value = {{$plageConsult->_id}};
  oForm.chir_id.value = {{$plageConsult->chir_id}};
  refreshListCategorie({{$plageConsult->chir_id}});
  PlageConsultSelector.init();
  {{/if}}
  
  {{if $consult->_id && $consult->patient_id}}
  $("print_fiche_consult").disabled = "";
  {{/if}}
});

</script>

<form name="editFrm" action="?m={{$m}}" class="watched" method="post" onsubmit="return checkFormRDV(this)">

<input type="hidden" name="dosql" value="do_consultation_aed" />
<input type="hidden" name="del" value="0" />
{{mb_key object=$consult}}

<input type="hidden" name="adresse_par_prat_id" value="{{$consult->adresse_par_prat_id}}" />
<input type="hidden" name="annule" value="{{$consult->annule|default:"0"}}" />
<input type="hidden" name="arrivee" value="" />
<input type="hidden" name="chrono" value="{{$consult|const:'PLANIFIE'}}" />
<input type="hidden" name="_operation_id" value="" />


<a class="button new" href="?m={{$m}}&amp;tab={{$tab}}&amp;consultation_id=0">
  {{tr}}CConsultation-title-create{{/tr}}
</a>
{{if $consult->_id}}
<a class="button search" href="?m={{$m}}&amp;tab=edit_consultation&amp;selConsult={{$consult->_id}}" style="float: right;">
  {{tr}}CConsultation-title-access{{/tr}}
</a>
{{/if}}

<table class="form">  
  <tr>
    {{if $consult->_id}}
      <th class="title modify" colspan="5">
        {{mb_include module=system template=inc_object_notes      object=$consult}}
        {{mb_include module=system template=inc_object_idsante400 object=$consult}}
        {{mb_include module=system template=inc_object_history    object=$consult}}
        {{tr}}CConsultation-title-modify{{/tr}}
        {{if $pat->_id}}de {{$pat->_view}}{{/if}}
        par le Dr {{$chir}}
      </th>
    {{else}}
      <th class="title" colspan="5">{{tr}}CConsultation-title-create{{/tr}}</th>
    {{/if}}
  </tr>
  {{if $consult->annule == 1}}
  <tr>
    <th class="category cancelled" colspan="3">{{tr}}CConsultation-annule{{/tr}}</th>
  </tr>
  {{/if}}
  {{if $consult->_id && $consult->_datetime < $today && $can->admin}}
  <tr>
    <td colspan="3">
      <div class="small-warning">Attention, vous �tes en train de modifier une consultation pass�e</div>
    </td>
  </tr>
  {{elseif $consult->_id && $consult->_datetime < $today}}
  <tr>
    <td colspan="3">
      <div class="small-info">Vous ne pouvez pas modifier une consultation pass�e, veuillez contacter un administrateur</div>
      <input type="hidden" name="_locked" value="1" />
    </td>
  </tr>
  {{/if}}
  <tr>
    <td style="width: 50%;">
      <table class="form">
        <tr>
        	<th class="category" colspan="3">Informations sur la consultation</th>
			  </tr>
        <tr>
          <th class="narrow">
            <label for="chir_id" title="Praticien pour la consultation">Praticien</label>
          </th>
          <td>
            <select name="chir_id" style="max-width: 150px" class="notNull" onChange="ClearRDV(); refreshListCategorie(this.value); if (this.value != '') $V(this.form._function_id, '');">
              <option value="">&mdash; Choisir un praticien</option>
              {{foreach from=$listPraticiens item=curr_praticien}}
              <option class="mediuser" style="border-color: #{{$curr_praticien->_ref_function->color}};" value="{{$curr_praticien->user_id}}" {{if $chir->user_id == $curr_praticien->user_id}} selected="selected" {{/if}}>
                {{$curr_praticien->_view}}
              </option>
             {{/foreach}}
            </select>
						<input type="checkbox" name="_pause" value="1" onclick="changePause()" {{if $consult->_id && $consult->patient_id==0}} checked="checked" {{/if}} {{if $attach_consult_sejour && $consult->_id}}disabled="disabled"{{/if}}/>
            <label for="_pause" title="Planification d'une pause">Pause</label>
          </td>
        </tr>

        <tr id="viewPatient" {{if $consult->_id && $consult->patient_id==0}}style="display:none;"{{/if}}>
          <th>
            {{mb_label object=$consult field="patient_id"}}
          </th>
          <td>
          	{{mb_field object=$pat field="patient_id" hidden=1 ondblclick="PatSelector.init()" onchange="requestInfoPat(); $('button-edit-patient').setVisible(this.value);"}}
          	<input type="text" name="_pat_name" size="35" value="{{$pat->_view}}" readonly="readonly" ondblclick="PatSelector.init()" onchange="checkCorrespondantMedical()"/>
						<button class="search" type="button" onclick="PatSelector.init()">{{tr}}Search{{/tr}}</button>
	          <script type="text/javascript">
	            PatSelector.init = function(){
	              this.sForm = "editFrm";
	              this.sId   = "patient_id";
	              this.sView = "_pat_name";
	              this.pop();
	            }
	          </script>
						<button id="button-edit-patient" type="button" 
						        onclick="location.href='?m=dPpatients&amp;tab=vw_edit_patients&amp;patient_id='+this.form.patient_id.value" 
										class="edit" {{if !$pat->_id}}style="display: none;"{{/if}}>
						  {{tr}}Edit{{/tr}}
					  </button>
					</td>
        </tr>
        
        
        <tr>
          <th>
            {{mb_label object=$consult field="motif"}}<br />
            <select name="_helpers_motif" size="1" onchange="pasteHelperContent(this)" class="helper">
              <option value="">&mdash; Aide</option>
              {{html_options options=$consult->_aides.motif.no_enum}}
            </select><br />
            <button class="new notext" title="Ajouter une aide � la saisie" type="button" onclick="addHelp('CConsultation', this.form.motif)">{{tr}}New{{/tr}}</button>            
          </th>
          <td>{{mb_field object=$consult field="motif" rows="3"}}</td>
        </tr>

        <tr>
          <th>
            {{mb_label object=$consult field="rques"}}<br />
            <select name="_helpers_rques" size="1" onchange="pasteHelperContent(this)" class="helper">
              <option value="">&mdash; Aide</option>
              {{html_options options=$consult->_aides.rques.no_enum}}
            </select><br />
            <button class="new notext" title="Ajouter une aide � la saisie" type="button" onclick="addHelp('CConsultation', this.form.rques)">{{tr}}New{{/tr}}</button>
          </th>
          <td>{{mb_field object=$consult field="rques" rows="3"}}</td>
        </tr>

      </table>

    </td>
    <td style="width: 50%;">

      <table class="form">
        <tr><th class="category" colspan="3">Rendez-vous</th></tr>

        <tr>
          <th style="width: 35%;">{{mb_label object=$consult field="premiere"}}</th>
          <td style="width: 65%;">
            <input type="checkbox" name="_check_premiere" value="1"
              {{if $consult->_check_premiere}} checked="checked" {{/if}}
              onchange="this.form.premiere.value = this.checked ? 1 : 0;" />
            {{mb_field object=$consult field="premiere" hidden="hidden"}}
            {{mb_label object=$consult field="_check_premiere"}}
          </td>
          <td rowspan="7" class="button">
            <button class="search" type="button" onclick="PlageConsultSelector.init()">Choix de l'horaire</button>
          </td>
        </tr>

        <tr>
          <th>{{mb_label object=$consult field="adresse"}}</th>
          <td>
            <input type="checkbox" name="_check_adresse" value="1"
              {{if $consult->_check_adresse}} checked="checked" {{/if}}
              onchange="$('correspondant_medical').toggle();
              $('_adresse_par_prat').toggle();
              if (this.checked) {
                this.form.adresse.value = 1;
              } else {
                this.form.adresse.value = 0;
                this.form.adresse_par_prat_id.value = '';
              }" />
            {{mb_field object=$consult field="adresse" hidden="hidden"}}
          </td>
        </tr>
        
        <tr id="correspondant_medical" {{if !$consult->_check_adresse}}style="display: none;"{{/if}}>
          {{assign var="object" value=$consult}}
          {{mb_include module=dPplanningOp template=inc_check_correspondant_medical}}
        </tr>
        
        <tr>
          <td></td>
          <td colspan="3">
            <div id="_adresse_par_prat" style="{{if !$medecin_adresse_par}}display:none{{/if}}; width: 300px;">
              {{if $medecin_adresse_par}}Autres : {{$medecin_adresse_par->_view}}{{/if}}
            </div>
          </td>
        </tr>
        
        <tr>
          <th>{{mb_label object=$consult field="si_desistement"}}</th>
          <td>{{mb_field object=$consult field="si_desistement" typeEnum="checkbox"}}</td>
        </tr>
        
        {{if $conf.dPcabinet.CConsultation.attach_consult_sejour}}
        <tr>
          <th>{{mb_label object=$consult field="_forfait_se"}}</th>
          <td>{{mb_field object=$consult field="_forfait_se" typeEnum="checkbox"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$consult field="_facturable"}}</th>
          <td>{{mb_field object=$consult field="_facturable" typeEnum="checkbox"}}</td>
        </tr>
        {{/if}}
        
        <tr>
          <th>{{mb_label object=$consult field="plageconsult_id"}}</th>
          <td>
            <input type="text" name="_date" value="{{$consult->_date|date_format:"%A %d/%m/%Y"}}" ondblclick="PlageConsultSelector.init()" readonly="readonly" onchange="if (this.value != '') $V(this.form._function_id, '')"/>
            {{mb_field object=$consult field="plageconsult_id" hidden=1 ondblclick="PlageConsultSelector.init()"}}
            <script type="text/javascript">
            PlageConsultSelector.init = function(){
              this.sForm            = "editFrm";
              this.sHeure           = "heure";
              this.sPlageconsult_id = "plageconsult_id";
              this.sDate            = "_date";
              this.sDuree           = "duree";
              this.sChir_id         = "chir_id";
              this.sFunction_id     = "_function_id";
              this.pop();
            }
           </script> 
          </td>
        </tr>


        <tr>
          <th>{{mb_label object=$consult field="heure"}}</th>
          <td>
            <input type="text" name="heure" value="{{$consult->heure}}" size="4" readonly="readonly" />
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$consult field="duree"}}</th>
          <td>
            <select name="duree">
              <option value="1" {{if $consult->duree == 1}} selected="selected" {{/if}}>x1</option>
              <option value="2" {{if $consult->duree == 2}} selected="selected" {{/if}}>x2</option>
              <option value="3" {{if $consult->duree == 3}} selected="selected" {{/if}}>x3</option>
              <option value="4" {{if $consult->duree == 4}} selected="selected" {{/if}}>x4</option>
              <option value="5" {{if $consult->duree == 5}} selected="selected" {{/if}}>x5</option>
              <option value="6" {{if $consult->duree == 6}} selected="selected" {{/if}}>x6</option>
            </select>
          </td>
        </tr>
        <tbody id="listCategorie">
          {{if $consult->_id || $chir->_id}}
	          {{include file="httpreq_view_list_categorie.tpl" 
          		categorie_id=$consult->categorie_id 
          		categories=$categories
          		listCat=$listCat}}
          {{elseif $chir->_id}}
          {{assign var="categorie_id" value=""}}
          {{assign var="categories" value=$categories}}
          {{include file="httpreq_view_list_categorie.tpl"
          		categorie_id=""
          		categories=$categories
          		listCat=$listCat}}
          {{/if}}
        </tbody>
        <tr>
          <th>Choix par cabinet</th>
          <td>
            <select name="_function_id" style="max-width: 130px;" onchange = "if (this.value != '') { $V(this.form.chir_id, ''); $V(this.form._date, '');}">
              <option value="">&mdash; choisir un cabinet</option>
              {{foreach from=$listFunctions item=_function}}
              <option value="{{$_function->_id}}" class="mediuser" style="border-color: #{{$_function->color}};">
                {{$_function->_view}}
              </option>
              {{/foreach}}
            </select>
          </td>
        </tr>
        <tr>
          <td colspan="5">
            {{if $conf.dPcabinet.CConsultAnesth.format_auto_rques}}
              <div class="small-info">
              Si vous laissez les champs <strong>Remarques</strong> ou <strong>Motif</strong> vides, <br />
              ils seront pr�-remplis selon <a href="?m=dPcabinet&amp;tab=configure">la configuration du module</a>.
              </div>
            {{/if}}
          </td>
        </tr>
      </table>
    
    </td>
  </tr>

  <tr>
    <td colspan="2">

      <table class="form">
        <tr>
          <td class="button">
          {{if $consult->_id}}
            <button class="modify" type="submit">
            	{{tr}}Edit{{/tr}}
            </button>
            {{if $consult->annule}}
	            <button class="change" type="button" onclick="annuleConsult(this.form, 0)">
	            	{{tr}}Restore{{/tr}}
	            </button>
            {{else}}
	            <button class="cancel" type="button" onclick="annuleConsult(this.form, 1)">
	            	{{tr}}Cancel{{/tr}}
	            </button>
            {{/if}}
            {{if $can->admin || !$consult->patient_id}}
            <button class="trash" type="button" onclick="confirmDeletion(this.form,{typeName:'la consultation de',objName:'{{$consult->_ref_patient->_view|smarty:nodefaults|JSAttribute}}'})">
              {{tr}}Delete{{/tr}}
            </button>
            {{/if}}
            <button class="print" id="print_fiche_consult" type="button" {{if !$consult->patient_id}}disabled="disabled"{{/if}}onclick="printForm();">
              {{tr}}Print{{/tr}}
            </button>
          {{else}}
            <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
          {{/if}}
          </td>
        </tr>
      </table>
    
    </td>
  </tr>
</table>

</form>

<table class="form">
  <tr>
    <td class="halfPane" style="width: 50%;">
      <fieldSet>
        <legend>Infos patient</legend>
        <div class="text" id="infoPat"></div>
      </fieldSet>
    </td>
    <td class="halfPane">
      {{if $consult->_id}}
      <fieldset>
        <legend>{{tr}}CCompteRendu{{/tr}} - {{tr}}{{$object_consult->_class_name}}{{/tr}}</legend>
        <div id="documents">
          <script type="text/javascript">
            Document.register('{{$object_consult->_id}}','{{$object_consult->_class_name}}','{{$consult->_praticien_id}}','documents');
          </script>
        </div>
      </fieldset>
      <fieldset>
        <legend>{{tr}}CFile{{/tr}} - {{tr}}{{$consult->_class_name}}{{/tr}}</legend>            
        <div id="files">
          <script type="text/javascript">
            File.register('{{$consult->_id}}','{{$consult->_class_name}}', 'files');
          </script>
        </div>
      </fieldset>
      {{/if}}
    </td>
  </tr>
</table>

