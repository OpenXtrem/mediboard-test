  </td>
	</tr>
	</table>
<script type="text/javascript">
{{if !$offline}}
  Main.add( function(){
    oCatField = new TokenField(document.filter_prescription.token_cat); 
    
    var cats = {{$cats|@json}};
    $$('input[type=checkbox]').each( function(oCheckbox) {
      if(cats.include(oCheckbox.value)){
        oCheckbox.checked = true;
      }
    });
    
    getForm("filter_prescription")._dateTime_min.observe("ui:change", resetPeriodes);
    getForm("filter_prescription")._dateTime_max.observe("ui:change", resetPeriodes);
  } );
  
  
  var groups = {{$all_groups|@json}};
  
  function preselectCat(cat_group_id){
    // On efface la selection de toutes les checkbox
    // (sauf par patient et seulement les pr�sents)
    $$('input[type=checkbox]').each( function(oCheckbox) {
      if (oCheckbox.name != "_present_only_vw" && oCheckbox.name != "by_patient") {
        oCheckbox.checked = false;
        oCatField.remove(oCheckbox.value);
      }
    });
    
    if (!cat_group_id) {
      return;
    }
    
  	// Selection des checkbox en fonction du groupe selectionn�
    group = groups[cat_group_id];
    group.each( function(item_id){
      $(item_id).checked = true;
      $(item_id).onclick();
    });
  }
  
  function resetPeriodes() {
    getForm("filter_prescription").select('input[name=periode]').each(function(e) {
      e.checked = false;
    });
  }
  
  selectChap = function(name_chap, oField){
    $$('input.'+name_chap).each(function(oCheckbox) { 
      if(!oCheckbox.checked){
        oCheckbox.checked = true;
        oField.add(oCheckbox.value);
      }
    });
  }
  
  var periodes = {{$conf.dPprescription.CPrisePosologie.heures|@json}};
  selectPeriode = function(element) {
    var form = getForm("filter_prescription");
    var start = form.elements._dateTime_min;
    var end = form.elements._dateTime_max;
    
    var startDate = Date.fromDATETIME($V(start));
    var endDate = Date.fromDATETIME($V(start));
    
    if (element.value == 'matin' || element.value == 'soir' || element.value == 'nuit') {
      startDate.setHours(periodes[element.value].min);
  
      var dayOffset = 0;
      if (periodes[element.value].max < periodes[element.value].min) {
        dayOffset = 1;
      }
      endDate.setDate(startDate.getDate()+dayOffset);
      endDate.setHours(periodes[element.value].max);
    }
    else {
      startDate.setHours(0);
      startDate.setMinutes(0);
      startDate.setSeconds(0);
      endDate.setTime(startDate.getTime()+24*60*60*1000-1000);
    }
    
    form._dateTime_min_da.value = startDate.toLocaleDateTime();
    form._dateTime_max_da.value = endDate.toLocaleDateTime();
    
    startDate = startDate.toDATETIME(true);
    endDate = endDate.toDATETIME(true);
    
    $V(start, startDate, false);
    $V(end, endDate, false);
  }
{{/if}}

