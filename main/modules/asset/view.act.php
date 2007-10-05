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
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");
require_once(POLYPHONY."/main/modules/tags/TagAction.abstract.php");

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
class viewAction 
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
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		try {
			return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view"), 
					$this->getAssetId());
		} catch (UnknownIdException $e) {
			try {
				$this->getAsset();
			} catch (PermissionDeniedException $e) {
				return false;
			}
			
			return true;
		}
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to view this <em>Asset</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$asset =$this->getAsset();
		return _("Viewing Asset")." <em>".$asset->getDisplayName()."</em> ";
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows =$this->getActionRows();
		$harmoni = Harmoni::instance();
		
		$asset =$this->getAsset();
		$repositoryId =$this->getRepositoryId();

		// function links
		ob_start();
		AssetPrinter::printAssetFunctionLinks($harmoni, $asset);
		$layout = new Block(ob_get_contents(), STANDARD_BLOCK);
		ob_end_clean();
		$actionRows->add($layout, "100%", null, LEFT, CENTER);
		
		// Columns for Description and thumbnail.
		$xLayout = new XLayout();
		$contentCols = new Container($xLayout, OTHER, 1);
		$actionRows->add($contentCols, "100%", null, LEFT, CENTER);
		
		// Description and dates
		ob_start();
		$assetId =$asset->getId();			
			
		print "\n\t<dl>";
		
		if ($asset->getDescription()) {
			$description = HtmlString::withValue($asset->getDescription());
			$description->clean();
			print "\n\t\t<dt style='font-weight: bold;'>"._("Description:")."</dt>";
			print "\n\t\t<dd>".$description->asString()."</dd>";
		}
		
		print  "\n\t\t<dt style='font-weight: bold;'>";
		print _("ID#");
		print ":</dt>\n\t\t<dd >";
		print $assetId->getIdString();
		print "</dd>";
		
		print  "\n\t\t<dt style='font-weight: bold;'>";
		print _("Type");
		print ":</dt>\n\t\t<dd >";
		try {
			print Type::typeToString($asset->getAssetType());
		} catch (UnimplementedException $e) {
			print "unknown";
		}
		print "</dd>";
		
		try {
			$date = $asset->getModificationDate();
			print  "\n\t\t<dt style='font-weight: bold;'>";
			print _("Modification Date");
			print ":</dt>\n\t\t<dd >";
			print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
			print "</dd>";
		} catch (UnimplementedException $e) {
			
		}
		
		try {
			$date = $asset->getCreationDate();
			print  "\n\t\t<dt style='font-weight: bold;'>";
			print _("Creation Date");
			print ":</dt>\n\t\t<dd >";
			print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
			print "</dd>";
		} catch (UnimplementedException $e) {
			
		}
	
		try {
			if(is_object($asset->getEffectiveDate())) {
				$date = $asset->getEffectiveDate();
				print  "\n\t\t<dt style='font-weight: bold;'>";
				print _("Effective Date");
				print ":</dt>\n\t\t<dd >";
				print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
				print "</dd>";
			}
		} catch (UnimplementedException $e) {
			
		}
		
		try {
			if(is_object($asset->getExpirationDate())) {
				$date = $asset->getExpirationDate();
				print  "\n\t\t<dt style='font-weight: bold;'>";
				print _("Expiration Date");
				print ":</dt>\n\t\t<dd >";
				print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
				print "</dd>";
			}
			print "\n\t</dl>";
		} catch (UnimplementedException $e) {
			
		}
		
		$contentCols->add(new Block(ob_get_clean(), STANDARD_BLOCK), "60%", null, LEFT, TOP);
		
		
		// Add the tagging manager script to the header
		$outputHandler =$harmoni->getOutputHandler();
		$outputHandler->setHead($outputHandler->getHead()
			."\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."javascript/Tagger.js'></script>"
			."\n\t\t<link rel='stylesheet' type='text/css' href='".POLYPHONY_PATH."javascript/Tagger.css' />");
		
		// Tags
		ob_start();
		print "\n\t<div style='font-weight: bold; margin-bottom: 10px;'>"._("Tags given to this Asset: ")."</div>";
		print "\n\t<div style=' text-align: justify;'>";
		print TagAction::getTagCloudForItem(TaggedItem::forId($assetId, 'concerto'), 'view');
		print "\n\t</div>";
		$contentCols->add(new Block(ob_get_clean(), STANDARD_BLOCK), "40%", null, LEFT, TOP);
		
		
		//***********************************
		// Info Records
		//***********************************
		ob_start();
		$printedRecordIds = array();
		
		// Get the set of RecordStructures so that we can print them in order.
		$setManager = Services::getService("Sets");
		$structSet =$setManager->getPersistentSet($repositoryId);
		if ($structSet->hasNext()) {
			// First, lets go through the info structures listed in the set and print out
			// the info records for those structures in order.
			while ($structSet->hasNext()) {
				$structureId =$structSet->next();
				$records =$asset->getRecordsByRecordStructure($structureId);
				while ($records->hasNext()) {
					$record =$records->next();
					$recordId =$record->getId();
					$printedRecordIds[] = $recordId->getIdString();
			
					print "<hr />";
					printRecord($repositoryId, $assetId, $record);
				}	
			}
		} 
		// if none are specified, print all.
		else {
			$structures = $asset->getRecordStructures();
			while($structures->hasNext()) {
				$structure = $structures->next();
				$structureId = $structure->getId();
				$records = $asset->getRecordsByRecordStructure($structureId);
				while ($records->hasNext()) {
					$record =$records->next();
					$recordId =$record->getId();
					$printedRecordIds[] = $recordId->getIdString();
			
					print "<hr />";
					printRecord($repositoryId, $assetId, $record);
				}
			}
		}
		
		$layout = new Block(ob_get_contents(), STANDARD_BLOCK);
		ob_end_clean();
		$actionRows->add($layout, "100%", null, LEFT, CENTER);
		
		
		//***********************************
		// Content
		//	If we can, we may want to print the content here.
		// 	@todo Add some sniffing of content so that we can either put it in if
		// 	it is text, image, etc, or do otherwise with it if it is some other form
		// 	of data.
		//***********************************
		try {
			$content =$asset->getContent();
			if ($string = $content->asString()) {
				ob_start();
				print "\n<textarea readonly='readonly' rows='30' cols='80'>";
				print htmlspecialchars($string);
				print "</textarea>";
				$layout = new Block(ob_get_contents(), STANDARD_BLOCK);
				ob_end_clean();
				$actionRows->add($layout, "100%", null, LEFT, CENTER);
			}
		} catch (UnimplementedException $e) {
			
		}
	}
}


//***********************************
// Function Definitions
//***********************************

function printRecord($repositoryId, $assetId, $record) {	
	$recordStructure =$record->getRecordStructure();
	$structureId =$recordStructure->getId();
	
	// Print out the fields parts for this structure
	$setManager = Services::getService("Sets");
	$partStructureSet =$setManager->getPersistentSet($structureId);
	
	$partStructureArray = array();
	// Print out the ordered parts/fields
	$partStructureSet->reset();
	while ($partStructureSet->hasNext()) {
		$partStructureId =$partStructureSet->next();
		$partStructureArray[] = $recordStructure->getPartStructure($partStructureId);
	}
	// Get the rest of the parts (the unordered ones);
	$partStructureIterator =$recordStructure->getPartStructures();
	while ($partStructureIterator->hasNext()) {
		$partStructure =$partStructureIterator->next();
		if (!$partStructureSet->isInSet($partStructure->getId()))
			$partStructureArray[] =$partStructure;
	}
	
	$moduleManager = Services::getService("InOutModules");
	print $moduleManager->generateDisplayForPartStructures($repositoryId, $assetId, $record, $partStructureArray);
}
