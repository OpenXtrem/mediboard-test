<?php
/**
 * $Id$
 *
 * @package    Mediboard
 * @subpackage Cabinet
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 */

CCanDo::checkRead();

global $can, $m, $exam_audio;

$exam_audio = new CExamAudio;
$exam_audio->load(CValue::getOrSession("examaudio_id"));

CAppUI::requireModuleFile($m, "inc_graph_audio_vocal");

AudiogrammeVocal::$graph->Stroke();