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
		if (RequestContext::value('help') || RequestContext::value('h') || RequestContext::value('?'))
			throw new HelpRequestedException(
"This is a command line script that will populate the OAI data tables from the 
repositories in Concerto. It takes no arguments or parameters.
");

		$harmoni = Harmoni::instance();
		$config = $harmoni->getAttachedData('OAI_CONFIG');
		
		if (!defined('OAI_UPDATE_OUTPUT_HTML')) {
			define("OAI_UPDATE_OUTPUT_HTML", TRUE);
		}
		
		while (ob_get_level())
			ob_end_flush();
		
		$harvesterConfig = $config->getProperty('OAI_HARVESTER_CONFIG');
		
		if (!isset($_SESSION['oai_table_setup_complete'])) {
			$dbc = Services::getService("DatabaseManager");
			$tables = $dbc->getTableList($config->getProperty('OAI_DBID'));
			
			foreach ($harvesterConfig as $configArray) {
				$table = 'oai_'.$configArray['name'];
				if (!in_array($table, $tables)) {
					$queryString = file_get_contents(
						dirname(__FILE__)."/phpoai2/doc/oai_records_mysql.sql");
					$queryString = str_replace('oai_records', $table, $queryString);
					
					$query = new GenericSQLQuery;
					$query->addSQLQuery(SQLUtils::parseSQLString($queryString));
					
					$dbc->query($query,	$config->getProperty('OAI_DBID'));
				}
			}
			
			$_SESSION['oai_table_setup_complete'] = true;
		}		
		
		$i = 1;
		foreach ($harvesterConfig as $configArray) {
			$tableMessage = "Updating table oai_".$configArray['name']." (table ".$i." of ".count($harvesterConfig).")";
			if (OAI_UPDATE_OUTPUT_HTML)
				print "\n<hr/><h2>".$tableMessage."</h2>";
			else
				print 
"---------------------------------------------------
| $tableMessage
---------------------------------------------------
";

			
			$this->updateTable(
				$configArray['name'], 
				$configArray['repository_ids'],
				$configArray['auth_group_ids']);
			
			$i++;
			
			print "\n\n";
		}
	}
	
	/**
	 * Update a record table for a set of harvesters and repositories
	 * 
	 * @param string $table
	 * @param array $allowedRepositoryIdStrings
	 * @param array $authGroupIdStrings	The ids of the groups to check view authorization for,
	 *	May be one of the following or another group Id string:
	 *		edu.middlebury.agents.everyone
	 *		edu.middlebury.agents.all_agents
	 *	If empty, all assets in the specified repositories will be added regardless of
	 *	their visibility.
	 *
	 * @return void
	 * @access public
	 * @since 3/9/07
	 */
	function updateTable ( $table, $allowedRepositoryIdStrings, $authGroupIdStrings ) {
		ArgumentValidator::validate($table, StringValidatorRule::getRule());
		ArgumentValidator::validate($allowedRepositoryIdStrings, ArrayValidatorRuleWithRule::getRule(
			StringValidatorRule::getRule()));
		ArgumentValidator::validate($authGroupIdStrings, ArrayValidatorRuleWithRule::getRule(
			StringValidatorRule::getRule()));
		
		$harmoni = Harmoni::instance();
		$config =$harmoni->getAttachedData('OAI_CONFIG');
		
		$repositoryManager = Services::getService('Repository');
		$authorizationManager = Services::getService('AuthZ');
		$idManager = Services::getService("IdManager");
		$dbc = Services::getService("DatabaseManager");
		
		$authGroupIds = array();
		foreach ($authGroupIdStrings as $id) {
			$authGroupIds[] = $idManager->getId($id);
		}
				
		$baseCheckQuery = new SelectQuery;
		$baseCheckQuery->addTable('oai_'.$table);
		$baseCheckQuery->addColumn('datestamp');
		$baseCheckQuery->addColumn('deleted');
		
		$baseUpdateQuery = new UpdateQuery;
		$baseUpdateQuery->setTable('oai_'.$table);
		
		$baseUpdateColumns = array(
			'datestamp',
			'deleted',
			'oai_set',
			'dc_title',
			'dc_description'
		);
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
		
		$baseInsertQuery = new InsertQuery;
		$baseInsertQuery->setTable('oai_'.$table);
		$baseInsertColumns = array(
			'datestamp',
			'oai_identifier',
			'deleted',
			'oai_set',
			'dc_title',
			'dc_description'	
		);
		$dcInsertColumns = array(
			'datestamp',
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
		
		$baseDeleteQuery = new UpdateQuery;
		$baseDeleteQuery->setTable('oai_'.$table);
		$baseDeleteQuery->addValue('deleted', 'true');
		$baseDeleteQuery->addRawValue('datestamp', 'NOW()');
		
		$baseUndeleteQuery = new UpdateQuery;
		$baseUndeleteQuery->setTable('oai_'.$table);
		$baseUndeleteQuery->addValue('deleted', 'false');
		$baseUndeleteQuery->addRawValue('datestamp', 'NOW()');
		
		
		$forceUpdate = false;
		$repositories =$repositoryManager->getRepositories();
		
		$r = 0;
		if (count($allowedRepositoryIdStrings))
			$numR = count($allowedRepositoryIdStrings);
		else
			$numR = $repositories->count();
		$numUpdates = 0;	
		$numDeleted = 0;	
		$message = _('Updating OAI records for repository (%1 of %2) : ');
		$message = str_replace('%2', $numR, $message);
		$instituteId =$idManager->getId('edu.middlebury.agents.users');
		$viewId =$idManager->getId('edu.middlebury.authorization.view');
		
		require_once(HARMONI."/utilities/Timer.class.php");
		$timer = new Timer;
		$timer->start();
		$existingRepositoryIds = array();
		
		while ($repositories->hasNext()) {
			$updatesInRepository = 0;
			$repository =$repositories->next();
			$repositoryId =$repository->getId();
			
			// Only work with allowed repositories
			if (count($allowedRepositoryIdStrings)
					&& !in_array($repositoryId->getIdString(), $allowedRepositoryIdStrings))
				continue;
			$r++;
			
			$existingRepositoryIds[] = $repositoryId->getIdString();
			
			$assets =$repository->getAssets();			
			$status = new CLIStatusStars(
				str_replace('%1', $r, $message).$repository->getDisplayName());
			$status->initializeStatistics($assets->count());
			
			$existingAssetIds = array();
			
			while ($assets->hasNext()) {
				$asset =$assets->next();
				$assetId =$asset->getId();
				$existingAssetIds[] = $assetId->getIdString();
				try {
					$modificationDate = $asset->getModificationDate();
				} catch (UnimplementedException $e) {
					$modificationDate = DateAndTime::now();
				}
				
				$query =$baseCheckQuery->copy();
				$query->addWhereEqual("oai_set", $repositoryId->getIdString());
				$query->addWhereEqual("oai_identifier", $assetId->getIdString());
				
				$result =$dbc->query($query, $config->getProperty('OAI_DBID'));
				
					
				if (!$result->getNumberOfRows()) {
// 					printpre("Doesn't exist:\t".$asset->getDisplayName()."");
					$query =$baseInsertQuery->copy();
					$query->addValue('oai_set', $repositoryId->getIdString());
					$query->addValue('oai_identifier', $assetId->getIdString());
				} else {
// 					printpre("Exists:\t".$asset->getDisplayName()."");
					if ($modificationDate->isGreaterThan(
							DateAndTime::fromString($result->field('datestamp')))
						|| $forceUpdate)
					{
// 						printpre("\tUpdating:\t".$asset->getDisplayName());
						$query =$baseUpdateQuery->copy();
						$query->addWhereEqual("oai_set", $repositoryId->getIdString());
						$query->addWhereEqual("oai_identifier", $assetId->getIdString());
					} 
					// If it is up to date, skip.
					else {
						$query = null;
					}
				}
				
				if ($query)
					$query->addRawValue('datestamp', 'NOW()');
				
				$isCurrentlyDeleted = (($result->getNumberOfRows() &&$result->field('deleted') == 'true')?true:false);
				$result->free();
				
				if (!count($authGroupIds)) {
					$isVisible = true;
				} else {
					$isVisible = false;
					try {
						foreach ($authGroupIds as $id) {
							if ($authorizationManager->isAuthorized($id, $viewId, $assetId)) {
								$isVisible = true;
								break;
							}
						}
					} catch (UnknownIdException $e) {
						$isVisible = true;
					}
				}
				
				if ($query) {
				//Add the data fields
					// Deleted
					if ($isVisible) {
						$query->addValue('deleted', 'false');
					} else {
						$query->addValue('deleted', 'true');
					}
					$query->addValue('dc_title', $asset->getDisplayName());
					$query->addValue('dc_description', $asset->getDescription());
					$this->addDublinCoreValues($asset, $query);
						
					$dbc->query($query, $config->getProperty('OAI_DBID'));
					
					$updatesInRepository++;
					$numUpdates++;
				} else {
					if ($isCurrentlyDeleted && $isVisible) 
					{
						$query =$baseUndeleteQuery->copy();
					} else if (!$isCurrentlyDeleted && !$isVisible) {
						$query =$baseDeleteQuery->copy();
					} else {
						$query = null;
					}
					
					if ($query) {
						$query->addWhereEqual("oai_set", $repositoryId->getIdString());
						$query->addWhereEqual("oai_identifier", $assetId->getIdString());
						$dbc->query($query, $config->getProperty('OAI_DBID'));
						$updatesInRepository++;
						$numUpdates++;
					}
				}
				
				$status->updateStatistics();
			}
			
			// Update any missing assets as deleted
			$query =$baseDeleteQuery->copy();
			$query->addWhereEqual("oai_set", $repositoryId->getIdString());
			if (count($existingAssetIds)) {
				$query->addWhereEqual("deleted", "false");
				$query->addWhereNotIn("oai_identifier", $existingAssetIds);
			}			
			$result =$dbc->query($query, $config->getProperty('OAI_DBID'));
			if ($result->getNumberOfRows()) {
				$updatesInRepository = $updatesInRepository + $result->getNumberOfRows();
				$numUpdates = $numUpdates + $result->getNumberOfRows();
			}
			
			print ((OAI_UPDATE_OUTPUT_HTML)?"<pre>":"\n");				
			print "Elapsed Time:\t";
			$timer->end();		
			printf("%1.2f", $timer->printTime());
			print " seconds";
			print ((OAI_UPDATE_OUTPUT_HTML)?"</pre>":"");
			print ((OAI_UPDATE_OUTPUT_HTML)?"<pre>":"\n");
			print "Updates: ".$updatesInRepository;
			print ((OAI_UPDATE_OUTPUT_HTML)?"</pre>":"\n");
		}	
		
		// Update any missing repositories as deleted
		$query =$baseDeleteQuery->copy();
		$query->addWhereEqual("deleted", "false");
		if (count($existingRepositoryIds))
			$query->addWhereNotIn("oai_set", $existingRepositoryIds);
		$result =$dbc->query($query, $config->getProperty('OAI_DBID'));
		if ($result->getNumberOfRows()) {
			$updatesInRepository = $updatesInRepository + $result->getNumberOfRows();
			$numUpdates = $numUpdates + $result->getNumberOfRows();
		}
		
		print ((OAI_UPDATE_OUTPUT_HTML)?"<pre>":"\n");
		print "Total Updates:\t".$numUpdates;
		print ((OAI_UPDATE_OUTPUT_HTML)?"</pre>":"\n");
		
	}
	
	/**
	 * Add Dublin Core fields to the result set where possible
	 * 
	 * @param object Asset $asset
	 * @param object Query $query
	 * @return void
	 * @access public
	 * @since 3/5/07
	 */
	function addDublinCoreValues ($asset, $query) {
		$idManager = Services::getService("Id");
		$records =$asset->getRecordsByRecordStructure($idManager->getId('dc'));
		// use the first Dublin Core record if availible
		if ($records->hasNext()) {
			$record =$records->next();
			$ids = array(
				'dc_creator' 		=> 'dc.creator',
				'dc_subject'	 	=> 'dc.subject',
				'dc_contributor'	=> 'dc.contributor',
				'dc_publisher'	 	=> 'dc.publisher',
				'dc_date' 			=> 'dc.date',
				'dc_type' 			=> 'dc.type',
				'dc_format' 		=> 'dc.format',
				'dc_identifier' 	=> 'dc.identifier',
				'dc_source' 		=> 'dc.source',
				'dc_language' 		=> 'dc.language',
				'dc_relation' 		=> 'dc.relation',
				'dc_coverage' 		=> 'dc.coverage',
				'dc_rights' 		=> 'dc.rights');
			
			foreach ($ids as $column => $partId) {
				$parts =$record->getPartsByPartStructure($idManager->getId($partId));
				if ($parts->hasNext())
					$query->addValue($column, $this->getPartsString($parts));
			}
			
			return;
		}
		
		$records =$asset->getRecordsByRecordStructure($idManager->getId('vra_core'));
		// otherwise use first VRA Core record
		if ($records->hasNext()) {
			$record =$records->next();
			$ids = array(
				'dc_creator' 		=> 'vra_core.creator',
				'dc_subject'	 	=> 'vra_core.subject',
				'dc_contributor'	=> 'vra_core.contributor',
// 				'dc_publisher'	 	=> 'vra_core.publisher',
				'dc_date' 			=> 'vra_core.date',
				'dc_type' 			=> 'vra_core.type',
				'dc_format' 		=> array(	'vra_core.material',
												'vra_core.measurements',
												'vra_core.technique'),
				'dc_identifier' 	=> 'vra_core.id_number',
				'dc_source' 		=> 'vra_core.source',
// 				'dc_language' 		=> 'vra_core.language',
				'dc_relation' 		=> 'vra_core.relation',
				'dc_coverage' 		=> array(	'vra_core.culture',
												'vra_core.style_period'),
				'dc_rights' 		=> 'vra_core.rights');
			
			foreach ($ids as $column => $partId) {
				if (is_array($partId)) {
					$parts = new MultiIteratorIterator;
					foreach ($partId as $id) {
						$parts->addIterator(
							$record->getPartsByPartStructure($idManager->getId($id)));
					}
				} else {
					$parts =$record->getPartsByPartStructure($idManager->getId($partId));
				}
				if ($parts->hasNext())
					$query->addValue($column, $this->getPartsString($parts));
			}
			
			return;
		}
	}
	
	/**
	 * Answer the string value of a part to be inserted into a field. Multiple
	 * values will be semicolon delimited.
	 * 
	 * @param object Iterator $partIterator
	 * @return string
	 * @access public
	 * @since 3/8/07
	 */
	function getPartsString ( $partIterator ) {
		$string = '';
		while ($partIterator->hasNext()) {
			$part =$partIterator->next();
			$value = $part->getValue();
			if (is_object($value) && method_exists($value, 'asString'))
				$string .= $value->asString();
			else
				$string = $string.$value;
			
			if ($partIterator->hasNext()) {
				$string .= ';';
			}
		}
		return $string;
	}
}

?>
