<?php
/**
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

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
	extends MainWindowAction
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
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$harmoni =& Harmoni::Instance();
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
		return _("You are not authorized to view this <em>Slideshow</em>.");
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
// 		$harmoni->request->startNamespace("modify_slideshow");

		$actionRows =& $this->getActionRows();
		
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		
		$slideshowId =& $idManager->getId(RequestContext::value('slideshow_id'));
		
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$slideshowAsset =& $repository->getAsset($slideshowId);
				
// 		$harmoni->request->endNamespace();
		
		
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
		
		$setManager =& Services::getService("Sets");
		$slideshowSet =& $setManager->getPersistentSet($slideshowId);
		$slideIterator =& $slideshowAsset->getAssets();
		$orderedSlides = array();
		$unorderedSlides = array();
		
		while ($slideIterator->hasNext()) {
			$slideAsset =& $slideIterator->next();
			$slideAssetId =& $slideAsset->getId();
			
			if ($slideshowSet->isInSet($slideAssetId))
				$orderedSlides[$slideshowSet->getPosition($slideAssetId)] =& $slideAsset;
			else
				$unorderedSlides[] =& $slideAsset;
		}
		ksort($orderedSlides);
		$slides =& array_merge($orderedSlides, $unorderedSlides);
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
		print "\t<slide>\n";
		
		// Title
		print "\t\t<title><![CDATA[";
		print htmlentities($slideAsset->getDisplayName(), ENT_COMPAT, 'UTF-8');
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
		if (!isset($mediaId))
			print "center";
		else if (isset($textPosition))
			print $textPosition->asString();
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
			
			$fileRecords =& $mediaAsset->getRecordsByRecordStructure(
				$idManager->getId("FILE"));
			
			while ($fileRecords->hasNext()) {
				$fileRecord =& $fileRecords->next();
				$fileRecordId =& $fileRecord->getId();
				print "\t\t<media>\n";
				print "\t\t\t<version>\n";
				
				print "\t\t\t\t<type>";
				print $this->getFirstPartValueFromRecord("MIME_TYPE", $fileRecord);
				print "</type>\n";
				
				print "\t\t\t\t<size>original</size>\n";
				
				$dimensions = $this->getFirstPartValueFromRecord("DIMENSIONS", 
					$fileRecord);
									
				if (isset($dimensions[1]) && $dimensions[1] > 0) {
					print "\t\t\t\t<height>";
					print $dimensions[1]."px";
					print "</height>\n";
				}
				
				if (isset($dimensions[0]) && $dimensions[0] > 0) {
					print "\t\t\t\t<width>";
					print $dimensions[0]."px";
					print "</width>\n";	
				}
				
				print "\t\t\t\t<url><![CDATA[";
				$filename = $this->getFirstPartValueFromRecord("FILE_NAME", 
					$fileRecord);
				
				$harmoni =& Harmoni::instance();
				$harmoni->request->startNamespace("polyphony-repository");
				
				print $harmoni->request->quickURL("repository", "viewfile", 
						array(
							"repository_id" => $mediaAssetRepositoryId->getIdString(),
							"asset_id" => $mediaId->getIdString(),
							"record_id" => $fileRecordId->getIdString(),
							"file_name" => $filename));
				
				
				$harmoni->request->endNamespace();
				print "]]></url>\n";
				
				print "\t\t\t</version>\n";
				print "\t\t</media>\n";
			}
		}
				
		print "\t</slide>\n";
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
			$value =& $part->getValue();
		} else
			$value = null;
		
		return $value;
	}
	
	/**
	 * Print out the full metadata for the Asset;
	 * 
	 * @param object Asset
	 * @return void
	 * @access public
	 * @since 9/28/05
	 */
	function printAsset ( &$asset ) {
		/*********************************************************
		 * Asset Info
		 *********************************************************/
		$assetId =& $asset->getId();
		print "\n<div>\n";
		print "\t<strong>"._("DisplayName").":</strong>\n";
		print "\t".$asset->getDisplayName()."\n";
		print "\t<br />\n";
		print "\t<strong>"._("Description").":</strong>\n";
		print "\t".$asset->getDescription()."\n";
		print "\t<br />\n";
		print "\t<strong>"._("ID#").":</strong>\n";
		print "\t".$assetId->getIdString()."\n";
	
		$effectDate =& $asset->getEffectiveDate();
		if(is_object($effectDate))
			print  "\t<br />\n\t<strong>"._("Effective Date").":</strong>\n\t<em>".$effectDate->asString()."</em>\n";
	
		$expirationDate =& $asset->getExpirationDate();
		if(is_object($expirationDate))
			print  "\t<br />\n\t<strong>"._("Expiration Date").":</strong>\n\t<em>".$expirationDate->asString()."</em>\n";
		
		
		/*********************************************************
		 * Info Records
		 *********************************************************/
		$printedRecordIds = array();
		
		// Get the set of RecordStructures so that we can print them in order.
		$setManager =& Services::getService("Sets");
		$idManager =& Services::getService("Id");
		$repository =& $asset->getRepository();
		$structSet =& $setManager->getPersistentSet($repository->getId());
		
		// First, lets go through the info structures listed in the set and print out
		// the info records for those structures in order.
		$structSet->reset();
		while ($structSet->hasNext()) {
			$structureId =& $structSet->next();
			if (!$structureId->isEqual($idManager->getId("FILE"))) {
				$records =& $asset->getRecordsByRecordStructure($structureId);
				while ($records->hasNext()) {
					$record =& $records->next();
					$recordId =& $record->getId();
					$printedRecordIds[] = $recordId->getIdString();
			
					print "\t<div style='padding: 5px; border-top: 1px solid;'>\n";
	 				slideshowxmlAction::printRecord($repository->getId(), $assetId, $record);
					print "\t</div>\n";
				}
			}
		}
		
		/*********************************************************
		 * Asset Content
		 *********************************************************/
		
		/*********************************************************
		 * Close up our tags.
		 *********************************************************/
		print "</div>\n";
	}
	
	/**
	 * Print out a record
	 * 
	 * @param object Id $repositoryId
	 * @param object Id $assetId
	 * @param object Record $record
	 * @return void
	 * @access public
	 * @since 9/28/05
	 */
	function printRecord ( &$repositoryId, &$assetId, &$record ) {
		$recordStructure =& $record->getRecordStructure();
		$structureId =& $recordStructure->getId();
		
		print "\t\t<div style='font-weight: bold; font-style: italic; font-size: large;'>";
		print $recordStructure->getDisplayName().":</div>\n";
		
		// Print out the fields parts for this structure
		$setManager =& Services::getService("Sets");
		$partStructureSet =& $setManager->getPersistentSet($structureId);
		
		$partStructureArray = array();
		// Print out the ordered parts/fields
		$partStructureSet->reset();
		while ($partStructureSet->hasNext()) {
			$partStructureId =& $partStructureSet->next();
			$partStructureArray[] =& $recordStructure->getPartStructure($partStructureId);
		}
		// Get the rest of the parts (the unordered ones);
		$partStructureIterator =& $recordStructure->getPartStructures();
		while ($partStructureIterator->hasNext()) {
			$partStructure =& $partStructureIterator->next();
			if (!$partStructureSet->isInSet($partStructure->getId()))
				$partStructureArray[] =& $partStructure;
		}
		
		$moduleManager =& Services::getService("InOutModules");
		print $moduleManager->generateDisplayForPartStructures($repositoryId, $assetId, $record, $partStructureArray);
	}
}