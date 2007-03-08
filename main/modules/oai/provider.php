<?php
/**
 * A single entry-point for the OAI harvesters that don't deal well with our
 * module/action urls
 * 
 * @since 3/8/07
 * @package concerto.oai
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
$_REQUEST['module'] = $_POST['module'] = $_GET['module'] = 'oai';
$_REQUEST['action'] = $_POST['action'] = $_GET['action'] = 'provider';

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

require_once(dirname(__FILE__)."/../../../index.php");

?>