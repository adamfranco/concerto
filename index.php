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
require_once(dirname(__FILE__)."/../harmoni/core/utilities/Timer.class.php");
$loadTimer =& new Timer;
$loadTimer->start();

/*********************************************************
 * Define a Constant reference to this application directory.
 *********************************************************/

define("MYDIR",dirname(__FILE__));

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
	$protocol = 'https';
else
	$protocol = 'http';

define("MYPATH", $protocol."://".$_SERVER['HTTP_HOST'].str_replace(
												"\\", "/", 
												dirname($_SERVER['PHP_SELF'])));
define("MYURL", MYPATH."/index.php");

define("LOAD_GUI", true);

/*********************************************************
 * Include our libraries
 *********************************************************/
require_once(dirname(__FILE__)."/main/include/libraries.inc.php");

/*********************************************************
 * Include our configuration and setup scripts
 *********************************************************/
require_once(dirname(__FILE__)."/main/include/setup.inc.php");

/*********************************************************
 * Execute our actions
 *********************************************************/

$loadTimer->end();


$execTimer =& new Timer;
// apd_set_pprof_trace();

$execTimer->start();

$harmoni->execute();

$execTimer->end();
print "\n<table>\n<tr><th align='right'>Load Time:</th>\n<td align='right'><pre>";
printf("%1.6f", $loadTimer->printTime());
print "</pre></td></tr>";
print "\n<tr><th align='right'>Execution Time:</th>\n<td align='right'><pre>";
printf("%1.6f", $execTimer->printTime());
print "</pre></td></tr>\n</table>";

$dbhandler =& Services::getService("DBHandler");
printpre("NumQueries: ".$dbhandler->getTotalNumberOfQueries());

// printpre($_SESSION);
// debug::output(session_id());
// Debug::printAll();

?>
