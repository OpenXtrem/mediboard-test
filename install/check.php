<?php
/**
 * Installation prerequisite checker
 *
 * PHP version 5.1.x+
 *  
 * @package    Mediboard
 * @subpackage Intaller
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version    SVN: $Id$ 
 * @link       http://www.mediboard.org
 */

require_once "header.php";

/**
 * Prerequisite abstract class
 */
class CPrerequisite {
  var $name = "";
  var $description = "";
  var $mandatory = false;
  var $reason = array();

  /**
   * Check prerequisite
   * 
   * @return bool
   */
  function check() {
    return false;
  }
}

/**
 * PEAR package prerequisite
 */
class CPearPackage extends CPrerequisite {
  var $status = "stable";

  /**
   * Check file inclusion
   * 
   * @see parent::check
   * 
   * @return bool
   */
  function check() {
    return @include_once "$this->name.php";
  }
}

/**
 * PHP extension prerequisite
 */
class CPHPExtension  extends CPrerequisite {

  /**
   * Check extension load
   * 
   * @see parent::check
   * 
   * @return bool
   */
  function check() {
    return extension_loaded(strtolower($this->name));
  }
}

/**
 * PHP version prerequisite
 */
class CPHPVersion extends CPrerequisite {

  /**
   * Compare PHP version
   * 
   * @see parent::check
   * 
   * @return bool
   */
  function check() {
    return phpversion() >= $this->name;
  }
}



$packages = array();

$package = new CPearPackage;
$package->name = "Archive/Tar";
$package->description = "Package de manipulation d'archives au format GNU TAR";
$package->mandatory = true;
$package->reasons[] = "Installation de Mediboard";
$package->reasons[] = "Import des fonctions de GHM";
$packages[] = $package;

$package = new CPearPackage;
$package->name = "Config";
$package->description = "Package de manipulation de fichiers de configuration";
$package->mandatory = true;
$package->reasons[] = "Configuration g�n�rale de Mediboard";
$packages[] = $package;

$package = new CPearPackage;
$package->name = "DB";
$package->description = "Package de manipulation de base de donn�es";
$package->mandatory = true;
$package->reasons[] = "Assistant d'installation de Mediboard";
$packages[] = $package;

$package = new CPearPackage;
$package->name = "Auth";
$package->description = "Package d'authentification multi-support";
$package->mandatory = true;
$package->reasons[] = "Assistant d'installation de Mediboard";
$packages[] = $package;

$package = new CPearPackage;
$package->name = "PHP/CodeSniffer";
$package->description = "Analyseur syntaxique de code source";
$package->status = "beta";
$package->mandatory = false;
$package->reasons[] = 
  "Outil de g�nie logiciel pour v�rifier la qualit� du code source de Mediboard";
$packages[] = $package;

$extensions = array();

$extension = new CPHPExtension;
$extension->name = "MySQL";
$extension->description = "Extension d'acc�s aux bases de donn�es MySQL";
$extension->mandatory = true;
$extension->reasons[] = "Acc�s � la base de donn�e de principale Mediboard";
$extension->reasons[] = "Acc�s aux bases de donn�es de codage CCAM, CIM et GHM";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "MBString";
$extension->description = "Extension de gestion des cha�nes de caract�res multi-octets";
$extension->mandatory = true;
$extension->reasons[] = "Internationalisation de Mediboard";
$extension->reasons[] = "Interop�rabilit� Unicode";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "ZLib";
$extension->description = "Extension de compression au format GNU ZIP (gz)";
$extension->mandatory = true;
$extension->reasons[] = "Installation de Mediboard";
$extension->reasons[] = "Accel�ration substancielle de l'application via une communication web compress�e";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "Zip";
$extension->description = "Extension de compression au format zip";
$extension->mandatory = true;
$extension->reasons[] = "Installation de Mediboard";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "JSON";
$extension->description = "Extension de manipulation de donn�es au format JSON. Inclus par d�faut avec PHP 5.2+";
$extension->mandatory = true;
$extension->reasons[] = "Passage de donn�es de PHP vers Javascript.";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "DOM";
$extension->description = "Extension de manipulation de fichier XML avec l'API DOM";
$extension->mandatory = true;
$extension->reasons[] = "Import de base de donn�es m�decin";
$extension->reasons[] = "Interop�rabilit� HPRIM XML, notamment pour le PMSI";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "SOAP";
$extension->description = "Extension permettant d'effectuer des requetes";
$extension->reasons[] = "Requetes vers les serveurs de r�sultats de laboratoire";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "FTP";
$extension->description = "Extension d'acc�s aux serveur FTP";
$extension->reasons[] = "Envoi HPRIM vers des serveurs de facturation";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "BCMath";
$extension->description = "Extension de calculs sur des nombres de pr�cision arbitraire";
$extension->reasons[] = "Validation des codes INSEE et ADELI";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "CURL";
$extension->description = 
  "Extension permettant de communiquer avec des serveurs distants, gr�ce � de nombreux protocoles";
