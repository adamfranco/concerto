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
class browsexmlAction 
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
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.access"), 
					$this->getAssetId());
	}
	
	/**
	 * This is the class-specific string for the message.
	 * 
	 * @return void
	 * @access public
	 * @since 5/4/06
	 */
	function printUnauthorizedString () {
		print _("You are not authorized to access this <em>Collection</em>.");
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
		return _("Browsing Asset")." <em>".$asset->getDisplayName()."</em> ";
	}
	
	/**
	 * Answer the title of this slideshow
	 * 
	 * @return string
	 * @access public
	 * @since 5/4/06
	 */
	function getTitle () {
		$asset =& $this->getAsset();
		return $asset->getDisplayName();
	}
	
	/**
	 * Pass throught he needed parameters
	 * 
	 * @return void
	 * @access public
	 * @since 5/4/06
	 */
	function setPassthrough () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->passthrough("collection_id");
		$harmoni->request->passthrough("asset_id");
	}
	
	/**
	 * Answer the assets to display in the slideshow
	 * 
	 * @return object AssetIterator
	 * @access public
	 * @since 5/4/06
	 */
	function &getAssets () {
		$parentAsset =& $this->getAsset();
		
		$assets = array();
		$assets[] =& $parentAsset;
		
		$childAssets =& $parentAsset->getAssets();
		while ($childAssets->hasNext())
			$assets[] =& $childAssets->next();
		
		$iterator =& new HarmoniIterator($assets);
		return $iterator;
	}
}
