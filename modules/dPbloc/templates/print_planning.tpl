{{* $Id$ *}}

{{*
 * @package Mediboard
 * @subpackage dPbloc
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

{{mb_script module="dPplanningOp" script="ccam_selector"}}

<script type="text/javascript">
function checkFormPrint(form) {
  if(!checkForm(form)){
    return false;
  }
  
  popPlanning();
}

  
function popPlanning() {
  var form = getForm("paramFrm");
  var url = new Url("dPbloc", "view_planning");
  url.addElement(form._date_min);
  url.addElement(form._date_max);
  url.addParam("_plage", $V(form._plage));
  url.addElement(form._codes_ccam);
  url.addElement(form._intervention);
  url.addElement(form._prat_id);
  url.addElement(form._specialite);
  url.addElement(form._bloc_id);
  url.addElement(form.salle_id);
  url.addElement(form.type);
  url.addParam("_ccam_libelle", $V(form._ccam_libelle));
  url.addParam("_coordonnees", $V(form._coordonnees));
  url.addParam("_print_numdoss", $V(form._print_numdoss));
	if(form.planning_perso.checked){// pour l'affichage du planning perso d'un anesthesiste
	  url.addParam("planning_perso", true);
	}
  url.popup(900, 550, 'Planning');
}

function changeDate(sDebut, sFin){
  var oForm = getForm("paramFrm");
  oForm._date_min.value = sDebut;
  oForm._date_max.value = sFin;
  oForm._date_min_da.value = Date.fromDATE(sDebut).toLocaleDate();
  oForm._date_max_da.value = Date.fromDATE(sFin).toLocaleDate();  
}

function changeDateCal(minChanged){
  var oForm = getForm("paramFrm");
  oForm.select_days[0].checked = false;
  oForm.select_days[1].checked = false;
  oForm.select_days[2].checked = false;
  oForm.select_days[3].checked = false;
  
  var minElement = oForm._date_min,
      maxElement = oForm._date_max,
      minView = oForm._date_min_da,
      maxView = oForm._date_max_da;
      
  if (minElement.value > maxElement.value) {
    if (minChanged) {
      $V(maxElement, minElement.value);
      $V(maxView, Date.fromDATE(maxElement.value).toLocaleDate());
    }
    else {
      $V(minElement, maxElement.value);
      $V(minView, Date.fromDATE(minElement.value).toLocaleDate());
    }
  }
}
//affiche ou cache le checkbox relatif � un anesth�siste
function showCheckboxAnesth(element){
  var form = getForm("paramFrm");
  if ($(element.options[element.selectedIndex]).hasClassName('anesth')){
	   $('perso').show();
		 form.planning_perso.checked = "";
	}
  else {
	 $('perso').hide();
  }
}

</script>


<form name="paramFrm" action="?m=dPbloc" method="post" onsubmit="return checkFormPrint(this)">
<input type="hidden" name="_class" value="COperation" />
<input type="hidden" name="_chir" value="{{$chir}}" />
<table class="main">
  <tr>
    <td>
      <table class="form">
        <tr>
          <th class="category" colspan="3">Choix de la p�riode</th>
        </tr>
        <tr>
          <th>{{mb_label object=$filter field="_date_min"}}</th>
          <td>{{mb_field object=$filter field="_date_min" form="paramFrm" canNull="false" onchange="changeDateCal(true)" register=true}} </td>
          <td rowspan="2">
            <label>
              <input type="radio" name="select_days" onclick="changeDate('{{$now}}','{{$now}}');"  value="day" checked="checked" /> 
              Jour courant
            </label>
            <br />
            <label>
              <input type="radio" name="select_days" onclick="changeDate('{{$tomorrow}}','{{$tomorrow}}');" value="tomorrow" /> 
              Lendemain
            </label>
            <br />
            <label>
              <input type="radio" name="select_days" onclick="changeDate('{{$week_deb}}','{{$week_fin}}');" value="week" /> 
              Semaine courante
            </label>
            <br />
            <label>
              <input type="radio" name="select_days" onclick="changeDate('{{$month_deb}}','{{$month_fin}}');" value="month" /> 
              Mois courant
            </label>
          </td>
        </tr>
        <tr>
           <th>{{mb_label object=$filter field="_date_max"}}</th>
           <td>{{mb_field object=$filter field="_date_max" form="paramFrm" canNull="false" onchange="changeDateCal(false)" register=true}} </td>
        </tr>
        <tr>
          <th class="category" colspan="3">Types d'intervention</th>
        </tr>
        <tr>
          <th>{{mb_label object=$filter field="_intervention"}}</th>
          <td colspan="2">
            <select name="_intervention">
              <option value="0">&mdash; Toutes les interventions &mdash;</option>
              <option value="1">ins�r�es dans le planning</option>
              <option value="2">� ins�rer dans le planning</option>
            </select>
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$filterSejour field="type"}}</th>
          <td colspan="2">
            {{mb_field object=$filterSejour field="type" canNull=true defaultOption="&mdash; Tous les types"}}
          </td>
        </tr>
      </table>

    </td>
    <td>

      <table class="form">
        <tr>
          <th class="category" colspan="2">Autres filtres</th>
        </tr>
        <tr>
          <th>{{mb_label object=$filter field="_prat_id"}}</th>
          <td>
            <select name="_prat_id" onchange="showCheckboxAnesth(this); this.form._specialite.value = '0';">
              <option value="0">&mdash; Tous les praticiens &mdash;</option>
              {{foreach from=$listPrat item=curr_prat}}
                <option {{if $curr_prat->isAnesth()}} class="mediuser anesth" {{else}} class="mediuser" {{/if}} style="border-color: #{{$curr_prat->_ref_function->color}};" value="{{$curr_prat->user_id}}" >
                  {{$curr_prat->_view}}
                </option>
              {{/foreach}}
            </select>
						<span id="perso" {{if !$praticien->isAnesth()}} style="display:none;"{{/if}}>
						  Planning personnel <input type="checkbox" name="planning_perso" />
						</span>
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$filter field="_specialite"}}</th>
          <td>
            <select name="_specialite" onchange="this.form._prat_id.value = '0';">
              <option value="0">&mdash; Toutes les sp�cialit�s &mdash;</option>
              {{foreach from=$listSpec item=curr_spec}}
                <option value="{{$curr_spec->function_id}}" class="mediuser" style="border-color: #{{$curr_spec->color}};">
                  {{$curr_spec->text}}
                </option>
              {{/foreach}}
            </select>
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$filter field="_bloc_id"}}</th>
          <td>
            <select name="_bloc_id">
              <option value="0">&mdash; Tous les blocs &mdash;</option>
              {{foreach from=$listBlocs item=curr_bloc}}
                <option value="{{$curr_bloc->_id}}" {{if $curr_bloc->_id == $filter->_bloc_id}}selected="selected"{{/if}}>
                  {{$curr_bloc->_view}}
                </option>
              {{/foreach}}
            </select>
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$filter field="salle_id"}}</th>
          <td>
            <select name="salle_id">
              <option value="0">&mdash; Toutes les salles &mdash;</option>
              {{foreach from=$listBlocs item=curr_bloc}}
                <optgroup label="{{$curr_bloc->_view}}">
                {{foreach from=$curr_bloc->_ref_salles item=curr_salle}}
                  <option value="{{$curr_salle->_id}}" {{if $curr_salle->_id == $filter->salle_id}}selected="selected"{{/if}}>
                    {{$curr_salle->nom}}
                  </option>
                {{/foreach}}
                </optgroup>
              {{/foreach}}
            </select>
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$filter field="_codes_ccam"}}</th>
          <td><input type="text" name="_codes_ccam" size="10" value="" />
          <button type="button" class="search" onclick="CCAMSelector.init()">Chercher un code</button>
          <script type="text/javascript">
            CCAMSelector.init = function(){
              this.sForm  = "paramFrm";
              this.sClass = "_class";
              this.sChir  = "_chir";
              this.sView  = "_codes_ccam";
              this.pop();
            }
            var oForm = getForm('paramFrm');
            Main.add(function() {
              var url = new Url("dPccam", "httpreq_do_ccam_autocomplete");
              url.autoComplete(oForm._codes_ccam, '', {
                minChars: 1,
                dropdown: true,
                width: "250px",
                updateElement: function(selected) {
                  $V(oForm._codes_ccam, selected.down("strong").innerHTML);
                }
              });
            });
          </script>
          </td>
        </tr>

      </table>

    </td>
  </tr>
  <tr>
    <td colspan="2">
      <table class="form">
        <tr>
          <th class="category" colspan="2">Param�tres d'affichage</th>
        </tr>
        {{assign var="class" value="CPlageOp"}}
            
        <tr>
          <th>
            <label for="_coordonnees_1" title="Afficher ou cacher le num�ro de tel du patient">Afficher les coordonn�es du patient</label>
          </th>
          <td>  
            <label for="_coordonnees_1">Oui</label>
            <input type="radio" name="_coordonnees" value="1" /> 
            <label for="_coordonnees_0">Non</label>
            <input type="radio" name="_coordonnees" value="0" checked="checked"/> 
          </td>
        </tr>
            
        <tr>
          <th>
            <label for="print_numdoss_1" title="Afficher ou cacher le num�ro de dossier">Afficher le num�ro de dossier</label>
          </th>
          <td>  
            <label for="_print_numdoss_1">Oui</label>
            <input type="radio" name="_print_numdoss" value="1" /> 
            <label for="_print_numdoss_0">Non</label>
            <input type="radio" name="_print_numdoss" value="0" checked="checked"/> 
          </td>
        </tr>
        <tr>
          <th style="width: 50%">{{mb_label object=$filter field="_plage"}}</th>
          <td>  
            {{assign var="var" value="plage_vide"}}
            <label for="_plage">Oui</label>
            <input type="radio" name="_plage" value="1" {{if $conf.$m.$class.$var == "1"}}checked="checked"{{/if}}/> 
            <label for="_plage">Non</label>
            <input type="radio" name="_plage" value="0" {{if $conf.$m.$class.$var == "0"}}checked="checked"{{/if}}/> 
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$filter field="_ccam_libelle"}}</th>
          <td>  
            {{assign var="var" value="libelle_ccam"}}
            <label for="_ccam_libelle">Oui</label>
            <input type="radio" name="_ccam_libelle" value="1" {{if $conf.$m.$class.$var == "1"}}checked="checked"{{/if}}/> 
            <label for="_ccam_libelle">Non</label>
            <input type="radio" name="_ccam_libelle" value="0" {{if $conf.$m.$class.$var == "0"}}checked="checked"{{/if}}/> 
          </td>
        </tr>
        <tr>
          <td colspan="2" class="button">
            <button class="print" type="button" onclick="checkFormPrint(this.form)">Afficher</button>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>