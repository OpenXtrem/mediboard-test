<script type="text/javascript">

function editDocument(compte_rendu_id) {
  var url = new Url();
  url.setModuleAction("dPcompteRendu", "edit_compte_rendu");
  url.addParam("compte_rendu_id", compte_rendu_id);
  url.popup(700, 700, "Document");
}

function createDocument(modele_id, operation_id) {
  var url = new Url();
  url.setModuleAction("dPcompteRendu", "edit_compte_rendu");
  url.addParam("modele_id", modele_id);
  url.addParam("object_id", operation_id);
  url.popup(700, 700, "Document");
}

function reloadAfterSaveDoc(){
  updateListOperations();
  updateListHospi("sortie");
}

function updateListConsults() {
  var url = new Url;
  url.setModuleAction("dPcabinet", "httpreq_vw_list_consult");

  url.addParam("chirSel"   , "{{$app->user_id}}");
  url.addParam("date"      , "{{$date}}");
  url.addParam("vue2"      , "{{$vue}}");
  url.addParam("selConsult", "");
  url.addParam("board"     , "1");

  url.requestUpdate('consultations');
}

function updateListOperations() {
  var url = new Url;
  url.setModuleAction("dPplanningOp", "httpreq_vw_list_operations");

  url.addParam("chirSel" , "{{$app->user_id}}");
  url.addParam("date"    , "{{$date}}");
  url.addParam("urgences", "0");
  url.addParam("board"   , "1");

  url.requestUpdate('operations');
}

function updateListHospi(typeHospi) {
  var url = new Url;
  url.setModuleAction("dPboard", "httpreq_vw_hospi");

  url.addParam("chirSel" , "{{$app->user_id}}");
  url.addParam("date"    , "{{$date}}");
  url.addParam("typeHospi", typeHospi);
  url.addParam("board"   , "1");

  url.requestUpdate(typeHospi);
}

function pageMain() {
  updateListConsults();
  updateListOperations();
  updateListHospi("entree");
  updateListHospi("sortie");
  regRedirectPopupCal("{{$date}}", "index.php?m={{$m}}&tab={{$tab}}&date=");
}

</script>

<table class="main">
  <tr>
    <th colspan="2">
      <form name="editFrmPratDate" action="?m={{$m}}" method="get">
      <input type="hidden" name="m" value="{{$m}}" />
      {{$date|date_format:"%A %d %B %Y"}}
      <img id="changeDate" src="./images/calendar.gif" title="Choisir la date" alt="calendar" />
      </form>
    </th>
  </tr>
  <tr>
    <td class="halfPane">
      <div style="overflow: auto; height: 250px;" id="consultations">
      </div>
    </td>
    <td class="halfPane">
      <div style="overflow: auto; height: 250px;" id="operations">
      </div>
    </td>
  </tr>
  <tr>
    <td>
      <div style="overflow: auto; height: 250px;" id="entree">
      </div>
    </td>
    <td>
      <div style="overflow: auto; height: 250px;" id="sortie">
      </div>
    </td>
  </tr>
</table>