<?php
/**
 * This script is a command-line entry point for the concerto OAI-updater, 
 * allowing updates to be run nightly via cron (for instance).
 *
 * 
 *
 * 
 * @since 3/8/07
 * @package concerto.oai
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/../../library/ArgumentParser.inc.php");
require_once(dirname(__FILE__)."/../../library/ArrayKeyFunctions.inc.php");

if (count($_SERVER['argv'])) {
	$options = getOptionArray(__FILE__, $_SERVER['argv']);
	$params = getParameterArray(__FILE__, $_SERVER['argv']);
} else {
	header('Content-type: text/plain;');
	$options = array();
	$params = array();
}

$helpFlags = array_intersect_key(array('help'=>TRUE, 'help'=>TRUE, 'h'=>TRUE, '?'=>TRUE), $options);

// check the number of args and print help if necessary
if (count($helpFlags) || count($options) || count ($params) )
{
?>

This is a command line script that will populate the OAI data tables from the
repositories in Concerto.

Options:
    
Usage:

<?php echo $argv[0]; ?> [options] 

<?php
}

// If we have the right number of args, run.
else {
	// Force the use of the oai.provider action
	$_REQUEST['module'] = $_POST['module'] = $_GET['module'] = 'oai';
	$_REQUEST['action'] = $_POST['action'] = $_GET['action'] = 'update';
	
	if (!defined('MYPATH')) {
		define("MYPATH", '');
	}
	
	if (!defined('OAI_UPDATE_OUTPUT_HTML')) {
		define("OAI_UPDATE_OUTPUT_HTML", false);
	}
	
	// Now that we have our path and module/action fixed, execute as normal
	require_once(dirname(__FILE__)."/../../../index.php");
}
?>