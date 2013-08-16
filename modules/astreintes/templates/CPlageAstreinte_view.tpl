{{mb_script module="astreintes" script="plage"}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<table class="tbl tooltip">
  <tr>
    <th class="title text" colspan="2">
      {{mb_include module=system template=inc_object_idsante400}}
      {{mb_include module=system template=inc_object_history}}
      {{mb_include module=system template=inc_object_notes}}
      {{$object}}
    </th>

  </tr>
    <tr>
      <th>{{tr}}User{{/tr}}</th>
      <td>{{mb_value object=$object field=user_id}}</td>
    </tr>

    <tr>
      <th>D�but</th>
      <td>
        {{$object->start|date_format:"%A %d %B %Y %H:%M"}}
      </td>
    </tr>
    <tr>
      <th>Fin</th>
      <td>
        {{$object->end|date_format:"%A %d %B %Y %H:%M"}}
      </td>
    </tr>

    <tr>
      <th>Dur�e</th>
      <td>{{mb_include module="system" template="inc_vw_duration" duration=$object->_duration}}</td>
    </tr>

    {{if $object->_ref_user}}
    <tr>
      <th><img src="images/icons/phone.png" alt="{{tr}}CPlageAstreinte.PhoneNumber{{/tr}}"/></th>
      <td>
        {{mb_value object=$object field=phone_astreinte}}
      </td>
    </tr>
    {{/if}}
     <tr style="text-align: center;">
      <td class="button" colspan="2">
        <button class="edit" onclick="PlageAstreinte.modal({{$object->_id}}, {{$object->user_id}})">{{tr}}Edit{{/tr}}</button>
      </td>
      </td>
    </tr>
</table>

