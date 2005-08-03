<?php
/**
 * This is the main control script for the application.
 *
 * @package concerto
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
error_reporting(E_ALL);

// Define a Constant reference to this application directory.

define("MYDIR",dirname(__FILE__));
define("MYPATH", str_replace("\\", "/", dirname($_SERVER['PHP_SELF'])));
define("MYURL", $_SERVER['PHP_SELF']);

define("OKI_VERSION", 2);
define("LOAD_THEMES", false);
define("LOAD_GUI", true);
define("LOAD_AUTHENTICATION", false);

/******************************************************************************
 * Include Harmoni - required
 ******************************************************************************/
$harmoniPath = "../harmoni/harmoni.inc.php";
if (!file_exists($harmoniPath)) {
	print "<h2>Harmoni was not found in the specified location, '";
	print $harmoniPath;
	print "'. Please install Harmoni there or change the location specifed.</h2>";
	print "<h3>Harmoni is part of the Harmoni project and can be downloaded from <a href='http://sf.net/projects/harmoni/'>http://sf.net/projects/harmoni/</a></h3>";
}
require_once ($harmoniPath);

/******************************************************************************
 * Include Polyphony
 ******************************************************************************/
$polyphonyPath = "../polyphony/polyphony.inc.php";
if (!file_exists($polyphonyPath)) {
	print "<h2>Polyphony was not found in the specified location, '";
	print $polyphonyPath;
	print "'. Please install Polyphony there or change the location specifed.</h2>";
	print "<h3>Polyphony is part of the Harmoni project and can be downloaded from <a href='http://sf.net/projects/harmoni/'>http://sf.net/projects/harmoni/</a></h3>";
}
require_once ($polyphonyPath);

/******************************************************************************
 * Include our libraries
 ******************************************************************************/
require_once "main/library/ConcertoMenuGenerator.class.php";
require_once "main/library/printers/AssetPrinter.static.php";
require_once "main/library/printers/RepositoryPrinter.static.php";

/******************************************************************************
 * Include any theme classes we want to use. They need to be included prior
 * to starting the session so that they can be restored properly.
 ******************************************************************************/
require_once(HARMONI."GUIManager/Themes/SimpleLinesTheme.class.php");


/******************************************************************************
 * Start the session so that we can use the session for storage.
 ******************************************************************************/
$harmoni->startSession();


/*********************************************************
 * If we pressed a button to reset concerto, clear the session
 * and delete our tables.
 *********************************************************/
if (isset($_REQUEST["reset_concerto"])) {
	$_SESSION = array();
	if (file_exists('config/database.conf.php'))
		require_once ('config/database.conf.php');
	else
		require_once ('config/database_default.conf.php');
	
	$dbc =& Services::getService("DatabaseManager");
	$tableList = $dbc->getTableList($dbID);
	if (count($tableList)) {
		$queryString = "DROP TABLE `".implode("`, `", $tableList)."`;";
		print $queryString;
		$query =& new GenericSQLQuery($queryString);
		$dbc->query($query, $dbID);
	}
}

/******************************************************************************
 * Include our configs
 ******************************************************************************/
require_once(HARMONI."/oki2/shared/ConfigurationProperties.class.php");
require_once(OKI2."/osid/OsidContext.php");

$configs = array(	'harmoni',
					'action',
					'database',
					'id',
					'agent',
					'authentication',
					'gui',
					'language',
					'authorization',
					'sets',
					'mime',
					'imageprocessor',
					'hierarchy',
					'installer',
					'datamanager',
					'repository',
					'post_config_setup'
				);

foreach ($configs as $config) {
	if (file_exists('config/'.$config.'.conf.php'))
		require_once ('config/'.$config.'.conf.php');
	else
		require_once ('config/'.$config.'_default.conf.php');
}

/******************************************************************************
 * 	Execute our actions
 ******************************************************************************/

$harmoni->execute();


// printpre($_SESSION);
// debug::output(session_id());
// Debug::printAll();

?>