</script>
{{if !$offline}}
<form name="filter_prescription" action="?" method="get" class="not-printable">
  <input type="hidden" name="token_cat" value="{{$token_cat}}" />     
  <input type="hidden" name="m" value="dPhospi" />
  <input type="hidden" name="a" value="vw_bilan_service" />
  <input type="hidden" name="dialog" value="1" />
  <input type="hidden" name="do" value="1" />
  <table class="form">
  	<tr>
  		<th class="title" colspan="5">Bilan du service {{$service->_view}}</th>
  	</tr>
    <tr>
      <th class="category" colspan="4">Param�tres d'impression</th>
    </tr>
    <tr>
      <th>A partir du</th>
      <td>
        {{mb_field object=$prescription field="_dateTime_min" canNull="false" form="filter_prescription" register="true"}}
        <label><input type="radio" name="periode" value="matin" onclick="selectPeriode(this)" {{if $periode=='matin'}}checked="checked"{{/if}} /> Matin</label>
        <label><input type="radio" name="periode" value="soir" onclick="selectPeriode(this)" {{if $periode=='soir'}}checked="checked"{{/if}}/> Soir</label>
        <label><input type="radio" name="periode" value="nuit" onclick="selectPeriode(this)" {{if $periode=='nuit'}}checked="checked"{{/if}}/> Nuit</label>
        <label><input type="radio" name="periode" value="today" onclick="selectPeriode(this)" {{if $periode=='today'}}checked="checked"{{/if}}/> Aujourd'hui</label>
      </td>
      <th>Jusqu'au</th>
      <td>
        {{mb_field object=$prescription field="_dateTime_max" canNull="false" form="filter_prescription" register="true"}}
       </td>
     </tr>
		 <tr>
		 	<th>
		 		Impression par patient 
			</th>
			<td colspan="3">
				<input type="checkbox" name="by_patient" value="true" {{if $by_patient}}checked="checked"{{/if}}
		 	</td>
    </tr>
    <tr>
      <th>
        {{tr}}CPatient.present_only{{/tr}}
      </th>
      <td colspan="3">
        <input type="checkbox" name="_present_only_vw" {{if $_present_only}}checked="checked"{{/if}}
          onchange="this.checked ? $V(this.form._present_only, 1) : $V(this.form._present_only, 0)"/>
        <input type="hidden" name="_present_only" value="{{$_present_only}}" />
      </td>
		 </tr>
		 <tr>
		   <th class="category" colspan="4">Pr�-s�lection des cat�gories</th>	
		 </tr>
		 <tr>
		   <td colspan="4" class="text" style="text-align: center;">
		   	 {{if $cat_groups|@count}}
				   <select name="cat_group_id" onchange="preselectCat(this.value);">
				   	 <option value="">&mdash; Groupe de cat�gories</option>
	           {{foreach from=$cat_groups item=_cat_group}}
	             <option value="{{$_cat_group->_id}}" {{if $_cat_group->_id == $cat_group_id}}selected="true"{{/if}}>
                 {{$_cat_group->libelle}}
               </option>
	           {{/foreach}}
	         </select>
	       {{else}}
				   <div class="small-info">Aucun groupe de cat�gories n'est disponible. <br />Pour pouvoir utiliser des pr�-s�lections de cat�gories, il faut tout d'abord les param�trer dans le module "Prescription", onglet "Groupe de cat�gories"</div>
         {{/if}}
			 </td>
		 </tr>
		 <tr>
		   <th class="category" colspan="4">S�lection des cat�gories</th>
     </tr>
     <tr>
       <td colspan="4">
         <table>
           <tr>
             <td>
               <strong>Transmissions</strong>
             </td>
             <td>
               <input type="checkbox" value="trans" id="trans" onclick="oCatField.toggle(this.value, this.checked);" />
             </td>
           </tr>
					 <tr>
             <td>
               <strong>{{tr}}CPrescription._chapitres.med{{/tr}}</strong>
             </td>
             <td>
               <input type="checkbox" value="med" id="med" onclick="oCatField.toggle(this.value, this.checked);" />
             </td>
           </tr>
           <tr>
             <td>
               <strong>{{tr}}CPrescription._chapitres.inj{{/tr}}</strong>
             </td>
             <td>
               <input type="checkbox" value="inj" id="inj" onclick="oCatField.toggle(this.value, this.checked);" />
             </td>
           </tr>
           <tr>
             <td>
               <strong>{{tr}}CPrescription._chapitres.perf{{/tr}}</strong>
             </td>
             <td>
               <input type="checkbox" value="perf" id="perf" onclick="oCatField.toggle(this.value, this.checked);" />
             </td>
           </tr>
					 <tr>
             <td>
               <strong>{{tr}}CPrescription._chapitres.aerosol{{/tr}}</strong>
             </td>
             <td>
               <input type="checkbox" value="aerosol" id="aerosol" onclick="oCatField.toggle(this.value, this.checked);" />
             </td>
           </tr>
           
           {{foreach from=$categories item=categories_by_chap key=name name="foreach_cat"}}
             {{if $categories_by_chap|@count}}
  	           <tr>
  	             <td>
  	               <button type="button" onclick="selectChap('{{$name}}', oCatField);" class="tick">Tous</button>
  	               <strong>{{tr}}CCategoryPrescription.chapitre.{{$name}}{{/tr}}</strong>  
  	             </td>
  	             {{foreach from=$categories_by_chap item=categorie}}
  	               <td style="white-space: nowrap; float: left; width: 10em;">
  	                 <label title="{{$categorie->_view}}">
  	                 <input class="{{$name}}" type="checkbox" id="{{$categorie->_id}}" value="{{$categorie->_id}}" onclick="oCatField.toggle(this.value, this.checked);"/> {{$categorie->_view}}
  	                 </label>
  	               </td>
  	             {{/foreach}}
  	           </tr>
             {{/if}}
           {{/foreach}}
         </table>
       </td>
    </tr>
		<tr>
		  <th colspan="4" class="category">Options</th>
		</tr>
		<tr>
      <th colspan="2" style="">
        Afficher les lignes inactives
      </th>
		  <td colspan="2">
		  	<input type="radio" name="show_inactive" value="1" {{if $show_inactive=='1'}}checked="checked"{{/if}} /> Oui
        <input type="radio" name="show_inactive" value="0" {{if $show_inactive=='0'}}checked="checked"{{/if}} /> Non
		  </td>
		</tr>
    <tr>
      <td style="text-align: center" colspan="4">
        <button class="tick">Filtrer</button>
        {{if $lines_by_patient|@count || $trans_and_obs|@count}}
          <button class="print" type="button" onclick="window.print()">Imprimer les r�sultats</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
{{/if}}

