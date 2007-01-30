<?php /* $Id: */

/**
* @package Mediboard
* @subpackage dPinterop
* @version $Revision: $
* @author Thomas Despoix
*/

global $AppUI, $canRead, $canEdit, $canAdmin, $m, $g;

if (!$canAdmin) {
  $AppUI->redirect( "m=system&a=access_denied" );
}

/**
 * Purge objects of a given class linked to an idSante400 from database
 * via a direct SQL query, no framework for better performance
 * @param string $className The class of objects to be removed
 * @param string $fakeClassName The fake class name to handle the CUser case
 */
function purgeObjects($className, $fakeClassName = null) {
  global $AppUI;
  $object = new $className;
  if (!is_a($object, "CMbObject")) {
    $AppUI->stepAjax("Impossible de purger des objets de la classe '$className'", UI_MSG_ERROR);
  }
    
  // Ugly hack for CUser case
  $classNameID = mbGetValue($fakeClassName, $className);
  
  $query = "DELETE FROM `$object->_tbl`" .
      "\nWHERE `$object->_tbl_key`" .
      "\nIN (" .
      "\n  SELECT object_id" .
      "\n  FROM `id_sante400`" .
      "\n  WHERE `object_class` = '$classNameID')";
  
  db_exec($query);
  $countObjects = db_affected_rows();
  
  $AppUI->stepAjax("$countObjects objets de type '$className' purg�s");

  if (!$fakeClassName) {
    $query = "DELETE FROM `id_sante400`" .
      "\n  WHERE `object_class` = '$classNameID'";
    db_exec($query);
    $countIDs = db_affected_rows();

    $AppUI->stepAjax("$countIDs identifiants purg�s pour le type '$className'");
  }  
}

purgeObjects("CGroups");
purgeObjects("CFunctions");
purgeObjects("CUser", "CMediusers");
purgeObjects("CMediusers");
purgeObjects("CPatient");
purgeObjects("CSejour");
purgeObjects("COperation");
purgeObjects("CActeCCAM");
purgeObjects("CNaissance");

?>