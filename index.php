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
 
 error_reporting(E_ALL & ~E_NOTICE);

// Define a Constant reference to this application directory.
define("MYDIR",dirname(__FILE__));
define("MYPATH",str_replace($_SERVER['DOCUMENT_ROOT'], "", str_replace("\\", "/", dirname(__FILE__))));
define("MYURL",str_replace($_SERVER['DOCUMENT_ROOT'], "", str_replace("\\", "/", dirname(__FILE__)))."/index.php");

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


/******************************************************************************
 * Include our configs
 ******************************************************************************/
$configPath = "config/harmoni.inc.php";
if (!file_exists($configPath)) {
	print "<h2>The configuration file was not found in the specified location, '";
	print $configPath;
	print "'. Please copy the sample config: 'config/harmoni.inc.php.dist' to '$configPath'.</h2>";
}
require_once ($configPath);


/******************************************************************************
 * 	Execute our actions
 ******************************************************************************/

$harmoni->execute();


// printpre($_SESSION);
// debug::output(session_id());
// Debug::printAll();

?>
