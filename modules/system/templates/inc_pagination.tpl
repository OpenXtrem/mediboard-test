{{* $change_page, $total, $current, $step = 20, jumper = 1 *}}

{{mb_default var="step" value=20}}
{{mb_default var="jumper" value=0}}
{{mb_default var="narrow" value=false}}

{{assign var="last_page" value=$total-1}}
{{assign var="last_page" value=$last_page/$step|intval}}
{{assign var="pagination" value=0|range:$last_page}}

<div class="pagination {{if $narrow}}narrow{{/if}}" style="min-height: 1em;">
  <div style="float: right;">{{$total}} {{tr}}results{{/tr}}</div>
  
  {{if $total > $step}}
  
    {{if $current >= $step}}
      <a href="#1" onclick="{{$change_page}}({{$current-$step}}); return false;" class="page">&lt;</a>
    {{else}}
      <span class="page disabled">&lt;</span>
    {{/if}}
  
    {{if $pagination|count > 12}}
      {{foreach from=$pagination item=page name=page}}
        {{if ($page < 5 || $page > ($pagination|@count-6))}}
          {{if $page*$step == $current || !$current && $smarty.foreach.page.first}}
            <span class="page active">{{$smarty.foreach.page.iteration}}</span>
          {{else}}
            <a href="#1" onclick="{{$change_page}}({{$page*$step}}); return false;" class="page">{{$smarty.foreach.page.iteration}}</a>
          {{/if}}
        {{else}}
          {{if $page == 5}}
             ... 
             <select onchange="{{$change_page}}($V(this))">
              <option selected="selected" disabled="disabled">{{$current/$step+1}}</option>
          {{/if}}
            {{* The test vs 0 is to avoid a warning "division by zero" *}}
            {{if $jumper == 0 || $page+1 % $jumper == 0}}
              <option {{if $page*$step == $current}}selected="selected"{{/if}} value="{{$page*$step}}">{{$page+1}}</option>
            {{/if}}
            {{if $jumper == 0 || $page+1 % ($jumper*10) == 0}}
              {{assign var=jumper value=$jumper*10}}
            {{/if}}
          {{if $page == $pagination|@count-6}}
            </select> ...
          {{/if}}
        {{/if}}
      {{/foreach}}
    {{else}}
      {{foreach from=$pagination item=page name=page}}
        {{if $page*$step == $current || !$current && $smarty.foreach.page.first}}
          <span class="page active">{{$smarty.foreach.page.iteration}}</span>
        {{else}}
          <a href="#1" onclick="{{$change_page}}({{$page*$step}}); return false;" class="page">{{$smarty.foreach.page.iteration}}</a>
        {{/if}}
      {{/foreach}}
    {{/if}}
    
    {{if $current < $last_page*$step}}
      <a href="#1" onclick="{{$change_page}}({{$current+$step}}); return false;" class="page">&gt;</a>
    {{else}}
      <span class="page disabled">&gt;</span>
    {{/if}}
    
  {{/if}}

</div>