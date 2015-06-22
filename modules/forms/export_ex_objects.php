<?php

/**
 * $Id$
 *
 * @category Forms
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision$
 * @link     http://www.mediboard.org
 */

CCanDo::checkEdit();

CStoredObject::$useObjectCache = false;
CApp::setMemoryLimit(1024);
CApp::setTimeLimit(600);

$ex_class_id    = CValue::get("ex_class_id");
$concept_search = CValue::get("concept_search");
$date_min       = CValue::get("date_min");
$date_max       = CValue::get("date_max");
$group_id       = CValue::get("group_id");

$limit = 10000;

$group_id = ($group_id ? $group_id : CGroups::loadCurrent()->_id);
$where    = array(
  "group_id = '$group_id' OR group_id IS NULL",
);

$ex_class = new CExClass();
$ex_class->load($ex_class_id);

foreach ($ex_class->loadRefsGroups() as $_group) {
  $_group->loadRefsFields();

  foreach ($_group->_ref_fields as $_field) {
    $_field->updateTranslation();
  }
}

/** @var CExObject[] $ex_objects */
$ex_objects = array();

$ref_objects_cache = array();

$search = null;
if ($concept_search) {
  $concept_search = stripslashes($concept_search);
  $search         = CExConcept::parseSearch($concept_search);
}

$ex_class_event  = new CExClassEvent();
$ex_class_events = null;

$ex_link = new CExLink();

$where = array(
  "ex_link.group_id"        => "= '$group_id'",
  "ex_link.ex_class_id"     => "= '$ex_class_id'",
  "ex_link.level"           => "= 'object'",
  "ex_link.datetime_create" => "BETWEEN '$date_min 00:00:00' AND '$date_max 23:59:59'",
);

$ljoin = array();

if (!empty($search)) {
  $ljoin["ex_object_$ex_class_id"] = "ex_object_$ex_class_id.ex_object_id = ex_link.ex_object_id";

  //$where = array_merge($where, $ex_class->getWhereConceptSearch($search));
}

$order = "ex_class.name ASC, ex_link.ex_object_id DESC";

$ljoin["ex_class"] = "ex_class.ex_class_id = ex_link.ex_class_id";

/** @var CExLink[] $links */
$links  = $ex_link->loadList($where, $order, $limit, "ex_link.ex_object_id", $ljoin);

//CExLink::massLoadExObjects($links);

/** @var CExObject[] $ex_objects */
foreach ($links as $_link) {
  $_ex               = $_link->loadRefExObject();
  $_ex->_ex_class_id = $_link->ex_class_id;
  $_ex->load();

  $_ex->updateCreationFields();

  $guid = "$_ex->object_class-$_ex->object_id";

  if (!isset($ref_objects_cache[$guid])) {
    $_ex->loadTargetObject();

    $ref_objects_cache[$guid] = $_ex->_ref_object;
  }
  else {
    $_ex->_ref_object = $ref_objects_cache[$guid];
  }

  if ($_ex->additional_id) {
    $_ex->loadRefAdditionalObject();
  }

  $ex_objects[$_ex->_id] = $_ex;
}

krsort($ex_objects);

$csv = new CCSVFile();

$get_field = function($class, $field) {
  return CAppUI::tr($class)." - ".CAppUI::tr("$class-$field");
};

$fields = array(
  array("CPatient", "_IPP"),
  array("CPatient", "nom"),
  array("CPatient", "nom_jeune_fille"),
  array("CPatient", "prenom"),
  array("CPatient", "prenom_2"),
  array("CPatient", "prenom_3"),
  array("CPatient", "prenom_4"),
  array("CPatient", "naissance"),
  array("CPatient", "sexe"),

  array("CSejour", "_NDA"),
  array("CSejour", "rques"),
  array("CSejour", "praticien_id"),
  array("CSejour", "type"),
  array("CSejour", "entree"),
  array("CSejour", "sortie"),
  array("CSejour", "annule"),
  array("CSejour", "DP"),
  array("CSejour", "DR"),

  array("COperation", "chir_id"),
  array("COperation", "anesth_id"),
  array("COperation", "salle_id"),
  array("COperation", "date"),
  array("COperation", "libelle"),
  array("COperation", "cote"),
  array("COperation", "temp_operation"),
  array("COperation", "codes_ccam"),
);

$meta_fields = array(
  array("CExObject", "datetime_create"),
  array("CExObject", "datetime_edit"),
  array("CExObject", "owner_id"),
);

$row = array();
foreach ($fields as $_field) {
  $row[] = $get_field($_field[0], $_field[1]);
}
foreach ($meta_fields as $_field) {
  $row[] = $get_field($_field[0], $_field[1]);
}

foreach ($ex_class->loadRefsGroups() as $_group) {
  foreach ($_group->_ref_fields as $_field) {
    $row[] = $_group->name . " - " . CAppUI::tr("CExObject_$ex_class->_id-$_field->name");
  }
}

// Write column headers
$csv->writeLine($row);

foreach ($ex_objects as $_ex_object) {
  /** @var CMbObject[] $_objects */
  $_objects = array();

  /** @var CPatient $_patient */
  $_patient = $_ex_object->getReferenceObject("CPatient");
  $_patient->loadIPP();
  $_objects["CPatient"] = $_patient;

  /** @var CSejour $_sejour */
  $_sejour  = $_ex_object->getReferenceObject("CSejour");
  $_objects["CSejour"] = $_sejour;

  /** @var COperation $_interv */
  $_interv  = $_ex_object->getReferenceObject("COperation");
  $_objects["COperation"] = $_interv;

  $_row = array();

  foreach ($fields as $_field) {
    list($_class, $_fieldname) = $_field;

    if (isset($_objects[$_class])) {
      $_row[] = $_objects[$_class]->getFormattedValue($_fieldname);
    }
    else {
      $_row[] = null;
    }
  }

  // Meta fields
  $_row[] = $_ex_object->getFormattedValue("datetime_create");
  $_row[] = $_ex_object->getFormattedValue("datetime_edit");
  $_row[] = $_ex_object->getFormattedValue("owner_id");

  foreach ($ex_class->loadRefsGroups() as $_group) {
    foreach ($_group->_ref_fields as $_field) {
      $_row[] = $_ex_object->getFormattedValue($_field->name);
    }
  }

  $csv->writeLine($_row);
}

$csv->stream($ex_class->name, true);
