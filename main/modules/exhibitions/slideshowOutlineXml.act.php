<?php
/**
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/modules/collection/browse_slide_xml.act.php");

/**
 * 
 * 
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class slideshowOutlineXmlAction 
	extends browse_slide_xmlAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can create an asset here.
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::Instance();
// 		$harmoni->request->startNamespace("modify_slideshow");

		$return = $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"),
			$idManager->getId(RequestContext::value('slideshow_id')));
		
// 		$harmoni->request->endNamespace();
		
		return $return;
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		/*********************************************************
		 * First print the header, then the xml content, then exit before
		 * the GUI system has a chance to try to theme the output.
		 *********************************************************/		
		header("Content-Type: text/xml; charset=\"utf-8\"");
		
		print<<<END
<?xml version="1.0" encoding="utf-8" ?>
<!DOCTYPE slideshow PUBLIC "- //Middlebury College//Slide-Show//EN" "http://concerto.sourceforge.net/dtds/viewer/2.0/slideshow.dtd">
<slideshow>

END;
		print "\t<title>"._("Not Authorized")."</title>\n";
		print "\t<slide>\n";
		
		// Title
		print "\t\t<title>"._("Not Authorized")."</title>\n";
		
		// Caption
		print "\t\t<caption><![CDATA[";
		print _("You are not authorized to view this <em>Slideshow</em>.");
		print"]]></caption>\n";
		print "\t\t<text-position>center</text-position>";
		print "\t</slide>\n";
		print "</slideshow>\n";		
		exit;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$slideshowAsset =$this->getAsset();
		return _("View the")
			." <em>".$slideshowAsset->getDisplayName()."</em> "
			._(" Slideshow");
	}
	
	/**
	 * Answer the repository used for this action
	 * 
	 * @return object Repository
	 * @access public
	 * @since 5/4/06
	 */
	function getRepository () {
		$idManager = Services::getService("Id");
		$repositoryManager = Services::getService("Repository");		
		return $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
	}
	
	/**
	 * Answer the asset used for this action
	 * 
	 * @return object Asset
	 * @access public
	 * @since 5/4/06
	 */
	function getAsset () {
		$idManager = Services::getService("Id");
		$slideshowId =$idManager->getId(RequestContext::value('slideshow_id'));
		$repository =$this->getRepository();
		return $repository->getAsset($slideshowId);
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$idManager = Services::getService("Id");
	
		$slideshowAsset =$this->getAsset();
		
		/*********************************************************
		 * First print the header, then the xml content, then exit before
		 * the GUI system has a chance to try to theme the output.
		 *********************************************************/		
		header("Content-Type: text/xml; charset=\"utf-8\"");
		
		print<<<END
<?xml version="1.0" encoding="utf-8" ?>
<!DOCTYPE slideshow PUBLIC "- //Middlebury College//Slide-Show//EN" "http://concerto.sourceforge.net/dtds/viewer/2.0/slideshow.dtd">
<slideshow>

END;
		print "\t<title>".$slideshowAsset->getDisplayName()."</title>\n";
		
		print "\t<media-sizes>\n";
		print "\t\t\t\t<size>small</size>\n";
		print "\t\t\t\t<size>medium</size>\n";
		print "\t\t\t\t<size>large</size>\n";
		print "\t\t\t\t<size>original</size>\n";
		print "\t</media-sizes>\n";
		
		print "\t<default_size>medium</default_size>\n";
		
		$setManager = Services::getService("Sets");
		$slideshowSet =$setManager->getPersistentSet($slideshowAsset->getId());
		$slideIterator =$slideshowAsset->getAssets();
		$orderedSlides = array();
		$unorderedSlides = array();
		
		while ($slideIterator->hasNext()) {
			$slideAsset =$slideIterator->next();
			$slideAssetId =$slideAsset->getId();
			
			if ($slideshowSet->isInSet($slideAssetId))
				$orderedSlides[$slideshowSet->getPosition($slideAssetId)] =$slideAsset;
			else
				$unorderedSlides[] =$slideAsset;
		}
		ksort($orderedSlides);
		$slides = array_merge($orderedSlides, $unorderedSlides);
		unset($orderedSlides, $unorderedSlides);
		
		foreach(array_keys($slides) as $key) {
			$this->printSlide($slides[$key]);
		}

		print "</slideshow>\n";		
		exit;
	}
	
	/**
	 * Print out a slide XML element
	 * 
	 * @param object Asset $slideAsset
	 * @return void
	 * @access public
	 * @since 9/28/05
	 */
	function printSlide ( $slideAsset ) {
		$assetId =$slideAsset->getId();
		$harmoni = Harmoni::instance();
		$idManager = Services::getService("Id");
		
		// Get our record and its data
		$slideRecords =$slideAsset->getRecordsByRecordStructure(
			$idManager->getId("Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure"));
		
		if ($slideRecords->hasNext()) {
			$slideRecord =$slideRecords->next();
			
			// Text-Position
			$textPosition =$this->getFirstPartValueFromRecord(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.text_position",
				$slideRecord);
			
			// Display Metadata
			$displayMetadata =$this->getFirstPartValueFromRecord(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.display_metadata",
				$slideRecord);
			
			// Media
			$mediaIdStringObj =$this->getFirstPartValueFromRecord(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.target_id",
				$slideRecord);
			if (strlen($mediaIdStringObj->asString()))
				$mediaId =$idManager->getId($mediaIdStringObj->asString());
			else
				$mediaId = null;
		}
		
		// ------------------------------------------
		
		print "\t<slide ";
		print "source='";
		print $harmoni->request->quickURL('exhibitions', 'slide_xml', 
			array('asset_id' => $assetId->getIdString()));
		print "'>\n";
		
		// Text-Position
		print "\t\t<text-position>";
		if (isset($textPosition))
			print $textPosition->asString();
		else if (!isset($mediaId))
			print "center";
		print "</text-position>\n";		
		
		print "\t</slide>\n";
	}
}