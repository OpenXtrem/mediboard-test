{{* $Id$ *}}

{{*
 * @package Mediboard
 * @subpackage dPmedicament
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

{{mb_script module="dPprescription" script="prescription"}}

<script type="text/javascript">

function loadArbreATC(codeATC, dialog){
  var url = new Url;
  url.setModuleAction("dPmedicament", "httpreq_vw_arbre_ATC");
  url.addParam("codeATC", codeATC);
  url.addParam("dialog", dialog);
  url.requestUpdate("ATC");
}

function loadArbreBCB(codeBCB, dialog){
  var url = new Url;
  url.setModuleAction("dPmedicament", "httpreq_vw_arbre_BCB");
  url.addParam("codeBCB", codeBCB);
  url.addParam("dialog", dialog);
  url.requestUpdate("BCB");
}

function viewATC(){
  $('ATC').show();
  $('BCB').hide();
}

function viewBCB(){
  $('BCB').show();
  $('ATC').hide();
}

function setClose(libelle, code_cip, code_ucd, code_cis) {
  var oSelector = window.opener.MedSelector;
  oSelector.set(libelle, code_cip, code_ucd, code_cis);
  window.close();
}

function changeFormSearch(){
  var oForm = document.formSearch;
  var type_recherche = oForm.type_recherche.value;
  if(type_recherche != "nom"){
    oForm.position_text.checked = false;
    oForm.position_text.disabled = true;
  } else {
    oForm.position_text.disabled = false;
  }
}

Main.add(function () {
  changeFormSearch();
  searchTabs = new Control.Tabs('main_tab_group');
  searchTabs.setActiveTab('{{$onglet_recherche}}');
  
  // Au chargement, vue des classes ATC
  viewATC();  
});
</script>

<ul id="main_tab_group" class="control_tabs">
  <li><a href="#produits">Produits</a></li>
	{{if !$gestion_produits}}
	  <li><a href="#classes">Classes</a></li>
	  <li><a href="#composants">Composants</a></li>
	  <li><a href="#DCI">DCI</a></li>
	{{/if}}
</ul>
<hr class="control_tabs" />

<div id="produits" style="display: none;">
  Recherche par
  <form name="formSearch" action="?" method="get">
    <input type="hidden" name="search_by_cis" value="{{$search_by_cis}}" />
		 <input type="hidden" name="gestion_produits" value="{{$gestion_produits}}" />
    {{if $dialog}}
    <input type="hidden" name="dialog" value="1" />
    <input type="hidden" name="m" value="dPmedicament" />
    <input type="hidden" name="a" value="vw_idx_recherche" />
    
    {{else}}
    <input type="hidden" name="m" value="dPmedicament" />
    <input type="hidden" name="tab" value="vw_idx_recherche" />
    
    {{/if}}
    <select name="type_recherche" onchange="changeFormSearch(this.value);">
      <option value="nom" {{if $type_recherche == 'nom'}}selected = "selected"{{/if}}>Nom</option>
      <option value="cip" {{if $type_recherche == 'cip'}}selected = "selected"{{/if}}>CIP</option>
      <option value="ucd" {{if $type_recherche == 'ucd'}}selected = "selected"{{/if}}>UCD</option>
    </select>
    <br />
    <input type="text" name="produit" value="{{$produit}}"/>
    <button type="button" class="search" onclick="submit();">Rechercher</button>
		<br />
		{{if $gestion_produits}}
		<input type="hidden" name="hors_specialite" value="1" />
		{{else}}
    <input type="checkbox" name="hors_specialite" value="1" {{if $hors_specialite == 1 || $gestion_produits}}checked = "checked"{{/if}} />
    Recherche hors sp�cialit�s
		<br />
    {{/if}}
		<input type="checkbox" name="supprime" value="1" {{if $supprime == 1}}checked = "checked"{{/if}} />
    Afficher les produits supprim�s
    <br />
    <input type="checkbox" name="position_text" value="partout" {{if $param_recherche == 'partout'}}checked = "checked"{{/if}} />
    Rechercher n'importe o� dans le nom du produit
    <br />
    <input type="checkbox" name="rechercheLivret" value="1" {{if $rechercheLivret == 1}}checked = "checked"{{/if}} />
    Rechercher uniquement dans le livret th�rapeutique
  </form>
  <table class="tbl">
    <tr>
      <th class="narrow"></th>
      {{if !$search_by_cis}}
      <th class="narrow">CIP</th>
      {{/if}}
      <th class="narrow"></th>
      <th>UCD</th>
      <th>CIS</th>
      <th>Produit</th>
      <th>Laboratoire</th>
    </tr>
    {{foreach from=$produits item="produit"}}
    <tr>
      <td>
        {{if $dialog && !$produit->_supprime}}
				  {{if $gestion_produits && $search_by_cis && ($produit->code_cis || $produit->code_ucd)}}
				    {{if $produit->code_cis}}
						  <button type="button" class="add notext" onclick="setClose('{{$produit->libelle}}','','','{{$produit->code_cis}}')"></button>
						{{else}}
						  <button type="button" class="add notext" onclick="setClose('{{$produit->libelle}}','','{{$produit->code_ucd}}')"></button>
						{{/if}}
          {{else}}
					  <button type="button" class="add notext" onclick="setClose('{{$produit->libelle}}','{{$produit->code_cip}}')"></button>
          {{/if}}
        {{/if}}
      </td>
      {{if !$search_by_cis}}
      <td>{{$produit->code_cip}}</td>
      {{/if}}
      <td>
        {{if !$produit->inLivret}}
        <img src="images/icons/livret_therapeutique_barre.gif" alt="Produit non pr�sent dans le livret th�rapeutique" title="Produit non pr�sent dans le livret th�rapeutique" />
        {{/if}}
        
        {{if $produit->hospitalier}}
        <img src="./images/icons/hopital.gif" alt="Produit hospitalier" title="Produit hospitalier" />
        {{/if}}
        {{if $produit->_generique}}
        <img src="./images/icons/generiques.gif" alt="Produit g�n�rique" title="Produit g�n�rique" />
        {{/if}}
        {{if $produit->_referent}}
        <img src="./images/icons/referents.gif" alt="Produit r�f�rent" title="Produit r�f�rent" />
        {{/if}}
        {{if $produit->_supprime}}
        <img src="images/icons/medicament_barre.gif" alt="Produit supprim�" title="Produit supprim�" />
        {{/if}}
        {{if !$produit->inT2A}}
          <img src="images/icons/T2A_barre.gif" alt="Produit hors T2A" title="Produit hors T2A" />
        {{/if}}  
      </td>
      <td>{{$produit->code_ucd}}</td>
      <td>{{$produit->code_cis}}</td>
      <td class="text">
        <a href="#produit{{$produit->code_cip}}" onclick="Prescription.viewProduit('','{{$produit->code_ucd}}','{{$produit->code_cis}}')" {{if $produit->_supprime}}style="color: red"{{/if}}>
	        {{if $search_by_cis}}
	          {{$produit->libelle_abrege}} {{$produit->dosage}} {{if $produit->forme}}({{$produit->forme}}){{/if}}
	        {{else}}
	          {{$produit->libelle_long}}
	        {{/if}}
        </a>
      </td>
      <td>
        {{$produit->nom_laboratoire}}
      </td>
    </tr>
    {{/foreach}}
  </table>
</div>
  
<div id="classes" style="display: none;">
  <input type="radio" name="type_classe" value="atc" checked="checked" onchange="viewATC();" />
  Classes ATC
  <input type="radio" name="type_classe" value="bcb" onchange="viewBCB();"/>
  CLasses BCB
  <div id="ATC">{{include file="inc_vw_arbre_ATC.tpl"}}</div>
  <div id="BCB">{{include file="inc_vw_arbre_BCB.tpl"}}</div>
</div>
<div id="composants" style="display: none;">{{include file="inc_vw_composants.tpl"}}</div>
<div id="DCI" style="display: none;">{{include file="inc_vw_DCI.tpl"}}</div>