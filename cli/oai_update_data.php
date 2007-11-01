<?php
/**
 * This script is a command-line entry point for the concerto OAI-updater, 
 * allowing updates to be run nightly via cron (for instance).
 *
 * 
 * 
 * @since 10/30/07
 * @package concerto.oai
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

if (!defined('HELP_TEXT')) 
	define("HELP_TEXT", 
"This is a command line script that will populate the OAI data tables from the
repositories in Concerto. It takes no arguments or parameters.
");

if (!defined("OAI_UPDATE_OUTPUT_HTML"))
	define("OAI_UPDATE_OUTPUT_HTML", false);

$_SERVER['argv'][] = '--module=oai';
$_SERVER['argv'][] = '--action=update';

require(dirname(__FILE__)."/index_cli.php");
?>