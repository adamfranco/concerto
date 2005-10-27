<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/modules/asset/AssetEditingAction.class.php");

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
class editAction 
	extends AssetEditingAction
{
	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 10/26/05
	 */
	function editAction () {
		$this->_init();
		
		$this->_recStructsToIgnore[] = 'FILE';
		
		$assetList = RequestContext::value("assets");
		$this->_cacheName = 'edit_asset_wizard_'.preg_replace("/[^a-zA-Z0-9]/", "_", $assetList);
		$this->_loadAssets(explode(",", RequestContext::value("assets")));
	}
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
// 		$authZ =& Services::getService("AuthZ");
// 		$idManager =& Services::getService("Id");
// 		return $authZ->isUserAuthorized(
// 					$idManager->getId("edu.middlebury.authorization.modify"), 
// 					$this->getAssetId());
		return true;
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
	 * Answer the asset properties Wizard step
	 * 
	 * @param object Wizard $wizard
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function &getAssetPropertiesStep () {
		/*********************************************************
	 *  :: Asset Properties ::
	 *********************************************************/
		$step =& new WizardStep();
		$step->setDisplayName(_("Basic Properties"));
		
	// Display Name
		$property =& $step->addComponent("display_name", new WTextField);
		$property->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$property->setSize(50);

		$property->setValue($this->_assets[0]->getDisplayName());
		
	// Description	
		$property =& $step->addComponent("description", new WTextArea);
		$property->setRows(3);
		$property->setColumns(50);
		
		$property->setValue($this->_assets[0]->getDescription());
				
	// Effective Date
		$property =& $step->addComponent("effective_date", new WTextField);
		$date =& $this->_assets[0]->getEffectiveDate();
		if (is_object($date))
			$property->setValue($date->yyyymmddString());
	
	// Expiration Date
		$property =& $step->addComponent("expiration_date", new WTextField);
		$date =& $this->_assets[0]->getExpirationDate();
		if (is_object($date))
			$property->setValue($date->yyyymmddString());
		
	 	
		$step->setContent($this->getAssetPropertiesContent());
		return $step;
	}
	
	/**
	 * Update the asset properties based on values from the wizard.
	 * 
	 * @param array $results
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function updateAssetProperties ( &$results, &$asset ) {
		// DisplayName
		$asset->updateDisplayName($results['display_name']);
		
		// Description
		$asset->updateDescription($results['description']);
		
		// Effective Date
		$newEffDate =& DateAndTime::fromString($results['effective_date']);
		$asset->updateEffectiveDate($newEffDate);
		
		// Expiration Date
		$newExpDate =& DateAndTime::fromString($results['expiration_date']);
		$asset->updateEffectiveDate($newExpDate);
	}
	
	/**
	 * Answer a WizardStep for the AssetContent
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 10/26/05
	 */
	function getAssetContentStep () {
		$step =& new WizardStep();
		$step->setDisplayName(_("Content")." ("._("optional").")");
		
		$property =& $step->addComponent("content", new WTextArea);
		$property->setRows(20);
		$property->setColumns(70);
		
		$content =& $this->_assets[0]->getContent();
		$property->setValue($content->asString());
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Content")."</h2>";
		print "\n"._("This is an optional place to put content for this <em>Asset</em>. <br />If you would like more structure, you can create new schemas to hold the <em>Asset's</em> data.");
		print "\n<br />[[content]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		return $step;
	}
	
	/**
	 * Update the asset content based on the values from the wizard
	 * 
	 * @param array $results
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function updateAssetContent ( &$results, &$asset ) {
		// Content 
		if ($results['checked'] == '1') {
			$content =& $asset->getContent();
			$newContent =& Blob::withValue($results['value']);
			if (is_object($content) && !$content->isEqualTo($newContent))
				$asset->updateContent($newContent);
		}
	}
	
	/**
	 * Answer a Wizard step for the Record structure passed.
	 * 
	 * @param object RecordStructure $recStruct
	 * @return object WizardStep
	 * @access public
	 * @since 10/26/05
	 */
	function &getRecordStructureStep ( &$recStruct ) {
		$step =& new WizardStep();
		$step->setDisplayName($recStruct->getDisplayName());
		
		$allRecordsComponent =& $step->addComponent("records", new WRepeatableComponentCollection());
		$allRecordsComponent->setStartingNumber(0);
		$this->addPartStructureComponents($allRecordsComponent, $recStruct);
		
		$records =& $this->getRecordsForRecordStructure($recStruct);
		$values =& $this->getValuesForRecords($records);
		
		for ($i = 0; $i < count($values); $i++) {
			$allRecordsComponent->addValueCollection($values[$i]);
		}
				
		$step->setContent("[[records]]");
		
		return $step;
	}
	
	/**
	 * Add a table of PartStructure components to the given component.
	 * 
	 * @param object WizardComponent
	 * @return void
	 * @access public
	 * @since 10/27/05
	 */
	function addPartStructureComponents ( &$parentComponent, &$recStruct ) {
	
		$parentComponent->addComponent('record_id', new WHiddenField);
		
		ob_start();
		print "\n<table class='edit_table' cellspacing='0'>";
		
		$partStructs =& $recStruct->getPartStructures();
		while ($partStructs->hasNext()) {
			$partStruct =& $partStructs->next();
			$partStructId =& $partStruct->getId();
		
		// PartStructure
			if (!$partStruct->isRepeatable()) {
				$component =& $this->getComponentForPartStruct($partStruct);
			} else {
				$component =& $this->getRepeatablePartStructComponent($partStruct);				
			}
			
			$parentComponent->addComponent(
				str_replace(".", "_", $partStructId->getIdString()),
				$component);
			
			
			print "\n\t<tr>";
			print "\n\t\t<th>";
			print "\n\t\t\t".$partStruct->getDisplayName();
	// 		print "\n"._("The Name for this <em>Asset</em>: ");
			print "\n\t\t</th>";
			print "\n\t\t<td>";
			print "\n\t\t\t[[".str_replace(".", "_", $partStructId->getIdString())."]]";
			print "\n\t\t</td>";
			print "\n\t</tr>";
		}
	
		print "\n</table>";
		print "\n[[record_id]]";
	
		$parentComponent->setContent(ob_get_contents());
		ob_end_clean();
	}
	
	/**
	 * If the part is repeatable, then we will build a list of all parts,
	 * keeping track of which ones are had by all, and which ones are had
	 * just by some.
	 * 
	 * @param object PartStructure $partStruct
	 * @return object WizardComponent
	 * @access public
	 * @since 10/26/05
	 */
	function &getRepeatablePartStructComponent ( &$partStruct ) {
		
	// Make a component for each of values
		$repeatableProperty =& new WRepeatableComponentCollection();
		$repeatableProperty->setStartingNumber(0);
				
		$property =& $repeatableProperty->addComponent('partvalue',
				$this->getComponentForPartStruct($partStruct));
// 		$property->setSize(50);
// 		$property->setReadOnly(true);
		
		ob_start();
		print "\n\t\t\t<div>";
		print "[[partvalue]]";
		print "\n\t\t\t</div>";
		$repeatableProperty->setElementLayout(ob_get_contents());
		ob_end_clean();
		
		return $repeatableProperty;
	}
	
	/**
	 * Answer an array of values for the records passed in an array
	 * 
	 * @param array $records
	 * @return array
	 * @access public
	 * @since 10/27/05
	 */
	function &getValuesForRecords ( &$records ) {
		$collections = array();
		for ($i = 0; $i < count($records); $i++) {
			$collections[$i] = array();
			$record =& $records[$i];
			$recordId =& $record->getId();
			$collections[$i]['record_id'] = $recordId->getIdString();
			
			$recordStruct =& $record->getRecordStructure();
			$partStructs =& $recordStruct->getPartStructures();
			while ($partStructs->hasNext()) {
				$partStruct =& $partStructs->next();
				$partStructId =& $partStruct->getId();
				$partStructIdString = str_replace(".", "_", $partStructId->getIdString());
				
				$partIterator =& $record->getPartsByPartStructure($partStructId);
				if ($partIterator->hasNext())
					$collections[$i][$partStructIdString] =& $this->getValuesForParts($partStruct, $partIterator);				
			}
		}
		return $collections;
	}
	
	/**
	 * Answer an array of values for the parts passed
	 * 
	 * @param array $parts
	 * @return array
	 * @access public
	 * @since 10/27/05
	 */
	function &getValuesForParts ( &$partStruct, &$parts ) {		
		if ($partStruct->isRepeatable()) {
			$collections = array();
			$i =0;
			while ($parts->hasNext()) {
				$part =& $parts->next();
				$collections[$i] = array();
				$collections[$i]['partvalue'] =& $part->getValue();
				$i++;
			}
			return $collections;
		} else {
			$part =& $parts->next();
			return $part->getValue();
		}
	}
}