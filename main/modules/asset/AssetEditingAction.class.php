<?php
/**
 * @since 10/26/05
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/RepositoryAction.class.php");
require_once(HARMONI."/utilities/StatusStars.class.php");

/**
 * This is an abstract class which contains methods for editing assets.
 * its child classes will handle the setting up of which assets to edit.
 * 
 * @since 10/26/05
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
abstract class AssetEditingAction
	extends RepositoryAction
{

	/**
	 * @var array $saveMessages;  
	 * @access protected
	 * @since 10/11/07
	 */
	protected $saveMessages = array();
	
	/**
	 * Initialize an instance of the object
	 * 
	 * @return void
	 * @access protected
	 * @since 10/26/05
	 */
	protected function _init () {
		$this->_recStructsToIgnore = array();
		$this->_multExistString = _("(multiple values exist)");
		$this->_newStructuredTagValues = array();
		
	}
	
	/**
	 * Load the assets for the array of Id-strings passed
	 * 
	 * @param array $assetIdStrings
	 * @return void
	 * @access private
	 * @since 10/26/05
	 */
	function _loadAssets ($assetIdStrings) {
		$idManager = Services::getService("Id");
	 	$repository = $this->getRepository();
	 	
	 	if (!$repository)
	 		throw new Exception("Repository not found");
	 	
	 	$this->_assets = array();
	 	
	 	foreach ($assetIdStrings as $idString) {
	 		// ignore whitepace or empty strings
			if (preg_match('/\\S+/', $idString))
	 			$this->_assets[] = $repository->getAsset($idManager->getId($idString));
	 	}
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough("collection_id", "asset_id", "assets");
		$centerPane =$this->getActionRows();
		
		$this->addTableStyles();
		
		// Thumbnails
		$wizard =$this->getWizard($this->_cacheName);
		$centerPane->add(new Block($wizard->assetThumbnails, EMPHASIZED_BLOCK), null, null, null, null);
		
		$this->runWizard ( $this->_cacheName, $centerPane );
		
// 		printpre($_REQUEST);
	}
	
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 4/28/05
	 */
	function saveWizard ( $cacheName ) {
// 		$asset =$this->getAsset();
		$wizard =$this->getWizard($cacheName);
				
		$results = $wizard->getAllValues();
		$initialState =$wizard->initialState;
		
// 		print "<hr><div style='background-color: #afa;'>";
// 		printpre($results);
// 		printpre($initialState);
// 		print "</div>";
				
		// Go through all of the assets and update all of the values if they have
		// changed.
		$idManager = Services::getService("Id");
	 	$repository =$this->getRepository();
	 	
	 	if (!$repository)
	 		throw new Exception("Repository not found");
	 	
	 	// Records
		$repositoryId =$this->getRepositoryId();
		
		// Get the set of RecordStructures so that we can print them in order.
		$setManager = Services::getService("Sets");
		$recStructSet =$setManager->getPersistentSet($repositoryId);
		
		$authZMan = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		// Log the success or failure
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log =$loggingManager->getLogForWriting("Concerto");			
			$item = new AgentNodeEntryItem("Modify Node", "Asset[s] modified");
		}
	 	
	 	$status = new StatusStars(_("Saving Assets"));
	 	$status->initializeStatistics(count($this->_assets));
	 	foreach (array_keys($this->_assets) as $key) {
	 		$asset =$this->_assets[$key];
	 		try {
				$isAuthorized = $authZMan->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify"), 
					$asset->getId());
			} catch (UnknownIdException $e) {
				$isAuthorized = true;
			}
			
			if ($isAuthorized) {
// 				printpre("<hr>".$asset->getDisplayName());
				
				if (isset($results['assetproperties']))
					$this->updateAssetProperties($results['assetproperties'], $asset);
				if (isset($results['contentstep']['content']))
					$this->updateAssetContent($results['contentstep']['content'], $asset);
				
				if (isset($results['filestep']))
					$this->updateFileRecords($results['filestep']['files'], $initialState, $asset);
				if (isset($results['remotefilestep']))
					$this->updateFileRecords($results['remotefilestep']['files'], $initialState, $asset, 'REMOTE_FILE');
				
				// First, lets go through the info structures listed in the set and print out
				// the info records for those structures in order.
				$recStructSet->reset();
				while ($recStructSet->hasNext()) {
					$recStructId =$recStructSet->next();
					if (in_array($recStructId->getIdString(), $this->_recStructsToIgnore))
						continue;
					
					if ($this->hasChangedParts($results, $initialState, $recStructId)) {
						$this->updateAssetRecords($results, $initialState, $recStructId, $asset);
						
						// Update the structured-metadata generated tags for this asset
						$this->updateAssetTags($asset->getId());
					}
					
				}
				
				if (isset($item))
					$item->addNodeId($asset->getId());
			}
			
			
			if (isset($results['parentstep'])
				&& $results['parentstep'] != $initialState['parentstep']) 
			{
				if (is_array($results['parentstep']['parent']))	{
					if ($results['parentstep']['parent']['checked'])
						$this->updateAssetParent(
							$results['parentstep']['parent']['value'], 
							$asset);
				} else
					$this->updateAssetParent($results['parentstep']['parent'], $asset);
			}
			
			$status->updateStatistics();
	 	}
	 	
	 	if (isset($log) && isset($item)) {
			$item->addNodeId($repository->getId());
			
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
	 	}
		
