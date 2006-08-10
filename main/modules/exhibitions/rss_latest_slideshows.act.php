<?php
/**
 * @since 8/10/06
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/RSSAction.class.php");

/**
 * build an RSS Feed of the most recently added (or modified) slideshows.
 * 
 * @since 8/10/06
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class rss_latest_slideshowsAction
	extends RSSAction
{
		
	/**
	 * @var integer $_numInFeed;  
	 * @access private
	 * @since 8/9/06
	 */
	var $_numInFeed = 20;
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/10/06
	 */
	function isExecutionAuthorized () {
		if (RequestContext::value('exhibition_id')) {
			// Check for authorization
			$authZManager =& Services::getService("AuthZ");
			$idManager =& Services::getService("IdManager");
			if ($authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.view"),
						$idManager->getId(RequestContext::value('exhibition_id'))))
			{
				return TRUE;
			} else {				
				return FALSE;
			}
		} 
		// If we are searching all exhibitions, force authentication
		else {
			if ($this->isAuthenticated())
				return true;
			else
				return false;
		}
	}
	
	/**
	 * Answer the HTTP Authentication 'Relm' to present to the user for authentication.
	 * 
	 * @return mixed string or null
	 * @access public
	 * @since 8/10/06
	 */
	function getRelm () {
		return 'Concerto'; // Override for custom relm.
	}
	
	/**
	 * Build the rss feed
	 * 
	 * @return void
	 * @access public
	 * @since 8/10/06
	 */
	function buildFeed () {
		$harmoni =& Harmoni::instance();
		$repositoryManager =& Services::getService('Repository');
		$authZManager =& Services::getService("AuthZ");
 		$idManager =& Services::getService("IdManager");
 		
 		$repositoryId =& $idManager->getId("edu.middlebury.concerto.exhibition_repository");
 		$repository =& $repositoryManager->getRepository($repositoryId);
 		
 		if (RequestContext::value('exhibition_id')) {
 			$exhibitionAssetId =& $idManager->getId(RequestContext::value('exhibition_id'));
 			$exhibitionAsset =& $repository->getAsset($exhibitionAssetId);
 		} else {
 			$exhibitionAssetId = null;
 			$exhibitionAsset = null;
 		}
 			
 		if ($exhibitionAsset) {
 			$title = $exhibitionAsset->getDisplayName();
 			$this->setDescription($exhibitionAsset->getDescription());
 			$this->setLink($harmoni->request->quickURL('exhibitions', 'browse_exhibition', 
				array('exhibition_id' => $exhibitionAssetId->getIdString())));
 		} else {
 			$title = _("All of Concerto");
	 		$this->setDescription(_("Slideshows from across all of Concerto."));
	 		$this->setLink($harmoni->request->quickURL('exhibitions', 'browse'));
 		}
 		
 		if (RequestContext::value('order') == 'modification')
 			$title .= " - "._("Recently Changed Slideshows");
 		else
	 		$title .= " - "._("Newest Slideshows");
	 	
	 	$this->setTitle($title);
		
		$slideshowAssets =& $this->getAssets($repository);
		$i = 0;
		$exhibitionAssetType =& new Type("Asset Types", "edu.middlebury.concerto", "Exhibition");
		while ($slideshowAssets->hasNext() && $i < 20) {
			$slideshowAsset =& $slideshowAssets->next();
			$slideshowAssetId =& $slideshowAsset->getId();
			
			// Limit to only one exhibition if necessary
			$include = true;
			if (is_object($exhibitionAssetId)) {
				$parents =& $slideshowAsset->getParents();
				while ($parents->hasNext()) {
					$parent =& $parents->next();
					if ($exhibitionAssetType->isEqual($parent->getAssetType())
						&& !$exhibitionAssetId->isEqual($parent->getId()))
					{
						$include = false;
					}
				}
			}
			
			// Check Authorization
			if ($include && $authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.view"),
						$slideshowAssetId))
			{
				$this->addItem($this->getAssetItem($slideshowAsset));
				$i++;
			}
		}
	}
	
	/**
	 * Answer an iterator of the latest assets
	 * 
	 * @param object Repository $repository
	 * @return object Iterator
	 * @access public
	 * @since 8/10/06
	 */
	function &getAssets (&$repository) {
		$searchModuleManager =& Services::getService("RepositorySearchModules");		
		
		$searchProperties =& new HarmoniProperties(
					Type::fromString("repository::harmoni::order"));
		if (RequestContext::value('order') == 'modification')
			$searchProperties->addProperty("order", $arg0 = 'ModificationDate');
		else
			$searchProperties->addProperty("order", $arg1 = "CreationDate");
		$searchProperties->addProperty("direction", $arg2 = 'DESC');
		
		$searchProperties->addProperty("allowed_types", $arg3 = array(
			new Type("Asset Types", "edu.middlebury.concerto", "Slideshow")));
					
		
		$assets =& $repository->getAssetsBySearch(
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
	function &getAssetItem (&$asset) {
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("IdManager");
		$assetId =& $asset->getId();
		
		$item =& new RSSItem;
		
		$item->setTitle($asset->getDisplayName());
		$item->addCategory("Slideshow");
		
		$item->setLink(VIEWER_URL."?&amp;source=".
			urlencode($harmoni->request->quickURL("exhibitions", "slideshowOutlineXml", 
						array("slideshow_id" => $assetId->getIdString()))));
		
		/*********************************************************
		 * Get number of slides and first thumbnail.
		 *********************************************************/	
		$slides =& $asset->getAssets();
		$count = 0;
		while ($slides->hasNext()) {
			$slideAsset =& $slides->next();
			$count++;
			
			if (!isset($firstMediaUrl)) {
				$slideRecords =& $slideAsset->getRecordsByRecordStructure(
					$idManager->getId(
						"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure"));
				if ($slideRecords->hasNext()) {
					$slideRecord =& $slideRecords->next();
					// Media
					$mediaIdStringObj =& $this->getFirstPartValueFromRecord(
							"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.target_id",
						$slideRecord);
					if (strlen($mediaIdStringObj->asString())) {
						$mediaId =& $idManager->getId($mediaIdStringObj->asString());
						$firstMediaUrl = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($mediaId);
					}
				}
			}
		}
		
		/*********************************************************
		 * item description text.
		 *********************************************************/
		ob_start();
		if (isset($firstMediaUrl))
			print "\n<img src='".$firstMediaUrl."' style='float: right'/>";
		print "\n<div style='margin-bottom: 10px'>".$asset->getDescription()."</div>";
		print "\n<div style='clear: both'>(".$count." "._("slides").")</div>";		
		
		$item->setDescription(ob_get_clean());
		
		return $item;
	}
	
	/**
	 * Answer the first Part's value object for the given PartStructureIdString 
	 * and Record 
	 * 
	 * @param string $partStructIdString
	 * @param object Record $record
	 * @return object SObject
	 * @access public
	 * @since 9/28/05
	 */
	function &getFirstPartValueFromRecord ( $partStructIdString, &$record ) {
		$idManager =& Services::getService("Id");
		
		$parts =& $record->getPartsByPartStructure(
			$idManager->getId($partStructIdString));
		
		if ($parts->hasNext()) {
			$part =& $parts->next();
			if (is_object($part->getValue()))
				$value =& $part->getValue();
			else
				$value = $part->getValue();
		} else {
			$value = null;
		}
		
		return $value;
	}
}

?>