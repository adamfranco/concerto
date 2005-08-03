<?php

/**
 * Set up the DatabaseHandler
 *
 * USAGE: Copy this file to database.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
 	$configuration =& new ConfigurationProperties;
	Services::startManagerAsService("DatabaseManager", $context, $configuration);
	
	//Set up the database connection
	$databaseManager =& Services::getService("DatabaseManager");
	$dbName = "my_concerto_database";
	$dbID = $databaseManager->addDatabase( new MySQLDatabase("localhost", $dbName,"test","test") );
	$databaseManager->pConnect($dbID);
	unset($databaseManager); // done with that for now