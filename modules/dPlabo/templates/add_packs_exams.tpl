<script type="text/javascript">

var Catalogue = {
  select : function(iCatalogue_id) {
    var url = new Url;
    url.setModuleAction("system", "httpreq_vw_complete_object");
    url.addParam("object_class" , "CCatalogueLabo");
    url.addParam("object_id"    , iCatalogue_id);
    url.requestUpdate('CatalogueView');
  }
}

function pageMain() {
  PairEffect.initGroup('tree-content', { 
    bStoreInCookie: false,
    bStartVisible: true
  } );
}

</script>

<table class="main">
  <tr>
    <td class="halfPane" rowspan="2">
      {{assign var="catalogue_id" value=0}}
      {{foreach from=$listCatalogues item="_catalogue"}}
      {{include file="tree_catalogues.tpl"}}
      {{/foreach}}
    </td>
    <td class="halfPane" id="CatalogueView">
    </td>
  </tr>
  <tr>
    <td id="PacksView">
    </td>
  </tr>
</table>