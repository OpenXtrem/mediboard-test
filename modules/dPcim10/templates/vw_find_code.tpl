{{if $dialog}}
{{assign var="action" value="dialog=1&amp;a"}}
{{else}}
{{assign var="action" value="tab"}}
{{/if}}

<form action="?" name="selectLang" method="get" >

{{if $dialog}}
<input type="hidden" name="a" value="{{$a}}" />
{{else}}
<input type="hidden" name="tab" value="{{$tab}}" />
{{/if}}

<input type="hidden" name="m" value="dPcim10" />
<input type="hidden" name="dialog" value="{{$dialog}}" />
<input type="hidden" name="keys" value="{{$keys}}" />

<table class="form">
  <tr>
    <th class="category" colspan="2">
      <select name="lang" style="float:right;" onchange="this.form.submit()">
        <option value="{{$smarty.const.LANG_FR}}" {{if $lang == $smarty.const.LANG_FR}}selected="selected"{{/if}}>
          Fran�ais
        </option>
        <option value="{{$smarty.const.LANG_EN}}" {{if $lang == $smarty.const.LANG_EN}}selected="selected"{{/if}}>
          English
        </option>
        <option value="{{$smarty.const.LANG_DE}}" {{if $lang == $smarty.const.LANG_DE}}selected="selected"{{/if}}>
          Deutsch
        </option>
      </select>
      Crit�res de recherche
    </th>
  </tr>
</table>

</form>

<form action="?" name="selection" method="get" onsubmit="return checkForm(this)">

{{if $dialog}}
<input type="hidden" name="a" value="{{$a}}" />
{{else}}
<input type="hidden" name="tab" value="{{$tab}}" />
{{/if}}

<input type="hidden" name="m" value="{{$m}}" />
<input type="hidden" name="dialog" value="{{$dialog}}" />

<table class="form">
  <tr>
    <th><label for="keys" title="Un ou plusieurs mots cl�s, s�par�s par des espaces. Obligatoire">Mots clefs</label></th>
    <td><input type="text" title="str" name="keys" value="{{$keys}}" /></td>
  </tr>
  <tr>
    <td class="button" colspan="2">
      <button class="search" type="submit">Rechercher</button>
    </td>
  </tr>
</table>

</form>

<table class="findCode">

  <tr>
    <th colspan="4">
      {{if $numresults == 100}}
      Plus de {{$numresults}} r�sultats trouv�s, seuls les 100 premiers sont affich�s:
      {{else}}
      {{$numresults}} r�sultats trouv�s:
      {{/if}}
    </th>
  </tr>

  {{foreach from=$master item=curr_master key=curr_key}}
  {{if $curr_key is div by 4}}
  <tr>
  {{/if}}
    <td>
      <strong>
        <a href="?m={{$m}}&amp;{{$action}}=vw_full_code&amp;code={{$curr_master.code}}">{{$curr_master.code}}</a>
      </strong>
      <br />
      {{$curr_master.text}}
    </td>
  {{if ($curr_key+1) is div by 4 or ($curr_key+1) == $master|@count}}
  </tr>
  {{/if}}
  {{/foreach}}

</table>