<?php
/**
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
 
 require_once(dirname(__FILE__)."/RepositoryAction.class.php");

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
class RecordStructureAction
	extends RepositoryAction
{
		
	/**
	 * Get the RecordStructureId for the id specified in the context
	 * 
	 * @return object Repository
	 * @access public
	 * @since 4/26/05
	 */
	function &getRecordStructureId () {
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("Id");
		return $idManager->getId($harmoni->pathInfoParts[3]);
	}
	
	/**
	 * Get the RecordStructure for the id specified in the context
	 * 
	 * @return object Repository
	 * @access public
	 * @since 4/26/05
	 */
	function &getRecordStructure () {
		// Get the Repository
		$repository =& $this->getRepository();
		return $repository->getRecordStructure($this->getRecordStructureId());
	}
	
}

?>