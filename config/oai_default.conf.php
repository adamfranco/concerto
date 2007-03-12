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

$config =& new ConfigurationProperties;
$config->addProperty('ENABLE_OAI', $arg0 = false);

$config->addProperty('OAI_REPOSITORY_NAME', $arg1 = 'Concerto at Example University');
$config->addProperty('OAI_REPOSITORY_ID', $arg2 = 'concerto.example.edu');
$config->addProperty('OAI_ADMIN_EMAIL', $arg3 = 'admin@example.edu');

$config->addProperty('OAI_DBID', $dbID);
$config->addProperty('OAI_DB_HOST', $dbHost);
$config->addProperty('OAI_DB_USER', $dbUser);
$config->addProperty('OAI_DB_PASSWD', $dbPass);
$config->addProperty('OAI_DB_NAME', $dbName);

$config->addProperty('OAI_TOKEN_DIR', $arg4 = '/tmp/concerto-oai_tokens');

// The search order is the array order. I.e, if the IP of the harvester matches the
// first entry, then that entry will be used and later ones ignored.
$harvesterConfig = array(
	array(	"name" 				=> 'any_harvester',	// Lowercase letters and underscores only
			"ips_allowed"		=> array(),			// An array of IP ranges, if 
													// empty, any will be allowed
													
			"repository_ids"	=> array(),			// An array of repository ids, if 
													// empty, all will be allowed
													
			"auth_group_ids"	=> array('edu.middlebury.agents.everyone')			
													// An array of groups ids to check
													// view authorization for. If 
	)												// empty, all repositories will be
													// included no matter who can view them.
			
);
$config->addProperty('OAI_HARVESTER_CONFIG', $harvesterConfig);

$harmoni =& Harmoni::instance();
$harmoni->attachData('OAI_CONFIG', $config);
unset($config, $arg0, $arg1, $arg2, $arg3, $arg4, $harvesterConfig);