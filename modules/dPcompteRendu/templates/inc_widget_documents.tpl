{{* $id: $
  * @param $object CMbObject Target Object for documents
  * @param $modelesByOwner array|CCompteRendu sorted by owner
  * @param $packs array|CPack  List of packs
  * @param $praticien CMediuser Owner of modeles
  *}}

{{mb_script module=dPhospi script=modele_etiquette ajax=true}}  
{{assign var=object_class value=$object->_class}}
{{assign var=object_id value=$object->_id}}
{{unique_id var=unique_id}}

{{if $nb_modeles_etiquettes}}
<form name="download_etiq_{{$object->_class}}_{{$object->_id}}" style="display: none;" action="?" target="_blank" method="get" class="prepared">
  <input type="hidden" name="m" value="dPhospi" />
  <input type="hidden" name="a" value="print_etiquettes" />
  <input type="hidden" name="object_class" value="{{$object->_class}}" />
  <input type="hidden" name="object_id" value="{{$object->_id}}" />
  <input type="hidden" name="suppressHeaders" value="1" />
  <input type="hidden" name="dialog" value="1" />
</form>
{{/if}}

<form name="DocumentAdd-{{$unique_id}}-{{$object->_guid}}" action="?m={{$m}}" method="post" class="prepared">
<input type="text" value="&mdash; Mod�le" name="keywords_modele" class="autocomplete str" autocomplete="off" onclick="this.value = ''; this.onclick=null;" style="width: 5em;" />
<input type="text" value="&mdash; Pack" name="keywords_pack" class="autocomplete str" autocomplete="off" onclick="this.value = ''; this.onclick=null;" style="width: 4em;"/>

<script type="text/javascript">

Main.add(function() {
  var form = getForm('DocumentAdd-{{$unique_id}}-{{$object->_guid}}');
  var url;
  
  url = new Url("dPcompteRendu", "ajax_modele_autocomplete");
  url.addParam("user_id", "{{$praticien->_id}}");
  url.addParam("function_id", "{{$praticien->function_id}}");
  url.addParam("object_class", '{{$object_class}}');
  url.addParam("object_id", '{{$object_id}}');
  url.autoComplete(form.keywords_modele, '', {
    minChars: 2,
    afterUpdateElement: createDoc,
    dropdown: true,
    width: "250px"
  });

  url = new Url("dPcompteRendu", "ajax_pack_autocomplete");
  url.addParam("user_id", "{{$praticien->_id}}");
  url.addParam("function_id", "{{$praticien->function_id}}");
  url.addParam("object_class", '{{$object_class}}');
  url.addParam("object_id", '{{$object_id}}');
  url.autoComplete(form.keywords_pack, '', {
    minChars: 2,
    afterUpdateElement: createPack,
    dropdown: true,
    width: "250px"
  });
  
  ModeleEtiquette.nb_printers  = {{$nb_printers|@json}};
  
  function createDoc(input, selected) {
    var id = selected.down(".id").innerHTML;
    var object_class = null;

    if (id == 0) {
      object_class = '{{$object->_class}}';
    }
    
    if (selected.select(".fast_edit").length) {
      Document.fastMode('{{$object_class}}', id, '{{$object_id}}', null, null, '{{$unique_id}}');
    } else {
      Document.create(id, '{{$object_id}}', null, object_class, null);
    }
    
    $V(input, '');
  }

  function createPack(input, selected) {
    if (selected.select(".fast_edit").length) {
      Document.fastModePack(selected.down(".id").innerHTML, '{{$object_id}}');
    }
    else {
      Document.createPack(selected.down(".id").innerHTML, '{{$object_id}}');
    }
    $V(input, '');
  } 
});

//Cr�ation via ModeleSelector
modeleSelector[{{$object_id}}] = new ModeleSelector("DocumentAdd-{{$unique_id}}-{{$object->_guid}}", null, "_modele_id", "_object_id");
</script>

<button type="button" class="search notext" onclick="modeleSelector[{{$object_id}}].pop('{{$object_id}}','{{$object_class}}','{{$praticien->_id}}')">
  {{if $praticien->_can->edit}}
  Tous
  {{else}}
  Mod�les disponibles
  {{/if}}
</button>

<!-- Impression de tous les mod�les disponibles pour l'objet -->
<button type="button" class="print notext" onclick="Document.printSelDocs('{{$object_id}}', '{{$object_class}}');">
  {{tr}}Print{{/tr}}
</button>

{{if $nb_modeles_etiquettes}}
  <button type="button" class="modele_etiquette" onclick="ModeleEtiquette.print('{{$object_class}}', '{{$object_id}}')">Etiquettes</button>
{{/if}}

<input type="hidden" name="_modele_id" value="" />
<input type="hidden" name="_object_id" value="" onchange="Document.create(this.form._modele_id.value, this.value,'{{$object_id}}','{{$object_class}}'); $V(this, ''); $V(this.form._modele_id, ''); "/>

</form>

<table class="form" id="docs_{{$object_class}}{{$object_id}}">
  {{mb_include module="dPcompteRendu" template="inc_widget_list_documents"}}
</table>