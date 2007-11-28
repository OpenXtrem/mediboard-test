<?php /* $Id: $ */

/**
 *  @package Mediboard
 *  @subpackage dPqualite
 *  @version $Revision: $
 *  @author S�bastien Fillonneau
 */
 
// MODULE CONFIGURATION DEFINITION
$config = array();
$config["mod_name"]    = "dPqualite";
$config["mod_version"] = "0.15";
$config["mod_type"]    = "user";

class CSetupdPqualite extends CSetup {
  
  function __construct() {
    parent::__construct();
    
    $this->mod_name = "dPqualite";
    $this->makeRevision("all");
    $sql = "CREATE TABLE `doc_ged_suivi` (
               `doc_ged_suivi_id` int(11) NOT NULL auto_increment,
               `user_id` INT(11) NOT NULL DEFAULT 0,
               `doc_ged_id` INT(11) NOT NULL DEFAULT 0,
               `file_id` INT(11) DEFAULT NULL,
               `etat` TINYINT(4),
               `remarques` TEXT DEFAULT NULL,
               `date` DATETIME,
               `actif` TINYINT(1) DEFAULT 0,
               PRIMARY KEY  (doc_ged_suivi_id)
               ) TYPE=MyISAM COMMENT='Table de suivie des procedures';";
    $this->addQuery($sql);
    $sql = "CREATE TABLE `doc_ged` (
               `doc_ged_id` int( 11 ) NOT NULL AUTO_INCREMENT ,
               `group_id` INT( 11 ) NOT NULL DEFAULT 0,
               `doc_chapitre_id` INT( 11 ) NOT NULL DEFAULT 0,
               `doc_theme_id` INT( 11 ) NOT NULL DEFAULT 0,
               `doc_categorie_id` INT( 11 ) NOT NULL DEFAULT 0,
               `user_id` INT(11) NOT NULL DEFAULT 0,
               `titre` VARCHAR(50) DEFAULT NULL,
               `etat` TINYINT(4),
               `annule` TINYINT(1) NOT NULL DEFAULT 0,
               `version` float default NULL,
               `num_ref` MEDIUMINT(9) UNSIGNED NULL,
               PRIMARY KEY ( doc_ged_id )
               ) TYPE = MYISAM COMMENT = 'Table des procedures';";
    $this->addQuery($sql);
    $sql = "CREATE TABLE `doc_chapitres` (
               `doc_chapitre_id` int( 11 ) NOT NULL AUTO_INCREMENT ,
               `nom` VARCHAR( 50 ) DEFAULT NULL ,
               `code` VARCHAR( 10 ) DEFAULT NULL ,
               PRIMARY KEY ( doc_chapitre_id )
               ) TYPE = MYISAM COMMENT = 'Table des chapitres pour les procedures';";
    $this->addQuery($sql);
    $sql = "CREATE TABLE `doc_themes` (
               `doc_theme_id` int( 11 ) NOT NULL AUTO_INCREMENT ,
               `nom` VARCHAR( 50 ) DEFAULT NULL ,
               PRIMARY KEY ( doc_theme_id )
               ) TYPE = MYISAM COMMENT = 'Table des theme pour les procedures';";
    $this->addQuery($sql);
    $sql = "CREATE TABLE `doc_categories` (
               `doc_categorie_id` int( 11 ) NOT NULL AUTO_INCREMENT ,
               `nom` VARCHAR( 50 ) DEFAULT NULL ,
               `code` VARCHAR( 1 ) DEFAULT NULL ,
               PRIMARY KEY ( doc_categorie_id )
               ) TYPE = MYISAM COMMENT = 'Table des categories pour les procedures';";
    $this->addQuery($sql);
    $sql = "INSERT INTO `doc_categories` VALUES (1, 'Manuel qualit�', 'A');";  $this->addQuery($sql);
    $sql = "INSERT INTO `doc_categories` VALUES (2, 'Proc�dure', 'B');";       $this->addQuery($sql);
    $sql = "INSERT INTO `doc_categories` VALUES (3, 'Protocole', 'C');";       $this->addQuery($sql);
    $sql = "INSERT INTO `doc_categories` VALUES (4, 'Enregistement', 'D');";   $this->addQuery($sql);
    $sql = "INSERT INTO `doc_categories` VALUES (5, 'Donn�es', 'E');";         $this->addQuery($sql);
    $sql = "CREATE TABLE `ei_categories` (
                `ei_categorie_id` int( 11 ) NOT NULL AUTO_INCREMENT ,
                `nom` VARCHAR( 50 ) DEFAULT NULL ,
                PRIMARY KEY ( ei_categorie_id )
                ) TYPE = MYISAM COMMENT = 'Table des categories des EI'";
    $this->addQuery($sql);
    $sql = "CREATE TABLE `ei_item` (
                `ei_item_id` int( 11 ) NOT NULL AUTO_INCREMENT ,
                `ei_categorie_id` int( 11 ) NOT NULL DEFAULT 0 ,
                `nom` VARCHAR( 50 ) DEFAULT NULL ,
                PRIMARY KEY ( ei_item_id )
                ) TYPE = MYISAM COMMENT = 'Table des item des categories des EI'";
    $this->addQuery($sql);
    $sql = "CREATE TABLE `fiches_ei` (
                `fiche_ei_id` int ( 11 ) NOT NULL AUTO_INCREMENT ,
                `user_id` int ( 11 ) NOT NULL DEFAULT 0,
                `valid_user_id` int ( 11 ) DEFAULT NULL,
                `date_fiche` DATETIME,
                `date_incident` DATETIME,
                `date_validation` DATETIME,
                `evenements` VARCHAR( 255 ) DEFAULT NULL,
                `lieu` VARCHAR( 50 ) DEFAULT NULL,
                `type_incident` TINYINT(1) NOT NULL DEFAULT 0,
                `elem_concerne` int( 1 ) NOT NULL DEFAULT 0,
                `elem_concerne_detail` TEXT DEFAULT NULL,
                `autre` TEXT DEFAULT NULL,
                `descr_faits` TEXT DEFAULT NULL,
                `mesures` TEXT DEFAULT NULL,
                `descr_consequences` TEXT DEFAULT NULL,
                `gravite` int(1) NOT NULL DEFAULT 0,
                `plainte` TINYINT(1) NOT NULL DEFAULT 0,
                `commission` TINYINT(1) NOT NULL DEFAULT 0,
                `deja_survenu` int(1) DEFAULT NULL,
                `degre_urgence` int(1) DEFAULT NULL,
                PRIMARY KEY ( fiche_ei_id )
                ) TYPE = MYISAM COMMENT ='Table des fiches incidents'";
    $this->addQuery($sql);
    
    $this->makeRevision("0.1");
    $sql = "ALTER TABLE `fiches_ei`".
               "\nADD `service_valid_user_id` int ( 11 ) DEFAULT NULL," .
               "\nADD `service_date_validation` DATETIME," .
               "\nADD `service_actions` TEXT DEFAULT NULL," .
               "\nADD `service_descr_consequences` TEXT DEFAULT NULL," .
               "\nADD `qualite_user_id` int ( 11 ) DEFAULT NULL," .
               "\nADD `qualite_date_validation` DATETIME," .
               "\nADD `qualite_date_verification` DATETIME," .
               "\nADD `qualite_date_controle` DATETIME," .
               "\nADD `suite_even` ENUM('trans', 'plong', 'deces', 'autre') NOT NULL DEFAULT 'autre' AFTER `gravite`, " .
               "\nCHANGE `type_incident` `type_incident` ENUM('inc', 'ris') NOT NULL DEFAULT 'inc'," .
               "\nCHANGE `elem_concerne` `elem_concerne` ENUM( 'pat', 'vis', 'pers', 'med', 'mat' ) NOT NULL DEFAULT 'pat'," .
               "\nCHANGE `gravite` `gravite` ENUM('nul', 'mod', 'imp') NOT NULL DEFAULT 'nul'," .
               "\nCHANGE `plainte` `plainte` ENUM('non', 'oui') NOT NULL DEFAULT 'non'," .
               "\nCHANGE `commission` `commission` ENUM('non', 'oui') NOT NULL DEFAULT 'non'," .
               "\nCHANGE `deja_survenu` `deja_survenu` ENUM('non', 'oui') DEFAULT NULL;";
    $this->addQuery($sql);
    
    $this->makeRevision("0.11");
    $sql = "ALTER TABLE `fiches_ei` ADD `annulee` TINYINT(1) NOT NULL DEFAULT 0," .
                                 "\nADD `remarques` TEXT DEFAULT NULL;";
    $this->addQuery($sql);
    
    $this->makeRevision("0.12");
    $sql = "ALTER TABLE `fiches_ei` ADD `suite_even_descr` TEXT DEFAULT NULL AFTER `suite_even`;";
    $this->addQuery($sql);
    
    $this->makeRevision("0.13");
    $sql = "ALTER TABLE `doc_categories`" .
               "\nCHANGE `doc_categorie_id` `doc_categorie_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT," .
               "\nCHANGE `nom` `nom` varchar(50) NOT NULL," .
               "\nCHANGE `code` `code` varchar(1) NOT NULL;";
    $this->addQuery($sql);
    $sql = "ALTER TABLE `doc_chapitres`" .
               "\nCHANGE `doc_chapitre_id` `doc_chapitre_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT," .
               "\nCHANGE `nom` `nom` varchar(50) NOT NULL," .
               "\nCHANGE `code` `code` varchar(10) NOT NULL;";
    $this->addQuery($sql);
    $sql = "ALTER TABLE `doc_ged`" .
               "\nCHANGE `doc_ged_id` `doc_ged_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT," .
               "\nCHANGE `group_id` `group_id` int(11) UNSIGNED NOT NULL," .
               "\nCHANGE `doc_chapitre_id` `doc_chapitre_id` int(11) UNSIGNED NOT NULL," .
               "\nCHANGE `doc_theme_id` `doc_theme_id` int(11) UNSIGNED NOT NULL," .
               "\nCHANGE `doc_categorie_id` `doc_categorie_id` int(11) UNSIGNED NOT NULL," .
               "\nCHANGE `user_id` `user_id` int(11) UNSIGNED NOT NULL," .
               "\nCHANGE `etat` `etat` enum('0','16','32','48','64') NOT NULL DEFAULT '16'," .
               "\nCHANGE `annule` `annule` enum('0','1') NOT NULL DEFAULT '0'," .
               "\nCHANGE `num_ref` `num_ref` int(11) NULL;";
    $this->addQuery($sql);
    $sql = "ALTER TABLE `doc_ged_suivi`" .
               "\nCHANGE `doc_ged_suivi_id` `doc_ged_suivi_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT," .
               "\nCHANGE `user_id` `user_id` int(11) UNSIGNED NOT NULL," .
               "\nCHANGE `doc_ged_id` `doc_ged_id` int(11) UNSIGNED NOT NULL," .
               "\nCHANGE `etat` `etat` enum('0','16','32','48','64') NOT NULL DEFAULT '16'," .
               "\nCHANGE `actif` `actif` enum('0','1') NOT NULL DEFAULT '0'," .
               "\nCHANGE `remarques` `remarques` text NOT NULL," .
               "\nCHANGE `file_id` `file_id` int(11) UNSIGNED;";
    $this->addQuery($sql);
    $sql = "ALTER TABLE `ei_categories`" .
               "\nCHANGE `ei_categorie_id` `ei_categorie_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT," .
               "\nCHANGE `nom` `nom` varchar(50) NOT NULL;";
    $this->addQuery($sql);
    $sql = "ALTER TABLE `ei_item`" .
               "\nCHANGE `ei_item_id` `ei_item_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT," .
               "\nCHANGE `ei_categorie_id` `ei_categorie_id` int(11) UNSIGNED NOT NULL," .
               "\nCHANGE `nom` `nom` varchar(50) NOT NULL;";
    $this->addQuery($sql);
    $sql = "ALTER TABLE `fiches_ei`" .
               "\nCHANGE `fiche_ei_id` `fiche_ei_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT," .
               "\nCHANGE `user_id` `user_id` int(11) UNSIGNED NOT NULL," .
               "\nCHANGE `valid_user_id` `valid_user_id` int(11) UNSIGNED NULL," .
               "\nCHANGE `service_valid_user_id` `service_valid_user_id` int(11) UNSIGNED NULL," .
               "\nCHANGE `qualite_user_id` `qualite_user_id` int(11) UNSIGNED NULL," . 
               "\nCHANGE `annulee` `annulee` enum('0','1') NOT NULL DEFAULT '0'," .
               "\nCHANGE `date_fiche` `date_fiche` datetime NOT NULL," .
               "\nCHANGE `date_incident` `date_incident` datetime NOT NULL," .
               "\nCHANGE `evenements` `evenements` VARCHAR(255) NOT NULL," .
               "\nCHANGE `lieu` `lieu` VARCHAR(50) NOT NULL," .
               "\nCHANGE `elem_concerne_detail` `elem_concerne_detail` TEXT NOT NULL ," .
               "\nCHANGE `descr_faits` `descr_faits` TEXT NOT NULL ," .
               "\nCHANGE `mesures` `mesures` TEXT NOT NULL ," .
               "\nCHANGE `degre_urgence` `degre_urgence` enum('1','2','3','4') NULL ," .
               "\nCHANGE `descr_consequences` `descr_consequences` TEXT NOT NULL ," .
               "\nCHANGE `qualite_date_verification` `qualite_date_verification` DATE NULL," .
               "\nCHANGE `qualite_date_controle` `qualite_date_controle` DATE NULL;";
    $this->addQuery($sql);
    $sql = "ALTER TABLE `doc_themes`" .
               "\nCHANGE `doc_theme_id` `doc_theme_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT," .
               "\nCHANGE `nom` `nom` varchar(50) NOT NULL;";
    $this->addQuery($sql);
    
    $this->makeRevision("0.14");
    $sql = "ALTER TABLE `fiches_ei`
              CHANGE `gravite` `gravite` ENUM('1','2','3','4','5'), 
              ADD `vraissemblance` ENUM('1','2','3','4','5');";
    $this->addQuery($sql);
    
    $this->mod_version = "0.15";
    
  }
}
?>