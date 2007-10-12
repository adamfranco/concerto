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
 * @since 10/12/07
 * @package concerto.oai
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
 require_once(dirname(__FILE__)."/main/modules/oai/provider.php");

?>