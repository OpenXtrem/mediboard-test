<script type="text/javascript">

// Calcul de la date de debut lors de la modification de la fin
syncDate = function(oForm, curr_line_id, fieldName, type, object_class, cat_id) {
 
  // D�claration des div des dates
  oDivDebut = $('editDates-'+type+'-'+curr_line_id+'_debut_da');
  oDivFin = $('editDates-'+type+'-'+curr_line_id+'__fin_da');

  // Recuperation de la date actuelle
  var todayDate = new Date();
  var dToday = todayDate.toDATE();
  
  // Recuperation des dates des formulaires
  var sDebut = oForm.debut.value;
  var sFin = oForm._fin.value;
  var nDuree = parseInt(oForm.duree.value, 10);
  var sType = oForm.unite_duree.value;
  
  // Transformation des dates
  if(sDebut){
    var dDebut = Date.fromDATE(sDebut);  
  }
  if(sFin){
    var dFin = Date.fromDATE(sFin);  
  }
  
  // Modification de la fin en fonction du debut
  if(fieldName != "_fin" && sDebut && sType && nDuree) {
    dFin = dDebut;
    if(sType == "jour")      { dFin.addDays(nDuree-1);     }
    if(sType == "semaine")   { dFin.addDays(nDuree*7-1);   }
    if(sType == "quinzaine") { dFin.addDays(nDuree*14-1);  }
    if(sType == "mois")      { dFin.addDays(nDuree*30-1);  }
    if(sType == "trimestre") { dFin.addDays(nDuree*90-1);  }
    if(sType == "semestre")  { dFin.addDays(nDuree*180-1); }
    if(sType == "an")        { dFin.addDays(nDuree*365-1); }

  	oForm._fin.value = dFin.toDATE();
  	oDivFin.innerHTML = dFin.toLocaleDate();
  	if(curr_line_id){
  	  testPharma(curr_line_id);
  	}
  }
  
  //-- Lors de la modification de la fin --
  // Si debut, on modifie la duree
  if(sDebut && sFin && fieldName == "_fin"){
    var nDuree = parseInt((dFin - dDebut)/86400000,10);
    oForm.duree.value = nDuree+1;
    oForm.unite_duree.value = "jour";
    if(curr_line_id){
  	  testPharma(curr_line_id);
  	}
  }
  
  // Si !debut et duree, on modifie le debut
  if(!sDebut && nDuree && sType && fieldName == "_fin"){
    dDebut = dFin;
    if(sType == "jour")      { dDebut.addDays(-nDuree);     }
    if(sType == "semaine")   { dDebut.addDays(-nDuree*7);   }
    if(sType == "quinzaine") { dDebut.addDays(-nDuree*14);  }
    if(sType == "mois")      { dDebut.addDays(-nDuree*30);  }
    if(sType == "trimestre") { dDebut.addDays(-nDuree*90);  }
    if(sType == "semestre")  { dDebut.addDays(-nDuree*180); }
    if(sType == "an")        { dDebut.addDays(-nDuree*365); }

  	oForm.debut.value = dDebut.toDATE();
  	oDivDebut.innerHTML = dDebut.toLocaleDate();
  	
    if(curr_line_id){
  	  testPharma(curr_line_id);
  	}	
  }
  
  // Si !debut et !duree, on met le debut a aujourd'hui, et on modifie la duree
  if(!sDebut && !nDuree && fieldName == "_fin"){
    dDebut = todayDate;
    oForm.debut.value = todayDate.toDATE();
    oDivDebut.innerHTML = todayDate.toLocaleDate();
    var nDuree = parseInt((dFin - dDebut)/86400000,10);
    oForm.duree.value = nDuree;
    oForm.unite_duree.value = "jour";
    if(curr_line_id){
  	  testPharma(curr_line_id);
  	}
  }
  
  {{if $typeDate != "mode_grille"}}
	  if(object_class == 'CPrescriptionLineMedicament'){
	    var oTbody = $('line_medicament_'+curr_line_id);
	  } else {
	    var oTbody = $('line_element_'+curr_line_id);
	  }
	  
	  // Classes du tbody avant la modification
	  var classes_before = oTbody.className;
	  
	  // Ligne finie
	  var oDiv = $('th_line_'+object_class+'_'+curr_line_id);
	  if(oForm._fin.value != "" && oForm._fin.value < '{{$today}}'){
	    oDiv.addClassName("arretee");
	    oTbody.addClassName("line_stopped");
	  } else {
	    oDiv.removeClassName("arretee");
	    oTbody.removeClassName("line_stopped");
	  }
	
	  
	  // Classes du tbody apres la modification
	  var classes_after = oTbody.className;
	  
	  // Si modif, deplacement du tbody
	  if(classes_after != classes_before){
		  if(object_class == 'CPrescriptionLineMedicament'){
		    moveTbody(oTbody);
		  } else {
		    moveTbodyElt(oTbody, cat_id);
		  }
	  }
  {{/if}}  
}


