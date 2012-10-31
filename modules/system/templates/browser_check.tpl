{{* $Id: ajax_errors.tpl 7494 2009-12-02 16:34:38Z phenxdesign $ *}}

{{*
  * @package Mediboard
  * @subpackage system
  * @version $Revision: 7494 $
  * @author SARL OpenXtrem
  * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
  *}}


<script type="text/javascript">

Main.add(function() {
});
</script>

<div style="font-size: 120%; margin: 50px;">

<div class="small-info">
  <div><strong>Cette page est un autodiagnostic permettant de savoir si votre poste 
  permet l'usage de Mediboard dans de bonnes conditions.</strong></div>
  <div>Les v�rifications sont nature qualitatives et quantitatives.</div>
</div>

<h1>1. Navigateur</h1>
<div class="compact">Les navigateurs sont tr�s in�gaux en termes de performances et fonctionalit�s</div>
<div class="compact">Pour vous permettre la meilleure exp�rience web possible, l'usage de Mediboard exige un navigateur moderne.</div>
<div class="small-success">
  Navigateur de type <strong>Mozilla Firefox</strong> en version <strong>12</strong>.
</div>

<h1>2. R�solution</h1>
<div class="compact">Le contenu riche des affichage n�cessite une r�solution minimale pour des raisons �videntes de confort,
 en particulier sur des dispositifs mobiles.</div>
<div class="compact">Pensez � jouer ce test en mettant votre navigateur en condition r�elles (plein �cran par exemple).</div> 
<div class="small-success">
  la r�solution est de <strong>1420x960</strong> sup�rieur au minimum de 1280x640.
</div>


<h1>3. Performances</h1>
<div class="compact">Pour plus de conforts et de fluidit�, il est pr�f�rable d'utiliser poste de travail r�cent.</div>
<div class="compact">Cette section en v�rifie les performances en vitesse de rendu, le test prend quelques secondes.</div>
<div class="small-success">
  Score de rendu <strong>1281</strong> sup�rieur au minimum de 600.
</div>

<h1>4. Bande passante</h1>
<div class="compact">Comme toute application Mediboard a besoin d'un minimum de bande passante.</div>
<div class="compact">Cette section v�rifie les d�bits montants et descendants vers le serveur de Mediboard, le test prend quelques secondes.</div>
<div class="small-warning">
  <div>D�bit montant de <strong>389kb/s</strong> sup�rieur au minimum de 80kb/s</div>
  <div>D�bit descendant de <strong>410kb/s</strong> inf�rieur au minimum de 512kb/s</div>
</div>
