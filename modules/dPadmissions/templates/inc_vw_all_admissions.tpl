{{* $Id$ *}}

{{*
 * @package Mediboard
 * @subpackage dPadmissions
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

<table class="tbl" style="text-align: center;">
  <tr>
    <th class="title" colspan="4">
      <a style="display: inline" href="#1" onclick="$V(getForm('selType').date, '{{$lastmonth}}'); reloadFullAdmissions()">&lt;&lt;&lt;</a>
      {{$date|date_format:"%b %Y"}}
      <a style="display: inline" href="#1" onclick="$V(getForm('selType').date, '{{$nextmonth}}'); reloadFullAdmissions()">&gt;&gt;&gt;</a>
    </th>
  </tr>
    
  <tr>
    <th rowspan="2">{{tr}}dPAdmission.admission date{{/tr}}</th>
  </tr>

  <tr>
    <th class="text">
      <a class="{{if $selAdmis=='0' && $selSaisis=='0'}}selected{{else}}selectable{{/if}}" title="Toutes les admissions"
        href="#1" onclick="filterAdm(0, 0)">
        {{tr}}dPAdmission.admission.short{{/tr}}
      </a>
    </th>
    <th class="text">
      <a class="{{if $selAdmis=='0' && $selSaisis=='um'}}selected{{else}}selectable{{/if}}" title="Admissions non pr�par�es"
         href="#1" onclick="filterAdm(0, '5')">
        {{tr}}dPAdmission.admission non preparee.short{{/tr}}
      </a>
    </th>
    <th class="text">
      <a class="{{if $selAdmis=='n' && $selSaisis=='0'}}selected{{else}}selectable{{/if}}" title="Admissions non effectu�es"
         href="#1" onclick="filterAdm('n', 0)">
        {{tr}}dPAdmission.admission non effectuee.short{{/tr}}
      </a>
    </th>
  </tr>

  {{foreach from=$days key=day item=counts}}
  <tr {{if $day == $date}}class="selected"{{/if}}>
    {{assign var=day_number value=$day|date_format:"%w"}}
    <td style="text-align: right;
      {{if array_key_exists($day, $bank_holidays)}}
        background-color: #fc0;
      {{elseif $day_number == '0' || $day_number == '6'}}
        background-color: #ccc;
      {{/if}}">
      <a href="#1" onclick="reloadAdmissionDate(this, '{{$day|iso_date}}');" title="{{$day|date_format:$conf.longdate}}">
        <strong>
          {{$day|date_format:"%a"|upper|substr:0:1}}
          {{$day|date_format:"%d"}}
        </strong>
      </a>
    </td>
    <td {{if $selAdmis=='0' && $selSaisis=='0' && $day == $date}}style="font-weight: bold;"{{/if}}>
      {{if isset($counts.admissions|smarty:nodefaults) && $counts.admissions}}{{$counts.admissions}}{{else}}-{{/if}}
    </td>
    <td {{if $selAdmis=='0' && $selSaisis=='n' && $day == $date}}style="font-weight: bold;"{{/if}}>
      {{if isset($counts.admissions_non_preparee|smarty:nodefaults) && $counts.admissions_non_preparee}}{{$counts.admissions_non_preparee}}{{else}}-{{/if}}
    </td>
    <td {{if $selAdmis=='n' && $selSaisis=='0' && $day == $date}}style="font-weight: bold;"{{/if}}>
      {{if isset($counts.admissions_non_effectuee|smarty:nodefaults) && $counts.admissions_non_effectuee}}{{$counts.admissions_non_effectuee}}{{else}}-{{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="10" class="empty">{{tr}}CAdmission.none{{/tr}}</td>
  </tr>
  {{/foreach}}

  <tr>
    <td><strong>Total</strong></td>
    <td><strong>{{$totaux.admissions|smarty:nodefaults}}</strong></td>
    <td><strong>{{$totaux.admissions_non_preparee|smarty:nodefaults}}</strong></td>
    <td><strong>{{$totaux.admissions_non_effectuee|smarty:nodefaults}}</strong></td>
  </tr>
</table>