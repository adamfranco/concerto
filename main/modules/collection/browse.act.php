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
require_once(HARMONI."GUIManager/StyleProperties/TextAlignSP.class.php");

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
		
		
		$searchBar =& new Container(new XLayout(), BLOCK, 2);
		$actionRows->add($searchBar, "100%", null, CENTER, CENTER);
		
		
		// Limit selection form
		$currentUrl =& $harmoni->request->mkURL();	
		$searchBar->setPreHTML(
			"\n<form action='".$currentUrl->write()."' method='post'>");
		$searchBar->setPostHTML("\n</form>");
		
		ob_start();	
		print "\n\t<input type='radio' onchange='this.form.submit();'";
		print " name='".RequestContext::name("limit_by")."'";
		print " value='all'";
		if (!RequestContext::value("limit_type") || RequestContext::value("limit_by") == 'all' )
			print " checked='checked'";
		print "/>"._("All")."\n<br/>";
		
		print "\n\t<input type='radio' onchange='this.form.submit();'";
		print " name='".RequestContext::name("limit_by")."'";
		print " value='type'";
		if (RequestContext::value("limit_by") == 'type') {
			print " checked='checked'";
			print "/>"._("Type").": ";
			print "\n\t<select name='".RequestContext::name("type")."'";
			print " onchange='this.form.submit();'>";
				print "\n\t\t<option value=''";
				if (!RequestContext::value("type"))
					print " selected='selected'";
				print ">"._("All Types")."</option>";
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
			print "/>"._("Type")."\n<br/>";
		}
		
		print "\n\t<input type='radio' onchange='this.form.submit();'";
		print " name='".RequestContext::name("limit_by")."'";
		print " value='search'";
		if (RequestContext::value("limit_by") == 'search') {
			print " checked='checked'";
			print "/>"._("Search").": ";
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
			print " value='".RequestContext::value("searchstring")."'/>";
			print "\n\t<input type='submit'>";
			print "\n<br/>";
		} else {
			print "/>"._("Search")."\n<br/>";
		}		
		
		$searchForm =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$searchBar->add($searchForm, "70%", null, LEFT, TOP);
		
		
		// view options
		ob_start();
		print "\n<div style='text-align: right'>";
		print "\n\t\t"._("Assets Per Page").": ";
		
		if (isset($_SESSION["assetsPerPage"]))
			$defaultNumPerPage = $_SESSION["assetsPerPage"];
		else
			$defaultNumPerPage = 6;
		
		print "\n\t<select name='".RequestContext::name("num_per_page")."'";
		print " onchange='this.form.submit();'>";			
		for ($i = 1; $i < 20; $i++)
			$this->printSelectOption("num_per_page", $defaultNumPerPage, $i);
		for ($i = 20; $i < 100; $i=$i+10)
			$this->printSelectOption("num_per_page", $defaultNumPerPage, $i);
		for ($i = 100; $i <= 1000; $i=$i+100)
			$this->printSelectOption("num_per_page", $defaultNumPerPage, $i);
		print "\n\t</select>";
		
		print "\n\t\t<br/>"._("Columns").": ";
		
		if (isset($_SESSION["assetColumns"]))
			$defaultCols = $_SESSION["assetColumns"];
		else
			$defaultCols = 3;
		
		print "\n\t<select name='".RequestContext::name("columns")."'";
		print " onchange='this.form.submit();'>";
		for ($i = 1; $i < 20; $i++)
			$this->printSelectOption("columns", $defaultCols, $i);
		print "\n\t</select>";
		print "</div>";
		
		$searchForm =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$searchBar->add($searchForm, "30%", null, RIGHT, TOP);
		
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
		if (RequestContext::value("num_per_page")) {
			$numPerPage = RequestContext::value("num_per_page");
			$_SESSION["assetsPerPage"] = $numPerPage;
		} else if (isset($_SESSION["assetsPerPage"]))
			$numPerPage = $_SESSION["assetsPerPage"];
		else
			$numPerPage = $defaultNumPerPage;
			
		if (RequestContext::value("columns")) {
			$columns = RequestContext::value("columns");
			$_SESSION["assetColumns"] = $columns;
		} else if (isset($_SESSION["assetColumns"]))
			$columns = $_SESSION["assetColumns"];
		else
			$columns = $defaultCols;
			
		$resultPrinter =& new IteratorResultPrinter($assets, $columns, $numPerPage, "printAssetShort", $harmoni);
		
		$resultLayout =& $resultPrinter->getLayout($harmoni, "canView");
		$resultLayout->setPreHTML("<form id='AssetMultiEditForm' name='AssetMultiEditForm' action='' method='post'>");
		$resultLayout->setPostHTML("</form>");
		
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
		
	}
	
	/**
	 * Print out a select list option
	 * 
	 * @param string $fieldname
	 * @param string $default
	 * @param string $value
	 * @return void
	 * @access public
	 * @since 10/18/05
	 */
	function printSelectOption ( $fieldname, $default, $value ) {
		print "\n\t\t<option value='".$value."'";
		if (RequestContext::value($fieldname) == $value
			|| (!RequestContext::value($fieldname)
				&& $value == $default)) 
		{
			print " selected='selected'";
		}
		print ">".$value."</option>";
	}
}


