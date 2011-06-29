{{* $Id: vw_idx_dispensation.tpl 9660 2010-07-27 11:59:39Z phenxdesign $ *}}

{{*
 * @package Mediboard
 * @subpackage pharmacie
 * @version $Revision: 9660 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

<button class="print not-printable" onclick="window.print()">{{tr}}Print{{/tr}}</button>

{{if $date && $show_stock_quantity}}
  <h3>Etat des stocks le {{$date|date_format:$conf.datetime}}</h3>
{{/if}}

<table class="main tbl">
  <thead>
    <tr>
      <th class="narrow">{{tr}}CProduct-code{{/tr}}</th>
      <th>{{tr}}CProduct{{/tr}}</th>
      <th style="width: 50%">{{tr}}CProductStockGroup-quantity{{/tr}}</th>
    </tr>
  </thead>
  
  {{foreach from=$list_products item=_product}}
    <tr>
      <td>{{$_product->code}}</td>
      <td>{{$_product}}</td>
      <td>
        {{if $show_stock_quantity}}{{$_product->_ref_stock_group->quantity}}{{/if}}
      </td>
    </tr>
  {{/foreach}}
</table>