{{* $Id: inc_main_courante.tpl 8582 2010-04-15 20:53:09Z MyttO $ *}}

{{*
 * @package Mediboard
 * @subpackage dPurgences
 * @version $Revision: 8582 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

<td class="warning">
  <span onmouseover="ObjectTooltip.createEx(this, 'CSejour-{{$rpu->mutation_sejour_id}}')">
    <strong>Dossier de Mutation</strong>
    <br/>
    {{mb_include module=dPplanningOp template=inc_vw_numdos num_dossier=$rpu->_ref_sejour_mutation->_num_dossier}}
  </span> 
</td>
