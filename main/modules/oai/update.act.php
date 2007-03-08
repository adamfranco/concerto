<?php
/**
 * @package concerto.modules.admin
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * 
 * 
 * @package concerto.modules.admin
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class updateAction 
	extends Action
{
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
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function execute () {
		$harmoni =& Harmoni::instance();
		if (!isset($_SESSION['oai_table_setup_complete'])) {
			$dbc =& Services::getService("DatabaseManager");
			$tables = $dbc->getTableList(OAI_DBID);
			
			if (!in_array('oai_records', $tables))
				SQLUtils::runSQLfile(dirname(__FILE__)."/phpoai2/doc/oai_records_mysql.sql", OAI_DBID);
			
			$_SESSION['oai_table_setup_complete'] = true;
		}
		
		while (ob_get_level())
			ob_end_flush();
			
		$repositoryManager =& Services::getService('Repository');
		$authorizationManager =& Services::getService('AuthZ');
		$idManager =& Services::getService("IdManager");
		$dbc =& Services::getService("DatabaseManager");
				
		$baseCheckQuery =& new SelectQuery;
		$baseCheckQuery->addTable('oai_records');
		$baseCheckQuery->addColumn('datestamp');
		$baseCheckQuery->addColumn('deleted');
		
		$baseUpdateQuery =& new UpdateQuery;
		$baseUpdateQuery->setTable('oai_records');
		$baseUpdateQuery->setColumns(array(
			'datestamp',
			'deleted',
			'oai_set',
			'dc_title',
			'dc_description',
// 			'dc_creator',
// 			'dc_subject',
// 			'dc_contributor',
// 			'dc_publisher',
// 			'dc_date',
// 			'dc_type',
// 			'dc_format',
// 			'dc_identifier',
// 			'dc_source',
// 			'dc_language',
// 			'dc_relation',
// 			'dc_coverage',
// 			'dc_rights'
		));
		$dcUpdateColumns = array(
			'datestamp',
			'deleted',
			'oai_set',
			'dc_title',
			'dc_description',
			'dc_creator',
			'dc_subject',
			'dc_contributor',
			'dc_publisher',
			'dc_date',
			'dc_type',
			'dc_format',
			'dc_identifier',
			'dc_source',
			'dc_language',
			'dc_relation',
			'dc_coverage',
			'dc_rights'
		);
		
		$baseInsertQuery =& new InsertQuery;
		$baseInsertQuery->setTable('oai_records');
		$baseInsertQuery->setColumns(array(
			'datestamp',
			'repository',
			'oai_identifier',
			'deleted',
			'oai_set',
			'dc_title',
			'dc_description',
// 			'dc_creator',
// 			'dc_subject',
// 			'dc_contributor',
// 			'dc_publisher',
// 			'dc_date',
// 			'dc_type',
// 			'dc_format',
// 			'dc_identifier',
// 			'dc_source',
// 			'dc_language',
// 			'dc_relation',
// 			'dc_coverage',
// 			'dc_rights'		
		));
		$dcInsertColumns = array(
			'datestamp',
			'repository',
			'oai_identifier',
			'deleted',
			'oai_set',
			'dc_title',
			'dc_description',
			'dc_creator',
			'dc_subject',
			'dc_contributor',
			'dc_publisher',
			'dc_date',
			'dc_type',
			'dc_format',
			'dc_identifier',
			'dc_source',
			'dc_language',
			'dc_relation',
			'dc_coverage',
			'dc_rights'		
		);
		
		$baseDeleteQuery =& new UpdateQuery;
		$baseDeleteQuery->setTable('oai_records');
		$baseDeleteQuery->setColumns(array(
			'deleted',
			'datestamp'
		));
		$baseDeleteQuery->setValues(array(
			"'true'",
			"NOW()"
		));
		
		$baseUndeleteQuery =& new UpdateQuery;
		$baseUndeleteQuery->setTable('oai_records');
		$baseUndeleteQuery->setColumns(array(
			'deleted',
			'datestamp'
		));
		$baseUndeleteQuery->setValues(array(
			"'false'",
			"NOW()"
		));
		
		$forceUpdate = false;
		$repositories =& $repositoryManager->getRepositories();
		
		$r = 0;
		$numR = $repositories->count();
		$numUpdates = 0;	
		$numDeleted = 0;	
		$message = _('Updating OAI records for repository (%1 of %2) : ');
		$message = str_replace('%2', $numR, $message);
		$instituteId =& $idManager->getId('edu.middlebury.agents.users');
		$viewId =& $idManager->getId('edu.middlebury.authorization.view');
		
		require_once(HARMONI."/utilities/Timer.class.php");
		$timer =& new Timer;
		$timer->start();
		$existingRepositoryIds = array();
		
		while ($repositories->hasNext()) {
			$r++;
			$updatesInRepository = 0;
			$repository =& $repositories->next();
			$repositoryId =& $repository->getId();
			
			$existingRepositoryIds[] = "'".addslashes($repositoryId->getIdString())."'";
			
			$assets =& $repository->getAssets();			
			$status =& new StatusStars(
				str_replace('%1', $r, $message).$repository->getDisplayName());
			$status->initializeStatistics($assets->count());
			
			$existingAssetIds = array();
			
			while ($assets->hasNext()) {
				$asset =& $assets->next();
				$assetId =& $asset->getId();
				$existingAssetIds[] = "'".addslashes($assetId->getIdString())."'";
				$modificationDate =& $asset->getModificationDate();
				
				$query =& $baseCheckQuery->copy();
				$query->addWhere(
					"repository='".addslashes($repositoryId->getIdString())."'");
				$query->addWhere(
					"oai_identifier='".addslashes($assetId->getIdString())."'");
				
				$result =& $dbc->query($query, OAI_DBID);
				
				$values = array();
				$values[] = 'NOW()';
				
				if (!$result->getNumberOfRows()) {
// 					printpre("Doesn't exist:\t".$asset->getDisplayName()."");
					$query =& $baseInsertQuery->copy();
					$values[] = "'".addslashes($repositoryId->getIdString())."'";
					$values[] = "'".addslashes($assetId->getIdString())."'";
					if ($this->hasDublinCore($asset)) {
						$query->setColumns($dcInsertColumns);
					}
				} else {
// 					printpre("Exists:\t".$asset->getDisplayName()."");
					if ($modificationDate->isGreaterThan(
							DateAndTime::fromString($result->field('datestamp')))
						|| $forceUpdate)
					{
// 						printpre("\tUpdating:\t".$asset->getDisplayName());
						$query =& $baseUpdateQuery->copy();
						$query->addWhere(
							"repository='".addslashes($repositoryId->getIdString())."'");
						$query->addWhere(
							"oai_identifier='".addslashes($assetId->getIdString())."'");
						
						if ($this->hasDublinCore($asset)) {
							$query->setColumns($dcUpdateColumns);
						}
					} 
					// If it is up to date, skip.
					else {
						$query = null;
					}
				}
				
				$isCurrentlyDeleted = (($result->getNumberOfRows() &&$result->field('deleted') == 'true')?true:false);
				$result->free();
				
				if ($query) {
				//Add the data fields
					// Deleted
					if ($authorizationManager->isAuthorized($instituteId, $viewId, $assetId)) {
						$values[] = "'false'";
					} else {
						$values[] = "'true'";
					}
						
					// oai_set
					$values[] = "''";
					
					// title
					$values[] = "'".addslashes($asset->getDisplayName())."'";
					
					// description
					$values[] = "'".addslashes($asset->getDescription())."'";
					
					if ($this->hasDublinCore($asset)) {
						$this->addDublinCoreValues($asset, $values);
					}
					
					if (method_exists($query, 'addRowOfValues'))
						$query->addRowOfValues($values);
					else
						$query->setValues($values);
						
					$dbc->query($query, OAI_DBID);
					
					$updatesInRepository++;
					$numUpdates++;
				} else {
					if ($isCurrentlyDeleted 
						&& $authorizationManager->isAuthorized($instituteId, $viewId, $assetId)) 
					{
						$query =& $baseUndeleteQuery->copy();
					} else if (!$isCurrentlyDeleted 
						&& !$authorizationManager->isAuthorized(
								$instituteId, $viewId, $assetId)) 
					{
						$query =& $baseDeleteQuery->copy();
					} else {
						$query = null;
					}
					
					if ($query) {
						$query->addWhere(
							"repository='".addslashes($repositoryId->getIdString())."'");
						$query->addWhere(
							"oai_identifier='".addslashes($assetId->getIdString())."'");
						$dbc->query($query, OAI_DBID);
						$updatesInRepository++;
						$numUpdates++;
					}
				}
				
				$status->updateStatistics();
			}
			
			// Update any missing assets as deleted
			$query =& $baseDeleteQuery->copy();
			$query->addWhere("repository='".addslashes($repositoryId->getIdString())."'");
			if (count($existingAssetIds)) {
				$query->addWhere("deleted='false'");
				$query->addWhere("oai_identifier NOT IN (".implode(", ", $existingAssetIds).")");			
			}			
			$result =& $dbc->query($query, OAI_DBID);
			if ($result->getNumberOfRows()) {
				$updatesInRepository = $updatesInRepository + $result->getNumberOfRows();
				$numUpdates = $numUpdates + $result->getNumberOfRows();
			}
			
			print "<pre>Elapsed Time:\t";
			$timer->end();		
			printf("%1.2f", $timer->printTime());
			print " seconds</pre>";
			printpre("Updates: ".$updatesInRepository);
		}	
		
		// Update any missing repositories as deleted
		$query =& $baseDeleteQuery->copy();
		$query->addWhere("deleted='false'");
		$query->addWhere("repository NOT IN (".implode(", ", $existingRepositoryIds).")");
		$result =& $dbc->query($query, OAI_DBID);
		if ($result->getNumberOfRows()) {
			$updatesInRepository = $updatesInRepository + $result->getNumberOfRows();
			$numUpdates = $numUpdates + $result->getNumberOfRows();
		}
		
		printpre("Total Updates:\t".$numUpdates);
		
		exit;
	}
	
	/**
	 * Answer true if the asset has Dublin Core-mappable metadata.
	 * 
	 * @param object Asset $asset
	 * @return boolean
	 * @access public
	 * @since 3/5/07
	 */
	function hasDublinCore ( &$asset ) {
		$assetId =& $asset->getId();
		
		if (!isset($this->_hasDcResults)) {
			$this->_hasDcResults = array();
			$idManager =& Services::getService("Id");
			$this->_dcId =& $idManager->getId('dc');
			$this->_vraId =& $idManager->getId('vra_core');
		}
			
		if (!isset($this->_hasDcResults[$assetId->getIdString()])) {
			$this->_hasDcResults[$assetId->getIdString()] = false;
			$recStructs =& $asset->getRecordStructures();
			while ($recStructs->hasNext()) {
				$recStruct =& $recStructs->next();
				if ($this->_dcId->isEqual($recStruct->getId())
					|| $this->_vraId->isEqual($recStruct->getId()))
				{
					$this->_hasDcResults[$assetId->getIdString()] = true;
					break;
				}
			}
			
		}
		return $this->_hasDcResults[$assetId->getIdString()];
	}
	
	/**
	 * Add Dublin Core fields to the result set where possible
	 * 
	 * @param object Asset $asset
	 * @param ref array $values
	 * @return void
	 * @access public
	 * @since 3/5/07
	 */
	function addDublinCoreValues (&$asset, &$values) {
		$records =& $asset->getRecordsByRecordStructure($idManager->getId('dc'));
		// use the first Dublin Core record if availible
		if ($records->hasNext()) {
			$record =& $records->next();
			// 'dc_creator',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('dc.creator')));
			
			// 'dc_subject',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('dc.subject')));
					
			// 'dc_contributor',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('dc.contributor')));
					
			// 'dc_publisher',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('dc.publisher')));
					
			// 'dc_date',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('dc.date')));
					
			// 'dc_type',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('dc.type')));
					
			// 'dc_format',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('dc.format')));
					
			// 'dc_identifier',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('dc.identifier')));
					
			// 'dc_source',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('dc.source')));
					
			// 'dc_language',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('dc.language')));
					
			// 'dc_relation',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('dc.relation')));
					
			// 'dc_coverage',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('dc.coverage')));
					
			// 'dc_rights'	
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('dc.rights')));
					
			return;
		}
		
		$records =& $asset->getRecordsByRecordStructure($this->_vraId);
		// otherwise use first VRA Core record
		if ($records->hasNext()) {
			$record =& $records->next();
			// 'dc_creator',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.creator')));
			
			// 'dc_subject',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.subject')));
					
			// 'dc_contributor',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.contributor')));
					
			// 'dc_publisher',
			$values[] = "''";
// 			$values[] = $this->getPartsString(
// 				$record->getPartsByPartStructure(
// 					$idManager->getId('vra_core.publisher')));
					
			// 'dc_date',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.date')));
					
			// 'dc_type',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.type')));
					
			// 'dc_format',
			$parts =& new MultiIteratorIterator;
			$parts->addIterator(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.material')));
			$parts->addIterator(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.measurements')));
			$parts->addIterator(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.technique')));
			$values[] = $this->getPartsString($parts);
					
			// 'dc_identifier',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.id_number')));
					
			// 'dc_source',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.source')));
					
			// 'dc_language',
			$values[] = "''";
// 			$values[] = $this->getPartsString(
// 				$record->getPartsByPartStructure(
// 					$idManager->getId('vra_core.language')));
					
			// 'dc_relation',
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.relation')));
					
			// 'dc_coverage',
			$parts =& new MultiIteratorIterator;
			$parts->addIterator(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.culture')));
			$parts->addIterator(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.style_period')));
			$values[] = $this->getPartsString($parts);
					
			// 'dc_rights'	
			$values[] = $this->getPartsString(
				$record->getPartsByPartStructure(
					$idManager->getId('vra_core.rights')));
			return;
		}
	}
}

?>
