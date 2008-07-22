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

/*********************************************************
 * Define a Constant reference to this application directory.
 *********************************************************/

define("MYDIR",dirname(__FILE__));

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
	$protocol = 'https';
else
	$protocol = 'http';

if ($_SERVER['SCRIPT_NAME'])
	$scriptPath = $_SERVER['SCRIPT_NAME'];
else
	$scriptPath = $_SERVER['PHP_SELF'];
	
if (!defined('MYPATH')) 
	define("MYPATH", $protocol."://".$_SERVER['HTTP_HOST'].str_replace(
												"\\", "/", 
												dirname($scriptPath)));

// The following lines set the MYURL constant.
if (file_exists(MYDIR.'/config/url.conf.php'))
	include_once (MYDIR.'/config/url.conf.php');
else
	include_once (MYDIR.'/config/url_default.conf.php');

if (!defined("MYURL"))
	define("MYURL", trim(MYPATH, '/')."/index.php");


define("LOAD_GUI", true);

/*********************************************************
 * Include our libraries
 *********************************************************/
require_once(dirname(__FILE__)."/main/include/libraries.inc.php");

try {

/*********************************************************
 * Include our configuration and setup scripts
 *********************************************************/
	require_once(dirname(__FILE__)."/main/include/setup.inc.php");

/*********************************************************
 * Execute our actions
 *********************************************************/
	if (defined('ENABLE_TIMERS') && ENABLE_TIMERS) {
		require_once(HARMONI."/utilities/Timer.class.php");
		$execTimer = new Timer;
		$execTimer->start();
		ob_start();
	}

	$harmoni->execute();

// Handle certain types of uncaught exceptions specially. In particular,
// Send back HTTP Headers indicating that an error has ocurred to help prevent
// crawlers from continuing to pound invalid urls.
} catch (UnknownActionException $e) {
	ConcertoErrorPrinter::handleException($e, 400);
} catch (NullArgumentException $e) {
	ConcertoErrorPrinter::handleException($e, 400);
} catch (PermissionDeniedException $e) {
	ConcertoErrorPrinter::handleException($e, 403);
} catch (UnknownIdException $e) {
	ConcertoErrorPrinter::handleException($e, 404);
}
// Default 
catch (Exception $e) {
	ConcertoErrorPrinter::handleException($e, 500);
}

if (defined('ENABLE_TIMERS') && ENABLE_TIMERS) {
	$execTimer->end();
	$output = ob_get_clean();
	
	ob_start();
	print "\n<table>\n<tr><th align='right'>Execution Time:</th>\n<td align='right'><pre>";
	printf("%1.6f", $execTimer->printTime());
	print "</pre></td></tr>\n</table>";
	
	
	$dbhandler = Services::getService("DBHandler");
	printpre("NumQueries: ".$dbhandler->getTotalNumberOfQueries());
	if (isset($dbhandler->recordQueryCallers) && $dbhandler->recordQueryCallers)
		print $dbhandler->getQueryCallerStats();
	
	try {
		$db = Harmoni_Db::getDatabase('concerto_db');
		print "<br/><div>".$db->getStats()."</div>";
	} catch (UnknownIdException $e) {
	}
	
// 	printpreArrayExcept($_SESSION, array('__temporarySets'));
	// debug::output(session_id());
	// Debug::printAll();
	
	print "\n\t</body>\n</html>";
	print preg_replace('/<\/body>\s*<\/html>/i', ob_get_clean(), $output);
}

?>
