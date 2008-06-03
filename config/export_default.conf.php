<?php
/**
 * @since 1/28/08
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2008, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
 
if (!defined('XML_EXPORT_TMP_DIR'))
	define('XML_EXPORT_TMP_DIR', '/tmp');
	
/*********************************************************
 * If Safe-Mode is on, then execution of shell commands is
 * restricted to those in a certain directory, make a symbolic 
 * link to 'tar' there and set the following option.
 *********************************************************/
// if (!defined('XML_EXPORT_EXEC_PATH'))
// 	define('XML_EXPORT_EXEC_PATH', '/usr/local/bin');