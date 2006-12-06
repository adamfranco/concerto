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
class slide_xmlAction 
	extends browse_slide_xmlAction
{
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$slideAsset =& $this->getAsset();
		return _("View the")
			." <em>".$slideAsset->getDisplayName()."</em> "
			._(" Slide");
	}
	
	/**
	 * Answer the repository used for this action
	 * 
	 * @return object Repository
	 * @access public
	 * @since 5/4/06
	 */
	function &getRepository () {
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");		
		return $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$authZ =& Services::getService("AuthZ");
	
		$slideAsset =& $this->getAsset();
		
		/*********************************************************
		 * First print the header, then the xml content, then exit before
		 * the GUI system has a chance to try to theme the output.
		 *********************************************************/		
		header("Content-Type: text/xml; charset=\"utf-8\"");
		
		print<<<END
<?xml version="1.0" encoding="utf-8" ?>
<!DOCTYPE slide PUBLIC "- //Middlebury College//Slide//EN" "http://concerto.sourceforge.net/dtds/viewer/2.0/slide.dtd">
<slide>

END;
		// Get our record and its data
		$slideRecords =& $slideAsset->getRecordsByRecordStructure(
			$idManager->getId("Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure"));
		
		if ($slideRecords->hasNext()) {
			$slideRecord =& $slideRecords->next();
			
			// Text-Position
			$textPosition =& $this->getFirstPartValueFromRecord(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.text_position",
				$slideRecord);
			
			// Display Metadata
			$displayMetadata =& $this->getFirstPartValueFromRecord(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.display_metadata",
				$slideRecord);
			
			// Media
			$mediaIdStringObj =& $this->getFirstPartValueFromRecord(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.target_id",
				$slideRecord);
			if (strlen($mediaIdStringObj->asString()))
				$mediaId =& $idManager->getId($mediaIdStringObj->asString());
			else
				$mediaId = null;
		}
		
		// ------------------------------------------
		
		// Title
		print "\t<title><![CDATA[";
		print htmlspecialchars($slideAsset->getDisplayName(), ENT_COMPAT, 'UTF-8');
		print "]]></title>\n";
		
		// Caption
		print "\t<caption><![CDATA[";
		print $slideAsset->getDescription();
		if (isset($displayMetadata) && $displayMetadata->isTrue()
			&& isset($mediaId)
			&& $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$mediaId)) 
		{
			print "\t\t<hr/>\n";
			$mediaAsset =& $repositoryManager->getAsset($mediaId);
			$this->printAsset($mediaAsset);
		}
		
		// Unauthorized to view Media Message
		if (isset($mediaId) && !$authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$mediaId))
		{
			print "\t\t<div style='font-size: large; font-weight: bold; border: 2px dotted; padding: 5px;'>";
			$harmoni =& Harmoni::instance();
			print "\n\t\t<p>";
			print _("You are not authorized to view the media for this slide.");
			print "</p>\n\t\t\t<p>";
			print _("If you have not done so, please go to ");
			print "<a href='".$harmoni->request->quickURL("home", "welcome");
			print "'>Concerto</a>";
			print _(" and log in.");
			print "\t\t\t</p>\n\t\t</div>\n";
		}
		print"]]></caption>\n";
		
		// Text-Position
		print "\t<text-position>";
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
			$mediaAsset =& $repositoryManager->getAsset($mediaId);
			$mediaAssetRepository =& $mediaAsset->getRepository();
			$mediaAssetRepositoryId =& $mediaAssetRepository->getId();
			
			$fileRecords =& new MultiIteratorIterator();
			$fileRecords->addIterator($mediaAsset->getRecordsByRecordStructure(
				$idManager->getId("FILE")));
			$fileRecords->addIterator($mediaAsset->getRecordsByRecordStructure(
				$idManager->getId("REMOTE_FILE")));
							
			while ($fileRecords->hasNext())
				$this->printFileRecord($fileRecords->next(), $mediaAssetRepositoryId, $mediaId);
		}

		print "</slide>\n";		
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
	function printSlide ( &$slideAsset ) {
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$authZ =& Services::getService("AuthZ");
		
		// Get our record and its data
		$slideRecords =& $slideAsset->getRecordsByRecordStructure(
			$idManager->getId("Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure"));
		
		if ($slideRecords->hasNext()) {
			$slideRecord =& $slideRecords->next();
			
			// Text-Position
			$textPosition =& $this->getFirstPartValueFromRecord(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.text_position",
				$slideRecord);
			
			// Display Metadata
			$displayMetadata =& $this->getFirstPartValueFromRecord(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.display_metadata",
				$slideRecord);
			
			// Media
			$mediaIdStringObj =& $this->getFirstPartValueFromRecord(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.target_id",
				$slideRecord);
			if (strlen($mediaIdStringObj->asString()))
				$mediaId =& $idManager->getId($mediaIdStringObj->asString());
			else
				$mediaId = null;
		}
		
		// ------------------------------------------
		print "<slide>\n";
		
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
			$mediaAsset =& $repositoryManager->getAsset($mediaId);
			$this->printAsset($mediaAsset);
		}
		
		// Unauthorized to view Media Message
		if (isset($mediaId) && !$authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$mediaId))
		{
			print "\t\t\t<div style='font-size: large; font-weight: bold; border: 2px dotted; padding: 5px;'>";
			$harmoni =& Harmoni::instance();
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
			$mediaAsset =& $repositoryManager->getAsset($mediaId);
			$mediaAssetRepository =& $mediaAsset->getRepository();
			$mediaAssetRepositoryId =& $mediaAssetRepository->getId();
			
			$fileRecords =& new MultiIteratorIterator();
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