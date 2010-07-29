<!-- $Id$ -->

{{mb_include_script module="dPpatients" script="autocomplete"}}
{{mb_include_script module="dPpatients" script="siblings_checker"}}
{{mb_include_script module="dPpatients" script="patient"}}

{{if $app->user_prefs.VitaleVision}}
  {{mb_include template=inc_vitalevision}}
  
	<script type="text/javascript">
		var lireVitale = VitaleVision.read;
	</script>
{{else}}
  {{mb_include template=inc_intermax}}
	
	<script type="text/javascript">
		Intermax.ResultHandler["Consulter Vitale"] =
		Intermax.ResultHandler["Lire Vitale"] = function() {
		  var url = new Url;
		//  url.setModuleTab("dPpatients", "vw_edit_patients");
		  url.addParam("m", "dPpatients");
		  url.addParam("{{$actionType}}",  "vw_edit_patients");
		  url.addParam("dialog",  "{{$dialog}}");
		  url.addParam("useVitale", 1);
		  url.redirect();
		}
		
		var lireVitale = function(){
      Intermax.trigger('Lire Vitale');
    }
	</script>
{{/if}}

<script type="text/javascript">

function copyAssureValues(element) {
	// Hack pour g�rer les form fields
	var sPrefix = element.name[0] == "_" ? "_assure" : "assure_";
  eOther = element.form[sPrefix + element.name];

  $V(eOther, $V(element));
  eOther.fire("mask:check");
}

function copyIdentiteAssureValues(element) {
	if (element.form.qual_beneficiaire.value == "0") {
		copyAssureValues(element);
	}
}

function confirmCreation(oForm){
  if (!checkForm(oForm)) {
    return false;
  }

  SiblingsChecker.submit = true;
  SiblingsChecker.request(oForm);
  return false;
}

var tabs;
Main.add(function () {
  InseeFields.initCPVille("editFrm", "cp", "ville","pays");
  InseeFields.initCPVille("editFrm", "cp_naissance", "lieu_naissance","_pays_naissance_insee");
  InseeFields.initCPVille("editFrm", "prevenir_cp", "prevenir_ville", "prevenir_tel");
  InseeFields.initCPVille("editFrm", "employeur_cp", "employeur_ville", "employeur_tel");
  initPaysField("editFrm", "_pays_naissance_insee", "nationalite");
  initPaysField("editFrm", "pays", "tel");
  
  InseeFields.initCPVille("editFrm", "assure_cp", "assure_ville","assure_pays_insee");
  InseeFields.initCPVille("editFrm", "assure_cp_naissance", "assure_lieu_naissance","_assure_pays_naissance_insee");
  initPaysField("editFrm", "_assure_pays_naissance_insee", "assure_nationalite");
  initPaysField("editFrm", "assure_pays", "assure_tel");

  tabs = new Control.Tabs('tab-patient');
  
  {{if $useVitale && $app->user_prefs.VitaleVision}}
  lireVitale.delay(1); // 1 second
  {{/if}}
});

</script>

<form name="delete-photo-identite-form" method="post" action="?">
  <input type="hidden" name="m" value="dPfiles" />
  <input type="hidden" name="file_id" value="" />
  <input type="hidden" name="del" value="1" />
  <input type="hidden" name="dosql" value="do_file_aed" />
</form>

{{if $patient->_id}}
<a class="button new" href="?m={{$m}}&amp;{{$actionType}}={{$action}}&amp;dialog={{$dialog}}&amp;patient_id=0">
  {{tr}}CPatient-title-create{{/tr}}
</a>
{{/if}}

<div id="modal-beneficiaire" style="display:none; text-align:center;">
  <p id="msg-multiple-benef">
    Cette carte vitale semble contenir plusieurs b�n�ficiaires, merci de s�lectionner la personne voulue :
  </p>
  <p id="msg-confirm-benef" style="display: none;">
    Vous �tes sur le point de remplacer les donn�es du formulaire par les donn�es de la carte. <br />
    Veuillez v�rifier le nom du b�n�ficiaire :
  </p>
	<p id="benef-nom">
	  <select id="modal-beneficiaire-select"></select>
    <span></span>
  </p>
  <div>
  	<button type="button" class="tick" onclick="VitaleVision.fillForm(getForm('editFrm'), $V($('modal-beneficiaire-select'))); VitaleVision.modalWindow.close();">{{tr}}Choose{{/tr}}</button>
	  <button type="button" class="cancel" onclick="VitaleVision.modalWindow.close();">{{tr}}Cancel{{/tr}}</button>
  </div>
</div>

