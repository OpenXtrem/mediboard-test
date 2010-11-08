{{* $Id$ *}}

{{*
 * @package Mediboard
 * @subpackage dPhospi
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

<script type="text/javascript">
  Main.add(Control.Tabs.create.curry('tabs-configure', true));
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#config-dPhospi">{{tr}}config-dPhospi{{/tr}}</a></li>
  <li><a href="#CService">{{tr}}CService{{/tr}}</a></li>
  <li><a href="#config-synchro_sejour_affectation">{{tr}}config-synchro_sejour_affectation{{/tr}}</a></li>
</ul>

<hr class="control_tabs" />

<div id="config-dPhospi" style="display: none;">
  {{mb_include template=inc_config_dPhospi}}
</div>

<div id="CService" style="display: none;">
  {{mb_include template=CService_config}}
</div>

<div id="config-synchro_sejour_affectation" style="display: none;">
  {{mb_include template=inc_config_synchro_sejour_affectation}}
</div>