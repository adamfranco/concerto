<?php
/**
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/AssetAction.class.php");

/**
 * 
 * 
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class browse_slide_xmlAction 
	extends AssetAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view"), 
					$this->getAssetId());
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
<!DOCTYPE slide PUBLIC "- //Middlebury College//Slide//EN" "http://concerto.sourceforge.net/dtds/viewer/2.0/slide.dtd">

END;
		print "\t<slide>\n";
		
		// Title
		print "\t\t<title>"._("Not Authorized")."</title>\n";
		
		// Caption
		print "\t\t<caption><![CDATA[";
		print _("You are not authorized to view this <em>Asset</em>.");
		print"]]></caption>\n";
		print "\t\t<text-position>center</text-position>";
		print "\t</slide>\n";
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
		$repository =& $this->getRepository();
		return _("Browse Assets in the")
			." <em>".$repository->getDisplayName()."</em> "
			._(" Collection");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		$harmoni->request->passthrough("collection_id");
		$harmoni->request->passthrough("asset_id");	
		$asset =& $this->getAsset();
		
		/*********************************************************
		 * First print the header, then the xml content, then exit before
		 * the GUI system has a chance to try to theme the output.
		 *********************************************************/		
		header("Content-Type: text/xml; charset=\"utf-8\"");
		
		print<<<END
<?xml version="1.0" encoding="utf-8" ?>
<!DOCTYPE slide PUBLIC "- //Middlebury College//Slide//EN" "http://concerto.sourceforge.net/dtds/viewer/2.0/slide.dtd">

