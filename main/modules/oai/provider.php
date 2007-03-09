<?php
/**
 * This script is a custom entry-point for the OAI harvesters that don't deal 
 * well with our module/action urls, assuming that there are no GET parameters
 * in the provider's base URL.
 *
 * This script can be executed directly, or for enhanced security, keep all
 * of the concerto code in a non-web accessible directory and symlink to 
 * the icons and javascript directories as well index.php and this file, provider.php
 * 
 * @since 3/8/07
 * @package concerto.oai
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
 
// Force the use of the oai.provider action
$_REQUEST['module'] = $_POST['module'] = $_GET['module'] = 'oai';
$_REQUEST['action'] = $_POST['action'] = $_GET['action'] = 'provider';


// As this is a custom entry point, we want all of our links to point to the
// normal entry point, concerto/index.php.
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
	$protocol = 'https';
else
	$protocol = 'http';

if (!defined('MYPATH')) {
	$path = $protocol."://".$_SERVER['HTTP_HOST'].str_replace(
												"\\", "/", 
												dirname($_SERVER['PHP_SELF']));
	$path = str_replace('/main/modules/oai', '', $path);					
	define("MYPATH", $path);
}

// Now that we have our path and module/action fixed, execute as normal
require_once(dirname(__FILE__)."/../../../index.php");

?>