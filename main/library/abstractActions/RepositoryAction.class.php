<?php
/**
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
 
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * The RepositoryAction provides common methods for accessing repositories by the
 * Id passed in in the third pathInfoPart
 * 
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class RepositoryAction
	extends MainWindowAction
{
		
	/**
	 * Get the repository for the collection id specified in the context
	 * 
	 * @return object Repository
	 * @access public
	 * @since 4/26/05
	 */
	function &getRepositoryId () {
		$harmoni =& Harmoni::instance();
		if (!$harmoni->request->get('collection_id')) {
			$false = false;
			return $false;
		}
		
		$idManager =& Services::getService("Id");
		return $idManager->getId($harmoni->request->get('collection_id'));
	}
	
	/**
	 * Get the repository for the collection id specified in the context
	 * 
	 * @return object Repository
	 * @access public
	 * @since 4/26/05
	 */
	function &getRepository () {
		$repositoryId =& $this->getRepositoryId();
		if (!$repositoryId) {
			$false = false;
			return $false;
		}
		
		// Get the Repository
		$repositoryManager =& Services::getService("Repository");
		return $repositoryManager->getRepository($repositoryId);
	}
	
}

?>