<table class="tbl">
  <tr>
    <th class="title" rowspan="2">Praticien</th>
    <th class="title" colspan="30">Totaux par {{tr}}{{$period}}{{/tr}}</th>
  </tr>
  <tr>
    {{if $period == "day"  }}{{assign var=format value="%d %a"}}{{/if}}
    {{if $period == "week" }}{{assign var=format value="%V"   }}{{/if}}
    {{if $period == "month"}}{{assign var=format value="%m"   }}{{/if}}
    {{if $period == "year" }}{{assign var=format value="%Y"   }}{{/if}}
    
    {{foreach from=$dates item=_date}}
    <th class="text narrow" title="{{$_date}}">{{$_date|date_format:$format}}</th>
    {{/foreach}}
  </tr>

  <tr>
    <th class="category" colspan="31">{{$group}}</th>
  </tr>
  {{foreach from=$functions item=_function}}
  <tr>
    <th class="section" colspan="31" style="text-align: left;">
      {{mb_include module=mediusers template=inc_vw_function function=$_function}}
    </th>
  </tr>
  {{foreach from=$_function->_ref_users key=user_id item=_user}}
  <tr>
    <td>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user}}
    </td>
    {{assign var=_dates value=$totals.$user_id}}
    {{foreach from=$dates item=_date}}     
    <td style="text-align: center;">
      {{if array_key_exists($_date, $_dates)}}
        <strong>{{$_dates.$_date}}</strong>
      {{else}}
        &ndash;
      {{/if}}
    </td>
    {{/foreach}}
  </tr>
  {{/foreach}}
  {{foreachelse}}
  <tr><td class="empty" colspan=31">{{tr}}CConsultation.none{{/tr}}</td></tr>
  {{/foreach}}
</table>