<script type="text/javascript">
	submitAddCommentSubst = function (object_class, object_id, commentaire) {
	  var oForm = getForm("addComment-"+object_class);
	  prepareForm(oForm);
	  oForm.dosql.value = "do_prescription_line_medicament_aed";
	  oForm.prescription_line_medicament_id.value = object_id;
	  oForm.commentaire.value = commentaire;
	  submitFormAjax(oForm, 'systemMsg');
	}
</script>

{{include file="../../dPprescription/templates/js_functions.tpl"}}

{{mb_include_script module="dPmedicament" script="medicament_selector"}}
{{mb_include_script module="dPmedicament" script="equivalent_selector"}}
{{mb_include_script module="dPprescription" script="element_selector"}}
{{mb_include_script module="dPprescription" script="prescription"}}

<form name="addComment-CPrescriptionLineMedicament" method="post" action="">
  <input type="hidden" name="m" value="dPprescription" />
  <input type="hidden" name="dosql" value="" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="prescription_line_medicament_id" value="" />
  <input type="hidden" name="commentaire" value="" />
</form>

<!-- Select de moments unitaire -->
<form name="moment_unitaire">
  <select name="moment_unitaire_id" style="width: 150px; display: none;">  
     <option value="">&mdash; S�lection du moment</option>
    {{foreach from=$moments key=type_moment item=_moments}}
    <optgroup label="{{$type_moment}}">
    {{foreach from=$_moments item=moment}}
    {{if $type_moment == "Complexes"}}
      <option value="complexe-{{$moment->code_moment_id}}">{{$moment->_view}}</option>
    {{else}}
      <option value="unitaire-{{$moment->_id}}">{{$moment->_view}}</option>
    {{/if}}
    {{/foreach}}
    </optgroup>
    {{/foreach}}
  </select>
</form>
	  
<form action="?" method="post" name="addLine" onsubmit="return checkForm(this);">  
  <input type="hidden" name="m" value="dPprescription" />
  <input type="hidden" name="dosql" value="do_prescription_line_medicament_aed" />
  <input type="hidden" name="prescription_line_medicament_id" value=""/>
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="prescription_id" value="{{$prescription->_id}}"/>
  <input type="hidden" name="praticien_id" value="{{$app->user_id}}" />
  <input type="hidden" name="creator_id" value="{{$app->user_id}}" />
  <input type="hidden" name="code_cip" value=""/>
  <input type="hidden" name="substitute_for" value="{{$line->_id}}" />
  <input type="hidden" name="substitution_active" value="0" />
</form>

{{if !$mode_pack}}
<table class="tbl">
  <tr>
    <th class="title">Ajout d'une ligne de substitution</th>
  </tr>
  <tr>
    <td>
      <form action="?" method="get" name="searchProd" onsubmit="return false;">
			    <input type="text" name="produit" value="" size="12" />
			    <input type="checkbox" name="_recherche_livret" {{if $prescription->type=="sejour"}}checked="checked"{{/if}} />
			    Livret Th�rap.
			    <div style="display:none;" class="autocomplete" id="produit_auto_complete"></div>
			    <button type="button" class="search" onclick="MedSelector.init('produit');">Rechercher</button>
			    <input type="hidden" name="code_cip" onchange="addSubstitutionLine(this.value);"/>
			    <script type="text/javascript">
			      MedSelector.init = function(onglet){
			        this.sForm = "searchProd";
			        this.sView = "produit";
			        this.sCode = "code_cip";
			        this.sRechercheLivret = document.searchProd._recherche_livret.value;
			        this.sSearch = document.searchProd.produit.value;
			        this.sOnglet = onglet;
			        this.selfClose = false;
			        this.pop();
			      }
			  </script>
			  </form>
    </td>
  </tr>
  <tr>
    <th class="title">Affichage des lignes de substitutions</th>
  </tr>
</table>
{{/if}}
<table class="tbl">
  {{foreach from=$line->_ref_substitution_lines item=curr_line}}
    {{if $mode_pack}}
      {{include file="../../dPprescription/templates/inc_vw_line_pack.tpl" line=$curr_line}}
    {{else}}
      {{include file="../../dPprescription/templates/inc_vw_line_medicament.tpl" mode_pharma=0}}
    {{/if}}
  {{/foreach}}
  
</table>

<script type="text/javascript">

if(document.addLine && document.searchProd){
  // UpdateFields de l'autocomplete de medicaments
  updateFieldsMedicament = function(selected) {
    Element.cleanWhitespace(selected);
    dn = selected.childNodes;
    oFormAddLine.code_cip.value = dn[0].firstChild.nodeValue;
    submitFormAjax(document.addLine, 'systemMsg', { onComplete: function() { Prescription.viewSubstitutionLines('{{$line->_id}}') } });
    $('searchProd_produit').value = "";
  }
  
  var oFormProduit = document.searchProd;
  var oFormAddLine = document.addLine;
  
  prepareForm(oFormAddLine);
  prepareForm(oFormProduit);
  
  // Autocomplete des medicaments
  urlAuto = new Url();
  urlAuto.setModuleAction("dPmedicament", "httpreq_do_medicament_autocomplete");
  urlAuto.addParam("produit_max", 40);
  
  urlAuto.autoComplete("searchProd_produit", "produit_auto_complete", {
    minChars: 3,
    updateElement: updateFieldsMedicament,
    callback: 
      function(input, queryString){
        return (queryString + "&inLivret="+($V(oFormProduit._recherche_livret)?'1':'0')); 
      }
  } );
}

// Ajout d'une ligne de substitution
addSubstitutionLine = function(code_cip){
  var oForm = document.addLine;
  oForm.code_cip.value = code_cip;
	submitFormAjax(document.addLine, 'systemMsg', { onComplete: function() { Prescription.viewSubstitutionLines('{{$line->_id}}') } });
}

// Suppression d'une ligne de substitution
Prescription.delLine =  function(line_id) {
	var oForm = document.addLine;
	oForm.prescription_line_medicament_id.value = line_id;
	oForm.del.value = 1;
	submitFormAjax(document.addLine, 'systemMsg', { onComplete: function() { Prescription.viewSubstitutionLines('{{$line->_id}}') } });
}

</script>