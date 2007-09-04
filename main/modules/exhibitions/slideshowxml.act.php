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
class slideshowxmlAction 
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
		require_once(HARMONI."/utilities/Timer.class.php");
		$execTimer = new Timer;
		$execTimer->start();

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
		
		$execTimer->end();
		print "\n\n<ExecutionTime>";
		printf("%1.6f", $execTimer->printTime());
		print "</ExecutionTime>\n";
		
		$dbhandler = Services::getService("DBHandler");
		print "<NumQueries>".$dbhandler->getTotalNumberOfQueries()."</NumQueries>\n";

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
		$idManager = Services::getService("Id");
		$repositoryManager = Services::getService("Repository");
		$authZ = Services::getService("AuthZ");
		
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
		print "\t<slide>\n";
		
		// Title
		print "\t\t<title><![CDATA[";
		print htmlspecialchars($slideAsset->getDisplayName(), ENT_COMPAT, 'UTF-8');
		print "]]></title>\n";
		
		// Caption
		print "\t\t<caption><![CDATA[";
		print $slideAsset->getDescription();
		if (isset($displayMetadata) && $displayMetadata->isTrue()
			&& isset($mediaId)
			&& $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$mediaId)) 
		{
			print "\t\t\t<hr/>\n";
			$mediaAsset =$repositoryManager->getAsset($mediaId);
			$this->printAsset($mediaAsset);
		}
		
		// Unauthorized to view Media Message
		if (isset($mediaId) && !$authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$mediaId))
		{
			print "\t\t\t<div style='font-size: large; font-weight: bold; border: 2px dotted; padding: 5px;'>";
			$harmoni = Harmoni::instance();
			print "\n\t\t\t\t<p>";
			print _("You are not authorized to view the media for this slide.");
			print "</p>\n\t\t\t\t<p>";
			print _("If you have not done so, please go to ");
			print "<a href='".$harmoni->request->quickURL("home", "welcome");
			print "'>Concerto</a>";
			print _(" and log in.");
			print "\t\t\t\t</p>\n\t\t\t</div>\n";
		}
		print"]]></caption>\n";
		
		// Text-Position
		print "\t\t<text-position>";
		if (isset($textPosition))
			print $textPosition->asString();
		else if (!isset($mediaId))
			print "center";
		print "</text-position>\n";
		
		/*********************************************************
		 * Media
		 *********************************************************/	
		if (isset($mediaId)
			&& $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$mediaId))		
		{		
			$mediaAsset =$repositoryManager->getAsset($mediaId);
			$mediaAssetRepository =$mediaAsset->getRepository();
			$mediaAssetRepositoryId =$mediaAssetRepository->getId();
			
			$fileRecords = new MultiIteratorIterator();
			$fileRecords->addIterator($mediaAsset->getRecordsByRecordStructure(
				$idManager->getId("FILE")));
			$fileRecords->addIterator($mediaAsset->getRecordsByRecordStructure(
				$idManager->getId("REMOTE_FILE")));
							
			while ($fileRecords->hasNext())
				$this->printFileRecord($fileRecords->next(), $mediaAssetRepositoryId, $mediaId);
		}
				
		print "\t</slide>\n";
	}
}