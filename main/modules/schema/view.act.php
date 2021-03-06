<?php
/**
 * @package concerto.modules.schema
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/RecordStructureAction.class.php");

/**
 * 
 * 
 * @package concerto.modules.schema
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class viewAction 
	extends RecordStructureAction
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
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view"), 
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
		return _("You are not authorized to view this <em>Schema</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return '';
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$centerPane =$this->getActionRows();
		$harmoni = Harmoni::instance();
		$recordStructureId =$this->getRecordStructureId();
		$recordStructure =$this->getRecordStructure();
		$setManager = Services::getService("Sets");
		$set =$setManager->getPersistentSet($recordStructureId);

		ob_start();
		
		// Prepare the return URL so that we can get back to where we were.
		print "\n<a href='";
		$repositoryId =$this->getRepositoryId();
		print $harmoni->history->getReturnURL(
			"concerto/collection/edit/".$repositoryId->getIdString());
		print "'><-- "._("Return")."</a>";
		
		$harmoni->history->markReturnURL(
				"concerto/schema/edit-return/".$recordStructureId->getIdString());
		
		$authZManager = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		// Schema Edit
		if (method_exists($recordStructure, 'createPartStructure')
			&& 	(preg_match("/^Repository::.+$/i", $recordStructureId->getIdString())
					&& ($authZManager->isUserAuthorized(
							$idManager->getId("edu.middlebury.authorization.modify_rec_struct"), 
							$repositoryId)
						|| $authZManager->isUserAuthorized(
							$idManager->getId("edu.middlebury.authorization.modify_authority_list"), 
							$repositoryId)))
				|| ($authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.modify_rec_struct"), 
						$idManager->getId("edu.middlebury.authorization.root"))
					|| $authZManager->isUserAuthorized(
							$idManager->getId("edu.middlebury.authorization.modify_authority_list"), 
							$idManager->getId("edu.middlebury.authorization.root"))))
		{
			print "\n | <a href='";
			print $harmoni->request->quickURL(
				"schema", "edit", array(
					"collection_id" => $repositoryId->getIdString(),
					"recordstructure_id" => $recordStructureId->getIdString()));
			print "'>"._("Edit")."</a>";
		}		
		
		print "\n<h3>".$recordStructure->getDisplayName()."</h3>";
		
		print "\n<em>".$recordStructure->getDescription()."</em>";
		print "\n<br /><strong>"._("Format").":</strong> ".$recordStructure->getFormat()."";
		
		// Print out the PartStructures
		print "\n<h4>"._("Fields").":</h4>";
		print "\n<table border='1'>";
		print "\n<th style='text-align: center'>"._("Order")."</th>";
		print "\n<th style='text-align: center'>"._("DisplayName")."</th>";
		print "\n<th style='text-align: center'>"._("Description")."</th>";
		print "\n<th style='text-align: center'>"._("Mandatory?")."</th>";
		print "\n<th style='text-align: center'>"._("Repeatable?")."</th>";
// 		print "\n<th style='text-align: center'>"._("IsPopulatedByRepository?")."</th>";
		print "\n<th style='text-align: center'>"._("Authoritative Values")."</th>";
		print "\n</tr>";
		$partStructures =$recordStructure->getPartStructures();
		$partStructureArray = array();
		while ($partStructures->hasNext()) {
			$partStructure =$partStructures->next();
			if ($set->isInSet($partStructure->getId()))
				$partStructureArray[$set->getPosition($partStructure->getId())] =$partStructure;
			else
				$partStructureArray[] =$partStructure;
		}
		
		ksort($partStructureArray);
		foreach (array_keys($partStructureArray) as $key) {
			$partStructure =$partStructureArray[$key];
			print "\n<tr>";
			print "\n<td style='vertical-align: top;'>".($key+1)."</td>";
			print "\n<td style='vertical-align: top;'><strong>".$partStructure->getDisplayName()."</strong></td>";
			print "\n<td style='vertical-align: top;'><em>".$partStructure->getDescription()."</em></td>";
			print "\n<td style='vertical-align: top; text-align: center'>".(($partStructure->isMandatory())?"<strong>"._("Yes")."</strong>":_("No"))."</td>";
			print "\n<td style='vertical-align: top; text-align: center'>".(($partStructure->isRepeatable())?"<strong>"._("Yes")."</strong>":_("No"))."</td>";
// 			print "\n<td style='text-align: center'>".(($partStructure->isPopulatedByRepository())?"<strong>"._("Yes")."</strong>":_("No"))."</td>";
			print "\n<td style='vertical-align: top; text-align: left;'>";
			$authoritativeValues =$partStructure->getAuthoritativeValues();
			if ($authoritativeValues->hasNext()) {
				print "\n\t<ul style='margin: 0px; padding-left: 20px; max-height: 200px; overflow: auto;";
				if ($authoritativeValues->count() > 12)
					print " height: 200px;";	// IE Doesn't understand max-height
				print "'>";
				while ($authoritativeValues->hasNext()) {
					$value =$authoritativeValues->next();
					print "\n\t\t<li>".$value->asString()."</li>";
				}
				print "\n\t</ul>";
			}
			print "\n</td>";
			print "\n</tr>";
		}
		print "\n</table>";
		
		$centerPane->add(new Block(ob_get_contents(), 3), "100%", null, LEFT, CENTER);
		ob_end_clean();
	}
}