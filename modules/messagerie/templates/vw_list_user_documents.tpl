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

{{mb_default var=ref_module value=bioserveur}}


{{mb_script module=dPfiles script=files}}
{{assign var=_script value=$ref_module|ucfirst}}
{{mb_script module=$ref_module script=$ref_module}}

<script>
  var last_account_id    = "";
  var last_mode_calendar = "unlinked";
  var last_start         = 0;

  /**
   * used to edit account
   */
  edit_account = function(account_id) {
    {{$_script}}.editModal(account_id);
  };

  /**
   * ajax refresh list
   */
  listDocuments = function(account_id, mode, page_start) {
    page_start = page_start ? page_start : 0;
    var url = new Url("messagerie", "ajax_list_external_document");
    if (account_id) {
      last_account_id = account_id;
    }
    if (mode) {
      last_mode_calendar = mode;
    }
    if (page_start && page_start != last_start) {
      last_start = page_start;
    }

    url.addParam("start", last_start);
    url.addParam("mode", last_mode_calendar);
    url.addParam("account_id", last_account_id);
    url.addParam("class", {{$_script}}._class);
    url.requestUpdate("list_document");
  };

  /**
   * used to do multiple actions on the list
   *
   * @param type
   */
  do_multi_action = function(type) {
    if (type == "delete") {
      if (!confirm("�tes vous sur de vouloir supprimer les documents s�lectionn�s ?")) {
        return;
      }
    }

    var ids = [];
    $$('#list_document input[class="input_doc"]:checked').each(function(data) {
      ids.push(data.get('object_guid'));
    });

    var url = new Url("messagerie", "do_document_multi_action", "dosql");
    url.addParam("type", type);
    url.addParam("document", ids.length > 0 ? ids.join() : '');
    url.requestUpdate("systemMsg", {method: "post", onComplete:listDocuments});
  };

  /**
   * select all items in the list
   *
   * @param input
   */
  selectAll = function(input) {
    $$('#list_document input[class="input_doc"]').each(function(elt){
      elt.checked = input.checked;
    });
  };

  /**
   * pop the link window
   *
   * @param document_guid
   */
  linkDocument = function(document_guid) {
    var url = new Url("messagerie", "ajax_do_move_file_bioserveur");
    url.addParam("document_guid", document_guid);
    url.requestModal(-40, -40);
  };
</script>

{{if !$users|@count}}
  <p class="empty">{{tr}}CMediusers.none{{/tr}}</p>
{{else}}
  <script>
    Main.add(function() {
      var tabs = Control.Tabs.create("account_list", true);
      tabs.activeLink.onmousedown();
    });
  </script>
  <table class="main">
    <tr>
      <td style="vertical-align: top; width: 15%">
        <ul class="control_tabs_vertical" id="account_list">
          {{foreach from=$users item=_user}}
            <li>
              <span style="float:left; margin:5px;">
                <button class="edit notext" onclick="edit_account('{{$_user->_id}}')">{{tr}}Edit{{/tr}}</button>
                {{if $ref_module == "bioserveur"}}
                  <button class="change notext" onclick="Bioserveur.updateAccount('{{$_user->_id}}')">Mettre � jour</button>
                {{/if}}
              </span>

              <a href="#list_document"
                 style="font-weight: normal; "  onmousedown="listDocuments('{{$_user->_id}}');">
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_user->_guid}}')">{{$_user}}<br/>
                   <span id="count_doc_{{$_user->_id}}" style="padding:0 3px; font-weight: bold;">

                   </span>
                </span>
              </a>
            </li>
          {{/foreach}}
        </ul>
      </td>
      <td id="list_document" style="width: 85%">
      </td>
    </tr>
  </table>
{{/if}}