END;
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $asset->getId()))
		{
			$this->printAssetXML($asset);
		}
		
		exit;
	}
	
	
	
	/**
	 * Function for printing the asset block of the slideshow XML file
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/14/05
	 */
	function printAssetXML( &$asset) {
		
		$assetId =& $asset->getId();
		$repository =& $asset->getRepository();
		$repositoryId =& $repository->getId();
		$idManager =& Services::getService("Id");
		
		
		// ------------------------------------------
		print "\t<slide>\n";
		
		// Title
		print "\t\t<title><![CDATA[";
		print htmlspecialchars($asset->getDisplayName(), ENT_COMPAT, 'UTF-8');
		print "]]></title>\n";
		
		// Caption
		print "\t\t<caption><![CDATA[";
		$this->printAsset($asset);
		print"]]></caption>\n";
		
		// Text-Position
		print "\t\t<text-position>";
			print "right";
		print "</text-position>\n";
		
		$fileRecords =& $asset->getRecordsByRecordStructure(
			$idManager->getId("FILE"));
		
		/*********************************************************
		 * Files
		 *********************************************************/
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-repository");
		$imgProcessor =& Services::getService("ImageProcessor");
		while ($fileRecords->hasNext()) {
			$this->printFileRecord($fileRecords->next(), $repositoryId, $assetId);
			
		}
		$harmoni->request->endNamespace();
		
		
		print "\t</slide>\n";
	}
	
	/**
	 * Print a FileRecord.
	 * 
	 * @param object Record $fileRecord
	 * @param object Id $repositoryId
	 * @param object Id $assetId
	 * @return void
	 * @access public
	 * @since 5/4/06
	 */
	function printFileRecord (&$fileRecord, &$repositoryId, &$assetId) {
		$fileRecordId =& $fileRecord->getId();
		
		$imgProcessor =& Services::getService("ImageProcessor");
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-repository");
		
		$dimensions = $this->getFirstPartValueFromRecord(
			"DIMENSIONS", 
			$fileRecord);
		$mimeType = $this->getFirstPartValueFromRecord(
			"MIME_TYPE", 
			$fileRecord);
		
			
		print "\t\t<media>\n";
		
		// Give multiple sizes for images
		if (preg_match('/^image.*$/', $mimeType)) {
			/*********************************************************
			 * Small Version
			 *********************************************************/
			print "\t\t\t<version>\n";
			
			print "\t\t\t\t<type>";
			if ($imgProcessor->isFormatSupported($mimeType))
				print $imgProcessor->getWebsafeFormat($mimeType);
			else
				print $mimeType;
			print "</type>\n";
			
			print "\t\t\t\t<size>small</size>\n";
			
			if ((isset($dimensions[1]) && $dimensions[1] > 0)
				&& (isset($dimensions[0]) && $dimensions[0] > 0)) 
			{
				$origHeight = $dimensions[1];
				$origWidth = $dimensions[0];
				
				if ($origHeight > $origWidth) {
					print "\t\t\t\t<height>";
					print "400px";
					print "</height>\n";
					
					$newWidth = round(400*$origWidth/$origHeight);
					print "\t\t\t\t<width>";
					print $newWidth."px";
					print "</width>\n";	
				} else {					
					$newHeight = round(400*$origHeight/$origWidth);
					print "\t\t\t\t<height>";
					print $newHeight."px";
					print "</height>\n";
				
					print "\t\t\t\t<width>";
					print "400px";
					print "</width>\n";
				}
			}
			
			print "\t\t\t\t<url><![CDATA[";
			$filename = $this->getFirstPartValueFromRecord("FILE_NAME", 
				$fileRecord);	
			print $harmoni->request->quickURL("repository", "viewfile", 
					array(
						"repository_id" => $repositoryId->getIdString(),
						"asset_id" => $assetId->getIdString(),
						"record_id" => $fileRecordId->getIdString(),
						"file_name" => $filename,
						"websafe" => "true",
						"size" => 400));
			print "]]></url>\n";
			
			print "\t\t\t</version>\n";
			
			/*********************************************************
			 * Medium Version
			 *********************************************************/
			print "\t\t\t<version>\n";
			
			print "\t\t\t\t<type>";
			if ($imgProcessor->isFormatSupported($mimeType))
				print $imgProcessor->getWebsafeFormat($mimeType);
			else
				print $mimeType;
			print "</type>\n";
			
			print "\t\t\t\t<size>medium</size>\n";
			
			if ((isset($dimensions[1]) && $dimensions[1] > 0)
				&& (isset($dimensions[0]) && $dimensions[0] > 0)) 
			{
				$origHeight = $dimensions[1];
				$origWidth = $dimensions[0];
				
				if ($origHeight > $origWidth) {
					print "\t\t\t\t<height>";
					print "800px";
					print "</height>\n";
					
					$newWidth = round(800*$origWidth/$origHeight);
					print "\t\t\t\t<width>";
					print $newWidth."px";
					print "</width>\n";	
				} else {					
					$newHeight = round(800*$origHeight/$origWidth);
					print "\t\t\t\t<height>";
					print $newHeight."px";
					print "</height>\n";
				
					print "\t\t\t\t<width>";
					print "800px";
					print "</width>\n";
				}
			}
			
			print "\t\t\t\t<url><![CDATA[";
			$filename = $this->getFirstPartValueFromRecord("FILE_NAME", 
				$fileRecord);	
			print $harmoni->request->quickURL("repository", "viewfile", 
					array(
						"repository_id" => $repositoryId->getIdString(),
						"asset_id" => $assetId->getIdString(),
						"record_id" => $fileRecordId->getIdString(),
						"file_name" => $filename,
						"websafe" => "true",
						"size" => 800));
			print "]]></url>\n";
			
			print "\t\t\t</version>\n";
			
			/*********************************************************
			 * Large Version
			 *********************************************************/
			print "\t\t\t<version>\n";
			
			print "\t\t\t\t<type>";
			if ($imgProcessor->isFormatSupported($mimeType))
				print $imgProcessor->getWebsafeFormat($mimeType);
			else
				print $mimeType;
			print "</type>\n";
			
			print "\t\t\t\t<size>large</size>\n";
								
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
			print $harmoni->request->quickURL("repository", "viewfile", 
					array(
						"repository_id" => $repositoryId->getIdString(),
						"asset_id" => $assetId->getIdString(),
						"record_id" => $fileRecordId->getIdString(),
						"file_name" => $filename,
						"websafe" => "true"));
			print "]]></url>\n";
			
			print "\t\t\t</version>\n";
		}
		
		/*********************************************************
		 * Original Version
		 *********************************************************/
		print "\t\t\t<version>\n";
		
		print "\t\t\t\t<type>";
		print $mimeType;
		print "</type>\n";
		
		print "\t\t\t\t<size>original</size>\n";
							
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
		print $harmoni->request->quickURL("repository", "viewfile", 
				array(
					"repository_id" => $repositoryId->getIdString(),
					"asset_id" => $assetId->getIdString(),
					"record_id" => $fileRecordId->getIdString(),
					"file_name" => $filename));
		print "]]></url>\n";
		
		print "\t\t\t</version>\n";
		
		print "\t\t</media>\n";
		$harmoni->request->endNamespace();
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
	
		
		if(is_object($asset->getEffectiveDate())) {
			$effectDate =& $asset->getEffectiveDate();
			print  "\t<br />\n\t<strong>"._("Effective Date").":</strong>\n\t<em>".$effectDate->asString()."</em>\n";
		}
		
		if(is_object($asset->getExpirationDate())) {
			$expirationDate =& $asset->getExpirationDate();
			print  "\t<br />\n\t<strong>"._("Expiration Date").":</strong>\n\t<em>".$expirationDate->asString()."</em>\n";
		}
		
		
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
	 				$this->printRecord($repository->getId(), $assetId, $record);
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