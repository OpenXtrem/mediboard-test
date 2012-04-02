<table class="tbl">
  <tr> 
    <th colspan="5" class="title">{{$sejour->_view}} </th>
  </tr>
  <tr>
    <th> </th>
    <th class="category">{{mb_title object=$movement field=movement_type}}</th>
    <th class="category">{{mb_title object=$movement field=original_trigger_code}}</th>
    <th class="category">{{mb_title object=$movement field=start_of_movement}}</th>
    <th class="category">{{mb_title object=$movement field=last_update}}</th>
  </tr>
  {{foreach from=$movements item=_movement}}
    <tr {{if $_movement->cancel}}class="hatching"{{/if}}>
      <td> 
        <code onmouseover="ObjectTooltip.createEx(this,'{{$_movement->_guid}}', 'identifiers')">{{$_movement->_view}}</code>
      </td>
      <td>{{mb_value object=$_movement field=movement_type}}</td>
      <td><code>{{mb_value object=$_movement field=original_trigger_code}}</code></td>
      <td>
        <label title='{{mb_value object=$_movement field="start_of_movement"}}'>
          {{mb_value object=$_movement field="start_of_movement" format=relative}}
        </label>
      </td>
      <td>
        <label title='{{mb_value object=$_movement field="last_update"}}'>
          {{mb_value object=$_movement field="last_update" format=relative}}
        </label>
      </td>
    </tr>
  {{foreachelse}}
  <tr> 
    <td colspan="5" class="empty">{{tr}}CMovement.none{{/tr}}</th>
  </tr>
  {{/foreach}}
</table>