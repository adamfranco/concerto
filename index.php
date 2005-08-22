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

/*********************************************************
 * Define a Constant reference to this application directory.
 *********************************************************/

define("MYDIR",dirname(__FILE__));
define("MYPATH", "http://".$_SERVER['HTTP_HOST'].str_replace(
												"\\", "/", 
												dirname($_SERVER['PHP_SELF'])));
define("MYURL", MYPATH."/index.php");

define("LOAD_THEMES", false);
define("LOAD_GUI", true);
define("LOAD_AUTHENTICATION", false);

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
$harmoni->execute();


// printpre($_SESSION);
// debug::output(session_id());
// Debug::printAll();

?>
