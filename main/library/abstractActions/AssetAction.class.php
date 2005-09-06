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
class AssetAction
	extends RepositoryAction
{
		
	/**
	 * Get the asset Id for the collection id specified in the context
	 * 
	 * @return object Id
	 * @access public
	 * @since 4/26/05
	 */
	function &getAssetId () {
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("Id");
		return $idManager->getId($harmoni->request->get('asset_id'));
	}
	
	/**
	 * Get the asset for the collection id specified in the context
	 * 
	 * @return object Asset
	 * @access public
	 * @since 4/26/05
	 */
	function &getAsset () {
		// Get the Repository
		$repository =& $this->getRepository();
		return $repository->getAsset($this->getAssetId());
	}
	
	/**
	 * Get the repository for the collection id specified in the context
	 * 
	 * @return object Repository
	 * @access public
	 * @since 4/26/05
	 */
	function &getRepositoryId () {
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");

		if (!is_null($harmoni->request->get('collection_id'))) {
			return parent::getRepositoryId();
		} else {
			$asset =& $repositoryManager->getAsset($this->getAssetId());
			$repository =& $asset->getRepository();
			return $repository->getId();
		}
	}
	
}

?>