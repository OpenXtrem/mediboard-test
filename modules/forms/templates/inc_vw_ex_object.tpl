
{{* 
<table class="main tbl">
	{{foreach from=$ex_object->_ref_ex_class->_ref_groups item=_ex_group}}
	  <tr>
	  	<th colspan="2">{{$_ex_group}}</th>
		</tr>
		
    {{foreach from=$_ex_group->_ref_fields item=_ex_field}}
			<tr>
	      <th>
	        {{mb_label object=$ex_object field=$_ex_field->name}}
	      </th>
	      <td class="text">
	        {{mb_value object=$ex_object field=$_ex_field->name}}
	      </td>
			</tr>
    {{/foreach}}
		
	{{/foreach}}
</table>
 *}}
 
{{foreach from=$ex_object->_ref_ex_class->_ref_groups item=_ex_group}}
  <span style="color: #4086FF;">{{$_ex_group}}</span>
	
	<ul>
  {{assign var=any value=false}}
	
  {{foreach from=$_ex_group->_ref_fields item=_ex_field}}
	  {{assign var=field_name value=$_ex_field->name}}
		
	  {{if $ex_object->$field_name !== null}}
      {{assign var=any value=true}}
	    <li>
	      <span style="color: #666;">{{mb_label object=$ex_object field=$field_name}}</span>
	      :
				{{mb_value object=$ex_object field=$field_name}}
	    </li>
		{{/if}}
  {{/foreach}}
	{{if !$any}}
	  <li class="empty">Aucune valeur</li>
	{{/if}}
  </ul>
	<br />
{{/foreach}}
