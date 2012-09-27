{{if !$sejour->_id}}
  {{assign var="sejour" value=$operation->_ref_sejour}}
{{/if}}
<table class="print">
  <tr>
    <th class="title" colspan="2">
      <span style="float:left; font-size:12px;">
        [{{$sejour->_NDA}}]
      </span>
      <span style="float:right;font-size:12px;">
        {{$sejour->_ref_group->text}}
      </span>
      <a href="#" onclick="window.print()">Fiche d'admission</a>
    </th>
  </tr>
  <tr>
    <td class="info" colspan="2">
    (Pri�re de vous munir pour la consultation d'anesth�sie de la photocopie
     de vos cartes de s�curit� sociale, de mutuelle, du r�sultat de votre
     bilan sanguin et de la liste des m�dicaments que vous prenez)<br />
     {{if $sejour->_ref_group->tel}}
       Pour tout renseignement, t�l�phonez au 
       {{mb_value object=$sejour->_ref_group field=tel}}
     {{/if}}
    </td>
  </tr>
  
  <tr>
    <th>Date</th>
    <td>{{$today|date_format:"%A %d/%m/%Y"}}</td>
  </tr>
  
  <tr>
    <th>Praticien</th>
    <td>
    {{if $operation->_id}}
      {{if $operation->_ref_chir}}
        Dr {{$operation->_ref_chir->_view}}
      {{/if}}
    {{else}}
      {{if $sejour->_ref_praticien}}
        Dr {{$sejour->_ref_praticien->_view}}
      {{/if}}
    {{/if}}
    </td>
  </tr>
  
  <tr>
    <th class="category" colspan="2">Renseignements concernant le patient</th>
  </tr>
  
  {{assign var="patient" value=$sejour->_ref_patient}}
  
  <tr>
    <th>Nom / Pr�nom</th>
    <td>{{$patient->_view}}</td>
  </tr>
  
  <tr>
    <th>Date de naissance / Sexe</th>
    <td>
      n�(e) le {{mb_value object=$patient field="naissance"}}
      de sexe 
      {{if $patient->sexe == "m"}}masculin{{else}}f�minin{{/if}}
    </td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$patient field=incapable_majeur}}</th>
    <td>{{mb_value object=$patient field=incapable_majeur}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$patient field=tel}}</th>
    <td>{{mb_value object=$patient field=tel}}</td>
  </tr>

  <tr>
    <th>Medecin traitant</th>
    <td>
    {{if $patient->_ref_medecin_traitant}}
      {{$patient->_ref_medecin_traitant->_view}}
    {{/if}}
    </td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$patient field=adresse}}</th>
    <td>
      {{mb_value object=$patient field=adresse}}
      {{mb_value object=$patient field=cp}} 
      {{mb_value object=$patient field=ville}}
    </td>
  </tr>
  
  <tr>
    <th class="category" colspan="2">Renseignements relatifs � l'hospitalisation</th>
  </tr>
  
  {{if $sejour->libelle}}
  <tr>
    <th>{{mb_label object=$sejour field=libelle}}</th>
    <td>{{mb_value object=$sejour field=libelle}}</td>
  </tr>
  {{/if}}
  
  {{if $sejour->_NDA}}
  <tr>
    <th>{{tr}}CSejour-_NDA{{/tr}}</th>
    <td>
      [{{$sejour->_NDA}}]
    </td>
  </tr>
  {{/if}}
  
  <tr>
    <th>Admission</th>
    <td>le {{$sejour->entree_prevue|date_format:"%A %d/%m/%Y � %Hh%M"}}</td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$sejour field=type}}</th>
    <td>{{mb_value object=$sejour field=type}}</td>
  </tr>

  {{if $conf.dPhospi.systeme_prestations == "standard"}}
  <tr>
    <th>{{mb_label object=$sejour field=chambre_seule}}</th>
    <td>{{mb_value object=$sejour field=chambre_seule}}</td>
  </tr>
  {{/if}}
  
  {{if $conf.dPplanningOp.CSejour.fiche_rques_sej && $sejour->rques}}
  <tr>
    <th>{{mb_label object=$sejour field=rques}}</th>
    <td>{{mb_value object=$sejour field=rques}}</td>
  </tr>
  {{/if}}
  
  {{if $conf.dPplanningOp.CSejour.fiche_conval && $sejour->convalescence}}
  <tr>
    <th>{{mb_label object=$sejour field=convalescence}}</th>
    <td>{{mb_value object=$sejour field=convalescence}}</td>
  </tr>
  {{/if}}
  
  {{if $operation->_id}}
  <tr>
    <th>Date d'intervention</th>
    <td>le {{$operation->_datetime|date_format:"%A %d/%m/%Y"}}</td>
  </tr>
  
  {{if !$simple_DHE}}
  {{if $operation->libelle}}
  <tr>
    <th>{{mb_label object=$operation field=libelle}}</th>
    <td class="text"><em>{{mb_value object=$operation field=libelle}}</em></td>
  </tr>
  {{/if}}

  {{if $conf.dPplanningOp.COperation.use_ccam && $operation->codes_ccam}}
  <tr>
    <th>Actes</th>
    <td class="text">
      {{foreach from=$operation->_ext_codes_ccam item=ext_code_ccam}}
      {{if $ext_code_ccam->code != "-"}}
      {{$ext_code_ccam->libelleLong}} ({{$ext_code_ccam->code}})<br />
      {{/if}}
      {{/foreach}}
    </td>
  </tr>
  {{/if}}
  
  <tr>
    <th>{{mb_label object=$operation field=cote}}</th>
    <td>{{mb_value object=$operation field=cote}}</td>
  </tr>
  
  {{if $conf.dPplanningOp.COperation.fiche_examen && $operation->examen}}
  <tr>
    <th>{{mb_label object=$operation field=examen}}</th>
    <td>{{mb_value object=$operation field=examen}}</td>
  </tr>
  {{/if}}

  {{if $conf.dPplanningOp.COperation.fiche_materiel && $operation->materiel}}
  <tr>
    <th>{{mb_label object=$operation field=materiel}}</th>
    <td>{{mb_value object=$operation field=materiel}}</td>
  </tr>
  {{/if}}

  {{if $conf.dPplanningOp.COperation.fiche_rques && $operation->rques}}
  <tr>
    <th>{{mb_label object=$operation field=rques}}</th>
    <td>{{mb_value object=$operation field=rques}}</td>
  </tr>
  {{/if}}

  {{/if}}
  {{/if}}
  
  {{if $conf.dPplanningOp.CSejour.accident && $sejour->date_accident}}
  <tr>
    <th>{{mb_label object=$sejour field=date_accident}}</th>
    <td class="text">{{mb_value object=$sejour field=date_accident}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field=nature_accident}}</th>
    <td class="text">{{mb_value object=$sejour field=nature_accident}}</td>
  </tr>
  {{/if}}
  
  {{if $conf.dPplanningOp.CSejour.assurances}}
  {{if $sejour->assurance_maladie}}
  <tr>
    <th>{{mb_label object=$sejour field=assurance_maladie}}</th>
    <td class="text">{{mb_value object=$sejour field=assurance_maladie}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field=rques_assurance_maladie}}</th>
    <td class="text">{{mb_value object=$sejour field=rques_assurance_maladie}}</td>
  </tr>
  {{/if}}
  {{if $sejour->assurance_accident}}
  <tr>
    <th>{{mb_label object=$sejour field=assurance_accident}}</th>
    <td class="text">{{mb_value object=$sejour field=assurance_accident}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field=rques_assurance_accident}}</th>
    <td class="text">{{mb_value object=$sejour field=rques_assurance_accident}}</td>
  </tr>
  {{/if}}
  {{/if}}
  
  <tr>
    <th>Dur�e pr�vue d'hospitalisation</th>
    <td>{{$sejour->_duree_prevue}} nuits</td>
  </tr>
 
  <tr>
    <th>Adresse</th>
    <td>
      {{$sejour->_ref_group->text}}<br />
      {{$sejour->_ref_group->adresse}}<br />
      {{$sejour->_ref_group->cp}}
      {{$sejour->_ref_group->ville}}
    </td>
  </tr>
  
  {{if $operation->_id}}
  {{if $operation->forfait}}
  <tr>
    <th>{{mb_label object=$operation field=forfait}}</th>
    <td>{{mb_value object=$operation field=forfait}}</td>
  </tr>
  {{/if}}  
  {{if $operation->fournitures}}
    <tr>
    <th>{{mb_label object=$operation field=fournitures}}</th>
    <td>{{mb_value object=$operation field=fournitures}}</td>
    </tr>
  {{/if}}

  <tr>
    <th class="category" colspan="2">Rendez vous d'anesth�sie</th>
  </tr>
    
  <tr>
    <td class="text" colspan="2">
      Veuillez prendre rendez-vous avec le cabinet d'anesth�sistes <strong>imp�rativement</strong>
      avant votre intervention.
     {{if $sejour->_ref_group->tel_anesth}}
       Pour cela, t�l�phonez au {{mb_value object=$sejour->_ref_group field=tel_anesth}}
     {{/if}}
    </td>
  <tr>
  {{/if}}
  
  <tr>
    <td class="info" colspan="2">
      <b>Pour votre hospitalisation, pri�re de vous munir de :</b>
      <ul>
        <li>Carte d'identit�</li>
        <li>
          Carte vitale et attestation de s�curit� sociale,
          carte de mutuelle et prise en charge compl�te aupr�s de votre mutuelle
          � transmettre au personnel des admissions lors de votre entr�e.
        </li>
        <li>Tous examens en votre possession (analyse, radio, carte de groupe sanguin...).</li>
        <li>Pr�voir linge et n�cessaire de toilette.</li>
        <li>Vos m�dicaments �ventuellement</li>
      </ul>
    </td>
  </tr>
</table>