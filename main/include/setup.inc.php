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

/*********************************************************
 * Set a low debug level to not store up all queries.
 *********************************************************/
debug::level(-100);

/******************************************************************************
 * Start the session so that we can use the session for storage.
 ******************************************************************************/
$harmoni->startSession();


/*********************************************************
 * If we pressed a button to reset concerto, clear the session
 * and delete our tables.
 *********************************************************/
if (isset($_REQUEST["reset_concerto"])) {
	$_SESSION = array();
	if (file_exists(MYDIR.'/config/database.conf.php'))
		require_once (MYDIR.'/config/database.conf.php');
	else
		require_once (MYDIR.'/config/database_default.conf.php');
	
	$dbc =& Services::getService("DatabaseManager");
	$tableList = $dbc->getTableList($dbID);
	if (count($tableList)) {
		$queryString = "DROP TABLE `".implode("`, `", $tableList)."`;";
		print $queryString;
		$query =& new GenericSQLQuery($queryString);
		$dbc->query($query, $dbID);
	}
}

/******************************************************************************
 * Include our configs
 ******************************************************************************/
require_once(HARMONI."/oki2/shared/ConfigurationProperties.class.php");
require_once(OKI2."/osid/OsidContext.php");

$configs = array(	'harmoni',
					'action',
					'database',
					'id',
					'authentication',
					'gui',
					'language',
					'authorization',
					'sets',
					'mime',
					'imageprocessor',
					'hierarchy',
					'installer',
					'agent',
					'datamanager',
					'repository',
					'post_config_setup',
					'viewer'
				);

foreach ($configs as $config) {
	if (file_exists(MYDIR.'/config/'.$config.'.conf.php'))
		require_once (MYDIR.'/config/'.$config.'.conf.php');
	else
		require_once (MYDIR.'/config/'.$config.'_default.conf.php');
}
