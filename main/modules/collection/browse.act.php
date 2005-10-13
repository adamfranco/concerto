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
class browseAction 
	extends RepositoryAction
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
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		
		$repository =& $this->getRepository();
				
		$harmoni->request->passthrough("collection_id");
		
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
		
		// function links
		ob_start();
		print _("Collection").": ";
		RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $repository);
		$layout =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$actionRows->add($layout, "100%", null, CENTER, CENTER);
		
		ob_start();
		// Limit selection form
		$currentUrl =& $harmoni->request->mkURL();
		print "\n<form action='".$currentUrl->write()."' method='post'>";
		
		print "\n\t<input type='radio' onchange='this.form.submit();'";
		print " name='".RequestContext::name("limit_by")."'";
		print " value='all'";
		if (!RequestContext::value("limit_type") || RequestContext::value("limit_by") == 'all' )
			print " checked='checked'";
		print ">"._("All")."\n<br/>";
		
		print "\n\t<input type='radio' onchange='this.form.submit();'";
		print " name='".RequestContext::name("limit_by")."'";
		print " value='type'";
		if (RequestContext::value("limit_by") == 'type') {
			print " checked='checked'";
			print ">"._("Type").": ";
			print "\n\t<select name='".RequestContext::name("type")."'";
			print " onchange='this.form.submit();'>";
				print "\n\t\t<option value=''";
				if (!RequestContext::value("type"))
					print " selected='selected'";
				print ">All Types</option>";
				$types =& $repository->getAssetTypes();
				while ($types->hasNext()) {
					$type =& $types->next();
					print "\n\t\t<option value='".Type::typeToString($type)."'";
					if (RequestContext::value("type") == Type::typeToString($type))
						print " selected='selected'";
					print ">".Type::typeToString($type)."</option>";
				}			
			print "\n\t</select>";
			print "\n<br/>";
		} else {
			print ">"._("Type")."\n<br/>";
		}
		
		print "\n\t<input type='radio' onchange='this.form.submit();'";
		print " name='".RequestContext::name("limit_by")."'";
		print " value='search'";
		if (RequestContext::value("limit_by") == 'search') {
			print " checked='checked'";
			print ">"._("Search").": ";
			print "\n\t<select name='".RequestContext::name("searchtype")."'";
			print " onchange='this.form.submit();'>";
				print "\n\t\t<option value=''";
				if (!RequestContext::value("searchtype"))
					print " selected='selected'";
				print ">All Types</option>";
				$types =& $repository->getSearchTypes();
				while ($types->hasNext()) {
					$type =& $types->next();
					print "\n\t\t<option value='".Type::typeToString($type)."'";
					if (RequestContext::value("searchtype") == Type::typeToString($type))
						print " selected='selected'";
					print ">".Type::typeToString($type)."</option>";
				}			
			print "\n\t</select>";
			print "\n\t<input type='text'";
			print " name='".RequestContext::name("searchstring")."'";
			print " value='".RequestContext::value("searchstring")."'>";
			print "\n\t<input type='submit'>";
			print "\n<br/>";
		} else {
			print ">"._("Search")."\n<br/>";
		}
		
		print "\n</form>";
		
		
		$introText =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$actionRows->add($introText, "100%", null, CENTER, CENTER);
		
		//***********************************
		// Get the assets to display
		//***********************************
		switch (RequestContext::value("limit_by")) {
			case 'type':
				if (RequestContext::value("type")) {
					$assets =& $repository->getAssetsByType(Type::stringToType(RequestContext::value("type")));
					break;
				}
				
			case 'search':
				if (RequestContext::value("searchtype") 
					&& RequestContext::value("searchstring")) 
				{
					$searchString = RequestContext::value("searchstring");
					$assets =& $repository->getAssetsBySearch(
						$searchString,
						Type::stringToType(RequestContext::value("searchtype")),
						$searchProperties = NULL);
					break;
				}
			
			default:
				if ($hasRootSearch) {
					$criteria = NULL;
					$assets =& $repository->getAssetsBySearch($criteria, $rootSearchType, $searchProperties = NULL);
				} 
				// Otherwise, just get all the assets
				else {
					$assets =& $repository->getAssets();
				}
		}
		
		//***********************************
		// print the results
		//***********************************
		$resultPrinter =& new IteratorResultPrinter($assets, 3, 6, "printAssetShort", $harmoni);
		$resultLayout =& $resultPrinter->getLayout($harmoni, "canView");
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
		
	}
}


// Callback function for printing Assets
function printAssetShort(& $asset, &$harmoni) {
	ob_start();
	
	$assetId =& $asset->getId();
	print  "\n\t<strong>".$asset->getDisplayName()."</strong> - "._("ID#").": ".
			$assetId->getIdString();
	print  "\n\t<br /><em>".$asset->getDescription()."</em>";	
	print  "\n\t<br />";
	
	AssetPrinter::printAssetFunctionLinks($harmoni, $asset);
	
	$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($assetId);
	if (!is_null($thumbnailURL)) {
		
		print "\n\t<br /><a href='";
		print $harmoni->request->quickURL("asset", "view", array('asset_id' => $assetId->getIdString()));
		print "'>";
		print "\n\t\t<img src='$thumbnailURL' alt='Thumbnail Image' border='0' />";
		print "\n\t</a>";
	}
	
	$xLayout =& new XLayout();
	$layout =& new Container($xLayout, BLOCK, 4);
	$layout2 =& new Block(ob_get_contents(), 3);
	$layout->add($layout2, null, null, CENTER, CENTER);
	//$layout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	return $layout;
}

// Callback function for checking authorizations
function canView( & $asset ) {
	$authZ =& Services::getService("AuthZ");
	$idManager =& Services::getService("Id");
	
	if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.access"), $asset->getId())
		|| $authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $asset->getId()))
	{
		return TRUE;
	} else {
		return FALSE;
	}
}