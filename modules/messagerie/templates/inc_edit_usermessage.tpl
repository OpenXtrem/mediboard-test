{{*
 * $Id$
 *  
 * @category Messagerie
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision$
 * @link     http://www.mediboard.org
*}}

<style>
  #list_dest li button {
    border:none;
    padding:0;
    background: url('style/mediboard/images/buttons/delete-tiny.png') transparent no-repeat;
    width: 11px;
    height:11px;
  }
  #list_dest li {
    list-style: none;
  }
</style>

<script>
  {{if $usermessage->_can_edit}}
    Main.add(function() {
      var form = getForm("edit_usermessage");
      var element = form.elements._to_autocomplete_view;
      var url = new Url("system", "ajax_seek_autocomplete");
      url.addParam("object_class", "CMediusers");
      url.addParam("input_field", element.name);
      url.addParam("show_view", true);
      url.autoComplete(element, null, {
        minChars: 3,
        method: "get",
        select: "view",
        dropdown: true,
        afterUpdateElement: function(field,selected){
          var id = selected.getAttribute("data-id");
          var name = selected.down('span.view').innerHTML;
          addDest(id, name);
          $V(element, '');
        }
      });
    });
  {{/if}}

  addDest = function(id, name) {
    var dest_list = $('list_dest');
    dest_list.insert('<li id="dest_'+id+'">'+name+'<input type="hidden" name="dest[]" value="'+id+'"/><button class="delete notext" type="button" style="display: inline;" onclick="removeDest(\''+id+'\');"></button></li>');
  };

  removeDest = function(id) {
    $('dest_'+id).remove();
  };

  sendMessage = function(oform) {
    if (confirm("envoyer le message ?")) {
      $V(oform._send, 1);
      oform.submit();
      window.parent.Control.Modal.close();
      UserMessage.refreshListCallback();
    }
  };

</script>

<form method="post" name="edit_usermessage" onsubmit="return checkForm(this)">
  <input type="hidden" name="m" value="messagerie"/>
  <input type="hidden" name="dosql" value="do_usermessage_aed"/>
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="_send" value="0" />
  <input type="hidden" name="_archive" value="0" />
  <input type="hidden" name="_readonly" value="{{if $usermessage->_can_edit}}0{{else}}1{{/if}}" />
  <input type="hidden" name="usermessage_id" value="{{$usermessage->_id}}" />
  <input type="hidden" name="in_reply_to" value="{{$usermessage->in_reply_to}}" />

  <table class="main">
    <tr>
      <td id="message_area" style="width:75%;">
        <table class="form">
          <tr>
            <th class="narrow">{{mb_label object=$usermessage field=creator_id}}</th>
            <td>
              {{mb_field object=$usermessage field=creator_id hidden=1}}
              <div class="mediuser" style="border-color: #{{$usermessage->_ref_user_creator->_ref_function->color}};">
                {{$usermessage->_ref_user_creator}}
              </div>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$usermessage field=subject}}</th>
            <td>
              {{if !$usermessage->_can_edit}}
                {{mb_value object=$usermessage field=subject}}
              {{else}}
                {{mb_field object=$usermessage field=subject size=60}}
              {{/if}}
            </td>
          </tr>
          <tr>
            <td colspan="2" style="height: 300px">{{mb_field object=$usermessage field=content id="htmlarea"}}</td>
          </tr>
        </table>
      </td>
      <td id="dest_area">
        <h2>Destinataires</h2>
        {{if $usermessage->_can_edit}}
          <input type="text" name="_to_autocomplete_view" />
        {{/if}}
        <ul id="list_dest">
        {{foreach from=$usermessage->_ref_destinataires item=_dest}}
          <li id="dest_{{$_dest->_ref_user_to->_id}}">
            <span class="mediuser" style="border-color: #{{$_dest->_ref_user_to->_ref_function->color}};">
              {{$_dest->_ref_user_to}} {{if $_dest->datetime_read}}(lu){{/if}}
            </span>

            {{if $usermessage->_can_edit}}
              <input type="hidden" name="dest[]" value="{{$_dest->_ref_user_to->_id}}"/>
              <button class="delete notext" type="button" style="display: inline;" onclick="removeDest('{{$_dest->_ref_user_to->_id}}');"></button>
            {{/if}}
          </li>
        {{/foreach}}
        </ul>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        {{if $usermessage->_can_edit}}
          <button class="send" type="button" onclick="sendMessage(this.form);">{{tr}}Send{{/tr}}</button>
          <button class="save" type="button" onclick="this.form.submit();window.parent.Control.Modal.close();">{{tr}}Save{{/tr}}</button>
          {{if $usermessage->_id}}<button class="trash" onclick="$V(this.form.del, 1); window.parent.Control.Modal.close();">{{tr}}Delete{{/tr}}</button>{{/if}}
        {{else}}
           {{*
            {{if $usermessage->_ref_dest_user->archived}}
              <button onclick="$V(this.form._archive, 0); window.parent.Control.Modal.close();">
                <img src="modules/messagerie/images/mail_archive_cancel.png" alt=""/>{{tr}}ToInbox{{/tr}}
              </button>
            {{else}}
              {{if $usermessage->_ref_dest_user->from_user_id != $usermessage->_ref_dest_user->to_user_id && $usermessage->_ref_dest_user->to_user_id != $usermessage->creator_id}}
                <button onclick="$V(this.form._archive, 1); window.parent.Control.Modal.close();">
                  <img src="modules/messagerie/images/mail_archive.png" alt=""/>{{tr}}Archive{{/tr}}
                </button>
              {{/if}}
            {{/if}}
            *}}
            <button class="send" type="button" onclick="window.parent.Control.Modal.close(); window.parent.UserMessage.create('{{$usermessage->creator_id}}', '{{$usermessage->_id}}');">{{tr}}CUserMail-button-answer{{/tr}}</button>
        {{/if}}
        <button class="cancel" type="button" onclick="window.parent.Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>