<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/modules/collection/browse_outline_xml.act.php");

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
class viewAssetsXmlAction 
	extends browse_outline_xmlAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Viewing Assets");
	}
	
	/**
	 * Answer the title of this slideshow
	 * 
	 * @return string
	 * @access public
	 * @since 5/4/06
	 */
	function getTitle () {
		return _("Viewing Assets");
	}
	
	/**
	 * Pass throught he needed parameters
	 * 
	 * @return void
	 * @access public
	 * @since 5/4/06
	 */
	function setPassthrough () {
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough("assets");
	}
	
	/**
	 * Answer the assets to display in the slideshow
	 * 
	 * @return object AssetIterator
	 * @access public
	 * @since 5/4/06
	 */
	function getAssets () {
		if (!isset($this->_assets)) {
			$idManager = Services::getService("Id");
			$authZ = Services::getService("AuthZ");
			$repositoryManager = Services::getService("Repository");
			
			$assetList = explode(",", RequestContext::value("assets"));
			foreach ($assetList as $stringId) {
				$assetId =$idManager->getId($stringId);
				
				// Check that the user can access this asset			
				if ($authZ->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.view"), 
						$assetId))
				{
					$this->_assets[] =$repositoryManager->getAsset($assetId);
				}
			}
		}
		
		$iterator = new HarmoniIterator($this->_assets);
		return $iterator;
	}
}
