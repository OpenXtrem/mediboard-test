{{mb_default var=timed_data value=false}}

{{if $timed_data}}
  <table class="tbl">
    {{foreach from=$data key=_time item=_data}}
      <tr>
        <th>{{$times.$_time|date_format:$conf.datetime}}</th>
        <td style="text-align: center;">{{if $_data.file_id}}<img src="{{$_data.datauri}}" height="45" /><br/>{{$_data.file->_no_extension}}{{else}}{{$_data.value}}{{/if}}</td>
      </tr>
    {{/foreach}}
  </table>
{{else}}
  <table class="tbl">
    <tr>
      <th></th>
      {{foreach from=$series key=_serie_id item=_serie}}
        <th>{{$_serie}}</th>
      {{/foreach}}
    </tr>

    {{foreach from=$data key=_time item=_data}}
      <tr>
        <th>{{$times.$_time|date_format:$conf.datetime}}</th>
        {{foreach from=$series key=_serie_id item=_serie}}
          {{if array_key_exists($_serie_id,$_data)}}
            {{assign var=_datum value=$_data.$_serie_id}}
            <td>
              {{if $_datum.label}}
                {{$_datum.label}}
              {{else}}
                {{$_datum.value}}
              {{/if}}

              {{$_datum.unit}}
            </td>
          {{else}}
            <td></td>
          {{/if}}
        {{/foreach}}
      </tr>
    {{/foreach}}
  </table>
{{/if}}
