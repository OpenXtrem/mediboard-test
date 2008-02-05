<script type="text/javascript">

Prescriptions = {
  collapse: function() {
    Element.hide.apply(null, $$("tbody.prescriptionEffect"));
  },
  
  expand: function() {
    Element.show.apply(null, $$("tbody.prescriptionEffect"));
  },
  
  initEffect: function(pack) {
    new PairEffect(pack);
  }
}

Object.extend(Droppables, {
  addPrescription: function(prescription_id) {
    var oDragOptions = {
      onDrop: function(element) {
        Prescription.Examen.drop(element.id, prescription_id)
      }, 
      hoverclass:'selected'
    }
    
    this.add('drop-listprescriptions-' + prescription_id,  oDragOptions);
  }
} );
  
</script>

{{if $prescription->_id}}
<table class="tbl" id="drop-listprescriptions-{{$prescription->_id}}">
  <tr>
    <th class="title" colspan="100">
      <a style="float:right;" href="#nothing" onclick="view_log('{{$prescription->_class_name}}', {{$prescription->_id}})">
        <img src="images/icons/history.gif" alt="historique" title="Voir l'historique" />
      </a>
      {{$prescription->_view}}
      <script type="text/javascript">
        Droppables.addPrescription({{$prescription->_id}});
      </script>
    </th>
  </tr>
  <tr>
    <th>Analyse</th>
    <th>Unit�</th>
    <th>R�f�rences</th>
    <th>Resultat</th>
    <th>Int</th>
    <th>Ext</th>
  </tr>
  
  <!-- Affichage des prescriptions sous forme de packs -->
  {{foreach from=$tab_pack_prescription item="pack" key="key"}}
  <tr id="{{$key}}-trigger">
    <th colspan="6">
      <!-- Affichage du nom du pack en passant par la premiere analyse -->
      {{$pack[0]->_ref_pack->_view}}   
    </th>
  </tr>
  
  <tbody class="prescriptionEffect" id="{{$key}}">
  <tr class="script"><td><script type="text/javascript">Prescriptions.initEffect("{{$key}}");</script></td></tr>
  {{foreach from=$pack item="_item"}}
    {{assign var="curr_examen" value=$_item->_ref_examen_labo}}
    {{include file="inc_view_analyse.tpl"}}
  {{/foreach}}
  </tbody>
 {{/foreach}}
 
 
  <!-- Affichage des autres analyses -->
  {{if $tab_pack_prescription && $tab_prescription}}
  <tr>
    <th colspan="6">Autres analyses</th>
  </tr>
  {{/if}}
  {{foreach from=$tab_prescription item="_item" key="key"}}
    {{assign var="curr_examen" value=$_item->_ref_examen_labo}}
    {{include file="inc_view_analyse.tpl"}}  
  {{/foreach}} 
   
</table>
{{/if}}