$extension->reasons[] = "Connexion au site web du Conseil National l'Ordre des M�decins";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "GD";
$extension->description = "Extension de manipulation d'image.";
$extension->reasons[] = "GD version 2 est recommand�e car elle permet un meilleur rendu";
$extension->reasons[] = "Module de statistiques graphiques";
$extension->reasons[] = "Fonction d'audiogrammes";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "PDO";
$extension->description = "Extension de connectivit� aux bases de donn�es";
$extension->reasons[] = "Interop�rabilit� avec des syst�mes tiers";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "PDO_ODBC";
$extension->description = "Pilote ODBC pour PDO";
$extension->reasons[] = "Interop�rabilit� avec des syst�mes tiers";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "APC";
$extension->description = "Extension d'optimsation d'OPCODE et de m�moire partag�e";
$extension->reasons[] = "Acc�l�ration globale du syst�me";
$extensions[] = $extension;

$extension = new CPHPExtension;
$extension->name = "GnuPG";
$extension->description = "GNU Privacy Guard (GPG ou GnuPG)";
$extension->reasons[] = "Transmettre des messages sign�s et/ou chiffr�s";
$extensions[] = $extension;

$versions = array();

// Do not use $version which is a Mediboard global
$php = new CPHPVersion;
$php->name = "5.1";
$php->mandatory = true;
$php->description = "Version de PHP5 r�cente";
$php->reasons[] = "Int�gration du support XML natif : utilisation pour l'int�rop�rabilit� HPRIM XML'";
$php->reasons[] = "Int�gration de PDO : acc�s universel et s�curis� aux base de donn�es";
$php->reasons[] = "Conception objet plus �volu�e";
$versions[] = $php;

showHeader();

?>

<h2>V�rification des pr�requis</h2>

<h3>Version de PHP</h3>

<p>
  PHP est le langage d'ex�cution de script c�t� serveur de Mediboard. Il est 
  n�cessaire d'installer une version r�cente de PHP pour assurer le bon 
  fonctionnement du syst�me.
</p>

<p>
  N'h�sitez pas � vous rendre sur le site officiel de <a href="http://www.php.net/">http://www.php.net/</a>
  pour obtenir les derni�res versions de PHP.
</p>

<table class="tbl">

<tr>
  <th class="title" colspan="5">Version de PHP</th>
</tr>


<tr>
  <th>Num�ro de version</th>
  <th>Description</th>
  <th>Obligatoire ?</th>
  <th>Utilit�</th>
  <th>Installation ?</th>
</tr>
  
<?php // @codingStandardsIgnoreStart ?>  
<?php foreach ($versions as $prereq) { ?>
  <tr>
    <td><strong><?php echo $prereq->name; ?></strong></td>
    <td class="text"><?php echo nl2br($prereq->description); ?></td>
    <td>
      <?php if ($prereq->mandatory) { ?>
      Oui
      <?php } else { ?>
      Recommand�e
      <?php } ?>
    </td>
    <td class="text">
      <ul>
        <?php foreach ($prereq->reasons as $reason) { ?>
        <li><?php echo $reason; ?></li>
        <?php } ?>
      </ul>
    </td>
    <td>
      <?php if ($prereq->check()) { ?>
        <div class="info">Oui, Version <?php echo phpVersion(); ?></div>
      <?php } else { ?>
        <div class="<?php echo $prereq->mandatory ? "error" : "warning"; ?>">
          Non, Version <?php echo phpVersion(); ?>
        </div>
      <?php } ?>
    </td>
  </tr>
<?php } ?>
<?php // @codingStandardsIgnoreEnd ?>  
  
</table>

<h3>Extensions PECL</h3>
<p>
  PECL est une biblioth�que d'extensions binaires de PHP. 
  <br />
  La plupart des  extensions de base de PHP est fournie avec votre 
  distribution de PHP. Si toutefois certaines extensions sont manquantes,
  v�rifiez que :
</p>
<ul>
  <li>L'extension est install�e sur votre d�ploiement PHP</li>
  <li>L'extension est bien charg�e dans la configuration de PHP (php.ini)</li>
</ul>  
<p>
  N'h�sitez pas � vous rendre sur le site officiel de PHP <a href="http://www.php.net/">http://www.php.net/</a>
  et de PECL <a href="http://pecl.php.net/">http://pecl.php.net/</a>  pour 
  obtenir de plus amples informations. 
</p>

<table class="tbl" >

<tr>
  <th class="title" colspan="5">Extensions PECL</th>
</tr>

<tr>
  <th>Nom</th>
  <th>Description</th>
  <th>Obligatoire ?</th>
  <th>Utilit�</th>
  <th>Installation ?</th>
</tr>

