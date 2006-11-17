<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage mediusers
* @version $Revision$
* @author Romain Ollivier
*/

$config = array();
$config["mod_name"]        = "mediusers";
$config["mod_version"]     = "0.19";
$config["mod_type"]        = "user";
$config["mod_config"]      = true;

if(@$a == "setup") {
  echo dPshowModuleConfig($config);
}

class CSetupmediusers {

  function configure() {
    global $AppUI;
    $AppUI->redirect( "m=mediusers&a=configure" );
    return true;
  }

  function remove() {
    db_exec("DROP TABLE `users_mediboard`;")    ; db_error();
    db_exec("DROP TABLE `functions_mediboard`;"); db_error();
    db_exec("DROP TABLE `discipline`;")         ; db_error();
    return null;
  }


  function upgrade($old_version) {
    switch ($old_version) {
      case "all":
        $sql = "CREATE TABLE `users_mediboard` (" .
          "\n`user_id` INT(11) UNSIGNED NOT NULL," .
          "\n`function_id` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0'," .
          "\nPRIMARY KEY (`user_id`)" .
          "\n) TYPE=MyISAM;";
        db_exec($sql); db_error();
        
        $sql = "CREATE TABLE `functions_mediboard` (" .
          "\n`function_id` TINYINT(4) UNSIGNED NOT NULL AUTO_INCREMENT," .
          "\n`group_id` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0'," .
          "\n`text` VARCHAR(50) NOT NULL," .
          "\n`color` VARCHAR(6) NOT NULL DEFAULT 'ffffff'," .
          "\nPRIMARY KEY (`function_id`)" .
          "\n) TYPE=MyISAM;";
        db_exec($sql); db_error();
      case "0.1":
        $sql = "ALTER TABLE `users_mediboard` ADD `remote` TINYINT DEFAULT NULL;";
        db_exec($sql);  db_error();
        
      case "0.11":
        $sql = "ALTER TABLE `users_mediboard` ADD `adeli` int(9) DEFAULT NULL;";
        db_exec($sql);  db_error();

      case "0.12":
        $sql = "ALTER TABLE `users_mediboard` CHANGE `adeli` `adeli` VARCHAR(9);";
        db_exec($sql);  db_error();

      case "0.13": 
        $sql = "CREATE TABLE `discipline` (" .
            "\n`discipline_id` TINYINT(4) UNSIGNED NOT NULL AUTO_INCREMENT," .
            "\n`text` VARCHAR(100) NOT NULL," .
            "\nPRIMARY KEY (`discipline_id`)" .
            "\n) TYPE=MyISAM;";
        db_exec($sql); db_error();
        $sql = "ALTER TABLE `users_mediboard` ADD `discipline_id` TINYINT(4) DEFAULT NULL AFTER `function_id`";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('ADDICTOLOGIE CLINIQUE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('AIDE MEDICALE URGENTE OU MEDECINE D\'URGENCE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('ALLERGOLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('ANATOMIE ET CYTOLOGIE PATHOLOGIQUES');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('ANDROLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('ANESTHESIE-REANIMATION');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('ANGIOLOGIE/ MEDECINE VASCULAIRE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('BIOLOGIE MEDICALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('CANCEROLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('CARDIOLOGIE / PATHOLOGIE CARDIO-VASCULAIRE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE DE LA FACE ET DU COU');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE GENERALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE INFANTILE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE MAXILLO-FACIALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE MAXILLO-FACIALE ET STOMATOLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE ORTHOPEDIQUE ET TRAUMATOLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE PLASTIQUE, RECONSTRUCTRICE ET ESTHETIQUE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE THORACIQUE ET CARDIO-VASCULAIRE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE UROLOGIQUE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE VASCULAIRE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE VISCERALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('DERMATOLOGIE ET VENEREOLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('ENDOCRINOLOGIE ET METABOLISMES');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('EVALUATION ET TRAITEMENT DE LA DOULEUR');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('GASTRO-ENTEROLOGIE ET HEPATOLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('GENETIQUE MEDICALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('GERIATRIE / GERONTOLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('GYNECOLOGIE MEDICALE, OBSTETRIQUE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('GYNECOLOGIE-OBSTETRIQUE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('HEMATOLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('HEMOBIOLOGIE-TRANSFUSION / TECHNOLOGIE TRANSFUSION');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('HYDROLOGIE ET CLIMATOLOGIE MEDICALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('IMMUNOLOGIE ET IMMUNOPATHOLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE AEROSPATIALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE DE CATASTROPHE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE DE LA REPRODUCTION ET GYNECOLOGIE MEDICAL');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE DU TRAVAIL');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE ET BIOLOGIE DU SPORT');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE GENERALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE INTERNE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE LEGALE ET EXPERTISES MEDICALES');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE NUCLEAIRE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE PENITENTIAIRE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE PHYSIQUE ET DE READAPTATION');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('NEPHROLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('NEUROCHIRURGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('NEUROLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('NEUROPSYCHIATRIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('NUTRITION');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('ONCOLOGIE MEDICALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('ONCOLOGIE OPTION RADIOTHERAPIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('OPHTALMOLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('ORTHOPEDIE DENTO-MAXILLO-FACIALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('OTO-RHINO-LARYNGOLOGIE ET CHIRURGIE CERVICO-FACIALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('PATHOLOGIE INFECTIEUSE ET TROPICALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('PEDIATRIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('PHONIATRIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('PNEUMOLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('PSYCHIATRIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('PSYCHIATRIE DE L\'ENFANT ET DE L\'ADOLESCENT');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('RADIO-DIAGNOSTIC ET IMAGERIE MEDICALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('RADIOTHERAPIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('REANIMATION MEDICALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('RECHERCHE MEDICALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('RHUMATOLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('SANTE PUBLIQUE ET MEDECIN SOCIALE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('STOMATOLOGIE');";
        db_exec($sql); db_error();
        $sql = "INSERT INTO `discipline` (`text`) VALUES('TOXICOMANIES ET ALCOOLOGIE');";
        db_exec($sql); db_error();

      case "0.14":
        $sql = "ALTER TABLE `functions_mediboard` ADD `type` ENUM('administratif', 'cabinet') DEFAULT 'administratif' NOT NULL AFTER `group_id`;";
        db_exec($sql); db_error();
        $sql = "SHOW TABLE STATUS LIKE 'groups_mediboard'";
        $result = db_loadResult($sql);
        if($result) {
          $sql = "UPDATE `functions_mediboard`, `groups_mediboard`" .
              "\nSET `functions_mediboard`.`type` = 'cabinet'" .
              "\nWHERE `functions_mediboard`.`group_id` = `groups_mediboard`.`group_id`" .
              "\nAND `groups_mediboard`.`text` = 'Cabinets'";
          db_exec($sql); db_error();
        }
      case "0.15":
        $sql = "ALTER TABLE `functions_mediboard` ADD INDEX ( `group_id` ) ;";
        db_exec($sql); db_error();
        $sql = "ALTER TABLE `users_mediboard` ADD INDEX ( `function_id` ) ;";
        db_exec($sql); db_error();
        $sql = "ALTER TABLE `users_mediboard` ADD INDEX ( `discipline_id` ) ;";
        db_exec($sql); db_error();
      case "0.16":
        $sql = "ALTER TABLE `discipline` " .
               "\nCHANGE `discipline_id` `discipline_id` int(11) unsigned NOT NULL AUTO_INCREMENT," .
               "\nCHANGE `text` `text` varchar(255) NOT NULL;";
        db_exec( $sql ); db_error();
        
        $sql = "ALTER TABLE `functions_mediboard` " .
               "\nCHANGE `function_id` `function_id` int(11) unsigned NOT NULL AUTO_INCREMENT," .
               "\nCHANGE `group_id` `group_id` int(11) unsigned NOT NULL DEFAULT '0'," .
               "\nCHANGE `text` `text` varchar(255) NOT NULL;";
        db_exec( $sql ); db_error();
        
        $sql = "ALTER TABLE `users_mediboard` " .
               "\nCHANGE `user_id` `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT," .
               "\nCHANGE `function_id` `function_id` int(11) unsigned NOT NULL DEFAULT '0'," .
               "\nCHANGE `adeli` `adeli` int(9) unsigned zerofill NULL," .
               "\nCHANGE `remote` `remote` enum('0','1') NULL," .
               "\nCHANGE `discipline_id` `discipline_id` int(11) unsigned NULL;";
        db_exec( $sql ); db_error();
        
      case "0.17":
        $sql = "ALTER TABLE `users_mediboard` " .
               "\nADD `commentaires`  text NULL," .
               "\nADD `actif` enum('0','1') NOT NULL DEFAULT '1';";
        db_exec( $sql ); db_error();
        
      case "0.18":
        $sql = "ALTER TABLE `users_mediboard` " .
               "\nADD `deb_activite` datetime NULL," .
               "\nADD `fin_activite` datetime NULL;";
        db_exec( $sql ); db_error();
        
      case "0.19":
        return "0.19";
    }

    return false;
  }
}

?>