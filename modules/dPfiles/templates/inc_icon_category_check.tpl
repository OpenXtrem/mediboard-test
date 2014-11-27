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
  Main.add(function() {
    FilesCategory.iconInfoReadFilesGuid('{{$object->_guid}}');
  });
</script>

<div id="{{$object->_guid}}_check_category" {{*style="display:none;"*}}>
</div>