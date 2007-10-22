<?php

/**
 * Library locations configuration file.
 *
 * USAGE: Copy this file to libraries.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/*********************************************************
 * Harmoni Location
 * 		the location on the file system
 *********************************************************/
define("HARMONI_DIR", MYDIR."/main/harmoni/");

/*********************************************************
 * Polyphony location
 *		DIR: the location on the file system
 *		PATH: the location as seen by the browser. For image urls.
 *********************************************************/
define("POLYPHONY_DIR", MYDIR."/main/polyphony/");
define("POLYPHONY_PATH", dirname(MYURL)."/main/polyphony/");