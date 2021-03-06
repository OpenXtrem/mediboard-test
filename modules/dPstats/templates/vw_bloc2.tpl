{{* $Id$ *}}

{{*
 * @package Mediboard
 * @subpackage dPstats
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

<script>
function getSpreadSheet() {
  var form = document.bloc;
  var url = new Url('stats', 'vw_bloc2', 'raw');
  url.addParam('mode', 'csv');
  url.addElement(form.bloc_id);
  url.addElement(form.deblistbloc);
  url.addElement(form.finlistbloc);
  url.addElement(form.type);
  url.popup(550, 300, 'statsBloc');
}

Main.add(function () {
  Calendar.regField(getForm("bloc").deblistbloc);
  Calendar.regField(getForm("bloc").finlistbloc);
});
</script>
{{assign var=colspan_th value=9}}
{{if $conf.dPsalleOp.COperation.use_entree_bloc}}
  {{assign var=colspan_th value=$colspan_th+1}}
{{/if}}
{{assign var=colspan_td value=30}}
{{if $conf.dPsalleOp.COperation.use_entree_bloc}}
  {{assign var=colspan_td value=$colspan_td+1}}
{{/if}}

<form name="bloc" action="?" method="get" onsubmit="return checkForm(this)">
<input type="hidden" name="m" value="dPstats" />
<table class="form">
  <tr>
    <th colspan="5" class="title">Tableau d'activit� du bloc sur une journ�e</th>
  </tr>
  <tr>
    <td class="button" rowspan="4" style="width: 1%">
      <img src="images/pictures/spreadsheet.png" title="T�l�charger le fichier CSV" onclick="getSpreadSheet()" />
    </td>
    <td class="button" rowspan="4" style="width: 10%">
      <div class="small-info">Cliquer sur l'icone pour t�l�charger les donn�es au format CSV.</div>
    </td>
    <th><label for="deblistbloc" title="Date de d�but">Du</label></th>
    <td>
      <input type="hidden" name="deblistbloc" class="notNull date" value="{{$deblist}}" />
    </td>
  </tr>
  <tr>
    <th><label for="finlistbloc" title="Date de d�but">Au</label></th>
    <td>
      <input type="hidden" name="finlistbloc" class="notNull date" value="{{$finlist}}" />
    </td>
  </tr>
  <tr>
    <th><label for="bloc_id" title="Bloc op�ratoire">Bloc</label></th>
    <td colspan="4">
      <select name="bloc_id">
        <option value="">&mdash; {{tr}}CBlocOperatoire.select{{/tr}}</option>
        {{foreach from=$blocs item=_bloc}}
        <option value="{{$_bloc->_id}}" {{if $_bloc->_id == $bloc->_id }}selected="selected"{{/if}}>
          {{$_bloc->nom}}
        </option>
        {{/foreach}}
      </select>
    </td>
  </tr>
  <tr>
    <th><label for="type" title="Type">Type</label></th>
    <td colspan="4">
      <select name="type">
        <option value="all" {{if $type == "all"}}selected="selected" {{/if}}>{{tr}}All{{/tr}}</option>
        <option value="prevue" {{if $type == "prevue"}}selected="selected" {{/if}}>Programm�es seules</option>
        <option value="hors_plage" {{if $type == "hors_plage"}}selected="selected" {{/if}}>Hors plages seules</option>
      </select>
    </td>
  </tr>
  <tr>
    <td class="button" colspan="5">
      <button class="search" type="submit">Afficher</button>
    </td>
  </tr>
</table>
</form>

<table class="tbl">
  <tr>
    <th rowspan="2">Date</th>
    <th colspan="2">Salle</th>
    <th colspan="2">Vacation</th>
    <th colspan="2">N� d'ordre</th>
    <th rowspan="2">Patient</th>
    <th colspan="3">Hospitalisation</th>
    <th rowspan="2">Chirurgien</th>
    <th rowspan="2">Anesth�siste</th>
    <th colspan="3">Nature</th>
    <th rowspan="2">Type<br />anesth�sie</th>
    <th rowspan="2">Code<br />ASA</th>
    <th rowspan="2">Placement<br />programme</th>
    <th colspan="{{$colspan_th}}">Timings intervention</th>
    <th colspan="3">Timings reveil</th>
  </tr>
  <tr>
    <th>Pr�vu</th>
    <th>R�el</th>
    <th>D�but</th>
    <th>Fin</th>
    <th>Pr�vu</th>
    <th>R�el</th>
    <th>Type</th>
    <th>Entree pr�vue</th>
    <th>Entr�e r�elle</th>
    <th>libelle</th>
    <th>DP</th>
    <th>Actes</th>
    {{if $conf.dPsalleOp.COperation.use_entree_bloc}}
      <th>entr�e<br />bloc</th>
    {{/if}}
    <th>entr�e<br />salle</th>
    <th>debut<br />induction</th>
    <th>fin<br />induction</th>
    <th>pose<br />garrot</th>
    <th>d�but<br />intervention</th>
    <th>fin<br />intervention</th>
    <th>retrait<br />garrot</th>
    <th>sortie<br />salle</th>
    <th>patient<br />suivant</th>
    <th>entr�e</th>
    <th>sortie</th>
  </tr>
  {{if $type == "prevue"}}
    {{foreach from=$plages item=_plage}}
    <tr>
      <th colspan="{{$colspan_td}}" class="section">
        {{$_plage}} 
        &mdash; {{$_plage->_ref_salle}}
        &mdash; {{$_plage->_ref_owner}}
      </th>
    </tr>

    {{foreach from=$_plage->_ref_operations item=_operation}}
      {{mb_include template=inc_bloc2_line}}
    {{foreachelse}}
      <tr>
        <td colspan="{{$colspan_td}}" class="empty">{{tr}}COperation.none{{/tr}}</td>
      </tr>
    {{/foreach}}

    {{foreachelse}}
      <tr>
        <td colspan="{{$colspan_td}}" class="empty">{{tr}}CPlageOp.none{{/tr}}</td>
      </tr>
    {{/foreach}}

  {{else}}
    {{foreach from=$operations item=_operation}}
      {{mb_include template=inc_bloc2_line}}
    {{foreachelse}}
      <tr>
        <td colspan="{{$colspan_td}}" class="empty">{{tr}}COperation.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  {{/if}}
</table>