<?php
/**
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/RepositoryAction.class.php");
require_once(MYDIR."/main/modules/exhibitions/slideshowxml.act.php");

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
	extends RepositoryAction
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
					$idManager->getId("edu.middlebury.authorization.access"), 
					$this->getRepositoryId());
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
		print _("You are not authorized to access this <em>Asset</em>.");
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
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		$harmoni->request->passthrough("collection_id");
		$repository =& $this->getRepository();
		$assetId =& $idManager->getId(RequestContext::value("asset_id"));
		$asset =& $repository->getAsset($assetId);
		
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
		slideshowxmlAction::printAsset($asset);
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
			$fileRecord =& $fileRecords->next();
			$fileRecordId =& $fileRecord->getId();
			$mimeType = slideshowxmlAction::getFirstPartValueFromRecord("MIME_TYPE", $fileRecord);
			print "\t\t<media>\n";
			
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
			
// 			$dimensions = slideshowxmlAction::getFirstPartValueFromRecord("DIMENSIONS", 
// 				$fileRecord);
// 								
// 			if (isset($dimensions[1]) && $dimensions[1] > 0) {
// 				print "\t\t\t\t<height>";
// 				print $dimensions[1]."px";
// 				print "</height>\n";
// 			}
// 			
// 			if (isset($dimensions[0]) && $dimensions[0] > 0) {
// 				print "\t\t\t\t<width>";
// 				print $dimensions[0]."px";
// 				print "</width>\n";	
// 			}
			
			print "\t\t\t\t<url><![CDATA[";
			$filename = slideshowxmlAction::getFirstPartValueFromRecord("FILE_NAME", 
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
			
// 			$dimensions = slideshowxmlAction::getFirstPartValueFromRecord("DIMENSIONS", 
// 				$fileRecord);
// 								
// 			if (isset($dimensions[1]) && $dimensions[1] > 0) {
// 				print "\t\t\t\t<height>";
// 				print $dimensions[1]."px";
// 				print "</height>\n";
// 			}
// 			
// 			if (isset($dimensions[0]) && $dimensions[0] > 0) {
// 				print "\t\t\t\t<width>";
// 				print $dimensions[0]."px";
// 				print "</width>\n";	
// 			}
			
			print "\t\t\t\t<url><![CDATA[";
			$filename = slideshowxmlAction::getFirstPartValueFromRecord("FILE_NAME", 
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
			
			
			print "\t\t</media>\n";
		}
		$harmoni->request->endNamespace();
		
		
		print "\t</slide>\n";
	}

}