<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/AssetAction.class.php");
require_once(MYDIR."/main/modules/exhibitions/slideshowxml.act.php");

/**
 * 
 * 
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class browsexmlAction 
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
					$idManager->getId("edu.middlebury.authorization.access"), 
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
<!DOCTYPE slideshow PUBLIC "- //Middlebury College//Slide-Show//EN" "http://concerto.sourceforge.net/dtds/viewer/2.0/slideshow.dtd">
<slideshow>

END;
		print "\t<title>"._("Not Authorized")."</title>\n";
		print "\t<slide>\n";
		
		// Title
		print "\t\t<title>"._("Not Authorized")."</title>\n";
		
		// Caption
		print "\t\t<caption><![CDATA[";
		print _("You are not authorized to access this <em>Asset</em>.");
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
		$asset =& $this->getAsset();
		return _("Browsing Asset")." <em>".$asset->getDisplayName()."</em> ";
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
		
		$repository =& $this->getRepository();
		$parentAsset =& $this->getAsset();
		$parentAssetId =& $parentAsset->getId();
				
		$harmoni->request->passthrough("collection_id");
		$harmoni->request->passthrough("asset_id");	
		
		
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
		print "\t<title>".$parentAsset->getDisplayName()."</title>\n";
		
		// Print out the slide for the parent asset
		$this->printAssetXML($parentAsset);
		
		//***********************************
		// Get the child assets to display
		//***********************************
		$assets =& $parentAsset->getAssets();
		
		while ($assets->hasNext()) {
			$asset =& $assets->next();
			if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $asset->getId()))
			{
				$this->printAssetXML($asset);
			}
		}
		
		print "</slideshow>\n";		
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
		while ($fileRecords->hasNext()) {
			$fileRecord =& $fileRecords->next();
			$fileRecordId =& $fileRecord->getId();
			print "\t\t<media>\n";
			print "\t\t\t<version>\n";
			
			print "\t\t\t\t<type>";
			print slideshowxmlAction::getFirstPartValueFromRecord("MIME_TYPE", $fileRecord);
			print "</type>\n";
			
			print "\t\t\t\t<size>original</size>\n";
			
			$dimensions = slideshowxmlAction::getFirstPartValueFromRecord("DIMENSIONS", 
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
			$filename = slideshowxmlAction::getFirstPartValueFromRecord("FILE_NAME", 
				$fileRecord);
			
			$harmoni =& Harmoni::instance();
			$harmoni->request->startNamespace("polyphony-repository");
			
			print $harmoni->request->quickURL("repository", "viewfile", 
					array(
						"repository_id" => $repositoryId->getIdString(),
						"asset_id" => $assetId->getIdString(),
						"record_id" => $fileRecordId->getIdString(),
						"file_name" => $filename));
			
			
			$harmoni->request->endNamespace();
			print "]]></url>\n";
			
			print "\t\t\t</version>\n";
			print "\t\t</media>\n";
		}
		
		
		print "\t</slide>\n";
	}

}
