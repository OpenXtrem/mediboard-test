<!-- $Id$ -->
<script type="text/javascript" src="modules/dPpatients/javascript/autocomplete.js?build={{$mb_version_build}}"></script>
<script type="text/javascript">
var httpreq_running = false;
function confirmCreation(oForm){
  if(httpreq_running) {
    return false;
  }
  if(!checkForm(oForm)){
    return false;
  }
  httpreq_running = true;
  var url = new Url;
  url.setModuleAction("dPpatients", "httpreq_get_siblings");
  url.addParam("patient_id", oForm.patient_id.value);
  url.addParam("nom", oForm.nom.value);
  url.addParam("prenom", oForm.prenom.value);  
  if(oForm._annee.value!="" && oForm._mois.value!="" && oForm._jour.value!=""){
    url.addParam("naissance", oForm._annee.value + "-" + oForm._mois.value + "-" + oForm._jour.value);
  }
  url.requestUpdate('divSiblings', { evalScripts: true, waitingText: null });
  return false;
}

function printPatient(id) {
  var url = new Url();
  url.setModuleAction("dPpatients", "print_patient");
  url.addParam("patient_id", id);
  url.popup(700, 550, "Patient");
}

function popMed(type) {
  var url = new Url();
  url.setModuleAction("dPpatients", "vw_medecins");
  url.addParam("type", type);
  url.popup(700, 450, "Medecin");
}

function delCmu(){
  oForm = document.editFrm;
  oForm.cmu.value = "";
  oDateDiv = $("editFrm_cmu_da");
  oDateDiv.innerHTML = "";
  
}

function delMed(sElementName) {
  oForm = document.editFrm;
  
  oFieldMedecin = eval("oForm.medecin" + sElementName);
  oFieldMedecinName = eval("oForm._medecin" + sElementName + "_name");
	
  oFieldMedecin.value = "";
  oFieldMedecinName.value = "";
}

function setMed( key, nom, prenom, sElementName ){
  oForm = document.editFrm;
  
  oFieldMedecin = eval("oForm.medecin" + sElementName);
  oFieldMedecinName = eval("oForm._medecin" + sElementName + "_name");
	
  oFieldMedecin.value = key;
  oFieldMedecinName.value = "Dr. " + nom + " " + prenom;
}


function pageMain() {
  initInseeFields("editFrm", "cp", "ville","pays");
  initInseeFields("editFrm", "prevenir_cp", "prevenir_ville", "_tel31");
  initInseeFields("editFrm", "employeur_cp", "employeur_ville", "_tel41");
  initPaysField("editFrm", "pays","_tel1");
  regFieldCalendar("editFrm", "cmu");
}

</script>

<table class="main">
  {{if $patient->patient_id}}
  <tr>
    <td><a class="buttonnew" href="?m={{$m}}&amp;patient_id=0">Cr�er un nouveau patient</a></td>
  </tr>
  {{/if}}

  <tr>
    <td>
      <form name="editFrm" action="?m={{$m}}" method="post" onsubmit="return confirmCreation(this)">
      <input type="hidden" name="dosql" value="do_patients_aed" />
      <input type="hidden" name="del" value="0" />
      {{mb_field object=$patient field="patient_id" hidden=1 prop=""}}
      {{if $dialog}}
      <input type="hidden" name="dialog" value="{{$dialog}}" />
      {{/if}}
      
      <table class="main">

      <tr>
      {{if $patient->patient_id}}
        <th class="title modify" colspan="5">
          {{if $canSante400->read}}
          <a style="float:right;" href="#" onclick="view_idsante400('CPatient',{{$patient->patient_id}})">
            <img src="images/icons/sante400.gif" alt="Sante400" title="Identifiant sante 400"/>
          </a>
          {{/if}}
          <a style="float:right;" href="#" onclick="view_log('CPatient',{{$patient->patient_id}})">
            <img src="images/icons/history.gif" alt="historique" />
          </a>
          Modification du dossier de {{$patient->_view}}
        </th>
      {{else}}
        <th class="title" colspan="5">Cr�ation d'un dossier</th>
      {{/if}}
      </tr>
      
      <tr>
        <td colspan="5">
          <div class="accordionMain" id="accordionConsult">
          
            <div id="Identite">
              <div id="IdentiteHeader" class="accordionTabTitleBar">
                Identit�
              </div>
              <div id="IdentiteContent"  class="accordionTabContentBox">
              {{include file="inc_acc/inc_acc_identite.tpl"}}
              </div>
            </div>
            <div id="Medical">
              <div id="MedicalHeader" class="accordionTabTitleBar">
                M�dical
              </div>
              <div id="MedicalContent"  class="accordionTabContentBox">
              {{include file="inc_acc/inc_acc_medical.tpl"}}
              </div>
            </div>
            <div id="Corresp">
              <div id="CorrespHeader" class="accordionTabTitleBar">
                Correspondance
              </div>
              <div id="CorrespContent"  class="accordionTabContentBox">
              {{include file="inc_acc/inc_acc_corresp.tpl"}}
              </div>
            </div>
          </div>
        </td>
      </tr>
      
      <tr>
        <td class="button" colspan="5" style="text-align:center;">
          <div id="divSiblings" style="display:none;"></div>
          {{if $patient->patient_id}}
            <button tabindex="400" type="submit" class="submit">Valider</button>
            <button type="button" class="print" onclick="printPatient({{$patient->patient_id}})">
              Imprimer
            </button>
            <button type="button" class="trash" onclick="confirmDeletion(this.form,{typeName:'le patient',objName:'{{$patient->_view|smarty:nodefaults|JSAttribute}}'})">
              Supprimer
            </button>
          {{else}}
            <button tabindex="400" type="submit" class="submit">Cr�er</button>
          {{/if}}
        </td>
      </tr>

      </table>
      </form>
    </td>
  </tr>
</table>
<script language="Javascript" type="text/javascript">
var oAccord = new Rico.Accordion( $('accordionConsult'), { 
  panelHeight: 250, 
  showDelay:50, 
  showSteps:3 
} );
</script>
