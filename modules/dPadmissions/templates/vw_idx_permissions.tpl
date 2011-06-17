{{* $Id: vw_idx_admission.tpl 11880 2011-04-15 09:35:38Z rhum1 $ *}}

{{*
 * @package Mediboard
 * @subpackage dPadmissions
 * @version $Revision: 11880 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}
* 
{{mb_script module=dPadmissions script=admissions}}

<script type="text/javascript">

function showLegend() {
  var url = new Url("dPadmissions", "vw_legende");
  url.popup(300, 170, "Legende");
}

function reloadFullPermissions(filterFunction) {
  var oForm = getForm("selType");
  var url = new Url("dPadmissions", "httpreq_vw_all_permissions");
  url.addParam("date"      , "{{$date}}");
  url.addParam("type"      , $V(oForm._type_admission));
  url.addParam("service_id", $V(oForm.service_id));
  url.requestUpdate('allAdmissions');
	reloadAdmission(filterFunction);
}

function reloadPermission(filterFunction) {
  var oForm = getForm("selType");
  var url = new Url("dPadmissions", "httpreq_vw_permissions");
  url.addParam("date"      , "{{$date}}");
  url.addParam("type"      , $V(oForm._type_admission));
  url.addParam("service_id", $V(oForm.service_id));
	if(!Object.isUndefined(filterFunction)){
	  url.addParam("filterFunction", filterFunction);
	}
  url.requestUpdate('listPermissions');
}

Main.add(function () {
  var totalUpdater = new Url("dPadmissions", "httpreq_vw_all_permissions");
  totalUpdater.addParam("date", "{{$date}}");
  totalUpdater.periodicalUpdate('allPermissions', { frequency: 120 });
  
  var listUpdater = new Url("dPadmissions", "httpreq_vw_permissions");
  listUpdater.addParam("date", "{{$date}}");
  listUpdater.periodicalUpdate('listPermissions', { frequency: 120 });
});

</script>

<table class="main">
<tr>
  <td>
    <a href="#" onclick="showLegend()" class="button search">L�gende</a>
  </td>
</tr>
  <tr>
    <td id="allPermissions" style="width: 250px">
    </td>
    <td id="listPermissions" style="width: 100%">
    </td>
  </tr>
</table>