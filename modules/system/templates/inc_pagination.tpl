{{* $change_page, $total, $current, $step = 20 *}}

{{if !isset($step|smarty:nodefaults)}}
  {{assign var="step" value=20}}
{{/if}}

{{assign var="last_page" value=$total-1}}
{{assign var="last_page" value=$last_page/$step|intval}}
{{assign var="pagination" value=0|range:$last_page}}

<div class="pagination" style="min-height: 1em;">
  <div style="float: right;">{{$total}} {{tr}}results{{/tr}}</div>
  {{if $total > $step}}
    {{if $pagination|count > 12}}
      {{foreach from=$pagination item=page name=page}}
        {{if ($page < 5 || $page > ($pagination|@count-6))}}
          {{if $page*$step == $current || !$current && $smarty.foreach.page.first}}
            <span class="page active">{{$smarty.foreach.page.iteration}}</span>
          {{else}}
            <a href="#1" onclick="{{$change_page}}({{$page*$step}})" class="page">{{$smarty.foreach.page.iteration}}</a>
          {{/if}}
        {{else}}
          {{if $page == 5}}... <select onchange="{{$change_page}}($V(this))"><option selected="selected" disabled="disabled">{{$current/$step+1}}</option>{{/if}}
            <option {{if $page*$step == $current}}selected="selected"{{/if}} value="{{$page*$step}}">{{$page+1}}</option>
          {{if $page == $pagination|@count-6}}</select> ...{{/if}}
        {{/if}}
      {{/foreach}}
    {{else}}
      {{foreach from=$pagination item=page name=page}}
        {{if $page*$step == $current || !$current && $smarty.foreach.page.first}}
          <span class="page active">{{$smarty.foreach.page.iteration}}</span>
        {{else}}
          <a href="#1" onclick="{{$change_page}}({{$page*$step}})" class="page">{{$smarty.foreach.page.iteration}}</a>
        {{/if}}
      {{/foreach}}
    {{/if}}
  {{/if}}
</div>