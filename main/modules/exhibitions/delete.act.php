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

/**
 * 
 * 
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class deleteAction 
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
		// Check that the user can delete this exhibition
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.delete"), 
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
		return _("You are not authorized to delete this <em>Exhibition</em> or its <em>Slide-Shows</em>.");
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
		return _("Delete Exhibition")." <em>".$asset->getDisplayName()."</em> ";
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

		$success = $this->recursiveDeleteAsset($asset);
		
		if ($success) {
			RequestContext::locationHeader(
				$harmoni->request->quickURL("exhibitions", "browse"));
		} else {
			ob_start();
			print  "<p>";
			print  _("An error occured while trying to delete this exhibition.");
			print  "</p>";
			
			$actionRows->add(new Block(ob_get_contents(), 2), "100%", null, LEFT, CENTER);
			ob_end_clean();
		}
	}
	
	/**
	 * Recursively delete an Asset
	 * 
	 * @param object Asset $asset	The Asset to delete.
	 * @return boolean
	 * @access public
	 * @since 8/4/05
	 */
	function recursiveDeleteAsset (&$asset) {
		$children =& $asset->getAssets();
		
		while ($children->hasNext()) {
			$child =& $children->next();
			print "Deleting Child: "; printpre($child->getId());
			$this->recursiveDeleteAsset($child);
		}
		
		// Make sure that this asset now has no children.
		// if it does not have children delete it.
		// Check that the user can delete this asset
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");

		$children =& $asset->getAssets();
		if (!$children->hasNext()
			&& $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.delete"), 
					$asset->getId()))
		{
			print "Deleting Asset: "; printpre($asset->getId());
			
			$repository =& $asset->getRepository();
			$repository->deleteAsset($asset->getId());
			return TRUE;
		} else {
			return FALSE;
		}
	}
}