syncDateSubmit = function(oForm, curr_line_id, fieldName, type, object_class, cat_id) {
  if(!checkForm(oForm)){
    return;
  }
  syncDate(oForm, curr_line_id, fieldName, type, object_class, cat_id);
  if(!curr_line_id){
    return;
  }
  submitFormAjax(oForm, 'systemMsg');
}


</script>

{{assign var=line_id value=$line->_id}}
{{assign var=_object_class value=$line->_class_name}}

{{if $line->_class_name == "CPrescriptionLineElement"}}
  {{assign var=category_id value=$category->_id}}
  {{assign var=chapitre value=$line->_ref_element_prescription->_ref_category_prescription->chapitre}}
{{else}}
  {{assign var=category_id value=""}}
  {{assign var=chapitre value=""}}
{{/if}}


{{if $typeDate != "mode_grille"}}
  {{assign var=onchange value="submitFormAjax(this.form, 'systemMsg');"}}
{{else}}
  {{assign var=onchange value=""}}
{{/if}}

{{if $prescription->object_id}}
<form name="editDates-{{$typeDate}}-{{$line->_id}}" action="?" method="post">
   <input type="hidden" name="m" value="dPprescription" />
   <input type="hidden" name="dosql" value="{{$dosql}}" />
   <input type="hidden" name="del" value="0" />
   <input type="hidden" name="{{$line->_spec->key}}" value="{{$line->_id}}" />
  
   <table>
	     {{if !$line->fin}}
	     <tr>
	       <td style="border:none">
	         {{mb_label object=$line field=debut}}
	       </td>    
	       {{if $line->_can_modify_dates || $typeDate == "mode_grille"}}
	       <td class="date" style="border:none; {{if $typeDate == 'mode_grille'}}width: 130px;{{/if}}">
	         {{if $prescription->type != "externe" && $typeDate == "mode_grille"}}
		           <select name="debut_date" onchange="$('editDates-{{$typeDate}}-{{$line->_id}}_debut_da').innerHTML = new String;
					 				                                  this.form.debut.value = '';
		           																		 if(this.value == 'other') {
							 				          					           $('date_mb_field-{{$typeDate}}-{{$line_id}}').show();
							 				          					  			 } else { 
							 				          					  			   this.form.debut.value = this.value;
							 				          				             $('date_mb_field-{{$typeDate}}-{{$line_id}}').hide();
							 				          				           }">
							 	 <option value="other">Autre date</option>
							   <optgroup label="S�jour">
							   <option value="{{$prescription->_ref_object->_entree|date_format:'%Y-%m-%d'}}">Entr�e: {{$prescription->_ref_object->_entree|date_format:"%d/%m/%Y"}}</option>
							   <option value="{{$prescription->_ref_object->_sortie|date_format:'%Y-%m-%d'}}">Sortie: {{$prescription->_ref_object->_sortie|date_format:"%d/%m/%Y"}}</option>
							   </optgroup>
							   <optgroup label="Op�ration">
							   {{foreach from=$prescription->_ref_object->_dates_operations item=_date_operation}}
							   <option value="{{$_date_operation}}">{{$_date_operation|date_format:"%d/%m/%Y"}}</option>
							   {{/foreach}}
								 </optgroup>					   
							 </select>
							 <div id="date_mb_field-{{$typeDate}}-{{$line_id}}" style="border:none;">
							   {{if $typeDate != "mode_grille" && ($line->_traitement || $chapitre == "consult" || $chapitre == "anapath" || $chapitre == "imagerie")}}
							     {{mb_field object=$line field=debut canNull=false form=editDates-$typeDate-$line_id onchange="submitFormAjax(this.form, 'systemMsg');"}}
							   {{else}}
							     {{mb_field object=$line field=debut canNull=false form=editDates-$typeDate-$line_id onchange="syncDateSubmit(this.form, '$line_id', this.name, '$typeDate','$_object_class','$category_id');"}}
				         {{/if}}
				         {{mb_field object=$line field=time_debut form=editDates-$typeDate-$line_id onchange="$onchange"}}
				        
				       </div>	       
		       {{else}}
		           {{if $typeDate != "mode_grille" && ($line->_traitement || $chapitre == "consult" || $chapitre == "anapath" || $chapitre == "imagerie")}}
				         {{mb_field object=$line field=debut form=editDates-$typeDate-$line_id onchange="submitFormAjax(this.form, 'systemMsg');"}}  
		           {{else}}
		             {{mb_field object=$line field=debut form=editDates-$typeDate-$line_id onchange="syncDateSubmit(this.form, '$line_id', this.name, '$typeDate','$_object_class','$category_id');"}}  
		           {{/if}}
		           {{mb_field object=$line field=time_debut form=editDates-$typeDate-$line_id onchange="$onchange"}}    
				   {{/if}} 
	       </td>
	       {{else}}
	       <td style="border:none">
	         {{if $line->debut}}
	           {{$line->debut|date_format:"%d/%m/%Y"}}
	         {{else}}
	          -
	         {{/if}}				   
	       </td>
	       {{/if}}

	       {{if !$line->_traitement && $chapitre != "consult" && $chapitre != "anapath" && $chapitre != "imagerie"}}
	       <td style="border:none;">
	         {{mb_label object=$line field=duree}}
	       </td>
	       <td style="border:none">
		       {{if $line->_can_modify_dates || $typeDate == "mode_grille"}}
				     {{mb_field object=$line field=duree increment=1 min=1 form=editDates-$typeDate-$line_id onchange="syncDateSubmit(this.form, '$line_id', this.name, '$typeDate','$_object_class','$category_id');" size="1" }}
				     {{mb_field object=$line style="width: 70px;" field=unite_duree onchange="syncDateSubmit(this.form, '$line_id', this.name, '$typeDate','$_object_class','$category_id');"}}
				   {{else}}
				     {{if $line->duree}}
				       {{$line->duree}}
				     {{else}}
				       -
				     {{/if}}
				     {{if $line->unite_duree}}
				       {{tr}}CPrescriptionLineMedicament.unite_duree.{{$line->unite_duree}}{{/tr}}	      
				     {{/if}}
				   {{/if}}

	       </td>
	       <td style="border:none">
	         {{mb_label object=$line field=_fin}} 
	       </td>
	       {{if $line->_can_modify_dates || $typeDate == "mode_grille"}}
	       <td class="date" style="border:none;">
	         {{mb_field object=$line field=_fin form=editDates-$typeDate-$line_id onchange="syncDateSubmit(this.form, '$line_id', this.name, '$typeDate','$_object_class','$category_id');"}}
	         {{mb_field object=$line field=time_fin form=editDates-$typeDate-$line_id onchange="syncDateSubmit(this.form, '$line_id', this.name, '$typeDate','$_object_class','$category_id');"}}
	       </td>
	       {{else}}
	       <td style="border:none">
		       {{if $line->_fin}}
		         {{$line->_fin|date_format:"%d/%m/%Y"}}
		       {{else}}
		        -
		       {{/if}}				   
	       </td>
	      </tr>
	     {{/if}}
	   
	    </tr>
	    {{/if}}
	    
	    {{/if}}
	    
	    
	    {{if $line->fin}}
	    <tr>
	      <td style="border:none">
	         Fin
	      </td>       
	     {{if $line->_can_modify_dates}}
	       <td class="date" style="border:none;">
	         {{mb_field object=$line field=fin canNull=false form=editDates-$typeDate-$line_id onchange="submitFormAjax(this.form, 'systemMsg');"}}
	         {{mb_field object=$line field=time_fin form=editDates-$typeDate-$line_id onchange="submitFormAjax(this.form, 'systemMsg');"}}
	       </td>
	       {{else}}
	       <td style="border:none">
	         {{if $line->fin}}
		         {{$line->fin|date_format:"%d/%m/%Y"}}
		       {{else}}
		        -
		       {{/if}}				   
	       </td>
	       {{/if}}
	     {{/if}}
	     
  </table>
 </form> 
  {{else}}
	  <table>
	    <tr>
	      <td>
	        <!-- Selection d'une date dans le cas des protocoles -->
	        {{include file="../../dPprescription/templates/line/inc_vw_duree_protocole_line.tpl"}}
	      </td>
	    </tr>
	  </table>
	{{/if}}
