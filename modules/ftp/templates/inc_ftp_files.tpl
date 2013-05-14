{{* $Id: configure_ftp.tpl 6239 2009-05-07 10:26:49Z phenxdesign $ *}}

{{*
 * @package Mediboard
 * @subpackage system
 * @version $Revision: 6239 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

<table class="main">
  <tr>
    <td>
      <table class="tbl">
        {{if is_array($files)}}
          <tr>
            <th> Liste des fichiers du dossier </th>
          </tr>
          {{foreach from=$files item=_file}}
          <tr>
            <td class="text">
              {{if $_file.size === "-1"}}
                <img src="modules/ftp/images/directory.png"/>
              {{else}}
                <a target="blank"
                   href="?m=system&a=download_file&filename={{$_file.path}}&exchange_source_guid={{$exchange_source->_guid}}&dialog=1&suppressHeaders=1"
                   class="button download notext">
                    {{tr}}Download{{/tr}}
                </a>
              {{/if}}
              {{$_file.path}}
            </td>
          </tr>
          {{/foreach}}
        {{else}}
          <tr>
            <th>Impossible de lister les fichiers</th>
          </tr>
        {{/if}}
      </table>
    </td>
  </tr>
</table>