<script>
  Main.add(function(){
    var form = getForm("tools-{{$_tool_class}}-{{$_tool}}");

    form.count.addSpinner({min: 1});
  });
</script>

<form name="tools-{{$_tool_class}}-{{$_tool}}" method="get" action="?"
      onsubmit="return onSubmitFormAjax(this, null, 'tools-{{$_tool_class}}-{{$_tool}}')">
  <input type="hidden" name="m" value="eai" />
  <input type="hidden" name="a" value="ajax_send_messages" />
  <input type="hidden" name="tool" value="{{$_tool}}" />
  <input type="hidden" name="suppressHeaders" value="1" />

  <table class="main form">
    <tr>
      <th>{{mb_label class=CExchangeDataFormat field="_date_min"}}</th>
      <td>
        <input class="dateTime" type="hidden" name="date_min" value="" /> <br />
        <script type="text/javascript">
          Main.add(function () {
            Calendar.regField(getForm('tools-{{$_tool_class}}-{{$_tool}}').date_min);
          });
        </script>
      </td>
    </tr>

    <tr>
      <th>{{mb_label class=CExchangeDataFormat field="_date_max"}}</th>
      <td>
        <input class="dateTime" type="hidden" name="date_max" value="" /> <br />
        <script type="text/javascript">
          Main.add(function () {
            Calendar.regField(getForm('tools-{{$_tool_class}}-{{$_tool}}').date_max);
          });
        </script>
      </td>
    </tr>

    <tr>
      <th></th>
      <td>
        <select name="exchange_class">
          {{foreach from=$exchanges_classes key=sub_classes item=_child_classes}}
            <optgroup label="{{tr}}{{$sub_classes}}{{/tr}}">
              {{foreach from=$_child_classes item=_class}}
                <option value="{{$_class->_class}}">{{tr}}{{$_class->_class}}{{/tr}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th></th>
      <td><input type="text" name="count" value="30" size="3" title="Nombre d'�changes � traiter" /></td>
    </tr>

    <tr>
      <th></th>
      <td><label><input type="checkbox" name="continue" value="1" title="Automatique" /> Automatique</label></td>
    </tr>

    <tr>
      <td></td>
      <td>
        <button type="submit" class="change">{{tr}}CEAI-tools-{{$_tool_class}}-{{$_tool}}-button{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>