{{if $offline}}
  <button class="print not-printable" style="float: right;" onclick="window.print();">{{tr}}Print{{/tr}}</button>
{{/if}}

{{if $trans_and_obs|@count}}
<br />
<table class="tbl">
	<tr>
		<th colspan="7" class="title">Transmissions  - 
    {{$service->_view}} - du {{$dateTime_min|date_format:$conf.datetime}} au {{$dateTime_max|date_format:$conf.datetime}}
		</th>
	</tr>
  {{foreach from=$trans_and_obs key=patient_id item=_trans_and_obs_by_patient}}
	  {{foreach from=$_trans_and_obs_by_patient item=_trans_and_obs_by_date name=foreach_trans_date}}
		  {{foreach from=$_trans_and_obs_by_date item=_trans_and_obs name=foreach_trans}}
			
			  {{if $smarty.foreach.foreach_trans_date.first && $smarty.foreach.foreach_trans.first}}
          {{if $_trans_and_obs instanceof CTransmissionMedicale || $_trans_and_obs instanceof CObservationMedicale}} 
  			    {{assign var=sejour value=$_trans_and_obs->_ref_sejour}}
          {{else}}
            {{assign var=sejour value=$_trans_and_obs->_ref_context}}
          {{/if}}
  			  {{assign var=patient value=$sejour->_ref_patient}}
          {{assign var=operation value=$sejour->_ref_last_operation}}
					<tr>
			      <th colspan="7" class="text">
              <span style="float: left; text-align: left;">
                {{foreach from=$sejour->_ref_affectations item=_affectation}}
                   <strong
                    {{if $_affectation->entree < $dateTime_min &&
                      ($_affectation->sortie < $dateTime_min || $_affectation->sortie > $dateTime_max)}}
                      style="color: #666"
                    {{/if}}>
                      {{$_affectation->_view}}
                    </strong>
                   {{if $sejour->_ref_affectations|@count > 1}}
                   <small
                    {{if $_affectation->entree < $dateTime_min &&
                      ($_affectation->sortie < $dateTime_min || $_affectation->sortie > $dateTime_max)}}
                      style="color: #666"
                    {{/if}}>
                     (du {{$_affectation->entree|date_format:$conf.date}} au {{$_affectation->sortie|date_format:$conf.date}})
                   </small>
                  {{/if}}
                  <br />
                {{/foreach}}
              </span>
			        <span style="float: right">
			          DE: {{$sejour->_entree|date_format:"%d/%m/%Y"}}<br />
			          DS:  {{$sejour->_sortie|date_format:"%d/%m/%Y"}}
			        </span>
			        <strong>{{$patient->_view}}</strong>
			        N�(e) le {{mb_value object=$patient field=naissance}} - ({{$patient->_age}} ans) - ({{$patient->_ref_constantes_medicales->poids}} kg)
			        <br />
              {{if $operation->_id}}
  			        {{$operation->_ref_chir->_view}} - Intervention le {{$operation->_ref_plageop->date|date_format:"%d/%m/%Y"}} - 
		  	        <strong>(I{{if $operation->_compteur_jour >=0}}+{{/if}}{{$operation->_compteur_jour}}) - {{mb_label object=$operation field="cote"}} {{mb_value object=$operation field="cote"}}</strong>
              {{/if}}
			      </th>
			    </tr>
			    <tr>
			      <th class="element text" colspan="7" style="text-align: left">
			        <strong>{{$operation->libelle}}</strong> 
			        {{if !$operation->libelle}}
			          {{foreach from=$operation->_ext_codes_ccam item=curr_ext_code}}
			            <strong>{{$curr_ext_code->code}}</strong> :
			            {{$curr_ext_code->libelleLong}}<br />
			          {{/foreach}}
			        {{/if}}
			      </th>
			    </tr>
			  {{/if}}
			  <tr id="{{$_trans_and_obs->_guid}}" {{if $_trans_and_obs instanceof CTransmissionMedicale}}class="{{$_trans_and_obs->_cible}}"{{/if}}
          {{if $_trans_and_obs instanceof CTransmissionMedicale && $_trans_and_obs->degre == 'high'}}
            style="font-weight: bold;"
          {{/if}}>
          {{include file=../../dPhospi/templates/inc_line_suivi.tpl _suivi=$_trans_and_obs show_patient=false readonly=true nodebug=true}}
        </tr>
		  {{/foreach}}
	  {{/foreach}}
	{{/foreach}}
