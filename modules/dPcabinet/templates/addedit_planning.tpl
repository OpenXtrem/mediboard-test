<!-- $Id$ -->

{{mb_include_script module="dPpatients" script="pat_selector"}}
{{mb_include_script module="dPcabinet" script="plage_selector"}}
{{mb_include_script module="dPcompteRendu" script="document"}}
{{mb_include_script module="dPcompteRendu" script="modele_selector"}}

<script type="text/javascript">


function refreshListCategorie(praticien_id){
  var url = new Url;
  url.setModuleAction("dPcabinet", "httpreq_view_list_categorie");
  url.addParam("praticien_id", praticien_id);
  url.requestUpdate("listCategorie", {
    waitingText: null
  });
}


function changePause(){
  oForm = document.editFrm;
  if(oForm._pause.checked){
    oForm.patient_id.value = "";
    oForm._pat_name.value = "";
    $('viewPatient').hide();
    myNode = document.getElementById("infoPat");
    myNode.innerHTML = "";
    myNode = document.getElementById("clickPat");
    myNode.innerHTML = "Infos patient (indisponibles)";
  }else{
    $('viewPatient').show();
  }
}


function requestInfoPat() {
  var oForm = document.editFrm;
  if(!oForm.patient_id.value){
    return false;
  }
  var url = new Url;
  url.setModuleAction("dPpatients", "httpreq_get_last_refs");
  url.addElement(oForm.patient_id);
  url.addElement(oForm.consultation_id);
  url.requestUpdate("infoPat", {
    waitingText: "Chargement des ant�c�dents du patient"
  });
}


function ClearRDV(){
  var f = document.editFrm;
  f.plageconsult_id.value = 0;
  f._date.value = "";
  f.heure.value = "";
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
    alert("Veuillez Selectionner un Patient");
    PatSelector.init();
    return false;
  }else{
    return checkForm(oForm);
  }
}

function printForm() {
  var url = new Url;
  url.setModuleAction("dPcabinet", "view_consultation"); 
  url.addElement(document.editFrm.consultation_id);
  url.popup(700, 500, "printConsult");
  return;
}

function printDocument(iDocument_id) {
	oForm = document.editFrm;
  if (iDocument_id.value != 0) {
    var url = new Url;
    url.setModuleAction("dPcompteRendu", "edit_compte_rendu");
    url.addElement(oForm.consultation_id, "object_id");
    url.addElement(iDocument_id, "modele_id");
    url.popup(700, 600, "Document");
    return true;
  }
  
  return false;
}

Main.add(function () {
  var oForm = document.editFrm;

  requestInfoPat();

  {{if $plageConsult->plageconsult_id && !$consult->consultation_id}}
  oForm.plageconsult_id.value = {{$plageConsult->plageconsult_id}};
  oForm.chir_id.value = {{$plageConsult->chir_id}};
  refreshListCategorie({{$plageConsult->chir_id}});
  PlageConsultSelector.init();
  {{/if}}
});

</script>

<form name="editFrm" class="watched" action="?m={{$m}}" method="post" onsubmit="return checkFormRDV(this)">

<input type="hidden" name="dosql" value="do_consultation_aed" />
<input type="hidden" name="del" value="0" />
{{mb_field object=$consult field="consultation_id" hidden=1 prop=""}}
<input type="hidden" name="annule" value="{{$consult->annule|default:"0"}}" />
<input type="hidden" name="arrivee" value="" />
<input type="hidden" name="chrono" value="{{$consult|const:'PLANIFIE'}}" />

