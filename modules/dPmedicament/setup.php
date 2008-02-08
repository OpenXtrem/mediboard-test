<?php /* $Id: $ */

/**
 *	@package Mediboard
 *	@subpackage dPmedicament
 *	@version $Revision: $
 *  @author Alexis Granger
 */

global $AppUI;
 
// MODULE CONFIGURATION DEFINITION
$config = array();
$config["mod_name"]        = "dPmedicament";
$config["mod_version"]     = "0.13";
$config["mod_type"]        = "user";


class CSetupdPmedicament extends CSetup {
  
  function __construct() {
    parent::__construct();
    
    $this->mod_name = "dPmedicament";
       
    $this->makeRevision("all");
    
    $this->makeRevision("0.1");
    
    $sql = "CREATE TABLE `produit_livret_therapeutique` (
            `produit_livret_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
            `group_id` INT(11) UNSIGNED NOT NULL, 
            `code_cip` INT(11) NOT NULL, 
            `prix_hopital` FLOAT, 
            `prix_ville` FLOAT, 
            `date_prix_hopital` DATE, 
            `date_prix_ville` DATE,  
            `code_interne` INT(11), 
            `commentaire` TEXT, 
            PRIMARY KEY (`produit_livret_id`)) TYPE=MYISAM;";
    $this->addQuery($sql);
    
    $this->makeRevision("0.11");
    
    $sql = "ALTER TABLE `produit_livret_therapeutique`
            ADD `libelle` TEXT;";
    $this->addQuery($sql);
    
    $this->makeRevision("0.12");
    
    $sql = "ALTER TABLE `produit_livret_therapeutique`
            DROP `libelle`;";
    $this->addQuery($sql);
    
    $this->mod_version = "0.13";
  }  
}

?>