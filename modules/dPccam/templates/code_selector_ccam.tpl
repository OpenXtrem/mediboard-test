<div class="small-info">
  Nouvelle interface de recherche de codes CCAM par mots-cl�s. <br />
  Vous pouvez retrouver l'ancienne recherche dans les pr�f�rences utilisateur (volet CCAM).
</div>

<form name="filterCode" method="get" action="?" onsubmit="return onSubmitFormAjax(this, null, 'code_area');">
  <input type="hidden" name="m" value="dPccam" />
  <input type="hidden" name="a" value="code_selector_ccam" />
  <input type="hidden" name="only_list" value="1" />
  <input type="hidden" name="_all_codes" value="0" />
  <input type="hidden" name="chir" value="{{$chir}}" />
  <input type="hidden" name="anesth" value="{{$anesth}}" />
  <input type="hidden" name="object_class" value="{{$object_class}}" />
  <table class="tbl">
    <tr>
      <th colspan="3">
        Filtre de recherche
      </th>
    </tr>
    <tr>
      <td>
        Mot-cl� : <input type="text" name="_keywords_code"/>
        <button type="submit" class="search notext"></button>
      </td>
      <td>
        <label>
          <input type="checkbox" id="_all_codes_view" onchange="$V(this.form._all_codes, this.checked ? 1 : 0)"/>
          Chercher dans toute la base CCAM
        </label>
      </td>
      <td>
        <label for="tag_id">Tag</label>
        <select name="tag_id" onchange="this.form.onsubmit()" class="taglist">
          <option value=""> &mdash; {{tr}}All{{/tr}} </option>
          {{mb_include module=ccam template=inc_favoris_tag_select depth=0}}
        </select>
      </td>
    </tr>
  </table>
</form>
<div id="code_area" style="height: 60%; text-align: left;">
  {{mb_include module=ccam template=inc_code_selector_ccam}}
</div>