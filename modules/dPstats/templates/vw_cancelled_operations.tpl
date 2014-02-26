<script type="text/javascript">
Main.add(function(){
  Control.Tabs.create("cancelled-operations");
});
</script>

<ul class="control_tabs" id="cancelled-operations">
{{foreach from=$counts item=_count key=_month}}
  <li><a href="#month-{{$_month}}">
    {{$_month}} <small>({{$_count}})</small>
  </a></li>
{{/foreach}}
</ul>
<span style="float: right">
  <form name="intervs" action="?" method="get" onsubmit="return checkForm(this)">
    <input type="hidden" name="m" value="stats" />
    <input type="hidden" name="tab" value="vw_cancelled_operations" />
    <select name="type_modif" onchange="this.form.submit()">
      <option value="annule" {{if $type_modif == "annule"}}selected="selected"{{/if}}>Interventions annul�es le jour m�me</option>
      <option value="ajoute" {{if $type_modif == "ajoute"}}selected="selected"{{/if}}>Interventions ajout�es le jour m�me</option>
    </select>
    Jusqu'au {{mb_field class=COperation field="_date_max" value=$date_max form="intervs" canNull="false" register=true onchange="this.form.submit()"}}
  </form>
</span>
<hr class="control_tabs" />
  
{{foreach from=$list item=month key=month_label}}
  <table id="month-{{$month_label}}" class="main tbl" style="display: none;">
    <tr>
      <th>{{mb_title class=COperation field=date}}</th>
      <th>{{mb_title class=COperation field=salle_id}}</th>
      <th>{{mb_title class=COperation field=chir_id}}</th>
      <th>{{mb_title class=CSejour field=patient_id}}</th>
      <th>{{mb_title class=CSejour field=type}}</th>
      <th>{{mb_title class=COperation field=libelle}}</th>
      <th>{{mb_title class=COperation field=rques}}</th>
      <th>{{mb_title class=COperation field=codes_ccam}}</th>
    </tr>

    {{foreach from=$month key=plage_status item=_operations}}
    <tr>
      <th colspan="100" class="section">{{tr}}COperation-title-{{$plage_status}}{{/tr}}</th>
    </tr>
    {{foreach from=$_operations item=op}}
      <tr>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$op->_guid}}')">
            {{mb_value object=$op field=_datetime}}
          </span>
        </td>
        <td class="text">{{mb_value object=$op field=salle_id tooltip=true}}</td>
        <td>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$op->_ref_praticien}}
        </td>
        <td class="text">{{mb_value object=$op->_ref_sejour field=patient_id}}</td>
        <td>{{mb_value object=$op->_ref_sejour field=type}}</td>
        <td class="text">{{mb_value object=$op field=libelle}}</td>
        <td class="text compact">{{mb_value object=$op field=rques}}</td>
        <td>
          {{foreach from=$op->_codes_ccam item=_code}}
            {{$_code}}
          {{foreachelse}}
            <div class="empty">{{tr}}CActeCCAM.none{{/tr}}</div>
          {{/foreach}}
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="100" class="empty">{{tr}}COperation.none{{/tr}}</td>
      </tr>
    {{/foreach}}

    {{foreachelse}}
    <tr>
      <td colspan="100" class="empty">{{tr}}COperation.none{{/tr}}</td>
    </tr>
    {{/foreach}}

  </table>
{{/foreach}}