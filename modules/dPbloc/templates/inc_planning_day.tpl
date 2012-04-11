{{* $Id$ *}}

{{*
 * @package Mediboard
 * @subpackage dPbloc
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

<tr>
  <th class="narrow">
    <a href="?m=dPbloc&amp;tab=vw_edit_planning&amp;date={{$curr_day}}" >
      <strong>{{$curr_day|date_format:"%a %d %b"}}</strong>
    </a>
    {{if $can->edit}}
      <br />
      {{assign var=plages_ids value=$listPlages.$curr_day}}
      <form name="chg-{{$curr_day}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        <input type="hidden" name="m" value="bloc" />
        <input type="hidden" name="@class" value="CPlageOp" />
        <input type="hidden" name="verrouillage" value="oui" />
        <input type="hidden" name="plageop_ids" value="{{$plages_ids|@array_keys|@join:"-"}}" />
        <button type="button" class="new notext" onclick="EditPlanning.edit('','{{$curr_day}}');">{{tr}}Edit{{/tr}}</button>
        <button type="submit" class="lock notext">{{tr}}Lock{{/tr}}</button>
        <button type="button" class="print notext" onclick="EditPlanning.popPlanning('{{$curr_day}}');">{{tr}}Print{{/tr}}</button>
      </form>
    {{/if}}
  </th>
  {{foreach from=$listHours item=_hour}}
  <th colspan="4" class="heure">{{$_hour}}:00</th>
  {{/foreach}}
</tr>
{{foreach from=$listSalles item=_salle key=salle_id}}
{{assign var="keyHorsPlage" value="$curr_day-s$salle_id-HorsPlage"}}
<tr {{if isset($_salle->_blocage.$curr_day|smarty:nodefaults)}}class="hatching"{{/if}}>
  <td class="salle" {{if $affichages.$keyHorsPlage|@count}}rowspan="2"{{/if}}>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$_salle->_guid}}')"
      onclick="EditPlanning.monitorDaySalle('{{$_salle->_id}}', '{{$curr_day}}');">
      {{$_salle->nom}}
    </span>
  </td>
  {{mb_include template=inc_planning_bloc_line}}
</tr>
{{if $affichages.$keyHorsPlage|@count}}
<tr>
  <td colspan="100" class="empty">
    <a href="?m=dPbloc&tab=vw_urgences&date={{$curr_day}}">
      + {{$affichages.$keyHorsPlage|@count}} intervention(s) hors plage
    </a>
  </td>
</tr>
{{/if}}
{{foreachelse}}
<tr>
  <td colspan="100" class="empty">{{tr}}CSalle.none{{/tr}}</td>
</tr> 
{{/foreach}} 