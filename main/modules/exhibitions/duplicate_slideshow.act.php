<?php
/**
 * @since 4/3/07
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This action creates a duplicate of the slideshow
 * 
 * @since 4/3/07
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class duplicate_slideshowAction
	extends MainWindowAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/15/06
	 */
	function isAuthorizedToExecute () {
		// Check that the user can delete this exhibition
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return ($authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.add_children"), 
					$idManager->getId(RequestContext::value('exhibition_id')))
				&& $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view"), 
					$idManager->getId(RequestContext::value('slideshow_id'))));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 8/15/06
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to duplicate this slideshow here.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 8/15/06
	 */
	function getHeadingText () {
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$asset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('slideshow_id')));
		return _("Duplicate the")." <em>".$asset->getDisplayName()."</em> Slideshow.";
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 8/15/06
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$setManager =& Services::getService("Sets");
		
		$this->_repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		
		$exhibitionId =& $idManager->getId(RequestContext::value('exhibition_id'));
		$exhibition =& $this->_repository->getAsset($exhibitionId);
		
		$oldSlideshowId =& $idManager->getId(RequestContext::value('slideshow_id'));
		$oldSlideshowAsset =& $this->_repository->getAsset($oldSlideshowId);
		
	// Duplicate the Slideshow
		$newSlideshow =& $this->_repository->createAsset(
			$oldSlideshowAsset->getDisplayName()." "._("Copy"),
			$oldSlideshowAsset->getDescription(),
			$oldSlideshowAsset->getAssetType());
		$newSlideshowId =& $newSlideshow->getId();
		$exhibition->addAsset($newSlideshow->getId());
	
	// Duplicate each slide
		$newSlideOrder =& $setManager->getPersistentSet($newSlideshow->getId());
		
		$slideIterator =& $oldSlideshowAsset->getAssets();
		$slideOrder =& $setManager->getPersistentSet($oldSlideshowId);
		$orderedSlides = array();
		$orderlessSlides = array();
		while ($slideIterator->hasNext()) {
			$slideAsset =& $slideIterator->next();
			$slideId =& $slideAsset->getId();
			
			if ($slideOrder->isInSet($slideId))
				$orderedSlides[$slideOrder->getPosition($slideId)] =&
					$slideAsset;
			else 
				$orderlessSlides[] =& $slideAsset;
		}
		
		ksort($orderedSlides);
		foreach ($orderedSlides as $slide) {
			$newSlide =& $this->duplicateSlide($newSlideshow, $slide);
			$newSlideOrder->addItem($newSlide->getId());
		}
		foreach($orderlessSlides as $slide) {
			$newSlide =& $this->duplicateSlide($newSlideshow, $slide);
			$newSlideOrder->addItem($newSlide->getId());
		}
		
	
	// Move the copy to the correct position
		$exhibitionSet =& $setManager->getPersistentSet($exhibitionId);
		$oldPosition = $exhibitionSet->getPosition($oldSlideshowId);
		$exhibitionSet->addItem($newSlideshow->getId());
		$exhibitionSet->moveToPosition($newSlideshow->getId(), $oldPosition + 1);
			
			
	// Go to the editing screen
		$harmoni->request->startNamespace('modify_slideshow');
		$url =& $harmoni->request->quickURL("exhibitions", "modify_slideshow", 
					array("slideshow_id" => $newSlideshowId->getIdString()));
		$harmoni->request->endNamespace();
		RequestContext::locationHeader($url);
	}
	
	/**
	 * Duplicate a slide
	 * 
	 * @param object Asset $newSlideshow
	 * @param object Asset $oldSlide
	 * @return object Asset
	 * @access public
	 * @since 4/3/07
	 */
	function &duplicateSlide ( &$newSlideshow, &$oldSlide ) {
		$newSlide =& $this->_repository->createAsset(
			$oldSlide->getDisplayName(),
			$oldSlide->getDescription(),
			$oldSlide->getAssetType());
		$newSlideshow->addAsset($newSlide->getId());
		
		$idManager =& Services::getService('Id');
		
		$slideRecordStructId =& $idManager->getId(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure");
		
		$textPositionId =& $idManager->getId(
			"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.text_position");
		$showMetadataId =& $idManager->getId(
			"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.display_metadata");
		$targetId =& $idManager->getId(
			"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.target_id");
		
		$newRecord =& $newSlide->createRecord($slideRecordStructId);
		
		$iterator =& $oldSlide->getPartValuesByPartStructure(
			$textPositionId);
		if ($iterator->hasNext()) {
			$value =& $iterator->next();
			$newRecord->createPart($textPositionId, $value);
		}
		
		$iterator =& $oldSlide->getPartValuesByPartStructure(
			$showMetadataId);
		if ($iterator->hasNext()) {
			$value =& $iterator->next();
			$newRecord->createPart($showMetadataId, $value);
		}
		
		$iterator =& $oldSlide->getPartValuesByPartStructure(
			$targetId);
		if ($iterator->hasNext()) {
			$value =& $iterator->next();
			$newRecord->createPart($targetId, $value);
		}
		
		return $newSlide;
	}
}

?>