<table class="main">
  <tr>
  {{if $patient->_id}}
    <th class="title modify" colspan="5">
      {{if $app->user_prefs.GestionFSE}}
	      <button class="search singleclick" type="button" onclick="lireVitale();" style="float: left;">
	        Lire Vitale
	      </button>
	      {{if !$app->user_prefs.VitaleVision}}
		      {{if $patient->_id_vitale}}
			      <button class="search singleclick" type="button" onclick="Intermax.Triggers['Consulter Vitale']({{$patient->_id_vitale}});" style="float: left;">
			        Consulter Vitale
			      </button>
		      {{/if}}
		      <button class="change intermax-result" type="button" onclick="Intermax.result();" style="float: left;">
		        R�sultat Vitale
		      </button>
	      {{/if}}
			{{/if}}
    
			{{if $patient->_id_vitale}}
      <div style="float:right;">
	      <img src="images/icons/carte_vitale.png" title="B�n�ficiaire associ� � une carte Vitale" />
      </div>
      {{/if}}
      
      {{mb_include module=system template=inc_object_idsante400 object=$patient}}
      {{mb_include module=system template=inc_object_history    object=$patient}}
      Modification du dossier de {{$patient}} 
      {{mb_include module=dPpatients template=inc_vw_ipp ipp=$patient->_IPP}}
      {{if $patient->_bind_vitale}}{{tr}}UseVitale{{/tr}}{{/if}}
    </th>
  {{else}}
    <th class="title" colspan="5">
      {{if $app->user_prefs.GestionFSE}}
        <button class="search singleclick" type="button" onclick="lireVitale();" style="float: left;">
          Lire Vitale
        </button>
				{{if !$app->user_prefs.VitaleVision}}
		      <button class="change intermax-result" type="button" onclick="Intermax.result();" style="float: left;">
		        R�sultat Vitale
		      </button>
				{{/if}}
			{{/if}}
			{{tr}}Create{{/tr}}
      {{if $patient->_bind_vitale}}{{tr}}UseVitale{{/tr}}{{/if}}
    </th>
  {{/if}}
  </tr>
  
  <tr>
    <td colspan="5">
      <ul id="tab-patient" class="control_tabs">
        <li><a href="#identite">Identit�</a></li>
        <li><a href="#medecins">Correspondants m�dicaux</a></li>
        <li><a href="#correspondance">Correspondance</a></li>
        <li><a href="#assure">Assur� social</a></li>
        <li><a href="#beneficiaire">B�n�ficiaire de soins</a></li>
      </ul>
      <hr class="control_tabs" />
      
      <form name="editFrm" action="?m={{$m}}" method="post" onsubmit="return confirmCreation(this)">
        <input type="hidden" name="dosql" value="do_patients_aed" />
        <input type="hidden" name="del" value="0" />
			  <input type="hidden" name="_purge" value="0" />
        {{if $patient->_bind_vitale}}
        <input type="hidden" name="_bind_vitale" value="1" />
        {{/if}}
        
        <button type="submit" style="display: none;">&nbsp;</button>
        
        {{mb_field object=$patient field="patient_id" hidden=1 prop=""}}
        
        {{if !$patient->_id}}
				{{mb_field object=$patient field="medecin_traitant" hidden=1}}
				{{/if}}
				
        {{if $dialog}}
        <input type="hidden" name="dialog" value="{{$dialog}}" />
        {{/if}}
        
        <div id="identite" style="display: none;">
          {{mb_include template=inc_acc/inc_acc_identite}}
        </div>
        <div id="correspondance" style="display: none;">
          {{mb_include template=inc_acc/inc_acc_corresp}}
        </div>
        <div id="assure" style="display: none;">
          {{mb_include template=inc_acc/inc_acc_assure}}
        </div>
        <div id="beneficiaire" style="display: none;">
          {{mb_include template=inc_acc/inc_acc_beneficiaire}}   
        </div>
      </form>
      
      <div id="medecins" style="display: none;">
        {{mb_include template=inc_acc/inc_acc_medecins}}
      </div>
    </td>
  </tr>
  
  <tr>
    <td class="button" colspan="5" style="text-align:center;" id="button">
      <div id="divSiblings" style="display:none;"></div>
      {{if $patient->_id}}
        <button tabindex="400" id="submit-patient" type="submit" class="submit" onclick="return document.editFrm.onsubmit();">
          {{tr}}Save{{/tr}}
          {{if $patient->_bind_vitale}}
          &amp; {{tr}}BindVitale{{/tr}}
          {{/if}}
        </button>
        
        <button type="button" class="print" onclick="Patient.print('{{$patient->_id}}')">
          {{tr}}Print{{/tr}}
        </button>
        
        <button type="button" class="trash" onclick="confirmDeletion(document.editFrm,{typeName:'le patient',objName:'{{$patient->_view|smarty:nodefaults|JSAttribute}}'})">
          {{tr}}Delete{{/tr}}
        </button>

        {{if $can->admin}}        
        <script type="text/javascript">
          function confirmPurge() {
          	var oForm = document.editFrm;
          	if (confirm("ATTENTION : Vous �tes sur le point de purger le dossier de ce patient")) {
          	  oForm._purge.value = "1";
	          	confirmDeletion(oForm,	{
	          		typeName:'le patient',
	          		objName:'{{$patient->_view|smarty:nodefaults|JSAttribute}}'
	          	} );
	          }
	        }
        </script>
			
        <button type="button" class="cancel" onclick="confirmPurge();">
          {{tr}}Purge{{/tr}}
        </button>
        {{/if}} 

      {{else}}
        <button tabindex="400" id="submit-patient" type="submit" class="submit" onclick="return document.editFrm.onsubmit();">
          {{tr}}Create{{/tr}}
          {{if $patient->_bind_vitale}}
          &amp; {{tr}}BindVitale{{/tr}}
          {{/if}}
        </button>
      {{/if}}
    </td>
  </tr>
</table>
