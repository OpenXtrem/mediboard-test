<table class="tbl">
  <tr>
    <th colspan="4" class="title">Historique {{if $type=="substitutions"}} - Substitutions{{/if}}</th>
  </tr>
  <tr>
    <th>Ligne</th>
    {{if $type == "historique"}}
    <th>Signature Prat</th>
    {{/if}}
    <th>Posologies</th>
    {{if $type == "substitutions"}}
    <th>Cr�� par</th>
    <th>Produit substitu�</th>
    {{/if}}
  </tr>
  {{foreach from=$hist key=hist_line_id item=_hist_lines}}  
  {{assign var=line value=$lines.$hist_line_id}}
  <tr>
    <!-- Affichage du libelle du medicament -->
    <th colspan="4">{{$line->_view}}
    {{if $line->_traitement}}(Traitement Personnel){{/if}}
    </th>
  </tr>
  {{foreach from=$_hist_lines item=_line name="foreach_line"}}
  <tr>
    <td>Ligne pr�vue initialement du {{$_line->debut|date_format:"%d/%m/%Y"}} au {{$_line->_fin|date_format:"%d/%m/%Y"}}.
    {{if $_line->date_arret}}
    <br />
    Arr�t le {{$_line->date_arret|date_format:"%d/%m/%Y"}}
    {{/if}}</td>
    {{if $type ==  "historique"}}
	    <td>
	    {{if $_line->signee}}
	    Oui
	    {{else}}
	    Non
	    {{/if}}
	    </td>
    {{/if}}
    <td>
    {{foreach from=$_line->_ref_prises item=prise name=foreach_prise}}
	    {{if $prise->quantite}}
	        {{$prise->_view}}
	      {{if !$smarty.foreach.foreach_prise.last}},{{/if}} 
	    {{/if}}
	  {{/foreach}}
    </td>
    {{if $type == "substitutions"}}
    <td>{{$_line->_ref_creator->_view}}</td>
    
    <td>
    {{if !$smarty.foreach.foreach_line.last}}
    {{$_line->_ref_produit->libelle}}
    {{else}}
    Produit actuel
    {{/if}}
    
    </td>
    {{/if}}
  </tr>
  {{/foreach}}
  {{/foreach}}
</table>