{{* $Id: configure.tpl 6341 2009-05-21 11:52:48Z mytto $ *}}

{{*
 * @package Mediboard
 * @subpackage dPurgences
 * @version $Revision: 6341 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

<table class="main">
  <tr>
    <th class="title">{{mb_title object=$extractPassages field="message"}}</th>
  </tr>
  <tr>
    <td>{{mb_value object=$extractPassages field="message"}}</td>
  </tr>
</table>