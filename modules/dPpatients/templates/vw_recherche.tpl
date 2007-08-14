<script type="text/javascript">

function pageMain() {
  regFieldCalendar("recherche", "date_debut");
  
  var oAccord = new Rico.Accordion($('accordionResultats'), { 
    panelHeight: ViewPort.SetAccordHeight('accordionResultats'),
    showDelay: 50, 
    showSteps: 3 
  } );       
}

</script>


<form name="recherche" action="./index.php" method="get">
  <table class="main">
    <tr>  
      <td class="halfPane">
 
    <input type="hidden" name="m" value="{{$m}}" />
    <input type="hidden" name="tab" value="{{$tab}}" />
    <input type="hidden" name="new" value="1" />
      
       
    <table class="form">
      <tr>
        <th class="category" colspan="4">Recherche d'un dossier patient</th>
      </tr>
        
      <!-- Criteres sur les patients -->  
      <tr>
        <th class="category" colspan="4">Patient</th>
      </tr>
      <tr>
        <th>{{mb_label object=$ant field="rques"}}</th>
        <td><input type="text" name="antecedent_patient" value="{{$antecedent_patient|stripslashes}}" /></td>
        <th>{{mb_label object=$trait field="traitement"}}</th>
        <td><input type="text" name="traitement_patient" value="{{$traitement_patient|stripslashes}}" /></td>
      </tr>
      <tr>
        <th>{{mb_label object=$pat_diag field="listCim10"}}</th>
        <td colspan="4"><input type="text" name="diagnostic_patient" value="{{$diagnostic_patient|stripslashes}}" /></td>
      </tr>       
         
      
         
      <!-- Criteres sur les consultations -->   
      <tr>
        <th class="category" colspan="4">Consultation</th>
      </tr>
      <tr>
        <td colspan="4">Au moins un crit�re <input type="radio" name="recherche_consult" value="or" {{if $recherche_consult == "or"}}checked{{/if}} />
        Tous les crit�res <input type="radio" name="recherche_consult" value="and" {{if $recherche_consult == "and"}}checked{{/if}} /></td>
      </tr>
      <tr>
        <th>{{mb_label object=$consult field="motif"}}</th>
        <td><input type="text" name="motif_consult" value="{{$motif_consult|stripslashes}}"/></td>
      
        <th>{{mb_label object=$consult field="rques"}}</th>
        <td><input type="text" name="remarque_consult" value="{{$remarque_consult|stripslashes}}"/></td>
      </tr>
      <tr>
        <th>{{mb_label object=$consult field="examen"}}</th>
        <td><input type="text" name="examen_consult" value="{{$examen_consult|stripslashes}}"/></td>
      
        <th>{{mb_label object=$consult field="traitement"}}</th>
        <td><input type="text" name="traitement_consult" value="{{$traitement_consult|stripslashes}}"/></td>
      </tr>
      
      
      <!-- Crit�res sur les s�jours --> 
      <tr>
        <th class="category" colspan="4">S�jour</th>
      </tr>
      <tr>
        <td colspan="4">Au moins un crit�re <input type="radio" name="recherche_sejour" value="or" {{if $recherche_sejour == "or"}}checked{{/if}} />
        Tous les crit�res <input type="radio" name="recherche_sejour" value="and" {{if $recherche_sejour == "and"}}checked{{/if}} /></td>
      </tr>
      <tr>
        <th>{{mb_label object=$sejour field="type"}}</th>
        <td><input type="text" name="typeAdmission_sejour" value="{{$typeAdmission_sejour|stripslashes}}" /></td>
        <th>{{mb_label object=$sejour field="convalescence"}}</th>
        <td><input type="text" name="convalescence_sejour" value="{{$convalescence_sejour|stripslashes}}" /></td>
      </tr>
      <tr>
        <th>{{mb_label object=$sejour field="rques"}}</th>
        <td colspan="4"><input type="text" name="remarque_sejour"  value="{{$remarque_sejour|stripslashes}}" /></td>
      </tr>       

       
      <!-- Crit�res sur les interventions -->         
      <tr>
        <th class="category" colspan="4">Intervention</th>
      </tr>
      <tr>
        <td colspan="4">Au moins un crit�re <input type="radio" name="recherche_intervention" value="or" {{if $recherche_intervention == "or"}}checked{{/if}} />
        Tous les crit�res <input type="radio" name="recherche_intervention" value="and" {{if $recherche_intervention == "and"}}checked{{/if}} /></td>
      </tr>
      <tr>
        <!-- materiel a prevoir / examens per-op -->
        <th>{{mb_label object=$intervention field="materiel"}}</th>
        <td><input type="text" name="materiel_intervention" value="{{$materiel_intervention|stripslashes}}"/></td>
        <!-- bilan pre-op -->
        <th>{{mb_label object=$intervention field="examen"}}</th>
        <td><input type="text" name="examen_intervention" value="{{$examen_intervention|stripslashes}}"/></td>
      </tr>
      <tr>
        <th>{{mb_label object=$intervention field="rques"}}</th>
        <td colspan="4"><input type="text" name="remarque_intervention" value="{{$remarque_intervention|stripslashes}}"/></td>
      </tr>   
      
    
      <tr>
        <td class="button" colspan="4">
          <button class="search" type="submit">Rechercher</button>
        </td>
      </tr>
    </table>
    
    </td>
    
      <td class="halfPane">
        
        <div class="accordionMain" id="accordionResultats">
 
          {{if $patients_diag}}
          <div id="acc_diagnostic">
            <div  class="accordionTabTitleBar" id="IdentiteHeader">
              R�sultats par Diagnostics ({{$patients_diag|@count}})
            </div>
            <div class="accordionTabContentBox" id="IdentiteContent"  >
              <table class="form">
                <tr>
                 <td>
                 {{foreach from=$patients_diag item=curr_patient}}
                 <tr>
                   <td class="text">
                   <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_patient->_id}}">
                      {{mb_value object=$curr_patient field="_view"}}
                   </a>
                   </td>
                   <td class="text">
                   <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_patient->_id}}">
                     {{mb_value object=$curr_patient field="naissance"}}
                   </a>
                   </td>
                   <td class="text">
                   <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_patient->_id}}">
                     {{mb_value object=$curr_patient field="adresse"}}
                     {{mb_value object=$curr_patient field="cp"}}
                     {{mb_value object=$curr_patient field="ville"}}
                   </a>
                   </td>
                 </tr>
                {{/foreach}} 
             </td>
           </tr>
         </table>
         </div>
        </div>
        {{/if}}
          
          
         {{if $traitements}}
         <div id="acc_traitement">
           <div  class="accordionTabTitleBar" id="IdentiteHeader">
             R�sultats par Traitements ({{$traitements|@count}})
           </div>
           <div class="accordionTabContentBox" id="IdentiteContent"  > 
             <table class="form">
               <tr>
                 <td>
                   {{foreach from=$traitements item=curr_traitement}}
                    <tr>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_traitement->_ref_object->_id}}">
                        {{mb_value object=$curr_traitement->_ref_object field="_view"}}
                      </a>
                      </td>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_traitement->_ref_object->_id}}">
                        {{mb_value object=$curr_traitement->_ref_object field="naissance"}}
                      </a>
                      </td>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_traitement->_ref_object->_id}}">
                        {{mb_value object=$curr_traitement->_ref_object field="adresse"}}
                        {{mb_value object=$curr_traitement->_ref_object field="cp"}}
                        {{mb_value object=$curr_traitement->_ref_object field="ville"}}
                      </a>
                      </td>
                    </tr>
                   {{/foreach}} 
                 </td>
               </tr>
             </table>
           </div>
         </div> 
         {{/if}}
        
        
        
        {{if $antecedents}}
         <div id="acc_antecedent">
           <div  class="accordionTabTitleBar" id="IdentiteHeader">
             R�sultats par Ant�c�dents ({{$antecedents|@count}})
           </div>
           <div class="accordionTabContentBox" id="IdentiteContent"  > 
             <table class="form">
               <tr>
                 <td>
                   {{foreach from=$antecedents item=curr_antecedent}}
                    <tr>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_antecedent->_ref_object->_id}}">
                        {{mb_value object=$curr_antecedent->_ref_object field="_view"}}
                      </a>
                      </td>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_antecedent->_ref_object->_id}}">
                        {{mb_value object=$curr_antecedent->_ref_object field="naissance"}}
                      </a>
                      </td>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_antecedent->_ref_object->_id}}">
                        {{mb_value object=$curr_antecedent->_ref_object field="adresse"}}
                        {{mb_value object=$curr_antecedent->_ref_object field="cp"}}
                        {{mb_value object=$curr_antecedent->_ref_object field="ville"}}
                      </a>
                      </td>
                    </tr>
                    {{/foreach}} 
                 </td>
               </tr>
             </table>
           </div>
         </div> 
         {{/if}}
        
          
         
         {{if $consultations}}
         <div id="acc_consultation">
           <div  class="accordionTabTitleBar" id="IdentiteHeader">
             R�sultats par Consultations ({{$consultations|@count}})
           </div>
           <div class="accordionTabContentBox" id="IdentiteContent"  > 
             <table class="form">
               <tr>
                 <td>
                   {{foreach from=$consultations item=curr_consultation}}
                    <tr>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_consultation->_ref_patient->_id}}&consultation_id={{$curr_consultation->_id}}">
                        {{mb_value object=$curr_consultation->_ref_patient field="_view"}}
                      </a>
                      </td>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_consultation->_ref_patient->_id}}&consultation_id={{$curr_consultation->_id}}">
                        {{mb_value object=$curr_consultation->_ref_patient field="naissance"}}
                      </a>
                      </td>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_consultation->_ref_patient->_id}}&consultation_id={{$curr_consultation->_id}}">
                        {{mb_value object=$curr_consultation->_ref_patient field="adresse"}}
                        {{mb_value object=$curr_consultation->_ref_patient field="cp"}}
                        {{mb_value object=$curr_consultation->_ref_patient field="ville"}}
                      </a>
                      </td>
                    </tr>
                    {{/foreach}} 
                 </td>
               </tr>
             </table>
           </div>
         </div> 
         {{/if}}
         
          
         {{if $interventions}}
         <div id="acc_intervention">
           <div  class="accordionTabTitleBar" id="IdentiteHeader">
             R�sultats par Interventions ({{$interventions|@count}})
           </div>
           <div class="accordionTabContentBox" id="IdentiteContent"  > 
             <table class="form">
               <tr>
                 <td>
                   {{foreach from=$interventions item=curr_intervention}}
                    <tr>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_intervention->_ref_sejour->_ref_patient->_id}}&operation_id={{$curr_intervention->_id}}">
                        {{mb_value object=$curr_intervention->_ref_sejour->_ref_patient field="_view"}}
                      </a>
                      </td>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_intervention->_ref_sejour->_ref_patient->_id}}&operation_id={{$curr_intervention->_id}}">
                        {{mb_value object=$curr_intervention->_ref_sejour->_ref_patient field="naissance"}}
                      </a>
                      </td>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_intervention->_ref_sejour->_ref_patient->_id}}&operation_id={{$curr_intervention->_id}}">
                        {{mb_value object=$curr_intervention->_ref_sejour->_ref_patient field="adresse"}}
                        {{mb_value object=$curr_intervention->_ref_sejour->_ref_patient field="cp"}}
                        {{mb_value object=$curr_intervention->_ref_sejour->_ref_patient field="ville"}}
                      </a>
                      </td>
                    </tr>
                    {{/foreach}} 
                 </td>
               </tr>
             </table>
           </div>
         </div> 
         {{/if}}
          
          
         {{if $sejours}}
         <div id="acc_sejour">
           <div  class="accordionTabTitleBar" id="IdentiteHeader">
             R�sultats par S�jours ({{$sejours|@count}})
           </div>
           <div class="accordionTabContentBox" id="IdentiteContent"  > 
             <table class="form">
               <tr>
                 <td>
                   {{foreach from=$sejours item=curr_sejour}}
                    <tr>
                      <td class="text">
                       <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_sejour->_ref_patient->_id}}&sejour_id={{$curr_sejour->_id}}">
                         {{mb_value object=$curr_sejour->_ref_patient field="_view"}}
                       </a>
                      </td>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_sejour->_ref_patient->_id}}&sejour_id={{$curr_sejour->_id}}">
                        {{mb_value object=$curr_sejour->_ref_patient field="naissance"}}
                      </a>
                      </td>
                      <td class="text">
                      <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$curr_sejour->_ref_patient->_id}}&sejour_id={{$curr_sejour->_id}}">
                        {{mb_value object=$curr_sejour->_ref_patient field="adresse"}}
                        {{mb_value object=$curr_sejour->_ref_patient field="cp"}}
                        {{mb_value object=$curr_sejour->_ref_patient field="ville"}}
                      </a>
                      </td>
                    </tr>
                    {{/foreach}} 
                 </td>
               </tr>
             </table>
           </div>
         </div> 
         {{/if}}
          
          
          {{if !$antecedents && !$traitements && !$patients_diag && !$consultations && !$sejours && !$interventions }}
          <div id="acc_aucunResultat">
            <div  class="accordionTabTitleBar" id="IdentiteHeader">
              Aucun r�sultat pour la recherche
            </div>
            <div class="accordionTabContentBox" id="IdentiteContent"  >
              
            </div>
          </div>          
          {{/if}}
        </div>    
    </td>
  </table>
</form>