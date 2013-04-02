{{* $Id$ *}}

{{mb_script module=pmsi script=PMSI ajax=true}}
{{mb_default var=confirmCloture value=0}}
{{mb_default var=NDA value=""}}
{{mb_default var=IPP value=""}}

<script>
  PMSI.confirmCloture = {{$confirmCloture}};
</script>

{{if !$conf.sa.send_only_with_ipp_nda || ($IPP && $NDA)}}
<div id="export_{{$object->_class}}_{{$object->_id}}">
  {{if $object->facture}}
    {{if $canUnlockActes}}
    <button class="cancel " onclick="PMSI.deverouilleDossier('{{$object->_id}}', '{{$object->_class}}', '{{$m}}')">
      D�verrouiller le dossier
    </button>
    {{else}}
    <div class="small-info">
      Veuillez contacter le PMSI pour d�verrouiller le dossier
    </div>
    {{/if}}
  {{else}}
    <button class="tick singleclick"
     onclick="{{if $object instanceof COperation && $conf.dPsalleOp.CActeCCAM.del_actes_non_cotes}}
         PMSI.checkActivites('{{$object->_id}}', '{{$object->_class}}', null, '{{$m}}');
       {{else}}
         PMSI.exportActes('{{$object->_id}}', '{{$object->_class}}', null, '{{$m}}');
       {{/if}}">
      {{if $object->_class == "CSejour"}}
        Export des diagnostics et actes du s�jour
      {{else}}
        Export des actes de l'intervention
      {{/if}}
    </button>
  {{/if}}
  
  <div class="text">
    {{if $object->_nb_exchanges}}
      <div class="small-success">
        Export d�j� effectu� {{$object->_nb_exchanges}} fois
      </div>
    {{else}}
      <div class="small-info">
        Pas d'export effectu�
      </div>
    {{/if}}
  </div>
</div>
{{else}}
<div class="small-warning">
  Vous ne pouvez pas exporter les actes pour les raisons suivantes :
  <ul>
    {{if !$NDA}}
    <li>Numero de dossier manquant</li>
    {{/if}}
    {{if !$IPP}}
    <li>IPP manquant</li>
    {{/if}}
  </ul>
</div>
{{/if}}
