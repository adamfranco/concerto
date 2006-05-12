<?php
/**
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/AssetAction.class.php");
require_once(MYDIR."/main/modules/exhibitions/slideshowxml.act.php");
require_once(HARMONI."oki2/shared/MultiIteratorIterator.class.php");


/**
 * 
 * 
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class browse_outline_xmlAction 
	extends AssetAction
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
		/*********************************************************
		 * First print the header, then the xml content, then exit before
		 * the GUI system has a chance to try to theme the output.
		 *********************************************************/		
		header("Content-Type: text/xml; charset=\"utf-8\"");
		
		print<<<END
<?xml version="1.0" encoding="utf-8" ?>
<!DOCTYPE slideshow PUBLIC "- //Middlebury College//Slide-Show//EN" "http://concerto.sourceforge.net/dtds/viewer/2.0/slideshow.dtd">
<slideshow>

END;
		print "\t<title>"._("Not Authorized")."</title>\n";
		print "\t<slide>\n";
		
		// Title
		print "\t\t<title>"._("Not Authorized")."</title>\n";
		
		// Caption
		print "\t\t<caption><![CDATA[";
		$this->printUnauthorizedString();
		print"]]></caption>\n";
		print "\t\t<text-position>center</text-position>";
		print "\t</slide>\n";
		print "</slideshow>\n";		
		exit;
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
		$repository =& $this->getRepository();
		return _("Browse Assets in the")
			." <em>".$repository->getDisplayName()."</em> "
			._(" Collection");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {		
		$this->setPassthrough();
						
		/*********************************************************
		 * First print the header, then the xml content, then exit before
		 * the GUI system has a chance to try to theme the output.
		 *********************************************************/		
		header("Content-Type: text/xml; charset=\"utf-8\"");
		
		print<<<END
<?xml version="1.0" encoding="utf-8" ?>
<!DOCTYPE slideshow PUBLIC "- //Middlebury College//Slide-Show//EN" "http://concerto.sourceforge.net/dtds/viewer/2.0/slideshow.dtd">
<slideshow>

END;
		print "\t<title>".$this->getTitle()."</title>\n";
		
		print "\t<media-sizes>\n";
		print "\t\t\t\t<size>small</size>\n";
		print "\t\t\t\t<size>medium</size>\n";
		print "\t\t\t\t<size>large</size>\n";
		print "\t\t\t\t<size>original</size>\n";
		print "\t</media-sizes>\n";
		
		print "\t<default_size>medium</default_size>\n";
	
		
		$assets =& $this->getAssets();
		
		
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		while ($assets->hasNext()) {
			$asset =& $assets->next();
			if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $asset->getId()))
			{
				$this->printAssetXML($asset);
			}
		}
		
		print "</slideshow>\n";		
		exit;
	}
	
	/**
	 * Answer the title of this slideshow
	 * 
	 * @return string
	 * @access public
	 * @since 5/4/06
	 */
	function getTitle () {
		$repository =& $this->getRepository();
		return $repository->getDisplayName();
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
	}
	
	/**
	 * Answer the assets to display in the slideshow
	 * 
	 * @return object AssetIterator
	 * @access public
	 * @since 5/4/06
	 */
	function &getAssets () {
		$repository =& $this->getRepository();
		
		// If the Repository supports searching of root assets, just get those
		$hasRootSearch = FALSE;
		$rootSearchType =& new HarmoniType("Repository","edu.middlebury.harmoni","RootAssets", "");
		$searchTypes =& $repository->getSearchTypes();
		while ($searchTypes->hasNext()) {
			if ($rootSearchType->isEqual( $searchTypes->next() )) {
				$hasRootSearch = TRUE;
				break;
			}
		}
		
		// if we are limiting by type
		if (RequestContext::value("limit_by_type") == 'true') {
			$types =& $repository->getAssetTypes();
			$selectedTypes = array();
			while ($types->hasNext()) {
				$type =& $types->next();
				if (RequestContext::value("type___".Type::typeToString($type)) == 'true')
					$selectedTypes[] =& $type;
			}
		}
		
		//***********************************
		// Get the assets to display
		//***********************************
		$searchProperties =& new HarmoniProperties(
					Type::fromString("repository::harmoni::order"));
		if (!($order = RequestContext::value("order")))
			$order = 'DisplayName';
		$searchProperties->addProperty("order", $order);
		
		if (!($direction = RequestContext::value("direction")))
			$direction = 'ASC';
		$searchProperties->addProperty("direction", $direction);
		
		if (isset($selectedTypes) && count($selectedTypes)) {
			$searchProperties->addProperty("allowed_types", $selectedTypes);
		}
					

		if (isset($selectedSearchType)
			&& $searchModuleManager->getSearchCriteria($repository, $selectedSearchType)) 
		{				
			$criteria = $searchModuleManager->getSearchCriteria($repository, $selectedSearchType);
			
			$assets =& $repository->getAssetsBySearch(
				$criteria,
				$selectedSearchType,
				$searchProperties);
		} else if (isset($selectedTypes) && count($selectedTypes)) {
			$assets =& new MultiIteratorIterator($null = null);
			foreach (array_keys($selectedTypes) as $key) {
				$assets->addIterator($repository->getAssetsByType($selectedTypes[$key]));
			}
		} else if ($hasRootSearch) {
			$criteria = NULL;
			$assets =& $repository->getAssetsBySearch(
				$criteria, 
				$rootSearchType, 
				$searchProperties);
		} 
		// Otherwise, just get all the assets
		else {
			$assets =& $repository->getAssets();
		}
		
		return $assets;
	}
	
	
	
	/**
	 * Function for printing the asset block of the slideshow XML file
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/14/05
	 */
	function printAssetXML( &$asset) {
		
		$assetId =& $asset->getId();
		$repository =& $asset->getRepository();
		$repositoryId =& $repository->getId();
		$idManager =& Services::getService("Id");
		$harmoni =& Harmoni::instance();
		
		
		// ------------------------------------------
		
		print "\t<slide ";
		print "source='";
		print $harmoni->request->quickURL('collection', 'browse_slide_xml', 
			array('asset_id' => $assetId->getIdString()));
		print "'>\n";
		
		// Text-Position
		print "\t\t<text-position>";
			print "right";
		print "</text-position>\n";		
		
		print "\t</slide>\n";
	}

}
