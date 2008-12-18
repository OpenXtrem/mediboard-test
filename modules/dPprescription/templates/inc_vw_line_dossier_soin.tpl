{{assign var=line_id value=$line->_id}}
{{assign var=line_class value=$line->_class_name}}
{{assign var=transmissions_line value=$line->_transmissions}}
{{assign var=administrations_line value=$line->_administrations}}

{{if $line->_class_name == "CPrescriptionLineMedicament"}}
  {{assign var=nb_lines_chap value=$prescription->_nb_produit_by_chap.$type}}
{{else}}
  {{assign var=nb_lines_chap value=$prescription->_nb_produit_by_chap.$name_chap}}
{{/if}}

<tr id="line_{{$line_class}}_{{$line_id}}">
  {{if $smarty.foreach.$first_foreach.first && $smarty.foreach.$last_foreach.first}}
    {{if $line_class == "CPrescriptionLineMedicament"}}
      <!-- Cas d'une ligne de medicament -->
      <th class="text" rowspan="{{$prescription->_nb_produit_by_cat.$type.$_key_cat_ATC}}">
        {{$line->_ref_produit->_ref_ATC_2_libelle}}
      </th>
    {{else}}
        <!-- Cas d'une ligne d'element, possibilit� de rajouter une transmission � la categorie -->
        {{assign var=categorie_id value=$categorie->_id}}
        <th class="text {{if @$transmissions.CCategoryPrescription.$name_cat|@count}}transmission{{else}}transmission_possible{{/if}}" 
            rowspan="{{$prescription->_nb_produit_by_cat.$name_cat}}" 
            onclick="addCibleTransmission('CCategoryPrescription','{{$name_cat}}','{{tr}}CCategoryPrescription.chapitre.{{$name_chap}}{{/tr}} - {{$categorie->nom}}');">
          <div class="tooltip-trigger" onmouseover="ObjectTooltip.create(this, {mode: 'dom',  params: {element: 'tooltip-content-{{$name_cat}}'} })">
            <a href="#">{{$categorie->nom}}</a>
          </div>
          <div id="tooltip-content-{{$name_cat}}" style="display: none; color: black; text-align: left">
       		{{if @is_array($transmissions.CCategoryPrescription.$name_cat)}}
  		      <ul>
  			  {{foreach from=$transmissions.CCategoryPrescription.$name_cat item=_trans}}
  			    <li>{{$_trans->_view}} le {{$_trans->date|date_format:$dPconfig.datetime}}:<br /> {{$_trans->text}}</li>
  			  {{/foreach}}
  		      </ul>
  			{{else}}
  			  Aucune transmission
  			{{/if}}
		  </div>
	    </th>
    {{/if}}
  {{/if}}
  {{if $smarty.foreach.$last_foreach.first}}
    <td class="text" rowspan="{{$nb_line}}" style="text-align: center;">
    {{if !$line->conditionnel}}
     -
    {{else}}
      <form action="?" method="post" name="activeCondition-{{$line_id}}-{{$line_class}}">
        <input type="hidden" name="m" value="dPprescription" />
        <input type="hidden" name="dosql" value="{{$dosql}}" />
        <input type="hidden" name="{{$line->_spec->key}}" value="{{$line->_id}}" />
        <input type="hidden" name="del" value="0" />
        
        {{if !$line->condition_active}}
	      <!-- Activation -->
	      <input type="hidden" name="condition_active" value="1" />
	      <button class="tick" type="button" onclick="submitFormAjax(this.form, 'systemMsg', { onComplete: function(){ refreshDossierSoin(); } });">
	        Activer
	      </button>
	      {{else}}
 				<!-- Activation -->
	      <input type="hidden" name="condition_active" value="0" />
	      <button class="cancel" type="button" onclick="submitFormAjax(this.form, 'systemMsg', { onComplete: function(){ refreshDossierSoin(); } });">
	        D�sactiver
	      </button>
	       {{/if}}
       </form>
		{{/if}}
    </td>
    <td class="text" rowspan="{{$nb_line}}">
	  <div onclick='addCibleTransmission("{{$line_class}}","{{$line->_id}}","{{$line->_view}}");' 
	       class="{{if @$transmissions.$line_class.$line_id|@count}}transmission{{else}}transmission_possible{{/if}}">
	    <a href="#" onmouseover="ObjectTooltip.create(this, { params: { object_class: '{{$line_class}}', object_id: {{$line->_id}} } })">
	      {{if $line_class == "CPrescriptionLineMedicament"}}
	        {{$line->_ucd_view}}
	        {{if $line->_traitement}} (Traitement perso){{/if}}
	        {{if $line->commentaire}}<br /> ({{$line->commentaire}}){{/if}}
	      {{else}}
	        {{$line->_view}}
	      {{/if}} 
	    </a>
	  </div>
	  <small>
	  {{if $line->_class_name == "CPrescriptionLineMedicament"}}
	    {{$line->voie}}
	  {{/if}}
    <br />
    {{if $line->_class_name == "CPrescriptionLineMedicament"}}
      ({{$line->_unite_administration}})<br />
    {{/if}}
    </small>
	  {{if $line->_class_name == "CPrescriptionLineMedicament" && $line->_ref_substitution_lines|@count}}
    <form action="?" method="post" name="changeLine-{{$line_id}}">
      <input type="hidden" name="m" value="dPprescription" />
      <input type="hidden" name="dosql" value="do_substitution_line_aed" />
      <select name="prescription_line_medicament_id" style="width: 75px;" onchange="submitFormAjax(this.form, 'systemMsg', { onComplete: function() { refreshDossierSoin(); } } )">
        <option value="">Conserver</option>
      {{foreach from=$line->_ref_substitution_lines item=_line_subst}}
        <option value="{{$_line_subst->_id}}">{{$_line_subst->_view}}
        {{if !$_line_subst->substitute_for}}(originale){{/if}}</option>
      {{/foreach}}
      </select>
    </form>
    {{/if}}
	</td>
  {{/if}}
  
  
  <!-- Affichage des posologies de la ligne -->
  <td class="text">
    <small>
    {{if @$line->_prises_for_plan.$unite_prise}}
      {{if is_numeric($unite_prise)}}
        <!-- Cas des posologies de type "tous_les", "fois par" ($unite_prise == $prise->_id) -->
        <div style="white-space: nowrap;">
	        {{assign var=prise value=$line->_prises_for_plan.$unite_prise}}
	        {{$prise->_short_view}}
        </div>
      {{else}}
        <!-- Cas des posologies sous forme de moments -->
        {{foreach from=$line->_prises_for_plan.$unite_prise item=_prise}}
          <div style="white-space: nowrap;">
            {{$_prise->_short_view}}
					</div>
        {{/foreach}}
      {{/if}}
    {{/if}}
    </small>
  </td>
  
  {{if $smarty.foreach.$global_foreach.first && $smarty.foreach.$first_foreach.first && $smarty.foreach.$last_foreach.first}}
  <th id="before" style="cursor: pointer" onclick="showBefore();" rowspan="{{$nb_lines_chap}}" onmouseout="clearTimeout(timeOutBefore);">
   <img src="images/icons/a_left.png" title="" alt="" />
  </th>
  {{/if}}
  
  <!-- Affichage des heures de prises des medicaments -->			 
  {{foreach from=$tabHours key=_view_date item=_hours_by_moment}}
    {{foreach from=$_hours_by_moment key=moment_journee item=_dates}}
      {{foreach from=$_dates key=_date item=_hours}}
        {{foreach from=$_hours key=_heure_reelle item=_hour}}

		  {{assign var=list_administrations value=""}}
		  {{if @$line->_administrations.$unite_prise.$_date.$_hour.list}}
		    {{assign var=list_administrations value=$line->_administrations.$unite_prise.$_date.$_hour.list}}
		  {{/if}}
		  {{assign var=planification_id value=""}}
		  {{if @$line->_administrations.$unite_prise.$_date.$_hour.planification_id}}
		    {{assign var=planification_id value=$line->_administrations.$unite_prise.$_date.$_hour.planification_id}}
		  {{/if}}
		  
		  {{assign var=_date_hour value="$_date $_heure_reelle"}}						    
		
		  <!-- S'il existe des prises prevues pour la date $_date -->
	    {{if @is_array($line->_quantity_by_date.$unite_prise.$_date) || @$line->_administrations.$unite_prise.$_date.$_hour.quantite_planifiee}}
				{{assign var=prise_line value=$line->_quantity_by_date.$unite_prise.$_date}}
				
				<!-- Quantites prevues -->
			    {{assign var=quantite value="-"}}
			    {{assign var=quantite_depart value="-"}}
			    {{assign var=heure_reelle value=""}}
			    {{if (($line->_debut_reel <= $_date_hour && $line->_fin_reelle > $_date_hour) || (!$line->_fin_reelle && $line_class == "CPrescriptionLineMedicament")) 
			         && (@array_key_exists($_hour, $prise_line.quantites) || @$line->_administrations.$unite_prise.$_date.$_hour.quantite_planifiee)}}
			          
			      {{if @$line->_administrations.$unite_prise.$_date.$_hour.quantite_planifiee}}
			        {{assign var=quantite value=$line->_administrations.$unite_prise.$_date.$_hour.quantite_planifiee}}
			      {{else}}
					    {{assign var=quantite value=$prise_line.quantites.$_hour.total}}
					    {{if @$prise_line.quantites.$_hour.0.heure_reelle}}
					    {{assign var=heure_reelle value=$prise_line.quantites.$_hour.0.heure_reelle}}
					    {{/if}}
				    {{/if}}
				  {{/if}}
				  
				  {{assign var=_quantite value=$quantite}}
				  {{if !$heure_reelle}}
				    {{assign var=heure_reelle value=$_hour}}
				  {{/if}}
				  
	      <td id="drop_{{$line_id}}_{{$line_class}}_{{$unite_prise}}_{{$_date}}_{{$_hour}}" 
	      		class="{{$line_id}}_{{$line_class}} {{$_view_date}}-{{$moment_journee}} {{if $mode_dossier == 'planification' && ($quantite == '0' || $quantite == '-')}}canDrop{{/if}}" 
	      		style='text-align: center; {{if array_key_exists("$_date $_hour:00:00", $operations)}}border-right: 3px solid black;{{/if}}
	      		{{if $mode_dossier == "planification"}}background-color: #CAFFBA;{{/if}}'>
		   
					
				  				  			   
				  <div {{if $mode_dossier == "administration"}}onmouseover='ObjectTooltip.create(this, {mode: "dom",  params: {element: "tooltip-content-{{$line_id}}-{{$unite_prise}}-{{$_date}}-{{$_hour}}"} })'{{/if}}
				       id="drag_{{$line_id}}_{{$unite_prise}}_{{$_date}}_{{$heure_reelle}}_{{$_quantite}}_{{$planification_id}}"
				       {{if $mode_dossier == 'planification'}}onmousedown="addDroppablesDiv(this);"{{/if}}
				       {{if ($line->_fin_reelle && $line->_fin_reelle <= $_date_hour) || $line->_debut_reel > $_date_hour || !$line->_active}}
				      style="background-color: #aaa"
				       
				       
				      {{if $dPconfig.dPprescription.CAdministration.hors_plage}}
				        onclick='toggleSelectForAdministration(this, {{$line_id}}, "{{$quantite}}", "{{$unite_prise}}", "{{$line_class}}","{{$_date}}","{{$_hour}}","{{$list_administrations}}","{{$planification_id}}");'
			          ondblclick='addAdministration({{$line_id}}, "{{$quantite}}", "{{$unite_prise}}", "{{$line_class}}","{{$_date}}","{{$_hour}}","{{$list_administrations}}","{{$planification_id}}");'
				      {{/if}}
				    {{else}}
				      onclick='toggleSelectForAdministration(this, {{$line_id}}, "{{$quantite}}", "{{$unite_prise}}", "{{$line_class}}","{{$_date}}","{{$_hour}}","{{$list_administrations}}","{{$planification_id}}");'
			        ondblclick='addAdministration({{$line_id}}, "{{$quantite}}", "{{$unite_prise}}", "{{$line_class}}","{{$_date}}","{{$_hour}}","{{$list_administrations}}","{{$planification_id}}");'
				    {{/if}}
				    class="{{if $quantite && $quantite!="-" && $mode_dossier == "planification" && @$prise_line.quantites.$_hour|@count < 4}}
				      draggable
				    {{/if}}  
				      tooltip-trigger administration
			      {{if $quantite > 0}}
					    {{if @array_key_exists($_hour, $line->_administrations.$unite_prise.$_date)
					         && @$line->_administrations.$unite_prise.$_date.$_hour.quantite != ''}}
						     {{if @$line->_administrations.$unite_prise.$_date.$_hour.quantite == $quantite}} administre
						     {{elseif @$line->_administrations.$unite_prise.$_date.$_hour.quantite == 0}} administration_annulee
						     {{else}} administration_partielle
						     {{/if}}
					     {{else}}
					       {{if $_date_hour < $now}} non_administre
						     {{else}} a_administrer
						     {{/if}}
					     {{/if}}
				     {{/if}}
					 {{if @$line->_transmissions.$unite_prise.$_date.$_hour.nb}}transmission{{/if}}
					 {{if @$line->_administrations.$unite_prise.$_date.$_hour.quantite_planifiee}}
					 planification{{/if}}">
					 
		       {{if $quantite!="-" || @array_key_exists($_hour, $line->_administrations.$unite_prise.$_date)}}
						 {{if !$quantite}}
						   {{assign var=quantite value="0"}}
						 {{/if}}
						 {{if @$line->_administrations.$unite_prise.$_date.$_hour.quantite_planifiee}}
						    {{if @$line->_administrations.$unite_prise.$_date.$_hour.quantite}}
						      {{$line->_administrations.$unite_prise.$_date.$_hour.quantite}}
						    {{else}}
						      0
						    {{/if}}
						    /{{$line->_administrations.$unite_prise.$_date.$_hour.quantite_planifiee}}
						 {{else}}
				       {{if @$line->_administrations.$unite_prise.$_date.$_hour.quantite}}
					       {{$line->_administrations.$unite_prise.$_date.$_hour.quantite}}/{{$quantite}}
				       {{elseif $line->_active}}
					       {{if $quantite}}0/{{$quantite}}{{/if}}
					     {{/if}}
						 {{/if}}
				   {{/if}}
				   
				   
					</div>
			    <script type="text/javascript">
			      // $prise_line.quantites.$_hour|@count < 4 => pour empecher de deplacer une case ou il y a plusieurs prises
            {{if $quantite && $mode_dossier == "planification" && @$prise_line.quantites.$_hour|@count < 4}}
				      drag = new Draggable("drag_{{$line_id}}_{{$unite_prise}}_{{$_date}}_{{$heure_reelle}}_{{$_quantite}}_{{$planification_id}}", oDragOptions);
				    {{/if}}
				  </script>
          
          {{if $mode_dossier == "administration"}}
			    <div id="tooltip-content-{{$line_id}}-{{$unite_prise}}-{{$_date}}-{{$_hour}}" style="display: none; text-align: left">
				   {{if @is_array($line->_administrations.$unite_prise.$_date.$_hour.administrations)}}
				     <ul>
						   {{foreach from=$line->_administrations.$unite_prise.$_date.$_hour.administrations item=_log_administration}}
						     {{assign var=administration_id value=$_log_administration->_ref_object->_id}}
						     {{if $line_class == "CPrescriptionLineMedicament"}}
							     <li>{{$_log_administration->_ref_object->quantite}} {{$_log_administration->_ref_object->_ref_object->_ref_produit->libelle_unite_presentation}} administr� par {{$_log_administration->_ref_user->_view}} le {{$_log_administration->_ref_object->dateTime|date_format:$dPconfig.datetime}}</li>		 
							   {{else}}
								   <li>{{$_log_administration->_ref_object->quantite}} {{tr}}CCategoryPrescription.chapitre.{{$name_chap}}{{/tr}} effectu� par {{$_log_administration->_ref_user->_view}} le {{$_log_administration->_ref_object->dateTime|date_format:$dPconfig.datetime}}</li>		         				        
							   {{/if}}        
							   <ul>
							     {{foreach from=$line->_transmissions.$unite_prise.$_date.$_hour.list.$administration_id item=_transmission}}
								     <li>{{$_transmission->_view}} le {{$_transmission->date|date_format:$dPconfig.datetime}}:<br /> {{$_transmission->text}}</li>
							     {{/foreach}}
							   </ul>
						   {{/foreach}}
					   </ul>
			     {{else}}
				     {{if $line_class == "CPrescriptionLineMedicament"}}
				       Aucune administration
				     {{else}}
				       Pas de {{tr}}CCategoryPrescription.chapitre.{{$name_chap}}{{/tr}}
				     {{/if}}
			   {{/if}}
	       </div>
	       {{/if}}
		   </td>
	   {{else}}
	      <td class="{{$_view_date}}-{{$moment_journee}} {{if $mode_dossier == 'planification'}}canDrop{{/if}}"
	          id="drop_{{$line_id}}_{{$line_class}}_{{$unite_prise}}_{{$_date}}_{{$_hour}}"
	          style='text-align: center; {{if array_key_exists("$_date $_hour:00:00", $operations)}}border-right: 3px solid black;{{/if}}
	          {{if $mode_dossier == "planification"}}background-color: #CAFFBA;{{/if}}'>
		     <div class="tooltip-trigger administration  {{if @$line->_transmissions.$unite_prise.$_date.$_hour.nb}}transmission{{/if}}"
		          {{if $mode_dossier == "administration"}}onmouseover='ObjectTooltip.create(this, {mode: "dom",  params: {element: "tooltip-content-{{$line_id}}-{{$unite_prise}}-{{$_date}}-{{$_hour}}"} })'{{/if}}
		            {{if ($line->_fin_reelle && $line->_fin_reelle <= $_date_hour) || $line->_debut_reel > $_date_hour || !$line->_active}}
                    style="background-color: #aaa"
                  {{else}}
                    onclick='toggleSelectForAdministration(this, {{$line_id}}, "", "{{$unite_prise}}", "{{$line_class}}","{{$_date}}","{{$_hour}}","{{$list_administrations}}");'
                    ondblclick='addAdministration({{$line_id}}, "", "{{$unite_prise}}", "{{$line_class}}","{{$_date}}","{{$_hour}}","{{$list_administrations}}");'
                  {{/if}}
                  >
    	          {{if @$line->_administrations.$unite_prise.$_date.$_hour.quantite}}
	                {{$line->_administrations.$unite_prise.$_date.$_hour.quantite}} / -
	              {{/if}}
	           </div>
	           {{if $mode_dossier == "administration"}}
	           <div id="tooltip-content-{{$line_id}}-{{$unite_prise}}-{{$_date}}-{{$_hour}}" style="display: none; text-align: left">
		         {{if @is_array($line->_administrations.$unite_prise.$_date.$_hour.administrations)}}
			         <ul>
					       {{foreach from=$line->_administrations.$unite_prise.$_date.$_hour.administrations item=_log_administration}}
					         {{assign var=administration_id value=$_log_administration->_ref_object->_id}}
					           {{if $line_class == "CPrescriptionLineMedicament"}}
							     <li>{{$_log_administration->_ref_object->quantite}} {{$_log_administration->_ref_object->_ref_object->_ref_produit->libelle_unite_presentation}} administr� par {{$_log_administration->_ref_user->_view}} le {{$_log_administration->date|date_format:$dPconfig.datetime}}</li>		 
							   {{else}}
								 <li>{{$_log_administration->_ref_object->quantite}} {{tr}}CCategoryPrescription.chapitre.{{$name_chap}}{{/tr}} effectu� par {{$_log_administration->_ref_user->_view}} le {{$_log_administration->date|date_format:$dPconfig.datetime}}</li>		         				        
							   {{/if}}
						       <ul>
						        {{foreach from=$line->_transmissions.$unite_prise.$_date.$_hour.list.$administration_id item=_transmission}}
						          <li>{{$_transmission->_view}} le {{$_transmission->date|date_format:$dPconfig.datetime}}:<br /> {{$_transmission->text}}</li>
						        {{/foreach}}
						        </ul>
						     {{/foreach}}
				       </ul>
			       {{else}}
			         {{if $line_class == "CPrescriptionLineMedicament"}}
			           Aucune administration
			         {{else}}
			           Pas de {{tr}}CCategoryPrescription.chapitre.{{$name_chap}}{{/tr}}
			         {{/if}}
			       {{/if}}
			    </div>
			    {{/if}}
		     </td>
		   {{/if}}
	     {{/foreach}}
     {{/foreach}}		   
   {{/foreach}}
 {{/foreach}}
 
 {{if $smarty.foreach.$global_foreach.first &&  $smarty.foreach.$first_foreach.first  && $smarty.foreach.$last_foreach.first}}
   <th id="after" style="cursor: pointer" onclick="showAfter();" rowspan="{{$nb_lines_chap}}" onmouseout="clearTimeout(timeOutAfter);">
     <img src="images/icons/a_right.png" title="" alt="" />
   </th>
 {{/if}}
 
 <!-- Signature du praticien -->
 <td style="text-align: center">
   {{if $line->signee}}
   <img src="images/icons/tick.png" alt="Sign�e par le praticien" title="Sign�e par le praticien" />
   {{else}}
   <img src="images/icons/cross.png" alt="Non sign�e par le praticien" title="Non sign�e par le praticien" />
   {{/if}}
 </td>
 <!-- Signature du pharmacien -->
 <td style="text-align: center">
	  {{if $line_class == "CPrescriptionLineMedicament"}}
	    {{if $line->valide_pharma}}
	    <img src="images/icons/tick.png" alt="Sign�e par le pharmacien" title="Sign�e par le pharmacien" />
	    {{else}}
	    <img src="images/icons/cross.png" alt="Non sign�e par le pharmacien" title="Non sign�e par le pharmacien" />
	    {{/if}}
	  {{else}}
	    - 
	  {{/if}}
  </td>
</tr>