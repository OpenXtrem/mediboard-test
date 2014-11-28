{{*
 * $Id$
 *
 * @category Files
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision$
 * @link     http://www.mediboard.org
*}}

{{if !$app->user_prefs.show_file_view}}
  {{mb_return}}
{{/if}}

<script>
  FilesCategory.addObjectGuid('{{$object->_guid}}');
</script>

<div id="{{$object->_guid}}_check_category" style="position: relative; width:20px; height:20px; display: none"
     onclick="FilesCategory.openInfoReadFilesGuid('{{$object->_guid}}');">
  <img src="modules/dPfiles/images/icon.png" style="height:20px; cursor: pointer;" alt="" />

  <span style="display:block; cursor: pointer; width:8px; height:8px; font-size: 8px; text-align: center;
    position: absolute; top:0; right:0; background-color: red; color:white; border-radius: 100%; padding:2px;"></span>
</div>