<?php // @codingStandardsIgnoreStart ?>  
<?php foreach($extensions as $prereq) { ?>
  <tr>
    <td><strong><?php echo $prereq->name; ?></strong></td>
    <td class="text"><?php echo nl2br($prereq->description); ?></td>
    <td>
      <?php if ($prereq->mandatory) { ?>
      Oui
      <?php } else { ?>
      Recommand�e
      <?php } ?>
    </td>
    <td class="text">
      <ul>
        <?php foreach($prereq->reasons as $reason) { ?>
        <li><?php echo $reason; ?></li>
        <?php } ?>
      </ul>
    </td>
    <td>
      <?php if ($prereq->check()) { ?>
      <div class="info">Extension charg�e</div>
      <?php } else { ?>
      <div class="<?php echo $prereq->mandatory ? "error" : "warning"; ?>">Extension absente</div>
      <?php } ?>
    </td>
  </tr>
<?php } ?>
<?php // @codingStandardsIgnoreEnd ?>  

</table>

<h3>Packages PEAR</h3>

<p>
  PEAR est un framework de distributions de biblioth�ques �crites en PHP.
  <br />
  Si plusieurs ou tous les packages sont manquants, n'h�sitez pas � vous rendre 
  sur le site officiel <a href="http://pear.php.net/">http://pear.php.net/</a>
  pour les installer sur votre d�ploiement de PHP. 
</p>
  
<table class="tbl" >

<tr>
  <th class="title" colspan="6">Packages PEAR</th>
</tr>

<tr>
  <th>Nom</th>
  <th>Description</th>
  <th>Obligatoire ?</th>
  <th>Utilit�</th>
  <th>Statut</th>
  <th>Installation ?</th>
</tr>

<?php // @codingStandardsIgnoreStart ?>  
<?php foreach($packages as $prereq) { ?>
  <tr>
    <td><strong><?php echo $prereq->name; ?></strong></td>
    <td class="text"><?php echo nl2br($prereq->description); ?></td>
    <td>
      <?php if ($prereq->mandatory) { ?>
      Oui
      <?php } else { ?>
      Recommand�
      <?php } ?>
    </td>
    <td class="text">
      <ul>
        <?php foreach($prereq->reasons as $reason) { ?>
        <li><?php echo $reason; ?></li>
        <?php } ?>
      </ul>
    </td>
    <td><?php echo $prereq->status; ?></td>
    <td>
      <?php if ($prereq->check()) { ?>
        <div class="info">Package install�</div>
      <?php } else { ?>
        <div class="<?php echo $prereq->mandatory ? "error" : "warning"; ?>">Package manquant</div>
      <?php } ?>
    </td>
  </tr>
<?php } ?>
<?php // @codingStandardsIgnoreEnd ?>  

</table>

<div class="big-info">
  Certains packages Pear ne sont pas publi�s dans un statut <strong>stable</strong>, 
  bien que suffisemment fonctionnels pour Mediboard. 
  <br />Pour pouvoir installer les packages en statut <strong>beta</strong>, il peut �tre
  n�ccessaire de configurer PEAR avec la commande :
  
  <pre>pear config-set preferred_state beta</pre>
</div>

<h3>Droits d'acc�s distants</h3>

<p>
  Certaines ressources ne devraient pas �tre accessibles autrement que depuis le serveur local.
  <br />
  En revanche, il convient de s'assurer que certaines autres le soient bel et bien. 
</p>
  
<table class="tbl" >

<tr>
  <th class="title" colspan="3">Droits d'acc�s distants</th>
</tr>

<tr>
  <th>URL</th>
  <th>Pr�-requis</th>
  <th>Autorisation</th>
</tr>

<?php // @codingStandardsIgnoreStart ?>  
<?php require("testHTTP.php"); ?>
<?php
$success = true;

( array_key_exists( "HTTPS", $_SERVER ) ) ? $http = "https://" : $http = "http://";

$goodUrls[] = $http.dirname( dirname( $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) )."/tmp/locales-fr.js";

$badUrls[] = $http.dirname( dirname( $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) )."/files";
$badUrls[] = $http.dirname( dirname( $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) )."/tmp";
$badUrls[] = $http.dirname( dirname( $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) )."/tmp/mb-log.html";

$urls = array();

$urls = array_merge( testHTTPCode( $goodUrls, "Autoris�" ), testHTTPCode( $badUrls, "Interdit" ) );

foreach ( $urls as $url => $result ) {
  ?>
  <tr>
    <td><strong><?php echo $url; ?></strong></td>
    <td class="text"><?php echo $result["type"]; ?></td>
    <?php
      if ($result['result']) {
        (($result['type'] == "Autoris�")) ? $autorisation = "Autoris�" : $autorisation = "Interdit";
      }
      else {
        $success = false;
        (($result['type'] == "Autoris�")) ? $autorisation = "Interdit" : $autorisation = "Autoris�";
      }
    ?>
    <td class="<?php if ($result['result']) { echo 'ok'; } else { echo 'error'; } ?>" style="text-align: center"><?php echo $autorisation; ?></td>
  </tr>
  <?php
}
?>
<?php ?>
<?php // @codingStandardsIgnoreEnd ?>  

</table>

<?php 
require "valid.php";
checkAll();
showFooter(); 
?>
