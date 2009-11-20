{{mb_include_script module=system script="mb_object"}}

<script type="text/javascript">
Main.add(function () {
  var tabs = Control.Tabs.create('tab-fusion', false);
});

function setField (field, value, form) {
  field = $(document.forms[form].elements[field]);

  var dateView = $(document.forms[form].elements[field.name+'_da']);
  if (dateView) {
    dateView.value = value;
    $V(field, (value ? Date.fromLocaleDate(value).toDATE() : ''));
    return;
  }
  
  $V(field, value); 
  if (field.fire) {
    field.fire('mask:check');
  }
}

function toggleAltMode(check) {
  $(check.form).select("input[name='_base_object_id']").each(function(e){
    e.disabled = !e.disabled;
  });
}
</script>

{{assign var=object1 value=$patient1}}
{{assign var=object2 value=$patient2}}
{{assign var=object_final value=$finalPatient}}

<h2 class="module {{$m}}">Fusion de patients</h2>

{{if $testMerge}}
<div class="big-warning">
  <strong>La fusion de ces deux patients n'est pas possible</strong> � cause des probl�mes suivants :<br />
  - {{$testMerge}}<br />
  Veuillez corriger ces probl�mes avant toute fusion.
</div>
{{/if}}

<form name="editFrm" action="?m={{$m}}" method="post" onsubmit="return {{if 0 && $testMerge}}false{{else}}checkForm(this){{/if}}">
  <input type="hidden" name="dosql" value="do_patients_fusion" />
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="_merging[]" value="{{$patient1->_id}}" />
  <input type="hidden" name="_merging[]" value="{{$patient2->_id}}" />
  <input type="hidden" name="patient1_id" value="{{$patient1->_id}}" />
  <input type="hidden" name="patient2_id" value="{{$patient2->_id}}" />
  
  <ul id="tab-fusion" class="control_tabs">
    <li><a href="#identite">Identit�</a></li>
    <li><a href="#medical">M�dical</a></li>
    <li><a href="#correspondance">Correspondance</a></li>
    <li><a href="#assure">Assur� social</a></li>
  </ul>
  <hr class="control_tabs" />
  
  <table class="form">
    <tr>
      <th class="category">
        {{if !$alternative_mode}}
        <label style="font-weight: normal;">
          <input type="checkbox" name="_keep_object" value="1" onclick="toggleAltMode(this)" /> Mode de fusion alternatif
        </label>
        {{/if}}
      </th>
      <th width="30%" class="category">
        1er patient
        <br />
        <label style="font-weight: normal;">
          <input type="radio" name="_base_object_id" value="{{$patient1->_id}}" checked="checked" {{if !$alternative_mode}}disabled="disabled"{{/if}} />
          Utiliser comme base [#{{$patient1->_id}}]
        </label>
      </th>
      <th width="30%" class="category">
        2�me patient
        <br />
        <label style="font-weight: normal;">
          <input type="radio" name="_base_object_id" value="{{$patient2->_id}}" {{if !$alternative_mode}}disabled="disabled"{{/if}} />
          Utiliser comme base [#{{$patient2->_id}}]
        </label>
      </th>
      <th width="30%" class="category">R�sultat</th>
    </tr>
    <tbody id="identite" style="display: none;">{{include file="inc_acc/inc_acc_fusion_identite.tpl"}}</tbody>
    <tbody id="medical" style="display: none;">{{include file="inc_acc/inc_acc_fusion_medical.tpl"}}</tbody>
    <tbody id="correspondance" style="display: none;">{{include file="inc_acc/inc_acc_fusion_corresp.tpl"}}</tbody>
    <tbody id="assure" style="display: none;">{{include file="inc_acc/inc_acc_fusion_assure.tpl"}}</tbody>
  </table>

  <div class="button">
    <button type="button" class="search" onclick="MbObject.viewBackRefs('{{$patient1->_class_name}}', ['{{$patient1->_id}}', '{{$patient2->_id}}']);">
      {{tr}}CMbObject-merge-moreinfo{{/tr}}
    </button>
    <button type="submit" class="submit">
      {{tr}}Merge{{/tr}}
    </button>
  </div>
</form>