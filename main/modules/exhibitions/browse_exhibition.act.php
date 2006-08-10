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
require_once(MYDIR."/main/library/printers/ExhibitionPrinter.static.php");
require_once(MYDIR."/main/library/printers/SlideShowPrinter.static.php");
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");

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
class browse_exhibitionAction 
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
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.access"), 
					$idManager->getId(RequestContext::value('exhibition_id')));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to access this <em>Exhibition</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$asset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('exhibition_id')));
		return _("Browsing Exhibition")." <em>".$asset->getDisplayName()."</em> ";
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$asset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('exhibition_id')));

		// function links
		ob_start();
		ExhibitionPrinter::printFunctionLinks($asset);
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, CENTER, CENTER);
		
		
		/*********************************************************
		 * Description
		 *********************************************************/
		$description =& HtmlString::withValue($asset->getDescription());
		$description->clean();
		if (strlen($description->asString()))
			$actionRows->add(new Block($description->asString(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		
		//***********************************
		// Get the assets to display
		//***********************************
		$assets =& $asset->getAssets();
		
		//***********************************
		// print the results
		//***********************************
		$resultPrinter =& new IteratorResultPrinter($assets, 2, 6, "printAssetShort", $harmoni);
		$resultLayout =& $resultPrinter->getLayout($harmoni);
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
		
		ob_start();
		print  "<p>";
		print  _("Some <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
		print  "</p>";
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
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

// Callback function for printing Assets
function printAssetShort(& $asset, &$harmoni) {
	$idManager =& Services::getService("Id");
	$harmoni =& Harmoni::instance();
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
				$mediaIdStringObj =& browse_exhibitionAction::getFirstPartValueFromRecord(
						"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.target_id",
					$slideRecord);
				if (strlen($mediaIdStringObj->asString())) {
					$mediaId =& $idManager->getId($mediaIdStringObj->asString());
					$firstMediaUrl = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($mediaId);
				}
			}
		}
	}
	
	$assetId =& $asset->getId();
	ob_start();
	if (isset($firstMediaUrl)) {
		print "<a onclick='Javascript:window.open(";
		print '"'.VIEWER_URL."?&source=";
		print urlencode($harmoni->request->quickURL("exhibitions", "slideshowOutlineXml", 
					array("slideshow_id" => $assetId->getIdString())));
		print '", ';
		print '"'.$asset->getDisplayName().'", ';
		print '"toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=500"';
		print ")'>";
		print "\n<img src='".$firstMediaUrl."' style='float: right; border: 0px;'/>";
		print "</a>";
	}
	
	print "\n\t<table width='100%'>";
	print  "\n\t<tr><td style='font-weight: bold' title='"._("ID#").": ".
			$assetId->getIdString()."'>".$asset->getDisplayName()."</td>";
	print "\n\t<td style='text-align: right; font-size: smaller;''>(".$count." "._("slides").")</td>";
	print "</tr></table>";
	$description =& HtmlString::withValue($asset->getDescription());
	$description->clean();
	print  "\n\t<div style='font-size: smaller;'>".$description->asString()."</div>";
	
	print "\n<div style='clear: both;'>";
	SlideShowPrinter::printFunctionLinks($asset);
	print "</div>";
	
	$layout =& new Block(ob_get_contents(), EMPHASIZED_BLOCK);
	ob_end_clean();
	return $layout;
}