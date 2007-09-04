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
	function getAssetId () {
		$harmoni = Harmoni::instance();
		if (!$harmoni->request->get('asset_id')) {
			$false = false;
			return $false;
		}
			
		$idManager = Services::getService("Id");
		return $idManager->getId($harmoni->request->get('asset_id'));
	}
	
	/**
	 * Get the asset for the collection id specified in the context
	 * 
	 * @return object Asset
	 * @access public
	 * @since 4/26/05
	 */
	function getAsset () {
		$assetId =$this->getAssetId();
		if (!$assetId) {
			$false = false;
			return $false;
		}
			
		// Get the Repository
		$repository =$this->getRepository();
		return $repository->getAsset($this->getAssetId());
	}
	
	/**
	 * Get the repository for the collection id specified in the context
	 * 
	 * @return object Repository
	 * @access public
	 * @since 4/26/05
	 */
	function getRepositoryId () {
		$harmoni = Harmoni::instance();
		$idManager = Services::getService("Id");
		$repositoryManager = Services::getService("Repository");
		
		$repositoryId = parent::getRepositoryId();
		if ($repositoryId) {
			return $repositoryId;
		} else {
			$assetId =$this->getAssetId();
			if (!$assetId) {
				$false = false;
				return $false;
			}
			$asset =$repositoryManager->getAsset($assetId);
			$repository =$asset->getRepository();
			return $repository->getId();
		}
	}
	
}

?>