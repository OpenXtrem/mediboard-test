<tr>
  <td class="text">
    <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$patient->_id}}">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}};')">
        {{mb_value object=$patient field="_view"}}
      </span>
    </a>
  </td>
  <td class="text">
    <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$patient->_id}}">
      {{mb_value object=$patient field="naissance"}}
    </a>
  </td>
  <td class="text">
   <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$patient->_id}}">
     {{mb_value object=$patient field="adresse"}}
     {{mb_value object=$patient field="cp"}}
     {{mb_value object=$patient field="ville"}}
   </a>
  </td>
</tr>