// Callback function for printing Assets
function printAssetShort(& $asset, &$harmoni, $num) {
	$container =& new Container(new YLayout, BLOCK, 4);
	$fillContainerSC =& new StyleCollection("*.fillcontainer", "fillcontainer", "Fill Container", "Elements with this style will fill their container.");
	$fillContainerSC->addSP(new HeightSP("85%"));
// 	$fillContainerSC->addSP(new WidthSP("100%"));
// 	$fillContainerSC->addSP(new BorderSP("3px", "solid", "#F00"));
	$container->addStyle($fillContainerSC);
	
	$centered =& new StyleCollection("*.centered", "centered", "Centered", "Centered Text");
	$centered->addSP(new TextAlignSP("center"));	
	
	ob_start();
	$assetId =& $asset->getId();
	print "\n\t<strong>".htmlentities($asset->getDisplayName())."</strong>";
	print "\n\t<br/>"._("ID#").": ".$assetId->getIdString();
	print  "\n\t<br /><em>".htmlentities($asset->getDescription())."</em>";	
	print  "\n\t<br />";
	
	$component =& new Block(ob_get_contents(), 2);
	ob_end_clean();
	$container->add($component, "100%", null, LEFT, TOP);
	
	$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($assetId);
	if ($thumbnailURL !== FALSE) {
		ob_start();
		print "\n\t<a href='";
		print $harmoni->request->quickURL("asset", "view", array('asset_id' => $assetId->getIdString()));
		print "'>";
		print "\n\t\t<img src='$thumbnailURL' alt='Thumbnail Image' border='0' />";
		print "\n\t</a>";
		$component =& new Block(ob_get_contents(), 2);
		$component->addStyle($centered);
		ob_end_clean();
		$container->add($component, "100%", null, CENTER, CENTER);
	}
	
	
	ob_start();
	
	$authZ =& Services::getService("AuthZ");
	$idManager =& Services::getService("Id");
	$harmoni->request->startNamespace("AssetMultiEdit");
	print "<input type='checkbox'";
	print " name='".RequestContext::name("asset")."'";
	print " value='".$assetId->getIdString()."'";
	if (!$authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.modify"), $assetId))
		print " disabled='disabled'";
	print "/> | ";
	$harmoni->request->endNamespace();
	
	AssetPrinter::printAssetFunctionLinks($harmoni, $asset, NULL, $num);
	
	$component =& new Block(ob_get_contents(), 2);
	$component->addStyle($centered);
	ob_end_clean();
	$container->add($component, "100%", null, CENTER, BOTTOM);
	
	return $container;
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