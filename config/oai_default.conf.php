<?php

/**
 * Set up the parameters needed for OAI harvisting of metadata
 *
 * USAGE: Copy this file to oai.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

// Specify the include path for the PEAR libraries
ini_set('include_path', ini_get('include_path').':/usr/local/lib/php/PEAR');

define('OAI_REPOSITORY_NAME', 'Concerto at Middlebury College');


define('OAI_DBID', $dbID);

define('OAI_DB_HOST', $dbHost);
define('OAI_DB_USER', $dbUser);
define('OAI_DB_PASSWD', $dbPass);
define('OAI_DB_NAME', $dbName);

// define('OAI_DB_HOST', 'localhost');
// define('OAI_DB_USER', 'username');
// define('OAI_DB_PASSWD', 'password');
// define('OAI_DB_NAME', 'concerto_oai_cache');