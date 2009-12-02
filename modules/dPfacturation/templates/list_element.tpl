{{* $Id$ *}}

{{*
 * @package Mediboard
 * @subpackage dPfacturation
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

<table class="tbl">
	<tr>
	  <th class="title" colspan="100">
	    {{mb_include module=system template=inc_object_notes object=$facture}}
	    Elements(s) correspondant(s)
	  </th>
	</tr>
	<tr>
	   <th>Element</th>
	   <th>Prix H.T</th>
	   <th>Taxe</th>
	   <th>Prix T.T.C</th>
	</tr>
	{{foreach from=$facture->_ref_items item=_item}}
	  <tr>
	    <td class="text">
	    	<a href="?m=dPfacturation&amp;tab=vw_idx_factureitem&amp;facture_id={{$_item->facture_id}}&amp;factureitem_id={{$_item->factureitem_id}}" title="Modifier l'element">
              {{$_item->libelle}}
            </a>
        </td>
	    <td>{{mb_value object=$_item field="prix_ht"}}</td>
	    <td>{{mb_value object=$_item field="taxe"}}</td>
	    <td>{{mb_value object=$_item field="_ttc"}}</td>
	  </tr>
	{{foreachelse}}
	  <tr>
	   	<td class="button" colspan="4">Aucun �l�ment trouv�</td>
	  </tr>
	{{/foreach}}
	  <tr>
	     <th colspan="3">TOTAL</th>
		 <td>{{mb_value object=$facture field="_total"}}</td>
	  </tr>       
</table>