{{*
 * $Id$
 * 
 * @package    Mediboard
 * @subpackage reservation
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 *}}

{{mb_script module=ssr script=planning ajax=1}}

<script type="text/javascript">
  Main.add(function() {
    ViewPort.SetAvlHeight("planningInterventions", 1);
      $('planningWeek').style.height = "1500px";
    $$(".body").each(function(elt) {
      elt.setStyle({backgroundColor: elt.up().getStyle("backgroundColor")});
    });
  });
</script>

<style type="text/css">
  .planning .event .body{
    color: #000 !important;
    line-height: 120% !important;
  }
</style>
<div id="planningInterventions">
  {{mb_include module=ssr template=inc_vw_week}}
</div>

<script type="text/javascript">
  Main.add(function() {
    var planning = window["planning-{{$planning->guid}}"];
    planning.salles_ids = {{$salles_ids|@json}};
    
    planning.onMenuClick = function(event, object_id, elem) {

      window.cut_operation_id = null;
      window.copy_operation_id = null;
      
      switch (event) {
        case 'list':
          planningInter(object_id);
          break;


        case 'edit':
          // Commentaire
          if (elem.up().up().hasClassName("commentaire_planning")) {
            modifCommentaire(null, null, null, object_id, false, "Control.Modal.close");
          }

          else if (elem.up().up().hasClassName("plage_planning")) {
            modifPlage(object_id, '{{$date_planning}}');
          }
          // DHE
          else {
            if (elem.up().up().hasClassName("operation_enplage")) {
              modifIntervention('', '', '', object_id,'', true);
            }
            else if (elem.up().up().hasClassName("operation_horsplage")) {
              modifIntervention('', '', '', object_id,'', false);
            }
            else {
              modifIntervention('', '', '', '','', false);
            }
          }
          break;
        case 'cut':
        case 'copy':
          if (elem.up().up().hasClassName("commentaire_planning")) {
            modifCommentaire(null, null, null, object_id, true,  "Control.Modal.close");
            break;
          }

          window.cut_operation_id = null;
          window.copy_operation_id = null;
          
          if (window.save_elem && window.save_elem != elem) {
            window.save_elem.removeClassName("opacity-50");
          }
          
          if (elem.hasClassName("opacity-50")) {
            elem.removeClassName("opacity-50");
            window.save_elem = null;
          }
          else {
            elem.addClassName("opacity-50");
            if (event == "cut") {
              window.cut_operation_id = object_id;
            }
            else {
              window.copy_operation_id = object_id;
            }
            var span_infos = elem.up('div.toolbar').next('div.body').down('span').down('span');
            window.save_entree_prevue = span_infos.get("entree_prevue");
            window.save_sortie_prevue = span_infos.get("sortie_prevue");
            window.save_sejour_id     = span_infos.get("sejour_id");
            window.save_chir_id       = span_infos.get("chir_id");
            window.save_duree         = span_infos.get("duree");
            window.save_elem = elem;
          }
          updateStatusCut();
          break;
        case 'clock':
          modifSejour(object_id, null, null, null, null, null, "Control.Modal.close");
      }
    }
    
    planning.onEventChange = function(e) {
      var object_guid = e.draggable_guid;
      var object_id = object_guid.split("-")[1];
      var entree_prevue = /entree_prevue='([0-9 \:-]*)'/.exec(e.title)[1];
      var prevue_split = entree_prevue.split(" ");
      var date_entree_prevue = prevue_split[0];
      var heure_entree_prevue = prevue_split[1];
      var sortie_prevue = /sortie_prevue='([0-9 \:-]*)'/.exec(e.title)[1];
      var heure_sortie_prevue = sortie_prevue.split(" ")[1];
      var time = e.getTime();
      var temp_operation_date = new Date(1970, 1, 1, 0, time.length)
      var temp_operation = temp_operation_date.toTIME();
      
      var time_operation = time.start.toTIME();
      var index_salle = time.start.getFullYear()-2000;
      var salle_id = this.salles_ids[index_salle];
      
      if (index_salle < 0 || index_salle > this.salles_ids.length) {
        return;
      }
      
      // Pour un commentaire
      if (e.type == "commentaire_planning") {
        var form = getForm("editCommentairePlanning");
        
        var fin = time.start.addHours(temp_operation_date.getHours()).addMinutes(temp_operation_date.getMinutes());
        
        $V(form.commentaire_planning_id, object_id);
        $V(form.debut, "{{$date_planning}} " + time_operation);
        $V(form.fin, "{{$date_planning}} " + fin.toTIME());
        $V(form.salle_id, salle_id);
        
        onSubmitFormAjax(form, {onComplete: refreshPlanning});
        return;
      }
      
      // Pour une DHE
      
      var form = getForm("editOperation");
      
      // Popup de modification des dates d'entr�e et sortie pr�vue du s�jour
      // dans le cas o� la date et heure d'intervention n'est pas dans cet intervalle
      
      if ("{{$date_planning}} "+time_operation < entree_prevue) {
        modifSejour(object_id, "{{$date_planning}} "+time_operation, null, null, null, "afterModifSejour");
        
        window.save_operation =
          {"operation_id": object_id,
           "time_operation": time_operation,
           "temp_operation": temp_operation,
           "salle_id": salle_id};
        return;
      }
      
      // Sinon, on peut enregistrer
      $V(form.operation_id,   object_id);
      $V(form.time_operation, time_operation);
      $V(form.temp_operation, temp_operation);
      $V(form.salle_id,       salle_id);
      
      onSubmitFormAjax(form, {onComplete: refreshPlanning});
    }
    
    var planning_div = $("{{$planning->guid}}");

    
    {{if $can->edit}}
      // Cr�ation d'une interv sur une case � une heure donn�e
      planning_div.select("td").each(function(elt) {
        elt.observe('dblclick', function() {
          var classes = elt.className.split("  ");
          var hour = classes[0].split("-")[2];
          var salle_id = planning.salles_ids[classes[0].split("-")[1]];
          
          // Mode commentaire
          var form = getForm("filterPlanning");
          
          if (form._comment_mode.checked) {
            modifCommentaire("{{$date_planning}}", hour, salle_id, null, "Control.Modal.close");
            return;
          }
          
          // Mode DHE
          
          // - Couper coller
          if (window.cut_operation_id) {
            pasteIntervention(window.cut_operation_id, salle_id, hour);
            return;
          }
          
          // - Copier coller
          if (window.copy_operation_id) {
            pasteIntervention(window.copy_operation_id, salle_id, hour, window.save_sejour_id, window.save_duree);
            return;
          }
          
          // - Cr�ation
          modifIntervention("{{$date_planning}}", hour, salle_id);
        });
      });
    {{/if}}
  });
  </script>