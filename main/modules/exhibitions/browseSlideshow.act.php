<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/modules/asset/browseAsset.act.php");
require_once(MYDIR."/main/library/printers/SlideShowPrinter.static.php");

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
class browseSlideshowAction 
	extends browseAssetAction
{
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to access this <em>Slideshow</em>.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		
		$this->registerDisplayProperties();
		$this->registerState();
		
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		
		$harmoni->request->passthrough("collection_id");
		$harmoni->request->passthrough("asset_id");
		
		$asset =& $this->getAsset();
		$assetId =& $asset->getId();

		// function links
		ob_start();
		SlideShowPrinter::printFunctionLinks($asset);
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, CENTER, CENTER);
		
		ob_start();
		$description =& HtmlString::withValue($asset->getDescription());
		$description->clean();
		print  "\n\t<div style='font-size: smaller;'>".$description->asString()."</div>";

		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		
		
		$searchBar =& new Container(new YLayout(), BLOCK, STANDARD_BLOCK);
		$actionRows->add($searchBar, "100%", null, CENTER, CENTER);
		
		//***********************************
		// Get the assets to display
		//***********************************
		$setManager =& Services::getService("Sets");
		$slideshowSet =& $setManager->getPersistentSet($asset->getId());
		$slideIterator =& $asset->getAssets();
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
		$slides = array_merge($orderedSlides, $unorderedSlides);
		unset($orderedSlides, $unorderedSlides);
		
		
		//***********************************
		// print the results
		//***********************************
		$resultPrinter =& new ArrayResultPrinter($slides, 
									$_SESSION["asset_columns"], 
									$_SESSION["assets_per_page"], 
									"printSlideShort", $this->getParams(), $assetId->getIdString());
		$resultPrinter->setStartingNumber($this->_state['startingNumber']);
		
		$resultLayout =& $resultPrinter->getLayout($harmoni, "canView");
		$resultLayout->setPreHTML("<form id='AssetMultiEditForm' name='AssetMultiEditForm' action='' method='post'>");
		$resultLayout->setPostHTML("</form>");
		
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
		
		
		
		/*********************************************************
		 * Display options
		 *********************************************************/
		$currentUrl =& $harmoni->request->mkURL();	
		$searchBar->setPreHTML(
			"\n<form action='".$currentUrl->write()."' method='post'>
	<input type='hidden' name='".RequestContext::name('form_submitted')."' value='true'/>");
		$searchBar->setPostHTML("\n</form");
		
		ob_start();
		$searchBar->add(new UnstyledBlock(ob_get_clean()), null, null, LEFT, TOP);
		
		$searchBar->add($this->getDisplayOptions($resultPrinter, false), null, null, LEFT, TOP);
	}
}

