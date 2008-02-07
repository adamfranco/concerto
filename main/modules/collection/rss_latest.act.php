<?php
/**
 * @since 8/8/06
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/RSSAction.class.php");

/**
 * Get an RSS feed of the most recently added Assets
 * 
 * @since 8/8/06
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class rss_latestAction
	extends RSSAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/8/06
	 */
	function isExecutionAuthorized () {
		// Check for authorization
 		$authZManager = Services::getService("AuthZ");
 		$idManager = Services::getService("IdManager");
 		if ($authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.view"),
 					$idManager->getId(RequestContext::value('collection_id'))))
 		{
			return TRUE;
 		} else {
 			
 			return FALSE;
		}
	}
	
	/**
	 * Answer the HTTP Authentication 'Relm' to present to the user for authentication.
	 * 
	 * @return mixed string or null
	 * @access public
	 * @since 8/8/06
	 */
	function getRelm () {
		return 'Concerto'; // Override for custom relm.
	}
	
	/**
	 * Build the rss feed
	 * 
	 * @return void
	 * @access public
	 * @since 8/8/06
	 */
	function buildFeed () {
		$harmoni = Harmoni::instance();
		$repositoryManager = Services::getService('Repository');
		$authZManager = Services::getService("AuthZ");
 		$idManager = Services::getService("IdManager");
 		
 		$repositoryId =$idManager->getId(RequestContext::value('collection_id'));
 		$repository =$repositoryManager->getRepository($repositoryId);
 		
 		if (RequestContext::value('order') == 'modification')
 			$this->setTitle($repository->getDisplayName()." - "._("Recently Changed Assets"));
 		else
	 		$this->setTitle($repository->getDisplayName()." - "._("Newest Assets"));
	 	
 		$this->setDescription($repository->getDescription());
 		$this->setLink($harmoni->request->quickURL('collection', 'browse', 
 			array('collection_id' => $repositoryId->getIdString())));
		
		$assets =$this->getAssets($repository);
		$i = 0;
		while ($assets->hasNext() && $i < 20) {
			$this->addItem($this->getAssetItem($assets->next()));
			$i++;
		}
	}
	
	/**
	 * Answer an iterator of the latest assets
	 * 
	 * @param object Repository $repository
	 * @return object Iterator
	 * @access public
	 * @since 8/8/06
	 */
	function getAssets ($repository) {
		$searchModuleManager = Services::getService("RepositorySearchModules");		
		
		$searchProperties = new HarmoniProperties(
					HarmoniType::fromString("repository::harmoni::order"));
		if (RequestContext::value('order') == 'modification')
			$searchProperties->addProperty("order", $arg0 = 'ModificationDate');
		else
			$searchProperties->addProperty("order", $arg1 = "CreationDate");
		$searchProperties->addProperty("direction", $arg2 = 'DESC');
					
		
		$assets =$repository->getAssetsBySearch(
				$criteria = '*',
				new Type(	"Repository",
							"edu.middlebury.harmoni",
							"Keyword", 
							"Search with a string for keywords."),
				$searchProperties);
		
		return $assets;
	}
	
	/**
	 * Add an Asset to the feed
	 * 
	 * @param object Asset $asset
	 * @return object RSSItem
	 * @access public
	 * @since 8/8/06
	 */
	function getAssetItem ($asset) {
		$harmoni = Harmoni::instance();
		$idManager = Services::getService("IdManager");
		$assetId =$asset->getId();
		$repository =$asset->getRepository();
		$repositoryId =$repository->getId();
		
		$item = new RSSItem;
		
		$item->setTitle($asset->getDisplayName());
		
		$item->setLink($harmoni->request->quickURL('asset', 'view', array(
			'collection_id' => $repositoryId->getIdString(),
			'asset_id' => $assetId->getIdString())));
		
		if (RequestContext::value('order') == 'modification')
			$item->setPubDate($asset->getModificationDate());
		else
			$item->setPubDate($asset->getCreationDate());
		
		$type =$asset->getAssetType();
		$item->addCategory($type->getKeyword(), $type->getDomain());
		
		// The item HTML
		ob_start();
		print "<div>";
		/*********************************************************
		 * Files
		 *********************************************************/
		print "\n\t<div id='files' style='float: right; max-width: 60%;'>";
		$fileRecords =$asset->getRecordsByRecordStructure($idManager->getId("FILE"));
		while ($fileRecords->hasNext()) {
			$fileRecord =$fileRecords->next();
			$fileUrl = RepositoryInputOutputModuleManager::getFileUrlForRecord(
							$asset, $fileRecord);
			print "\n\t<div style='height: 200px; width: 200px; text-align: center; vertical-align: middle; float: left;'>";
			print "\n\t\t<a href='".$fileUrl."'>";
			print "\n\t\t<img src='";
			print RepositoryInputOutputModuleManager::getThumbnailUrlForRecord(
					$asset, $fileRecord);
			print "' style='vertical-align: middle;'/>";
			print "\n\t\t</a>";
			print "\n\t</div>";
			
			// Add it as an enclosure
			$fileSizeParts =$fileRecord->getPartsByPartStructure(
											$idManager->getId('FILE_SIZE'));
			$fileSizePart =$fileSizeParts->next();
			$mimeTypeParts =$fileRecord->getPartsByPartStructure(
											$idManager->getId('MIME_TYPE'));
			$mimeTypePart =$mimeTypeParts->next();
			$item->addEnclosure($fileUrl, $fileSizePart->getValue(), $mimeTypePart->getValue());
		}
		print "\n\t</div>";
		
		/*********************************************************
		 * Basic metadata
		 *********************************************************/
		print "\n\t<dl>";
		
		if ($asset->getDescription()) {
			$description = HtmlString::withValue($asset->getDescription());
			$description->clean();
			print "\n\t\t<dt style='font-weight: bold;'>"._("Description:")."</dt>";
			print "\n\t\t<dd>".$description->asString()."</dd>";
		}
		
		$date = $asset->getModificationDate();
		print  "\n\t\t<dt style='font-weight: bold;'>";
		print _("Modification Date");
		print ":</dt>\n\t\t<dd >";
		print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
		print "</dd>";
		
		$date = $asset->getCreationDate();
		print  "\n\t\t<dt style='font-weight: bold;'>";
		print _("Creation Date");
		print ":</dt>\n\t\t<dd >";
		print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
		print "</dd>";
	
		if(is_object($asset->getEffectiveDate())) {
			$date = $asset->getEffectiveDate();
			print  "\n\t\t<dt style='font-weight: bold;'>";
			print _("Effective Date");
			print ":</dt>\n\t\t<dd >";
			print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
			print "</dd>";
		}
		
		if(is_object($asset->getExpirationDate())) {
			$date = $asset->getExpirationDate();
			print  "\n\t\t<dt style='font-weight: bold;'>";
			print _("Expiration Date");
			print ":</dt>\n\t\t<dd >";
			print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
			print "</dd>";
		}
		print "\n\t</dl>";
		
		/*********************************************************
		 * Other Info Records
		 *********************************************************/		
		// Get the set of RecordStructures so that we can print them in order.
		$setManager = Services::getService("Sets");
		$structSet =$setManager->getPersistentSet($repositoryId);	
		$structSet->reset();
		// First, lets go through the info structures listed in the set and print out
		// the info records for those structures in order.
		while ($structSet->hasNext()) {
			$structureId =$structSet->next();
			if ($structureId->isEqual($idManager->getId("FILE")))
				continue;
			
			$recordStructure =$repository->getRecordStructure($structureId);
			$records =$asset->getRecordsByRecordStructure($structureId);
			while ($records->hasNext()) {
				$record =$records->next();
				$recordId =$record->getId();				
		
				print "\n\t<hr />";
				print "\n\t<h3>".$recordStructure->getDisplayName()."</h3>";
				$this->printRecord($repositoryId, $assetId, $record);
			}	
		}
		
		print "</div>";
		print "\n\t<div style='clear: both;'>";
		print "</div>";
		$item->setDescription(ob_get_clean());
		
		return $item;
	}
	
	/**
	 * Print out an info record
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 8/9/06
	 */
	function printRecord($repositoryId, $assetId, $record) {	
		$recordStructure =$record->getRecordStructure();
		$structureId =$recordStructure->getId();
		
		// Print out the fields parts for this structure
		$setManager = Services::getService("Sets");
		$partStructureSet =$setManager->getPersistentSet($structureId);
		
		$partStructureArray = array();
		// Print out the ordered parts/fields
		$partStructureSet->reset();
		while ($partStructureSet->hasNext()) {
			$partStructureId =$partStructureSet->next();
			$partStructureArray[] =$recordStructure->getPartStructure($partStructureId);
		}
		// Get the rest of the parts (the unordered ones);
		$partStructureIterator =$recordStructure->getPartStructures();
		while ($partStructureIterator->hasNext()) {
			$partStructure =$partStructureIterator->next();
			if (!$partStructureSet->isInSet($partStructure->getId()))
				$partStructureArray[] =$partStructure;
		}
		
		$moduleManager = Services::getService("InOutModules");
		print $moduleManager->generateDisplayForPartStructures($repositoryId, $assetId, $record, $partStructureArray);
	}
	
}

?>