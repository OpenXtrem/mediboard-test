<?php

/**
 * $Id$
 *  
 * @category CLI
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision$
 * @link     http://www.mediboard.org
 */

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\InvalidArgumentException;

use SVNClient\WorkingCopy;

/**
 * deploy:switchbranch command
 */
class DeploySwitchBranch extends MediboardCommand {
  /**
   * @see parent::configure()
   */
  protected function configure() {
    $aliases = array(
      'deploy:sb'
    );

    $this
      ->setName('deploy:switchbranch')
      ->setAliases($aliases)
      ->setDescription('Switch to another branch')
      ->setHelp('Executes an "svn switch" on the root, and changes every external folder to the right branch')
      ->addArgument(
        'branch',
        InputArgument::OPTIONAL,
        'Which branch ?'
      )
      ->addOption(
        'path',
        'p',
        InputOption::VALUE_OPTIONAL,
        'Working copy root',
        realpath(__DIR__."/../../")
      );
  }

  /**
   * @see parent::execute()
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $branch = $input->getArgument('branch');
    $path = $input->getOption('path');

    if (!is_dir($path)) {
      throw new InvalidArgumentException("'$path' is not a valid directory");
    }

    $wc = new WorkingCopy($path);
    $url = $wc->getURL();

    // Find the current branch name
    $current_branch = "trunk";

    $matches = array();
    if (preg_match("@/branches/(.*)@", $url, $matches)) {
      $current_branch = $matches[1];
    }

    $this->out($output, "Current branch: '<b>$current_branch</b>'");

    if (!$branch) {
      $branch = $this->getBranch($current_branch, $wc, $output);
    }

    if ($branch != "trunk") {
      $branch = "branches/$branch";
    }

    // Switch externals
    $paths = array(
      ".",
      "modules",
    );

    foreach ($paths as $_path) {
      $this->switchExternal($wc, $branch, $_path, $output);
    }

    $this->out($output, "Externals switched successfuly");

    // Switch working copy ...
    $new_url = $wc->getRepository()->getURL()."/".$branch;

    $this->out($output, "Switching working copy to '<b>$branch</b>' ...");

    try {
      $wc->sw($new_url);
    }
    catch (\SVNClient\Exception $e) {
      $output->writeln("<error>".$e->getMessage()."</error>");
      return;
    }

    $this->out($output, "Working copy successfuly switched to '<b>$branch</b>'");
  }

  /**
   * Switch branch of external modules
   *
   * @param WorkingCopy     $wc     Working copy
   * @param string          $branch Branch name
   * @param string          $path   Path to change the svn:externals property of
   * @param OutputInterface $output Output interface
   *
   * @return void
   */
  protected function switchExternal(WorkingCopy $wc, $branch, $path, OutputInterface $output) {
    $properties = $wc->listProperties($path);

    if (!isset($properties["svn:externals"])) {
      return;
    }

    $this->out($output, "Switching svn:externals of '<b>$path</b>' to '<b>$branch</b>' ...");

    $externals = preg_split("/[\r\n]+/", $properties["svn:externals"]);

    foreach ($externals as $_i => $_external) {
      $matches = array();
      if (preg_match("@(.*)\s+(.*)@", trim($_external), $matches)) {
        array_shift($matches);
        foreach ($matches as &$_match) {
          $_match = preg_replace("@(/trunk/|/branches/[^/]+/)@", "/$branch/", $_match);
        }
        unset($_match);

        $externals[$_i] = implode(" ", $matches);
      }
    }

    $multiline = implode("\n", $externals)."\n";

    $wc->setProperty($path, "svn:externals", $multiline);

    $this->out($output, "Properties updated on '<b>$path</b>'");
  }

  /**
   * Get branch
   *
   * @param string          $current_branch Current branch
   * @param WorkingCopy     $wc             Working copy
   * @param OutputInterface $output         Output
   *
   * @return string
   */
  protected function getBranch($current_branch, WorkingCopy $wc, OutputInterface $output) {
    $branches_detail = $wc->getBranches();

    $branches = array(
      "trunk"
    );

    foreach ($branches_detail as $_branch) {
      $_branch_name = $_branch['name'];

      if ($_branch['name'] == $current_branch) {
        $_branch_name .= " (current)";
      }

      $branches[] = $_branch_name;
    }

    /** @var \Symfony\Component\Console\Helper\DialogHelper $dialog */
    $dialog = $this->getHelperSet()->get('dialog');

    $branch_index = $dialog->select(
      $output,
      'Which branch ?',
      $branches
    );

    if ($branch_index == 0) {
      return "trunk";
    }

    return $branches_detail[$branch_index-1]["name"];
  }
}
