<table class="tbl" id="perfusion-{{$_perfusion->_id}}">
<tbody class="hoverable {{if $_perfusion->_fin < $now && !$_perfusion->_protocole}}line_stopped{{/if}}">
{{assign var=perfusion_id value=$_perfusion->_id}}
  <tr>
    <th colspan="8" id="th-perf-{{$_perfusion->_id}}" class="text element {{if $_perfusion->_fin < $now && !$_perfusion->_protocole}}arretee{{/if}}">
      
			{{if $_perfusion->_ref_parent_line->_id}}
      <div style="float: left">
        {{assign var=parent_perf value=$_perfusion->_ref_parent_line}}
        <img src="images/icons/history.gif" alt="Ligne poss�dant un historique" title="Ligne poss�dant un historique" 
             class="tooltip-trigger" 
             onmouseover="ObjectTooltip.createEx(this, '{{$parent_perf->_guid}}')"/>
      </div>
      {{/if}}
      
      <div class="mediuser" style="float: right; border-color: #{{$_perfusion->_ref_praticien->_ref_function->color}};">	  
        <!-- Siganture du praticien -->
        {{if $_perfusion->_can_vw_signature_praticien}}
          {{$_perfusion->_ref_praticien->_view}}
					{{if $_perfusion->signature_prat}}
					   <img src="images/icons/tick.png" alt="Ligne sign�e par le praticien" title="Ligne sign�e par le praticien" /> 
					{{else}}
					   <img src="images/icons/cross.png" alt="Ligne non sign�e par le praticien"title="Ligne non sign�e par le praticien" /> 
					{{/if}}
          {{if $prescription_reelle->type != "externe"}}
						{{if $_perfusion->signature_pharma}}
					    <img src="images/icons/signature_pharma.png" alt="Sign�e par le pharmacien" title="Sign�e par le pharmacien" />
					  {{else}}
						  <img src="images/icons/signature_pharma_barre.png" alt="Non sign�e par le pharmacien" title="Non sign�e par le pharmacien" />
				  	{{/if}}
			  	{{/if}}
        {{/if}} 
       <button class="edit notext" onclick="Prescription.reload('{{$prescription_reelle->_id}}', '', 'medicament', '', '{{$mode_pharma}}', null, true, false,'{{$_perfusion->_guid}}');"></button>
      </div>
        
      <strong>
				Perfusion :
				{{foreach from=$_perfusion->_ref_lines item=_line name=perf_line}}
				 {{$_line->_ucd_view}}{{if !$smarty.foreach.perf_line.last}},{{/if}}
				{{/foreach}}         
      </strong>
    </th>
  </tr>
  <tr>
    <td>
      <strong>{{mb_label object=$_perfusion field="type"}}</strong>:
      {{if $_perfusion->type}}
        {{mb_value object=$_perfusion field="type"}}
      {{else}}
        -
      {{/if}}
    </td>
    <td>
      <strong>{{mb_label object=$_perfusion field="vitesse"}}</strong>:
        {{if $_perfusion->vitesse}}
      {{mb_value object=$_perfusion field="vitesse"}} ml/h
      {{else}}
       -
      {{/if}}
    </td>
    <td>
      <strong>{{mb_value object=$_perfusion field="voie"}}</strong>
    </td>
    <td>
      <strong>{{mb_label object=$_perfusion field="date_debut"}}</strong>:
      {{if $_perfusion->date_debut}}
        {{mb_value object=$_perfusion field=date_debut}}
      {{/if}}
      {{if $_perfusion->time_debut}}
	      � 
		    {{mb_value object=$_perfusion field=time_debut}}
	    {{/if}}
	  </td>
    <td>
		  <strong>{{mb_label object=$_perfusion field=duree}}</strong>:
			{{mb_value object=$_perfusion field=duree}}heures
	  </td>	    
  </tr>
  {{if $_perfusion->type == "PCA"}}
    <tr>
      <td>
				<strong>{{mb_label object=$_perfusion field=mode_bolus}}</strong>:
				{{mb_value object=$_perfusion field=mode_bolus}}
      </td>
      {{if $_perfusion->mode_bolus != "sans_bolus"}}
      <td>
				<strong>{{mb_label object=$_perfusion field=dose_bolus}}</strong>:
				{{mb_value object=$_perfusion field=dose_bolus}} mg
      </td>
      <td>
				<strong>{{mb_label object=$_perfusion field=periode_interdite}}</strong>:
				{{mb_value object=$_perfusion field=periode_interdite}} min
      </td>
      {{else}}
      <td colspan="2" />
      {{/if}}
      <td />
      <td />
    </tr>
  {{/if}}
  <tr>
    <td colspan="8">
      <table class="form">
	      {{foreach from=$_perfusion->_ref_lines item=line}}
	        <tr>
	          <td style="border: none; width:30%" class="text">
	            {{include file="../../dPprescription/templates/line/inc_vw_alertes.tpl"}}
	            {{if $line->_can_vw_livret_therapeutique}}
					      <img src="images/icons/livret_therapeutique_barre.gif" alt="Produit non pr�sent dans le livret Th�rapeutique" title="Produit non pr�sent dans le livret Th�rapeutique" />
					    {{/if}}
					    {{if $line->_can_vw_generique}}
					      <img src="images/icons/generiques.gif" alt="Produit g�n�rique" title="Produit g�n�rique" />
					    {{/if}}
              {{if $line->_ref_produit->_supprime}}
                <img src="images/icons/medicament_barre.gif" alt="Produit supprim�" title="Produit supprim�" />
              {{/if}}
	            <strong>{{$line->_ucd_view}}</strong>
	          </td>
	          <td style="border: none; width:20%">
	            <strong>{{mb_label object=$line field=quantite}}</strong>:
	            {{mb_value object=$line field=quantite size=4}}
	            {{mb_value object=$line field=unite size=4}}
	          </td>
	          <td class="date" style="border: none; width:20%">
	            <strong>{{mb_label object=$line field=date_debut}}</strong>:
	            {{mb_value object=$line field=date_debut}}
	            {{if $line->time_debut}}
	              � {{mb_value object=$line field=time_debut}} 
	            {{/if}}
	          </td>
	        </tr>
	      {{foreachelse}}
	      	<div class="small-info">
		        Aucun produit n'est associ� � la perfusion
		      </div>
	      {{/foreach}}
      </table>
    </td>
  </tr>
</tbody>
</table>