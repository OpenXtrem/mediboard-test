{{if $perm_edit}}
  <form action="?" method="post" name="editLineALD-{{$line->_class_name}}-{{$line->_id}}">
     <input type="hidden" name="m" value="dPprescription" />
     <input type="hidden" name="dosql" value="{{$dosql}}" />
     <input type="hidden" name="{{$line->_tbl_key}}" value="{{$line->_id}}" />
     <input type="hidden" name="del" value="0" />
     {{mb_field object=$line field="ald" typeEnum="checkbox" onchange="submitFormAjax(this.form, 'systemMsg');"}}
     {{mb_label object=$line field="ald" typeEnum="checkbox"}}
  </form>
{{else}}
  {{mb_label object=$line field="ald" typeEnum="checkbox"}}:
  {{if $line->ald}}
    Oui
  {{else}}
    Non
  {{/if}} 
{{/if}}	        