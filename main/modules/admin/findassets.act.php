<?php

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");


//apd_set_pprof_trace(); 

class findassetsAction 
	extends MainWindowAction {
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("MediaDB Export");
	}

	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {	
		$dbHandler =& Services::getService("DBHandler");
		$repositoryManager =& Services::getService("Repository");
		$setManager =& Services::getService("Sets");
		$idManager =& Services::getService("Id");
		
		$mdbIndex = $dbHandler->addDatabase(
			new MySQLDatabase("localhost", "whitey_mediadb", "test", "test"));
		$dbHandler->connect($mdbIndex);
		
		$collectionsQuery =& new SelectQuery();
		$collectionsQuery->addTable("mediasets");
		$collectionsQuery->addColumn("mediasets.id", "collectionId");
		$collectionsQuery->addColumn("mediasets.title", "collectionName");
		$collectionsQuery->addColumn("mediasets.keywords",
			"collectionDescription");

		$collections =& $dbHandler->query($collectionsQuery, $mdbIndex);
		while ($collections->hasMoreRows()) {
			$collection =& $collections->next();
			
			$assetsQuery =& new SelectQuery();
			$assetsQuery->addTable("media");
			$assetsQuery->addColumn("*");
			$assetsQuery->addWhere(
				"id_set = ".$collection['collectionId']);
			
			$assets =& $dbHandler->query($assetsQuery, $mdbIndex);
			
			print "Collection: ".$collection['collectionId']."\t #Assets: ".
				$assets->getNumberOfRows()."<br/>";
			
		}
		exit();
	}
}