// Callback function for printing Slides
function printSlideShort(&$asset, $params, $slideshowIdString, $num) {
	$harmoni =& Harmoni::instance();
	$container =& new Container(new YLayout, BLOCK, EMPHASIZED_BLOCK);
	$fillContainerSC =& new StyleCollection("*.fillcontainer", "fillcontainer", "Fill Container", "Elements with this style will fill their container.");
	$fillContainerSC->addSP(new MinHeightSP("88%"));
	$container->addStyle($fillContainerSC);
	
	$centered =& new StyleCollection("*.centered", "centered", "Centered", "Centered Text");
	$centered->addSP(new TextAlignSP("center"));	
		
	$idManager =& Services::getService("Id");
	$repositoryManager =& Services::getService("Repository");
	$authZ =& Services::getService("AuthZ");
	
	// Get our record and its data
	$slideRecords =& $asset->getRecordsByRecordStructure(
		$idManager->getId("Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure"));
	
	if ($slideRecords->hasNext()) {
		$slideRecord =& $slideRecords->next();
		
		// Text-Position
		$textPosition =& getFirstPartValueFromRecord(
			"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.text_position",
			$slideRecord);
		
		// Display Metadata
		$displayMetadata =& getFirstPartValueFromRecord(
			"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.display_metadata",
			$slideRecord);
		
		// Media
		$mediaIdStringObj =& getFirstPartValueFromRecord(
			"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.target_id",
			$slideRecord);
		if (strlen($mediaIdStringObj->asString()))
			$mediaId =& $idManager->getId($mediaIdStringObj->asString());
		else
			$mediaId = null;
	}
	
	// ------------------------------------------
	ob_start();
	print "\n\t<a style='cursor: pointer;'";
	print " onclick='Javascript:window.open(";
	print '"'.VIEWER_URL."?&amp;source=";
	print urlencode($harmoni->request->quickURL('exhibitions', "slideshowOutlineXml", 
		array('slideshow_id' => $slideshowIdString)));
	print '&amp;start='.($num - 1).'", ';
	print '"_blank", ';
	print '"toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=500"';
	print ")'>";
	$viewerATag = ob_get_clean();	
	/*********************************************************
	 * Media
	 *********************************************************/	
	if (isset($mediaId)
		&& $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"),
			$mediaId)
		&& $_SESSION["show_thumbnail"] == 'true')		
	{		
		$mediaAsset =& $repositoryManager->getAsset($mediaId);
		$mediaAssetId =& $mediaAsset->getId();
		$mediaAssetRepository =& $mediaAsset->getRepository();
		$mediaAssetRepositoryId =& $mediaAssetRepository->getId();
				
		$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($mediaAsset);
		if ($thumbnailURL !== FALSE) {
			
			$thumbSize = $_SESSION["thumbnail_size"]."px";
	
			ob_start();
			print "\n<div style='height: $thumbSize; width: $thumbSize; margin: auto;'>";
			print $viewerATag;
			print "\n\t\t<img src='$thumbnailURL' alt='Thumbnail Image' border='0' style='max-height: $thumbSize; max-width: $thumbSize;' />";
			print "\n\t</a>";
			print "\n</div>";
			$component =& new UnstyledBlock(ob_get_clean());
			$component->addStyle($centered);
			$container->add($component, "100%", null, CENTER, CENTER);
		}
		
		// other files
		$fileRecords =& $mediaAsset->getRecordsByRecordStructure(
			$idManager->getId("FILE"));
	}
	
	// Link to viewer
		$numFiles = 0;
		if (isset($fileRecords)) {
			while ($fileRecords->hasNext()) {
				$record =& $fileRecords->next();
				$numFiles++;
			}
		}
		ob_start();
		print "\n<div height='15px; font-size: small;'>";
		print $viewerATag;
		print _("Open in Viewer");
		if ($numFiles > 1)
			print " (".($numFiles-1)." "._("more files").")";
		print "\n\t</a>";
		print "\n</div>";
		$component =& new UnstyledBlock(ob_get_clean());
		$component->addStyle($centered);
		$container->add($component, "100%", null, CENTER, CENTER);
	
	// Title
	ob_start();
	if ($_SESSION["show_displayName"] == 'true')
		print "\n\t<div style='font-weight: bold; height: 50px; overflow: auto;'>".htmlspecialchars($asset->getDisplayName())."</div>";
	
	// Caption
	if ($_SESSION["show_description"] == 'true') {
		$description =& HtmlString::withValue($asset->getDescription());
		$description->clean();
		if (isset($thumbnailURL))
			print  "\n\t<div style='font-size: smaller; height: 100px; overflow: auto;'>";
		else
			print  "\n\t<div style='font-size: smaller; height: ".($_SESSION["thumbnail_size"] + 100)."px; overflow: auto;'>";
		print $description->asString();
		if (isset($displayMetadata) && $displayMetadata->isTrue()
			&& isset($mediaId)
			&& $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$mediaId)) 
		{
			print "\t\t\t<hr/>\n";
			$mediaAsset =& $repositoryManager->getAsset($mediaId);
			printTargetAsset($mediaAsset);
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
		print "</div>";	
	}
	
	$container->add(new UnstyledBlock(ob_get_clean()), "100%", null, LEFT, TOP);
	
	
	// Controls
	ob_start();
	
	$container->add(new UnstyledBlock(ob_get_clean()), "100%", null, RIGHT, BOTTOM);
		
	return $container;
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
function printTargetAsset ( &$asset ) {
	/*********************************************************
	 * Asset Info
	 *********************************************************/
	$assetId =& $asset->getId();
	print "\n\t<dl>";		
	if ($asset->getDisplayName()) {
		print "\n\t\t<dt style='font-weight: bold;'>"._("Title:")."</dt>";
		print "\n\t\t<dd>".$asset->getDisplayName()."</dd>";
	}
	
	if ($asset->getDescription()) {
		$description =& HtmlString::withValue($asset->getDescription());
		$description->clean();
		print "\n\t\t<dt style='font-weight: bold;'>"._("Description:")."</dt>";
		print "\n\t\t<dd>".$description->asString()."</dd>";
	}
	
	print  "\n\t\t<dt style='font-weight: bold;'>";
	print _("ID#");
	print ":</dt>\n\t\t<dd >";
	print $assetId->getIdString();
	print "</dd>";
	
	$date = $asset->getModificationDate();
	print  "\n\t\t<dt style='font-weight: bold;'>";
	print _("Modification Date");
	print ":</dt>\n\t\t<dd >";
	print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
	print "</dd>";
	
	$date = $asset->getCreationDate();
	print  "\n\t\t<dt style='font-weight: bold;'>";
	print _("Creation Date");
	print ":</dt>\n\t\t<dd >";
	print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
	print "</dd>";

	if(is_object($asset->getEffectiveDate())) {
		$date = $asset->getEffectiveDate();
		print  "\n\t\t<dt style='font-weight: bold;'>";
		print _("Effective Date");
		print ":</dt>\n\t\t<dd >";
		print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
		print "</dd>";
	}
	
	if(is_object($asset->getExpirationDate())) {
		$date = $asset->getExpirationDate();
		print  "\n\t\t<dt style='font-weight: bold;'>";
		print _("Expiration Date");
		print ":</dt>\n\t\t<dd >";
		print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
		print "</dd>";
	}
	print "\n\t</dl>";
	
	
	/*********************************************************
	 * Expanding to child assets
	 *********************************************************/
	$children =& $asset->getAssets();
	if ($children->hasNext()) {
		printChildViewerLink($asset);
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
				printRecord($repository->getId(), $assetId, $record);
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

/**
 * Print a link/button to open a viewer that will display the children
 * 
 * @param object Asset $asset
 * @return void
 * @access public
 * @since 5/4/06
 */
function printChildViewerLink ( &$asset ) {
	$harmoni =& Harmoni::instance();
	
	$assetId =& $asset->getId();
	$repository =& $asset->getRepository();
	$repositoryId =& $repository->getId();
	
	print "\t<br />\n";
	print "\t<input type='button'";
	print " value='"._("View child-Assets")."'";
	print " onclick='";
	print "Javascript:window.open(";
	print '"'.VIEWER_URL."?&amp;source=";
	
	$params = array("collection_id" => $repositoryId->getIdString(),
				"asset_id" => $assetId->getIdString());
	
	print urlencode($harmoni->request->quickURL('asset', 'browsexml', $params));
	print '&amp;start=1", ';
// 		print '"'.preg_replace("/[^a-z0-9]/i", '_', $assetId->getIdString()).'", ';
	print '"_blank", ';
	print '"toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=500"';
	print ")";
	print "' />\n";
}