<table class="form">
  {{if $consult->consultation_id}}
  <tr>
    <td><a class="buttonnew" href="?m={{$m}}&amp;consultation_id=0">Cr�er une nouvelle consultation</a></td>
  </tr>
  {{/if}}
  <tr>
    {{if $consult->consultation_id}}
      <th class="title modify" colspan="5">
        <a style="float:right;" href="#" onclick="view_log('CConsultation',{{$consult->consultation_id}})">
          <img src="images/icons/history.gif" alt="historique" />
        </a>
        Modification de la consultation de {{$pat->_view}} pour le Dr {{$chir->_view}}
      </th>
    {{else}}
      <th class="title" colspan="5">Cr�ation d'une consultation</th>
    {{/if}}
  </tr>
  {{if $consult->annule == 1}}
  <tr>
    <th class="category cancelled" colspan="3">
    CONSULTATION ANNULEE
    </th>
  </tr>
  {{/if}}
  <tr>
    <td>
      <table class="form">
        <tr><th class="category" colspan="3">Informations sur la consultation</th></tr>
        
        <tr>
          <th>
            <label for="chir_id" title="Praticien pour la consultation">Praticien</label>
          </th>
          <td>
            <select name="chir_id" class="{{$consult->_props.patient_id}}" onChange="ClearRDV(); refreshListCategorie(this.value)">
              <option value="">&mdash; Choisir un praticien</option>
              {{foreach from=$listPraticiens item=curr_praticien}}
              <option class="mediuser" style="border-color: #{{$curr_praticien->_ref_function->color}};" value="{{$curr_praticien->user_id}}" {{if $chir->user_id == $curr_praticien->user_id}} selected="selected" {{/if}}>
                {{$curr_praticien->_view}}
              </option>
             {{/foreach}}
            </select>
          </td>
          <td>
            <input type="checkbox" name="_pause" value="1" onclick="changePause()" {{if $consult->consultation_id && $consult->patient_id==0}} checked="checked" {{/if}} />
            <label for="_pause" title="Planification d'une pause">Pause</label>
          </td>
        </tr>

        <tr id="viewPatient" {{if $consult->consultation_id && $consult->patient_id==0}}style="display:none;"{{/if}}>
          <th>
            {{mb_field object=$pat field="patient_id" hidden=1 prop="" ondblclick="PatSelector.init()" onchange="requestInfoPat()"}}
            {{mb_label object=$consult field="patient_id"}}
          </th>
          <td class="readonly"><input type="text" name="_pat_name" size="20" value="{{$pat->_view}}" readonly="readonly"  ondblclick="PatSelector.init()" /></td>
          <td class="button"><button class="search" type="button" onclick="PatSelector.init()">Rechercher un patient</button>
          <script type="text/javascript">
            PatSelector.init = function(){
              this.sForm = "editFrm";
              this.sId   = "patient_id";
              this.sView = "_pat_name";
              this.pop();
            }
          </script>             
          </td>
        </tr>     
        <tr>
          <th>
            {{mb_label object=$consult field="motif"}}<br />
            <select name="_helpers_motif" size="1" onchange="pasteHelperContent(this)">
              <option value="">&mdash; Choisir une aide</option>
              {{html_options options=$consult->_aides.motif.no_enum}}
            </select><br />
            <button class="new notext" title="Ajouter une aide � la saisie" type="button" onclick="addHelp('CConsultation', this.form.motif)">{{tr}}New{{/tr}}</button>            
          </th>
          <td colspan="2">{{mb_field object=$consult field="motif" rows="3"}}</td>
        </tr>

        <tr>
          <th>
            {{mb_label object=$consult field="rques"}}<br />
            <select name="_helpers_rques" size="1" onchange="pasteHelperContent(this)">
              <option value="">&mdash; Choisir une aide</option>
              {{html_options options=$consult->_aides.rques.no_enum}}
            </select><br />
            <button class="new notext" title="Ajouter une aide � la saisie" type="button" onclick="addHelp('CConsultation', this.form.rques)">{{tr}}New{{/tr}}</button>
          </th>
          <td colspan="2">{{mb_field object=$consult field="rques" rows="3"}}</td>
        </tr>

      </table>

    </td>
    <td>

      <table class="form">
        <tr><th class="category" colspan="3">Rendez-vous</th></tr>

        <tr>
          <th>{{mb_label object=$consult field="premiere"}}</th>
          <td>
            <input type="checkbox" name="_check_premiere" value="1"
              {{if $consult->_check_premiere}} checked="checked" {{/if}}
              onchange="if (this.checked) {this.form.premiere.value = 1;} else {this.form.premiere.value = 0;}" />
            {{mb_field object=$consult field="premiere" hidden="hidden"}}
            {{mb_label object=$consult field="_check_premiere"}}
          </td>
          <td rowspan="5" class="button">
            <button class="search" type="button" onclick="PlageConsultSelector.init()">choix de l'horaire</button>
          </td>
        </tr>

        <tr>
          <th>{{mb_label object=$consult field="adresse"}}</th>
          <td>
            <input type="checkbox" name="_check_adresse" value="1"
              {{if $consult->_check_adresse}} checked="checked" {{/if}}
              onchange="if (this.checked) {this.form.adresse.value = 1;} else {this.form.adresse.value = 0;}" />
            {{mb_field object=$consult field="adresse" hidden="hidden"}}
          </td>
        </tr>

        <tr>
          <th>{{mb_label object=$consult field="plageconsult_id"}}</th>
          <td class="readonly">
            <input type="text" name="_date" value="{{$consult->_date|date_format:"%A %d/%m/%Y"}}" ondblclick="PlageConsultSelector.init()" readonly="readonly" />
            {{mb_field object=$consult field="plageconsult_id" hidden=1 ondblclick="PlageConsultSelector.init()"}}
            <script type="text/javascript">
            PlageConsultSelector.init = function(){
              this.sForm = "editFrm";
              this.sHeure = "heure";
              this.sPlageconsult_id = "plageconsult_id";
              this.sDate = "_date";
              this.sDuree = "duree";
              this.sChir_id = "chir_id";
              this.pop();
            }
           </script> 
          </td>
        </tr>


        <tr>
          <th>{{mb_label object=$consult field="heure"}}</th>
          <td class="readonly">
            <input type="text" name="heure" value="{{$consult->heure|date_format:"%H:%M"}}" size="4" readonly="readonly" />
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$consult field="duree"}}</th>
          <td>
            <select name="duree">
              <option value="1" {{if $consult->duree == 1}} selected="selected" {{/if}}>simple</option>
              <option value="2" {{if $consult->duree == 2}} selected="selected" {{/if}}>double</option>
              <option value="3" {{if $consult->duree == 3}} selected="selected" {{/if}}>triple</option>
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
            <button class="trash" type="button" onclick="confirmDeletion(this.form,{typeName:'la consultation de',objName:'{{$consult->_ref_patient->_view|smarty:nodefaults|JSAttribute}}'})">
              Supprimer
            </button>
            <button class="print" type="button" onclick="printForm();">{{tr}}Print{{/tr}}</button>
          {{else}}
            <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
          {{/if}}
          </td>
        </tr>
      </table>
    
    </td>
  </tr>
</table>

<table class="form">
  <tr>
    <th id="clickPat" class="category" style="width: 50%">
      Infos patient
    </th>
    <th class="category" style="width: 50%">
      Documents
    </th>
  </tr>
  
  <tr>
    <td id="infoPat" class="text"></td>
    
    <td id="documents">
    	{{if $consult->_id}}
			{{mb_ternary var=object test=$consult->_is_anesth value=$consult->_ref_consult_anesth other=$consult}}
      <script type="text/javascript">
      	Document.register('{{$object->_id}}','{{$object->_class_name}}','{{$consult->_praticien_id}}','documents');
      </script>
      {{/if}}
    </td>
  </tr>
</table>

</form>