// 		printpre($results);
// 		exit;
		
		return TRUE;
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		$harmoni = Harmoni::instance();
		
		return $harmoni->history->getReturnURL("concerto/asset/edit-return");
		
		// old implementation:
		$repositoryId =$this->getRepositoryId();
		
		if (!$repositoryId)
	 		throw new Exception("Repository not found");
		
		$assets = explode(',', RequestContext::value("assets"));
		$assetIdString = RequestContext::value("asset_id");
		
		if (count($assets) == 1 && (!$assetIdString || $assets[0] == $assetIdString)) {
			return $harmoni->request->quickURL("asset", "view", 
					array("collection_id" => $repositoryId->getIdString(), 
					"asset_id" => $assets[0]));
		} else if ($assetIdString) {
			return $harmoni->request->quickURL("asset", "browseAsset", 
					array("collection_id" => $repositoryId->getIdString(), 
					"asset_id" => $assetIdString));
		} else {
			return $harmoni->request->quickURL("collection", "browse", 
					array("collection_id" => $repositoryId->getIdString()));
		}
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 4/28/05
	 */
	function createWizard () {
		$harmoni = Harmoni::instance();
	
		// Instantiate the wizard, then add our steps.
		$wizard = SimpleStepWizard::withDefaultLayout();
		
		
	/*********************************************************
	 * Asset thumbnails: Generate a text string and attach to
	 * the wizard for later retrieval.
	 *********************************************************/
	 	ob_start();
	 	$assetIds = array();
	 	for ($i = 0; $i < count($this->_assets); $i++) {
			$asset =$this->_assets[$i];
			$assetId =$asset->getId();
			$assetIds[] = $assetId->getIdString();
		}
		$params = array();
		$params["assetIds"] = implode(",", $assetIds);
		for ($i = 0; $i < count($this->_assets) && $i < 10; $i++) {
			$asset =$this->_assets[$i];
			$assetId =$asset->getId();
			$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($asset);
			if ($thumbnailURL !== FALSE) {				
				$thumbSize ="100px";
				
				print "\n<div style='height: $thumbSize; width: $thumbSize; margin: auto; float: left; text-align: center;'>";
				
				print "\n\t\t<img src='$thumbnailURL' class='thumbnail thumbnail_image' alt='Thumbnail Image' border='0'";
				
				print " onclick='Javascript:window.open(";
				print '"'.VIEWER_URL."?&amp;source=";
				print urlencode($harmoni->request->quickURL('asset', "viewAssetsXml", $params));
				print '&amp;start='.$i.'", ';
				print '"_blank", ';
				print '"toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=500"';
				print ")'";
				print " style='max-height: $thumbSize; max-width: $thumbSize; vertical-align: middle; cursor: pointer;'";
				print " />";
				print "\n</div>";
			}
		}
		
		if ($i < count($this->_assets)) {
			print "\n<div style='height: $thumbSize; width: $thumbSize; margin: auto; float: left; text-align: center; vertical-align: middle;'>";
			
			print _(" (and ").(count($this->_assets) - $i)._(" more) ");
			print "\n</div>";
		}
		
		$wizard->assetThumbnails = ob_get_clean();
		
	/*********************************************************
	 * :: Asset Properties ::
	 *********************************************************/
		$wizard->addStep("assetproperties", $this->getAssetPropertiesStep());
		
	/*********************************************************
	 *  :: Record Structures ::
	 *********************************************************/
	 	$repository =$this->getRepository();
	 	$repositoryId =$this->getRepositoryId();
	 	
	 	if (!$repository)
	 		throw new Exception("Repository not found");
	 	
	 	// Get the set of RecordStructures so that we can print them in order.
		$setManager = Services::getService("Sets");
		$recStructSet =$setManager->getPersistentSet($repositoryId);
		
		// File Record Id
		$idManager = Services::getService("Id");
		$fileRecStructId =$idManager->getId('FILE');
		$remoteFileRecStructId =$idManager->getId('REMOTE_FILE');
		
		
		
		// First, lets go through the info structures listed in the set and print out
		// the info records for those structures in order.
		while ($recStructSet->hasNext()) {
			$recStructId =$recStructSet->next();
			if (in_array($recStructId->getIdString(), $this->_recStructsToIgnore))
				continue;
			
			if ($recStructId->isEqual($fileRecStructId)) {
				if ($fileRecordStep =$this->getFileRecordsStep())
					$wizard->addStep("filestep", $fileRecordStep);
			} else if ($recStructId->isEqual($remoteFileRecStructId)) {
				if ($fileRecordStep =$this->getRemoteFileRecordsStep())
					$wizard->addStep("remotefilestep", $fileRecordStep);
			} else {			
				$recStruct =$repository->getRecordStructure($recStructId);
			
				$wizard->addStep($recStructId->getIdString(), $this->getRecordStructureStep($recStruct));
			}
		}
	 	
		
	/*********************************************************
	 *  :: Content ::
	 *********************************************************/
	 	$step = $this->getAssetContentStep();
	 	if (!is_null($step))
			$wizard->addStep("contentstep", $step);
		
	/*********************************************************
	 *  :: Content ::
	 *********************************************************/
	 	$step = $this->getParentStep();
	 	if (!is_null($step))
			$wizard->addStep("parentstep", $step);
		
		
		$wizard->initialState = $wizard->getAllValues();
	
		return $wizard;
	}
		
	/**
	 * Add needed styles to the pages head section
	 * 
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function addTableStyles () {
		$harmoni = Harmoni::instance();
		$style = "
		<style type='text/css'>			
			.edit_table td, th {
				padding: 5px;
				padding-bottom: 20px;
				vertical-align: top;
				text-align: left;
			}
			
			.edit_table .mod {
				vertical-align: middle;
				text-align: center;
			}
			
			.desc {
				border-top: 0px solid;
				vertical-align: bottom;
				text-align: center;
				padding-bottom: 5px;
				font-style: italic;
			}
			
		</style>
		";
		$outputHandler =$harmoni->getOutputHandler();
		$outputHandler->setHead($outputHandler->getHead().$style);
	}
	
	/**
	 * Answer the content for the AssetPropertiesStep
	 * 
	 * @return string
	 * @access public
	 * @since 10/27/05
	 */
	function getAssetPropertiesContent () {
		print "\n<table class='edit_table' cellspacing='0'>";
		
		print "\n\t<tr>";
		print "\n\t\t<th>";
		print "\n\t\t\t"._("Name");
// 		print "\n"._("The Name for this <em>Asset</em>: ");
		print "\n\t\t</th>";
		print "\n\t\t<td>";
		print "\n\t\t\t[[display_name]]";
		print "\n\t\t</td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";
		print "\n\t\t<th>";
		print "\n\t\t\t"._("Description");
// 		print "\n"._("The Description for this <em>Asset</em>: ");
		print "\n\t\t</th>";
		print "\n\t\t<td>";
		print "\n\t\t\t[[description]]";
		print "\n\t\t</td>";
		print "\n\t</tr>";
		
// 		print "\n\t<tr>";
// 		print "\n\t\t<th>";
// 		print "\n\t\t\t"._("Effective Date");
// // 		print "\n"._("The date that this <em>Asset</em> becomes effective: ");
// 		print "\n\t\t</th>";
// 		print "\n\t\t<td>";
// 		print "\n\t\t\t[[effective_date]]";
// 		print "\n\t\t</td>";
// 		print "\n\t</tr>";
		
// 		print "\n\t<tr>";
// 		print "\n\t\t<th>";
// 		print "\n\t\t\t"._("Expiration Date");
// // 		print "\n"._("The date that this <em>Asset</em> expires: ");
// 		print "\n\t\t</th>";
// 		print "\n\t\t<td>";
// 		print "\n\t\t\t[[expiration_date]]";
// 		print "\n\t\t</td>";
// 		print "\n\t</tr>";
		
		print "\n</table>";
		$text = ob_get_contents();
		ob_end_clean();
		
		return $text;
	}
	
	/**
	 * Add a table of PartStructure components to the given component.
	 * 
	 * @param object WizardComponent
	 * @return void
	 * @access public
	 * @since 10/27/05
	 */
	function addPartStructureComponents ( $parentComponent, $recStruct ) {
		ob_start();
		print "\n<table class='edit_table' cellspacing='0'>";
		
		$partStructText = array();
		$unorderedPartStructText = array();
		$setManager = Services::getService("Sets");
		$set =$setManager->getPersistentSet($recStruct->getId());
		
		$partStructs =$recStruct->getPartStructures();
		while ($partStructs->hasNext()) {
			ob_start();
			$partStruct =$partStructs->next();
			$partStructId =$partStruct->getId();
		
		// PartStructure
			if (!$partStruct->isRepeatable()) {
				$component =$this->getSingleValuedPartStructComponent($partStruct);
			} else {
				$component =$this->getRepeatablePartStructComponent($partStruct);				
			}
			
			$parentComponent->addComponent(
				preg_replace("/[^a-zA-Z0-9:_\-]/", "_", $partStructId->getIdString()),
				$component);
			
			
			print "\n\t<tr>";
			print "\n\t\t<th>";
			print "\n\t\t\t".$partStruct->getDisplayName();
			if ($partStruct->getDescription()) {
				print "\n\t\t\t&nbsp;&nbsp;<a onclick='";
				print 'var descStyle = this.nextSibling.nextSibling.style; if (descStyle.display == "block") { descStyle.display="none"; } else { descStyle.display="block"; }';
				print "'>"._("?")."</a>";
				print "\n\t\t\t<div style='font-size: small; font-weight: normal; margin: 5px; display: none;'>";
				print $partStruct->getDescription();
				print "</div>";
				
			}
			print "\n\t\t</th>";
			print "\n\t\t<td>";
			print "\n\t\t\t[[".preg_replace("/[^a-zA-Z0-9:_\-]/", "_", $partStructId->getIdString())."]]";
			print "\n\t\t</td>";
			print "\n\t</tr>";
			
			if ($set->isInSet($partStructId))
				$partStructText[$set->getPosition($partStructId)] = ob_get_clean();
			else
				$unorderedPartStructText[] = ob_get_clean();
		}
		ksort($partStructText);
		print implode('', $partStructText);
		print implode('', $unorderedPartStructText);
		print "\n</table>";
	
		$parentComponent->setContent(ob_get_contents());
		ob_end_clean();
	}
	
	/**
	 * Answer a step for all of the the files of the asset
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 10/31/05
	 */
	function getFileRecordsStep () {
		$false = false;
		return $false;
	}
	
	/**
	 * Answer a step for all of the the files of the asset
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 10/31/05
	 */
	function getRemoteFileRecordsStep () {
		$false = false;
		return $false;
	}
	
	/**
	 * Update the file records of the asset based on the values from the wizard
	 * 
	 * @param array $results
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function updateFileRecords ( $results, $initialState, $asset, $structIdString = 'FILE' ) {
		
	}
	
	/**
	 * Update the file records of the asset based on the values from the wizard
	 * 
	 * @param array $results
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function updateRemoteFileRecords ( $results, $asset ) {
		
	}
	
	/**
	 * Answer the records in the assets for the given record Structure
	 * 
	 * @param object RecordStructure $recStruct
	 * @return array
	 * @access public
	 * @since 10/27/05
	 */
	function getRecordsForRecordStructure ( $recStruct ) {
		$records = array();
		for ($i = 0; $i < count($this->_assets); $i++) {
			$recordIterator =$this->_assets[$i]->getRecordsByRecordStructure(
				$recStruct->getId());
			
			while ($recordIterator->hasNext()) {
				$records[] =$recordIterator->next();
			}
		}
		
		return $records;
	}
	
	/**
	 * Answer the component needed to modify a given PartStructure
	 * 
	 * @param object PartStructure $partStruct
	 * @return object WizardComponent
	 * @access public
	 * @since 10/26/05
	 */
	function getComponentForPartStruct ( $partStruct ) {

		// get the correct component for this data type
		$component = PrimitiveIOManager::createComponentForPartStructure($partStruct);
		
		$hasMethods = HasMethodsValidatorRule::getRule("setSize");
		if ($hasMethods->check($component))
			$component->setSize(40);
		
		return $component;
	}
	
	/**
	 * Answer true if some of the parts were changed in this record
	 * 
	 * @param array $results, the wizard results
	 * @param array $initialState, the initial wizard results
	 * @param object Id $recStructId
	 * @return boolean
	 * @access public
	 * @since 10/24/05
	 */
	function hasChangedParts ( $results, $initialState, $recStructId ) {
		$recStructIdString = preg_replace("/[^a-zA-Z0-9:_\-]/", "_",
								$recStructId->getIdString());
		if (
			// One isset only
			(isset($results[$recStructIdString]) && !isset($results[$recStructIdString]))
			|| (!isset($results[$recStructIdString]) && isset($results[$recStructIdString]))
			
			// Both are set and not equal
			|| ((isset($results[$recStructIdString]) && isset($results[$recStructIdString]))
				&& $results[$recStructIdString] != $initialState[$recStructIdString]))
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Update the records for the recStructId for the asset based on the results
	 * from the wizard.
	 * 
	 * @param array $results, the wizard results
	 * @param array $initialState, the initial wizard results
	 * @param object Id $recStructId
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function updateAssetRecords ($results, $initialState, $recStructId, $asset) {
		printpre("<hr>updateAssetRecords:".$asset->getDisplayName());
		
		// Create an array of new tag values;
		$assetId =$asset->getId();
		$this->_newStructuredTagValues[$assetId->getIdString()] = array();
		
		$recStructIdString = preg_replace("/[^a-zA-Z0-9:_\-]/", "_",  $recStructId->getIdString());
		
		$records =$asset->getRecordsByRecordStructure($recStructId);
		if (!$records->hasNext()) {
			$record =$asset->createRecord($recStructId);
			$this->updateRecord($results[$recStructIdString], 
				$initialState[$recStructIdString], $record, $assetId);
		} else {
			while ($records->hasNext()) {
				$this->updateRecord($results[$recStructIdString], 
				$initialState[$recStructIdString], $records->next(), $assetId);
			}
		}
	}
	
	/**
	 * Update the auto-generated tags for the asset
	 * 
	 * @param object Id $assetId
	 * @return void
	 * @access public
	 * @since 11/21/06
	 */
	function updateAssetTags ( $assetId ) {		
		// Clean the values into nice tag values
		$newTagValues = array();
		foreach ($this->_newStructuredTagValues[$assetId->getIdString()] as $value) {
			$tag = new Tag($value);
			if ($tag->getValue())
				$newTagValues[] = $tag->getValue();
		}
		
		// Get the TaggedItem for this asset
		$idManager = Services::getService("Id");
		$systemAgentId =$idManager->getId('system:concerto');
		$item = TaggedItem::forId($assetId, 'concerto');
		
		// Remove any missing tags
		$systemTags =$item->getTagsByAgent($systemAgentId);
		$existingTagValues = array();
		while ($systemTags->hasNext()) {
			$tag =$systemTags->next();
			if (in_array($tag->getValue(), $newTags))
				$existingTagValues[] = $tag->getValue();
			else {
				$tag->removeFromItemsForAgent($item, $systemAgentId);
				printpre("Removing tag '$tagValue'");
			}
		}
		
		// Add any new tags
		foreach ($newTagValues as $tagValue) {
			if (!in_array($tagValue, $existingTagValues)) {
				printpre("Adding tag '$tagValue'");
				$tag = new Tag($tagValue);
				$tag->tagItemForAgent($item, $systemAgentId);
			}
		}
		
		unset($this->_newStructuredTagValues[$assetId->getIdString()]);
	}
	
	/**
	 * Update the given record to reflect the values changed in the wizard
	 * 
	 * @param array $results, the wizard results
	 * @param array $initialState, the initial wizard results
	 * @param object Record $record
	 * @param object Id $assetId
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function updateRecord ($results, $initialState, $record, $assetId) {
		$recordId =$record->getId();
		$recStruct =$record->getRecordStructure();
		$recStructId =$recStruct->getId();
		print "<div style='background-color: #fdd;'>";
		printpre("Updating record: ".$recordId->getIdString()." for Structure: ".$recStructId->getIdString());
		print "Results: ";
		printpre($results);
		print "InitialState: ";
		printpre($initialState);
		
		$recStruct =$record->getRecordStructure();
		
		$partStructs =$recStruct->getPartStructures();
		while ($partStructs->hasNext()) {
			$partStruct =$partStructs->next();
			
			$partStructId = $partStruct->getId();
			$partStructIdString = preg_replace("/[^a-zA-Z0-9:_\-]/", "_", $partStructId->getIdString());
			
			if (isset($initialState[$partStructIdString]))
				$tmpInitialState = $initialState[$partStructIdString];
			else
				$tmpInitialState = array();
			
			if ($partStruct->isRepeatable()) {
				printpre("Updating RepeatablePart: ".$partStruct->getDisplayName());
				$this->updateRepeatablePart(
					$results[$partStructIdString], 
					$tmpInitialState, 
					$partStruct, $record, $assetId);
			} else {
				printpre("Updating SingleValuedPart: ".$partStruct->getDisplayName());
				$this->updateSingleValuedPart(
					$results[$partStructIdString],
					$tmpInitialState, 
					$partStruct, $record, $assetId);
			}
		}
		
		print "</div>";
	}
	
	/**
	 * Update the given single valued part to reflect the value changed in the wizard
	 * 
	 * @param array $results, the wizard results
	 * @param array $initialState, the initial wizard results
	 * @param object Record $record
	 * @param object Id $assetId
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function updateSingleValuedPart ($partResults, $partInitialState, 
		$partStruct, $record, $assetId) 
	{
		$partStructId = $partStruct->getId();
		
		
		$value =$partResults['value'];
		$initialValue = $partInitialState['value'];
		
		$authZManager = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$authoritativeValues =$partStruct->getAuthoritativeValues();
		if ($value 
			&& $authZManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.modify_authority_list"),
				$this->getRepositoryId())
			&& !$partStruct->isAuthoritativeValue($value)
			&& $authoritativeValues->hasNext()) 
		{
			$partStruct->addAuthoritativeValue($value);
			printpre("\tAdding AuthoritativeValue: ".$value->asString());
		}
		
		if ($partResults['checked'] == '1'
			&& is_object($value)
			&& ($partInitialState['checked'] =='0'
				|| $value != $initialValue))
		{
			$parts =$record->getPartsByPartStructure($partStructId);
			if ($parts->hasNext()) {
				$part =$parts->next();
				$part->updateValue($value);
			} else {
				$record->createPart($partStructId, $value);
			}
			
			// Add this value to the Structured Tags list for this asset.
			$tagGenerator = StructuredMetaDataTagGenerator::instance();
			if ($tagGenerator->shouldGenerateTagsForPartStructure(
					$partStruct->getRepositoryId(), $partStructId))
			{
				$this->_newStructuredTagValues[$assetId->getIdString()][] = $value->asString();
			}
		} 
		// If we aren't passed a value, then the user didn't enter a value
		// or deleted the one that was there.
		// Remove any existing parts.
		else if ($partResults['checked'] == '1' && !is_object($value)) {
			$parts =$record->getPartsByPartStructure($partStructId);
			while ($parts->hasNext()) {
				$part =$parts->next();
				$record->deletePart($part->getId());
			}
		}
	}
	
	/**
	 * Update the given repeatable part to reflect the value changed in the wizard.
	 *
	 * For "Value from Wizard" = wizVal and  "value originally in Part" = partVal
	 *	- If a partVal exists and is equal to a wizVal, leave it alone
	 *	- If a partVal exists, but is not equal to any wizVals, remove it.
	 *	- If a wizVal exists, but no partVals equal to it exist, add a new Part
	 * 
	 * @param array $results, the wizard results
	 * @param array $initialState, the initial wizard results
	 * @param object Record $record
	 * @param object Id $assetId
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function updateRepeatablePart ($partResults, $partInitialState, 
		$partStruct, $record, $assetId) 
	{
		$partStructId = $partStruct->getId();
		$partValsHandled = array();
		
		$parts =$record->getPartsByPartStructure($partStructId);
		while ($parts->hasNext()) {
			$part =$parts->next();
			$partVal =$part->getValue();
			$partStrVal = $partVal->asString();
			
			// Check for existance in the results.
			// if the value is not in the results, remove the part and continue.
			if (!$this->inWizArray($partVal, 'value', $partResults)) 
			{
				$record->deletePart($part->getId());
				$partValsHandled[] = $partStrVal;
				
				$partId =$part->getId();
				printpre("\tDeleting Part: Id: ".$partId->getIdString()." Value: ".$partStrVal);
				printpre("\t\tNot in:".print_r($partResults, true));
				
				continue;
			}
			
			// If the value is in the wizard results, do nothing
			$partValsHandled[] = $partStrVal;
			
			$partId =$part->getId();
			printpre("\tIgnoring Part: Id: ".$partId->getIdString()." Value: ".$partStrVal);
			
			// Add this value to the Structured Tags list for this asset.
			$tagGenerator = StructuredMetaDataTagGenerator::instance();
			if ($tagGenerator->shouldGenerateTagsForPartStructure(
					$partStruct->getRepositoryId(), $partStructId))
			{
				$this->_newStructuredTagValues[$assetId->getIdString()][] = $partStrVal;
			}
			
			continue;
		}
		
		// Go through all of the Wizard result values. If any of them haven't
		// been handled and need to be, add them.
		foreach ($partResults as $key => $valueArray) {
			$checked = ($valueArray['partvalue']['checked'] == '1')?true:false;
			
			$value =$valueArray['partvalue']['value'];
			if (is_object($value)) {
				$valueStr = $value->asString();
				
				
				
				$authZManager = Services::getService("AuthZ");
				$idManager = Services::getService("Id");
				$authoritativeValues =$partStruct->getAuthoritativeValues();
				if ($authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.modify_authority_list"),
						$this->getRepositoryId())
					&& !$partStruct->isAuthoritativeValue($value)
					&& $authoritativeValues->hasNext()) 
				{
					$partStruct->addAuthoritativeValue($value);
					printpre("\tAdding AuthoritativeValue: ".$valueStr);
				}
			
				
				if ($checked && !in_array($valueStr, $partValsHandled)) {
					$part =$record->createPart($partStructId, $value);
					
					$partId =$part->getId();
					printpre("\tAdding Part: Id: ".$partId->getIdString()." Value: ".$valueStr);
					
					// Add this value to the Structured Tags list for this asset.
					$tagGenerator = StructuredMetaDataTagGenerator::instance();
					if ($tagGenerator->shouldGenerateTagsForPartStructure(
							$partStruct->getRepositoryId(), $partStructId))
					{
						$this->_newStructuredTagValues[$assetId->getIdString()][] = $valueStr;
					}
				}
			}
		}
	}
	
	/**
	 * Answer true if the wizard results array contains the value for the 
	 * specified key.
	 * 
	 * @param string $val
	 * @param string $key
	 * @param array $parentArray
	 * @return boolean
	 * @access public
	 * @since 10/25/05
	 */
	function inWizArray ($val, $key, $parentArray) {
		foreach ( $parentArray as $i => $child ) {
			if ($i == $key && $val->isEqualTo($child))
				return true;
			else if (is_array($child)) {
				if ($this->inWizArray($val, $key, $child))
					return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * Update the asset parent based on values from the wizard.
	 * 
	 * @param array $results
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function updateAssetParent ( $results, $asset ) {
		$idManager = Services::getService("Id");
		$authZManager = Services::getService("AuthZ");
		
		// remove the parents if requested.
		try {
			$parents =$asset->getParents();
			if ($parents->hasNext() && $results == 'NONE') {
				printpre("<hr/>Removing Parents:");
				while ($parents->hasNext()) {
					$parent =$parents->next();
					
					if ($authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.remove_children"),
						$parent->getId()))
					{
						printpre("Removing from: "); 
						printpre($parent->getId());
						
						$parent->removeAsset($asset->getId(), TRUE);
					} else {
						printpre("No Authorization to remove from: "); 
						printpre($parent->getId());
					}
				}
				return;
			}
			
			// Change parents if needed
			if ($results && $results != 'NONE') {			
				$newParentId =$idManager->getId($results);
				
				printpre("<hr/>Trying to change or add Parents:");
				
				//verify the current parent and change parents if needed.
				if ($parents->hasNext()) {
					$parent =$parents->next();
					
					if (!$newParentId->isEqual($parent->getId())
						&& $authZManager->isUserAuthorized(
							$idManager->getId("edu.middlebury.authorization.remove_children"),
							$parent->getId())
						&& $authZManager->isUserAuthorized(
							$idManager->getId("edu.middlebury.authorization.add_children"),
							$newParentId))
					{
						printpre("Changing parents from: ");
						printpre($parent->getId());
						printpre("To: ");
						printpre($newParentId);
						$parent->removeAsset($asset->getId(), TRUE);
						
						$repository =$asset->getRepository();
						$newParent =$repository->getAsset($newParentId);
						$newParent->addAsset($asset->getId());
					}
				}
				// If there isn't a previous parent, then just add a parent
				else if ($authZManager->isUserAuthorized(
							$idManager->getId("edu.middlebury.authorization.add_children"),
							$newParentId))
				{
					printpre("Changing parents from NONE to: ");
					printpre($newParentId);
					
					$repository =$asset->getRepository();
					$newParent =$repository->getAsset($newParentId);
					$newParent->addAsset($asset->getId());
				}
			}
		} catch (UnimplementedException $e) {
			$this->saveMessages[] = _("Could not change Asset parent.")." "._("Not supported by this repository.");
		} catch (UnauthorizedException $e) {
			$this->saveMessages[] = _("Could not change Asset parent.")." "._("Unauthorized.");
		}
		
	}
	
	/**
	 * Answer the root assets in the current repository
	 * 
	 * @return object Iterator
	 * @access public
	 * @since 4/2/07
	 */
	function getRootAssets () {
		$repository = $this->getRepository();
		
		if (!$repository)
	 		throw new Exception("Repository not found");
		
		$criteria = NULL;
		$searchProperties = new HarmoniProperties(
				 HarmoniType::fromString("repository::harmoni::order"));
		$searchProperties->addProperty("order", $orderBy = 'DisplayName');
		$searchProperties->addProperty("direction", $direction = 'ASC');
		unset($orderBy, $direction);
		
		$assets =$repository->getAssetsBySearch(
			$criteria, 
			new HarmoniType("Repository","edu.middlebury.harmoni","RootAssets", ""), 
			$searchProperties);
		
		return $assets;
	}
	
	/**
	 * Add an asset option to a WSelectList recursively
	 * 
	 * @param object $field
	 * @param object $asset
	 * @return void
	 * @access public
	 * @since 4/2/07
	 */
	function addAssetOption ($field, $asset, $skip = array(), $depth = 0) {
		$assetId =$asset->getId();
		$authZManager = Services::getService('AuthZ');
		$idManager = Services::getService('Id');
		
		if (in_array($assetId->getIdString(), $skip))
			return;
		
		
		ob_start();
		for ($i = 0; $i <= $depth; $i++)
			print "-";
		
		print " ".$asset->getDisplayName();
		print " (".$assetId->getIdString().")";
		
		try {
			$isAuthorized = $authZManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"),
				$assetId);
		} catch (UnknownIdException $e) {
			$isAuthorized = true;
		}
		if ($isAuthorized) {
			$field->addOption($assetId->getIdString(), ob_get_clean());
		} else {
			$field->addDisabledOption($assetId->getIdString(), ob_get_clean());
		}
		
		
		
		try {
			$children =$asset->getAssets();
			while ($children->hasNext()) {
				$this->addAssetOption($field, $children->next(), $skip, $depth + 1);
			}
		} catch (UnimplementedException $e) {
		
		}
	}
}

?>