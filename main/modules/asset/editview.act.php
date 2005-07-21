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
class editviewAction 
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
					$idManager->getId("edu.middlebury.authorization.modify"), 
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
		return _("You are not authorized to edit this <em>Asset</em>.");
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
		return _("Editing Asset")." <em>".$asset->getDisplayName()."</em> ";
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
		
		$asset =& $this->getAsset();
		$repositoryId =& $this->getRepositoryId();
		$repository =& $this->getRepository();

		// function links
		ob_start();
		AssetPrinter::printAssetFunctionLinks($harmoni, $asset);
		$layout =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$actionRows->add($layout, "100%", null, LEFT, CENTER);
		
		// Columns for Description and thumbnail.
		$xLayout =& new XLayout();
		$contentCols =& new Container($xLayout, OTHER, 1);
		$actionRows->add($contentCols, "100%", null, LEFT, CENTER);
		
			// Description and dates
			ob_start();
			$assetId =& $asset->getId();
			print  "\n\t<strong>"._("Description").":</strong> \n<em>".$asset->getDescription()."</em>";
			print  "\n\t<br /><strong>"._("ID#").":</strong> ".$assetId->getIdString();
		
			$effectDate =& $asset->getEffectiveDate();
			if(is_Object($effectDate))
				print  "\n\t<br /><strong>"._("Effective Date").":</strong> \n<em>".$effectDate->asString()."</em>";
		
			$expirationDate =& $asset->getExpirationDate();
			if(is_Object($expirationDate))
				print  "\n\t<br /><strong>"._("Expiration Date").":</strong> \n<em>".$expirationDate->asString()."</em>";
		
			$layout =& new Block(ob_get_contents(), 3);
			ob_end_clean();
			$contentCols->add($layout, "100%", null, LEFT, CENTER);
			
			// Edit Links
			ob_start();
			print "\n\t<table>";
			print "\n\t<tr><td style='border-left: 1px solid; padding-left: 10px;' valign='top'>";
			
			// Info and links
			print "\n<strong>"._("Asset Information")."</strong>";
			print "\n<br /><a href='";
			print $harmoni->request->quickURL("asset", "edit", 
				array("collection_id" => $repositoryId->getIdString(),
					"asset_id" => $assetId->getIdString()));
			print "'>"._("edit")."</a>";
			
			print "\n\t</td>\n\t</tr>";
			print "\n</table>";
			$layout =& new Block(ob_get_contents(), 3);
			ob_end_clean();
			$contentCols->add($layout, "100%", null, LEFT, CENTER);
		
		
		//***********************************
		// Info Records
		//***********************************
		ob_start();
		$printedRecordIds = array();
		
		// Get the set of RecordStructures so that we can print them in order.
		$setManager =& Services::getService("Sets");
		$structSet =& $setManager->getSet($repositoryId);
		
		// First, lets go through the info structures listed in the set and print out
		// the info records for those structures in order.
		while ($structSet->hasNext()) {
			$structureId =& $structSet->next();
			$records =& $asset->getRecordsByRecordStructure($structureId);
			while ($records->hasNext()) {
				$record =& $records->next();
				$recordId =& $record->getId();
				$printedRecordIds[] = $recordId->getIdString();
		
				print "<hr />";
				printRecord($record, $assetId, $repositoryId);
			}	
		}
		
		$layout =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$actionRows->add($layout, "100%", null, LEFT, CENTER);
		
		
		//***********************************
		// Info Record Addition
		//***********************************
		$harmoni->history->markReturnURL("concerto/asset/editview", 
			$harmoni->request->mkURLWithPassthrough());
		ob_start();
		print "\n<hr />";
		print "\n<form action='";
		print $harmoni->request->quickURL("record", "add", 
			array("collection_id" => $repositoryId->getIdString(),
				"asset_id" => $assetId->getIdString()));
		print "' method='post'>";
		print "\n<div>";
		
		print "\n<input type='submit' value='"._("Add")."' /> ";
		print "\n"._("a new Record for the ");
		
		print "\n<select name='".$harmoni->request->name('structure')."'>";
		
		$structSet->reset();
		$i=1;
		// First, lets go through the info structures listed in the set and print out
		// the info records for those structures in order.
		while ($structSet->hasNext()) {
			$structureId =& $structSet->next();
			$structure =& $repository->getRecordStructure($structureId);
			print "\n\t<option value='".$structureId->getIdString()."'>";
			print $i.". ".$structure->getDisplayName();
			print "</option>";
			$i++;
		}
		
		print "\n</select>";
		
		print " "._("Schema").".";
		
		print "\n</div>";
		print"\n</form>";
		
		$layout =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$actionRows->add($layout, "100%", null, LEFT, CENTER);
		
		
		//***********************************
		// Content
		//	If we can, we may want to print the content here.
		// 	@todo Add some sniffing of content so that we can either put it in if
		// 	it is text, image, etc, or do otherwise with it if it is some other form
		// 	of data.
		//***********************************
		$content =& $asset->getContent();
		if ($string = $content->asString()) {
			ob_start();
			
			print "\n<table width='100%'>";
			print "\n\t<tr>\n\t<td>";
			
			print ($string);
			
			print "\n\t</td>\n\t<td style='border-left: 1px solid; padding-left: 10px;' valign='top'>";
			
			// Info and links
			print "\n<strong>"._("Asset Content")."</strong>";
			print "\n<br /><a href='";
			print $harmoni->request->quickURL("asset", "edit", 
				array("collection_id" => $repositoryId->getIdString(),
					"asset_id" => $assetId->getIdString()));
			print "'>"._("edit")."</a>";
			
			print "\n\t</td>\n\t</tr>";
			print "\n</table>";
			
			$layout =& new Block(ob_get_contents(), 4);
			ob_end_clean();
			$actionRows->add($layout, "100%", null, LEFT, CENTER);
		}
	}
}


//***********************************
// Function Definitions
//***********************************

function printRecord(& $record, & $assetId, & $repositoryId) {	
	$recordStructure =& $record->getRecordStructure();
	$structureId =& $recordStructure->getId();
	$recordId =& $record->getId();
	
	// Print out the parts/partstructures for this recordstructure
	$setManager =& Services::getService("Sets");
	$partStructureSet =& $setManager->getSet($structureId);
	
	$partsStructureArray = array();
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
	
	print "\n<table width='100%'>";
	print "\n\t<tr>\n\t<td>";
	
	$moduleManager =& Services::getService("InOutModules");
	print $moduleManager->generateDisplayForPartStructures($repositoryId, $assetId, $record, $partStructureArray);
	
	print "\n\t</td>\n\t<td style='border-left: 1px solid; padding-left: 10px;' valign='top'>";
	
	// Info and links
	print "\n<strong>".$recordStructure->getDisplayName()."</strong>";
	print "\n<br /><em>".$recordStructure->getDescription()."</em>";
	$harmoni = Harmoni::instance();
	print "\n<br /><a href='";
	print $harmoni->request->quickURL("record", "edit", 
		array("collection_id" => $repositoryId->getIdString(),
			"asset_id" => $assetId->getIdString(),
			"record_id" => $recordId->getIdString()));
	print "'>"._("edit")."</a>";
	print "\n | <a href='";
	print $harmoni->request->quickURL("record", "delete", 
		array("collection_id" => $repositoryId->getIdString(),
			"asset_id" => $assetId->getIdString(),
			"record_id" => $recordId->getIdString()));
	print "'>"._("delete")."</a>";
	
	print "\n\t</td>\n\t</tr>";
	print "\n</table>";
}
