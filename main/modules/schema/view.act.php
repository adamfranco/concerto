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
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
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
		return _("You are not authorized to view this <em>Collection</em>.");
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
		$centerPane =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		$recordStructureId =& $this->getRecordStructureId();
		$recordStructure =& $this->getRecordStructure();
		$setManager =& Services::getService("Sets");
		$set =& $setManager->getPersistentSet($recordStructureId);

		ob_start();
		
		// Prepare the return URL so that we can get back to where we were.
		print "<a href='";
		$repositoryId =& $this->getRepositoryId();
		print $harmoni->history->getReturnURL(
			"concerto/collection/edit/".$repositoryId->getIdString());
		print "'><-- "._("Return")."</a>";
		
		
		print "<h3>".$recordStructure->getDisplayName()."</h3>";
		
		print "<em>".$recordStructure->getDescription()."</em>";
		print "<br /><strong>"._("Format").":</strong> ".$recordStructure->getFormat()."";
		
		print "<br/><a href='";
		print $harmoni->request->quickURL(
			"schema", "edit", array(
				"collection_id" => $repositoryId->getIdString(),
				"recordstructure_id" => $recordStructureId->getIdString()));
		print "'>"._("Modify")."</a>";
		
		// Print out the PartStructures
		print "<h4>"._("Elements").":</h4>";
		print "\n<table border='1'>";
		print "\n<th>"._("Order")."</th>";
		print "\n<th>"._("DisplayName")."</th>";
		print "\n<th>"._("Description")."</th>";
		print "\n<th>"._("IsMandatory?")."</th>";
		print "\n<th>"._("IsRepeatable?")."</th>";
		print "\n<th>"._("IsPopulatedByRepository?")."</th>";
		print "\n</tr>";
		$partStructures =& $recordStructure->getPartStructures();
		$partStructureArray = array();
		while ($partStructures->hasNext()) {
			$partStructure =& $partStructures->next();
			if ($set->isInSet($partStructure->getId()))
				$partStructureArray[$set->getPosition($partStructure->getId())] =& $partStructure;
			else
				$partStructureArray[] =& $partStructure;
		}
		
		ksort($partStructureArray);
		foreach (array_keys($partStructureArray) as $key) {
			$partStructure =& $partStructureArray[$key];
			print "\n<tr>";
			print "\n<td>".($key+1)."</td>";
			print "\n<td><strong>".$partStructure->getDisplayName()."</strong></td>";
			print "\n<td><em>".$partStructure->getDescription()."</em></td>";
			print "\n<td>".(($partStructure->isMandatory())?"TRUE":"FALSE")."</td>";
			print "\n<td>".(($partStructure->isRepeatable())?"TRUE":"FALSE")."</td>";
			print "\n<td>".(($partStructure->isPopulatedByRepository())?"TRUE":"FALSE")."</td>";
			print "\n</tr>";
		}
		print "\n</table>";
		
		$centerPane->add(new Block(ob_get_contents(), 3), "100%", null, LEFT, CENTER);
		ob_end_clean();
	}
}