</table>
<br />
{{/if}}

{{foreach from=$lines_by_patient key=key1 item=_lines_by_chap name=foreach_chapitres}}
<table class="tbl" {{if !$smarty.foreach.foreach_chapitres.first || $trans_and_obs|@count}}style="page-break-before: always;"{{/if}}>
  {{if !$by_patient}}
	<tr>
    <th colspan="6" class="title">{{tr}}CPrescription._chapitres.{{$key1}}{{/tr}} - 
		{{$service->_view}} - du {{$dateTime_min|date_format:$conf.datetime}} au {{$dateTime_max|date_format:$conf.datetime}}</th>
  </tr>
	{{/if}}
	
	{{foreach from=$_lines_by_chap key=key2 item=_lines_by_patient name="foreach_lines"}}
		{{if $by_patient}}
		  {{assign var=lit value=$lits.$key1}}
		{{else}}
		   {{assign var=lit value=$lits.$key2}}
	  {{/if}}
	
	  {{foreach from=$_lines_by_patient key=sejour_id item=prises_by_dates }}
	    {{assign var=sejour value=$sejours.$sejour_id}}
	    {{assign var=patient value=$sejour->_ref_patient}}
			{{assign var=operation value=$sejour->_ref_last_operation}} 
			
			{{if !$by_patient || ($by_patient && $smarty.foreach.foreach_lines.first)}}
	    <tr><td colspan="6"><br /></td></tr>
			<tr>
	      <th colspan="6" class="text">
	        <span style="float: left">
            <strong>{{$lit->_ref_chambre->_view}}</strong>
            <br />
	          <strong>{{$lit->_short_view}}</strong>
	        </span>
			    <span style="float: right">
			      DE: {{$sejour->_entree|date_format:"%d/%m/%Y"}}<br />
			      DS:  {{$sejour->_sortie|date_format:"%d/%m/%Y"}}
			    </span>
			    <strong>{{$patient->_view}}</strong>
			    N�(e) le {{mb_value object=$patient field=naissance}} - ({{$patient->_age}} ans) - ({{$patient->_ref_constantes_medicales->poids}} kg)
			    <br />
	         Intervention le {{$operation->_ref_plageop->date|date_format:"%d/%m/%Y"}} - 
			    <strong>(I{{if $operation->_compteur_jour >=0}}+{{/if}}{{$operation->_compteur_jour}}) - {{mb_label object=$operation field="cote"}} {{mb_value object=$operation field="cote"}}</strong>
	      </th>
	    </tr>
			<tr>
				<th class="element text" colspan="6" style="text-align: left">
	        <strong>{{$operation->libelle}}</strong> 
	        {{if !$operation->libelle}}
	          {{foreach from=$operation->_ext_codes_ccam item=curr_ext_code}}
	            <strong>{{$curr_ext_code->code}}</strong> :
	            {{$curr_ext_code->libelleLong}}<br />
	          {{/foreach}}
	        {{/if}}
				</th>
			</tr>
			{{/if}}
			
			{{if $by_patient}}
			<tr>
			  <th colspan="6">{{tr}}CPrescription._chapitres.{{$key2}}{{/tr}}</th>
			</tr>
			{{/if}}
			
			{{foreach from=$prises_by_dates key=date item=prises_by_hour name="foreach_date"}}
		  <tr>
		    <td style="border:none;" class="narrow"><strong>{{$date|date_format:"%d/%m/%Y"}}</strong></td>
				<th style="width: 250px; border:none;">Libell�</th> 
				<th style="width: 50px; border:none;">Pr�vues</th>
				<th style="width: 50px; border:none;">Effectu�es</th>
				<th style="width: 150px; border:none;">Unit� adm.</th>
		  </tr>
		  {{foreach from=$prises_by_hour key=hour item=prises_by_type  name="foreach_hour"}}
			  {{assign var=_date_time value="$date $hour:00:00"}}
	      {{foreach from=$prises_by_type key=line_class item=prises name="foreach_unite"}}
					{{if $line_class == "CPrescriptionLineMix"}}
						{{foreach from=$prises key=prescription_line_mix_id item=lines}}
	          {{assign var=prescription_line_mix value=$list_lines.$line_class.$prescription_line_mix_id}}   
							{{if $prescription_line_mix->_active || $show_inactive}}
		            <tr>
								  <td>{{$hour}}h</td>
								 	<td colspan="5"><strong>{{$prescription_line_mix->_view}}</strong> {{if $prescription_line_mix->conditionnel}} - <strong>Conditionnel</strong>{{/if}}
									{{if $prescription_line_mix->commentaire}}
	                  <br />{{$prescription_line_mix->commentaire}}
	                {{/if}}
									 </td>
								</tr>
								{{if !$prescription_line_mix->signature_prat && $conf.dPprescription.CPrescription.show_unsigned_med_msg}}
									<tr>
									  <td></td>
			              <td class="text">
			              	<ul>
			              	{{foreach from=$lines key=perf_line_id item=_perf}}
			                  {{assign var=perf_line value=$list_lines.CPrescriptionLineMixItem.$perf_line_id}}
			                  <li>{{$perf_line->_ucd_view}}</li>
			                 {{/foreach}}
											 </ul>
			              </td>
										<td colspan="3">
											<div class="small-warning">
												Ligne non sign�e
											</div>
										</td>
										<td></td>
									</tr>
								{{else}}
		            {{foreach from=$lines key=perf_line_id item=_perf}}
		              {{assign var=perf_line value=$list_lines.CPrescriptionLineMixItem.$perf_line_id}}
							    <tr>
							    	<td></td>
							      <td class="text">
		                  <em>{{$perf_line->_ucd_view}}</em>
		                  {{if array_key_exists('prevu', $_perf) && array_key_exists('administre', $_perf) && $_perf.prevu == $_perf.administre}}
		                    <img src="images/icons/tick.png" alt="Administrations effectu�es" title="Administrations effectu�es" />
		                  {{/if}}
		                </td>
		                <td style="text-align: center;">
		                  {{if array_key_exists('prevu', $_perf)}}
		                    {{$_perf.prevu}}
		                  {{/if}}
		                </td>
		                <td style="text-align: center;">
		                  {{if array_key_exists('administre', $_perf)}}
		                  {{$_perf.administre}}
		                  {{/if}}
		                </td>
		                <td style="text-align: center;">
		                  {{if $perf_line->_ref_produit_prescription->_id}}
		                    {{$perf_line->_ref_produit_prescription->unite_prise}}
		                  {{else}}
		                    {{$perf_line->_unite_administration}}
		                  {{/if}}
		                </td>
		              </tr>
		           {{/foreach}}
						 {{/if}}
						 {{/if}}
				   {{/foreach}}
		      {{else}}
					  {{foreach from=$prises key=line_id item=quantite}}
	           {{assign var=line value=$list_lines.$line_class.$line_id}} 
						 {{if $line->_active || $show_inactive}}       
		            <tr>
		            	<td>{{$hour}}h</td>
		              <td style="width: 250px;" class="text">{{$line->_view}} {{if $line->conditionnel}} - <strong>Conditionnel</strong>{{/if}}
									{{if $line->commentaire}}
									  <br />{{$line->commentaire}}
									{{/if}}
		              {{if array_key_exists('prevu', $quantite) && array_key_exists('administre', $quantite) && $quantite.prevu == $quantite.administre}}
		                <img src="images/icons/tick.png" alt="Administrations effectu�es" title="Administrations effectu�es" />
		              {{/if}}
		              </td> 
									
									{{if !$line->signee && $line->_class_name == "CPrescriptionLineMedicament" && $conf.dPprescription.CPrescription.show_unsigned_med_msg}}
									<td colspan="3">
									  <div class="small-warning">
									  	Ligne non sign�e
									  </div>
										</td>
									{{else}}
		              <td style="width: 50px; text-align: center;">{{if array_key_exists('prevu', $quantite)}}{{$quantite.prevu}}{{else}} -{{/if}}</td>
		              <td style="width: 50px; text-align: center;">{{if array_key_exists('administre', $quantite)}}{{$quantite.administre}}{{else}}-{{/if}}</td>
		              <td style="width: 150px; text-align: center;" class="text">
		                {{if $line_class=="CPrescriptionLineMedicament"}}
		                  {{if $line->_ref_produit_prescription->_id}}
		                    {{$line->_ref_produit_prescription->unite_prise}}
		                  {{else}}
		                    {{$line->_ref_produit->libelle_unite_presentation}}
		                  {{/if}}
		                {{else}}
		                  {{$line->_unite_prise}}
		                {{/if}}
		            </td>
								{{/if}}
		          </tr>
						{{/if}}
	          {{/foreach}}
					{{/if}}
	      {{/foreach}}  
		  {{/foreach}}
		{{/foreach}}
		
		{{if $offline && $by_patient && $smarty.foreach.foreach_lines.last}}
		  <tr>
		  	<th colspan="6">
		  		Fin du dossier pour le patient {{$patient->_view}}
				</th>
		  </tr>
		{{/if}}
	  {{/foreach}}
	{{/foreach}}
	</table>
{{/foreach}}

{{if !$trans_and_obs|@count && !$lines_by_patient|@count && $token_cat}}
  <h2>Aucun r�sultat</h2>
{{/if}}
<table>
	<tr>
		<td>