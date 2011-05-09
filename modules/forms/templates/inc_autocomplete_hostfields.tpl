<ul>
	
{{foreach from=$host_fields item=element key=value}}
  <li data-prop="{{$element.prop}}" data-value="{{$value}}" title="{{$element.longview}}">
  	<small style="float: right; color: #666;">
      {{$element.type}}
    </small>
    
    <span class="view" {{if !$show_views}} style="display: none;" {{/if}}>
      {{$element.view}}
    </span>
		
		<span style="{{if $show_views}} display: none; {{/if}} padding-left: {{$element.level}}em; {{if $element.level == 0}}font-weight: bold{{/if}}">
			{{$element.title}}
		</span>
  </li>
{{/foreach}}

</ul>