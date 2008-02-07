<?php
/**
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/RepositoryAction.class.php");
require_once(MYDIR."/main/modules/exhibitions/slideshowxml.act.php");

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
class browsexmlAction 
	extends browse_slide_xmlAction
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
		return $authZ->isUserAuthorizedBelow(
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
		return _("You are not authorized to access this <em>Collection</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$repository =$this->getRepository();
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
		$actionRows =$this->getActionRows();
		$harmoni = Harmoni::instance();
		
		$repository =$this->getRepository();
				
		$harmoni->request->passthrough("collection_id");
		
		// If the Repository supports searching of root assets, just get those
		$hasRootSearch = FALSE;
		$rootSearchType = new HarmoniType("Repository","edu.middlebury.harmoni","RootAssets", "");
		$searchTypes =$repository->getSearchTypes();
		while ($searchTypes->hasNext()) {
			if ($rootSearchType->isEqual( $searchTypes->next() )) {
				$hasRootSearch = TRUE;
				break;
			}
		}		
		
		
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
		print "\t<title>".$repository->getDisplayName()."</title>\n";
	
		
		//***********************************
		// Get the assets to display
		//***********************************
		$searchProperties = new HarmoniProperties(
					HarmoniType::fromString("repository::harmoni::order"));
		if (!($order = RequestContext::value("order")))
			$order = 'DisplayName';
		$searchProperties->addProperty("order", $order);
					
		switch (RequestContext::value("limit_by")) {
			case 'type':
				if (RequestContext::value("type")) {
					$assets =$repository->getAssetsByType(HarmoniType::fromString(RequestContext::value("type")));
					break;
				}
				
			case 'search':
				$searchModuleManager = Services::getService("RepositorySearchModules");
				$selectedSearchType = HarmoniType::fromString(RequestContext::value("searchtype"));
				
				if (RequestContext::value("searchtype") 
					&& $searchModuleManager->getSearchCriteria($repository, $selectedSearchType)) 
				{
					$criteria = $searchModuleManager->getSearchCriteria($repository, $selectedSearchType);
					
					$assets =$repository->getAssetsBySearch(
						$criteria,
						$selectedSearchType,
						$searchProperties);
					break;
				}
			
			default:
				if ($hasRootSearch) {
					$criteria = NULL;
					$assets =$repository->getAssetsBySearch(
						$criteria, 
						$rootSearchType, 
						$searchProperties);
				} 
				// Otherwise, just get all the assets
				else {
					$assets =$repository->getAssets();
				}
		}
		
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		while ($assets->hasNext()) {
			$asset =$assets->next();
			if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $asset->getId()))
			{
				$this->printAssetXML($asset);
			}
		}
		
		print "</slideshow>\n";		
		exit;
	}
	
	
	
	/**
	 * Function for printing the asset block of the slideshow XML file
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/14/05
	 */
	function printAssetXML( $asset) {
		
		$assetId =$asset->getId();
		$repository =$asset->getRepository();
		$repositoryId =$repository->getId();
		$idManager = Services::getService("Id");
		
		
		// ------------------------------------------
		print "\t<slide>\n";
		
		// Title
		print "\t\t<title><![CDATA[";
		print htmlspecialchars($asset->getDisplayName(), ENT_COMPAT, 'UTF-8');
		print "]]></title>\n";
		
		// Caption
		print "\t\t<caption><![CDATA[";
		$this->printAsset($asset);
		print"]]></caption>\n";
		
		// Text-Position
		print "\t\t<text-position>";
			print "right";
		print "</text-position>\n";
		
		
		$fileRecords =$asset->getRecordsByRecordStructure(
			$idManager->getId("FILE"));
		
		/*********************************************************
		 * Files
		 *********************************************************/
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-repository");
		$imgProcessor = Services::getService("ImageProcessor");

		while ($fileRecords->hasNext())
			$this->printFileRecord($fileRecords->next(), $repositoryId, $assetId);
		
		
		print "\t</slide>\n";
	}
	
	$harmoni->request->endNamespace();

}
