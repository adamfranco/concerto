<?php

/**
 * Debugging and testing options.
 *
 * USAGE: Copy this file to debug.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/*********************************************************
 * Set to true to enable functionality of resetting the Concerto database to
 * a fresh install. Useful for data-corrupting testing/development.
 * Enabling this will allow all of your data to be deleted with one click.
 *********************************************************/
define ("ENABLE_RESET", false);

/*********************************************************
 * Enable the creation of a set of testing users (dwarves)
 * for the purpose of testing user/group functionality.
 *********************************************************/
define ("ENABLE_DWARVES", false);


/*********************************************************
 * Enable the display of timers and query-counters.
 * (Useful for debugging/testing).
 *********************************************************/
define ("ENABLE_TIMERS", false);
 
/*********************************************************
 * PHP error reporting setting. uncomment to enable override
 * of default environment.
 *********************************************************/
error_reporting(E_ALL);