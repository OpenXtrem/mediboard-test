{{assign var=operateurs value="-"|implode:$operateur_ids}}

<script type="text/javascript">
PoseDispVasc = {
  operateurs: "{{$operateurs}}",
  onClose: window.loadPosesDispVasc,
  edit: function(id, sejour_id, operation_id){
    var url = new Url("dPplanningOp", "ajax_edit_pose_disp_vasc");
    url.addParam("pose_disp_vasc_id", id);
    url.addParam("sejour_id", sejour_id);
    url.addParam("operation_id", operation_id);
    url.addParam("operateur_ids", PoseDispVasc.operateurs);
    url.requestModal(400, 300);
    url.modalObject.observe("afterClose", PoseDispVasc.onClose);
  },
  create: function(sejour_id, operation_id){
    PoseDispVasc.edit(0, sejour_id, operation_id);
  },
  checkList: function(guid){
    var url = new Url("dPsalleOp", "ajax_edit_object_check_lists");
    url.addParam("object_guid", guid);
    url.addParam("type_group", "disp-vasc");
    url.addParam("validateur_ids", PoseDispVasc.operateurs);
    url.requestModal(900, 600);
    url.modalObject.observe("afterClose", PoseDispVasc.onClose);
  },
  checkListCallback: function(id) {
    PoseDispVasc.checkList("CPoseDispositifVasculaire-"+id);
  }
}
</script>

<button type="button" class="new" onclick="PoseDispVasc.create('{{$sejour_id}}', '{{$operation_id}}')">
  {{tr}}CPoseDispositifVasculaire-title-create{{/tr}}
</button>

<table class="main tbl">
  <tr>
    <th class="narrow"></th>
    <th>{{mb_title class=CPoseDispositifVasculaire field=date}}</th>
    <th>{{mb_title class=CPoseDispositifVasculaire field=urgence}}</th>
    <th>{{mb_title class=CPoseDispositifVasculaire field=lieu}}</th>
    <th>{{mb_title class=CPoseDispositifVasculaire field=type_materiel}}</th>
    <th>{{mb_title class=CPoseDispositifVasculaire field=operateur_id}}</th>
    <th>{{mb_title class=CPoseDispositifVasculaire field=voie_abord_vasc}}</th>
  </tr>
  
  {{foreach from=$poses item=_pose}}
    <tr>
      <td>
        <button type="button" class="edit notext" onclick="PoseDispVasc.edit({{$_pose->_id}}, '{{$sejour_id}}', '{{$operation_id}}')">
          {{tr}}Edit{{/tr}}
        </button>
        <button type="button" class="tick notext" onclick="PoseDispVasc.checkList('{{$_pose->_guid}}')">{{tr}}CDailyCheckList{{/tr}}</button>
      </td>
      <td>{{mb_value object=$_pose field=date}}</td>
      <td {{if $_pose->urgence}} class="warning" {{/if}}>{{mb_value object=$_pose field=urgence}}</td>
      <td>{{mb_value object=$_pose field=lieu}}</td>
      <td>{{mb_value object=$_pose field=type_materiel}}</td>
      <td class="text">
        {{mb_value object=$_pose field=operateur_id}}
        {{if $_pose->encadrant_id}}
        (encadr� par {{mb_value object=$_pose field=encadrant_id}})
        {{/if}}
      </td>
      <td>{{mb_value object=$_pose field=voie_abord_vasc}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="7" class="empty">{{tr}}CPoseDispositifVasculaire.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
