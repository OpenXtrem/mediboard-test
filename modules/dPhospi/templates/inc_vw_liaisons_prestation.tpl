{{*
  * Affiche le souhait versus le r�alis� pour les prestations journali�res
  *  
  * @category 
  * @package  Mediboard
  * @author   SARL OpenXtrem <dev@openxtrem.com>
  * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
  * @version  SVN: $Id:$ 
  * @link     http://www.mediboard.org
*}}

{{foreach from=$liaisons item=liaison}}
  {{assign var=item_presta value=$liaison->_ref_item}}
  {{assign var=item_presta_realise value=$liaison->_ref_item_realise}}
  <strong title="{{if $item_presta->_id}}{{tr}}CItemLiaison-item_souhait_id{{/tr}} [{{$item_presta->nom}}]{{/if}} - {{if $item_presta_realise->_id}}{{tr}}CItemLiaison-item_realise_id{{/tr}} [{{$item_presta_realise->nom}}]{{/if}}"
    {{if $item_presta->_id && $item_presta_realise->_id}}
      class="{{if $item_presta->rank == $item_presta_realise->rank}}
               item_egal
             {{elseif $item_presta->rank > $item_presta_realise->rank}}
               item_inferior
             {{else}}
               item_superior
             {{/if}}"
    {{/if}}
      style="border: 2px solid #{{if $item_presta_realise->_id}}{{$item_presta_realise->color}}{{else}}{{$item_presta->color}}{{/if}}; margin-right: 1px;">

    <!-- display -->
    {{if $item_presta->_id == $item_presta_realise->_id || $conf.dPhospi.show_souhait_placement}}
      {{$item_presta->nom}}
    {{else}}
      {{if $item_presta_realise->nom}}{{$item_presta_realise->nom}}{{else}}{{$item_presta->nom}}{{/if}}
    {{/if}}

  </strong>
{{/foreach}}