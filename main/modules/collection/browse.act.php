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
require_once(HARMONI."GUIManager/StyleProperties/TextAlignSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/MinHeightSP.class.php");
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");
require_once(POLYPHONY."/main/library/RepositorySearchModules/RepositorySearchModuleManager.class.php");
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
class browseAction 
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
		if (!$this->getRepositoryId())
			return false;
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
		$description =& HtmlString::fromString($repository->getDescription());
		$description->clean();
		return $repository->getDisplayName()
			."<div style='font-size: small; margin-left: 25px;'>".$description->asString()."</div> ";
	}
	
	/**
	 * Register updates to the display properites inthe session
	 * 
	 * @return void
	 * @access public
	 * @since 5/11/06
	 */
	function registerDisplayProperties () {
		$properties = array (
			'thumbnail_size' => 200,
			
			'show_thumbnail' => 'true',
			'show_displayName' => 'true',
			'show_description' => 'true',
			'show_id' => 'false',
			'show_controls' => 'true',
			
			'assets_per_page' => 9,
			'asset_columns' => 3,
			
			'asset_order' => 'ModificationDate',
			'asset_order_direction' => 'DESC'
			
		);
				
		foreach($properties as $name => $default) {
			if (RequestContext::value('form_submitted'))
				$_SESSION[$name] = RequestContext::value($name);
			else if (!isset($_SESSION[$name]))
				$_SESSION[$name] = $default;
		}			
	}
	
	/**
	 * Answer the state array for the given Id
	 * 
	 * @param object Id 4id
	 * @return ref array
	 * @access public
	 * @since 5/15/06
	 */
	function &getState (&$id) {
		if (!isset($_SESSION['browse_state']))
			$_SESSION['browse_state'] = array();
		if (!isset($_SESSION['browse_state'][$id->getIdString()]))
			$_SESSION['browse_state'][$id->getIdString()] = array();
		
		return $_SESSION['browse_state'][$id->getIdString()];
	}
	
	/**
	 * Register the search and page state of this collection so that the
	 * next time that we return to it, we will get the same view.
	 * 
	 * @return void
	 * @access public
	 * @since 5/11/06
	 */
	function registerState () {				
		$this->_state =& $this->getState($this->getRepositoryId());
				
		// top-level properties
		$properties = array (
			'limit_by_type' => 'false'			
		);
		
		foreach($properties as $name => $default) {
			if (RequestContext::value('form_submitted'))
				$this->_state[$name] = RequestContext::value($name);
			else if (!isset($this->_state[$name]))
				$this->_state[$name] = $default;
		}
		
		// Search type
		if (RequestContext::value('searchtype'))
			$this->_state['searchtype'] = Type::fromString(RequestContext::value('searchtype'));
		else if (!isset($this->_state['searchtype']))
			$this->_state['searchtype'] = new Type(
										"Repository",
										"edu.middlebury.harmoni",
										"Keyword", 
										"Search with a string for keywords.");
		
		// Search Criteria
		$searchModuleManager =& Services::getService("RepositorySearchModules");
		if (RequestContext::value('form_submitted'))
			$this->_state['currentSearchValues'] = $searchModuleManager->getCurrentValues(
										$this->_state['searchtype']);
		else if (isset($this->_state['currentSearchValues']))
			$searchModuleManager->setCurrentValues(
										$this->_state['searchtype'],
										$this->_state['currentSearchValues']);
			
		
		
		// if we are limiting by type
		if ($this->_state["limit_by_type"] == 'true') {
			if (!isset($this->_state["selectedTypes"])) {
				$this->_state["selectedTypes"] = array();
			}
			
			$repository =& $this->getRepository();
			$types =& $repository->getAssetTypes();
			while ($types->hasNext()) {
				$type =& $types->next();
				if (RequestContext::value("type___".Type::typeToString($type)) == 'true') {
					$this->_state["selectedTypes"][Type::typeToString($type)] =& $type;
				} else if (RequestContext::value('form_submitted')) {
					unset($this->_state["selectedTypes"][Type::typeToString($type)]);
				}
			}
		}
		
		// unset our starting number if we have the new search terms
		if (RequestContext::value('form_submitted')
			|| isset($_REQUEST[ResultPrinter::startingNumberParam()])
			|| !isset($this->_state['numPerPage'])
			|| ($this->_state['numPerPage'] != $_SESSION['assets_per_page']))
		{
			$this->_state['startingNumber'] = ResultPrinter::getStartingNumber();
			$this->_state['numPerPage'] = $_SESSION['assets_per_page'];
		} else if (!isset($this->_state['startingNumber'])) {
			$this->_state['startingNumber'] = 1;
			$this->_state['numPerPage'] = $_SESSION['assets_per_page'];
		}	
	}
	
	/**
	 * Initialize our state
	 * 
	 * @return void
	 * @access public
	 * @since 5/17/06
	 */
	function init () {
		$this->registerDisplayProperties();
		$this->registerState();
		
		$harmoni =& Harmoni::instance();
		$harmoni->request->passthrough("collection_id");
		$harmoni->request->passthrough("asset_id");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$this->init();
		
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		
		$repository =& $this->getRepository();
						
		// function links
		ob_start();
		print _("Collection").": ";
		RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $repository);
		$layout =& new Block(ob_get_contents(), STANDARD_BLOCK);
		ob_end_clean();
		$actionRows->add($layout, "100%", null, CENTER, CENTER);
		
		
		$searchBar =& new Container(new YLayout(), BLOCK, STANDARD_BLOCK);
		$actionRows->add($searchBar, "100%", null, CENTER, CENTER);
		
		
		// Limit selection form
		$currentUrl =& $harmoni->request->mkURL();	
		$searchBar->setPreHTML(
			"\n<form action='".$currentUrl->write()."' method='post'>
	<input type='hidden' name='".RequestContext::name('form_submitted')."' value='true'/>");
		$searchBar->setPostHTML("\n</form>");
		
		ob_start();	
		// search fields
		print "\n<div style='margin-bottom: 10px;'>";
		$searchModuleManager =& Services::getService("RepositorySearchModules");
// 		print _("Search").": ";		
		print "\n\t<select name='".RequestContext::name("searchtype")."'";
		print " onchange='this.form.submit();'>";
			$types =& $repository->getSearchTypes();
			while ($types->hasNext()) {
				$type =& $types->next();				
				print "\n\t\t<option value='".Type::typeToString($type)."'";
				if ($this->_state['searchtype']->isEqual($type)) {
					print " selected='selected'";
				}
				print ">".$type->getKeyword()."</option>";
			}			
		print "\n\t</select>";
		
// 		print "\n\t<div style='margin-left: 25px;'>";
		print "\n\t\t".$searchModuleManager->createSearchFields($repository, $this->_state['searchtype']);	
		
		// submit
		print "\n\t<input type='submit' value='"._("Search")."' />";
		
// 		print "\n\t</div>";
		print "\n</div>";
		
		
		// Type limits
		print "\n<div style='margin-bottom: 10px;'>";
		print "\n\t<input type='checkbox' onchange='TypeLimitList.toggle(this, this.nextSibling.nextSibling);'";
		print " name='".RequestContext::name("limit_by_type")."'";
		print " value='true'";
		if ($this->_state["limit_by_type"] == 'true') {
			print " checked='checked'";
			$typesDisplay = 'block';
		} else {
			$typesDisplay = 'none';
		}
		print "/>"._("Limit to Types...")." ";
		
		print "\n\t<div id='listDiv' style='margin-left:25px; display: ".$typesDisplay.";'>";
		
		if ($this->_state["limit_by_type"] == 'true') {
			print "\n\t<table border='0'>";
			print "\n\t\t<tr>";
			$i = 0;
			$types =& $repository->getAssetTypes();
			$selectedTypes = array();
			while ($types->hasNext()) {
				print "\n\t\t\t<td>";
				$type =& $types->next();
				print "\n\t\t\t\t<input type='checkbox'";
				print " name='".RequestContext::name("type___".Type::typeToString($type))."'";
				print " value='true'";
				if (array_key_exists(Type::typeToString($type), $this->_state["selectedTypes"]))
					print " checked='checked'";
				print "/>".$type->getKeyword()."";
				print "\n\t\t\t<td>";
				$i++;
				if (($i % 4) == 0)
					print "\n\t\t</tr>\n\t\t<tr>";
			}
			print "\n\t\t</tr>";
			print "\n\t</table>";			
		}
		
		print "\n\t</div>";
		
		$repositoryId =& $repository->getId();
		$loadListUrl = str_replace('&amp;', '&', 
							$harmoni->request->quickUrl('collection', 'typeList', 
								array('collection_id', $repositoryId->getIdString())));
		$errorString = _('error');
		$loadingString = _('loading types');
		print <<< END

	<script type='text/javascript'>
	/* <![CDATA[ */
		
		/**
		 * The type limit list, a static class for managing the type limit list
		 * 
		 * @since 5/10/06
		 */
		function TypeLimitList () {
			
		}
		
		/**
		 * Toggle the showing and collapsing of the type limit list
		 * 
		 * @param element listDiv
		 * @return void
		 * @access public
		 * @since 5/10/06
		 */
		TypeLimitList.toggle = function (checkbox, listDiv) {
			if (!checkbox.checked) {
				listDiv.style.display = 'none';
				TypeLimitList.uncheckChildren(listDiv);
				return;
			} else {
				// if we have not loaded our table, load it via AJAX
				var hasChildTable = false;
				for (var i = 0; i < listDiv.childNodes.length; i++) {
					if (listDiv.childNodes[i].nodeName.toUpperCase() == 'TABLE') {
						hasChildTable = true;
						break;
					}
				}
				
				if (!hasChildTable) {
					TypeLimitList.load(listDiv, '$loadListUrl');
				}
				
				listDiv.style.display = 'block';
				TypeLimitList.checkChildren(listDiv);
			}
		}
		
		/**
		 * uncheck all of the descendent checkboxes of a node
		 * 
		 * @param element node
		 * @return void
		 * @access public
		 * @since 5/10/06
		 */
		TypeLimitList.load = function( destinationNode, url ) {
			/*********************************************************
			 * Do the AJAX request and repopulate the basket with 
			 * the contents of the result
			 *********************************************************/
			 
			destinationNode.innerHTML = "<div style='text-decoration: blink;'>$loadingString</div>";
						
			// branch for native XMLHttpRequest object (Mozilla, Safari, etc)
			if (window.XMLHttpRequest)
				var req = new XMLHttpRequest();
				
			// branch for IE/Windows ActiveX version
			else if (window.ActiveXObject)
				var req = new ActiveXObject("Microsoft.XMLHTTP");
			
			
			if (req) {
				req.onreadystatechange = function () {
					// only if req shows "loaded"
					if (req.readyState == 4) {
						// only if we get a good load should we continue.
						if (req.status == 200) {
							destinationNode.innerHTML = req.responseText;
							TypeLimitList.checkChildren(destinationNode);
						} else {
							destinationNode.innerHTML = "<div style='background-color: #FAA; border: 1px solid; padding: 5px;'>$errorString</div>";
							alert("There was a problem retrieving the XML data:\\n" +
								req.statusText);
						}
					}
				}
				
				req.open("GET", url, true);
				req.send(null);
			}
		}
		
		/**
		 * uncheck all of the descendent checkboxes of a node
		 * 
		 * @param element node
		 * @return void
		 * @access public
		 * @since 5/10/06
		 */
		TypeLimitList.uncheckChildren = function( node ) {
			if (node.type == 'checkbox') {
				node.checked = false;
// 				alert('unchecking: ' + node.name);
			}
			
			for (var i = 0; i < node.childNodes.length; i++) {
				TypeLimitList.uncheckChildren(node.childNodes[i]);
			}
		}
		
		/**
		 * check all of the descendent checkboxes of a node
		 * 
		 * @param element node
		 * @return void
		 * @access public
		 * @since 5/10/06
		 */
		TypeLimitList.checkChildren = function( node ) {
			if (node.type == 'checkbox') {
				node.checked = true;
// 				alert('checking: ' + node.name);
			}
			
			for (var i = 0; i < node.childNodes.length; i++) {
				TypeLimitList.checkChildren(node.childNodes[i]);
			}
		}
	
	/* ]]> */
	</script>
		
END;
		
		print "\n</div>";
		$searchBar->add(new UnstyledBlock(ob_get_clean()), null, null, LEFT, TOP);
		
		
		//***********************************
		// print the results
		//***********************************
		$resultPrinter =& new IteratorResultPrinter($this->getAssets(),
									$_SESSION["asset_columns"], 
									$_SESSION["assets_per_page"], 
									"printAssetShort", $this->getParams());
									
		$resultPrinter->setStartingNumber($this->_state['startingNumber']);
		
		$resultLayout =& $resultPrinter->getLayout($harmoni, "canView");
		$resultLayout->setPreHTML("<form id='AssetMultiEditForm' name='AssetMultiEditForm' action='' method='post'>");
		$resultLayout->setPostHTML("</form>");
		
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
		
		
		/*********************************************************
		 * Display options
		 *********************************************************/
		$searchBar->add($this->getDisplayOptions($resultPrinter), null, null, LEFT, TOP);
	}
	
	/**
	 * Answer the parameters to pass in a url
	 * 
	 * @return array
	 * @access public
	 * @since 5/17/06
	 */
	function getParams () {
		$params = array();
		$params["collection_id"] = RequestContext::value("collection_id");
		$params[RequestContext::name("limit_by_type")] = RequestContext::value("limit_by_type");
		$params[RequestContext::name("type")] = RequestContext::value("type");
		$params[RequestContext::name("searchtype")] = RequestContext::value("searchtype");
		if (isset($this->_state['searchtype'])) {
			$searchModuleManager =& Services::getService("RepositorySearchModules");
			foreach ($searchModuleManager->getCurrentValues($this->_state['searchtype']) as $key => $value) {
				$params[$key] = $value;
			}
		}
		if (isset($this->_state['selectedTypes']) && count($this->_state['selectedTypes'])) {
			foreach(array_keys($this->_state['selectedTypes']) as $typeString) {
				$params[RequestContext::name("type___".$typeString)] = 
					RequestContext::value("type___".$typeString);
			}
		}
		
		return $params;
	}
	
	/**
	 * Answer the assets that we are searching for
	 * 
	 * @return object Iterator
	 * @access public
	 * @since 5/15/06
	 */
	function &getAssets () {
		$repository =& $this->getRepository();
		$searchModuleManager =& Services::getService("RepositorySearchModules");		
		
		$searchProperties =& new HarmoniProperties(
					Type::fromString("repository::harmoni::order"));
		$searchProperties->addProperty("order", $_SESSION["asset_order"]);
		$searchProperties->addProperty("direction", $_SESSION['asset_order_direction']);
		
		if (isset($this->_state['selectedTypes']) && count($this->_state['selectedTypes'])) {
			$searchProperties->addProperty("allowed_types", $this->_state['selectedTypes']);
		}
					

		if (isset($this->_state['searchtype'])
			&& $searchModuleManager->getSearchCriteria($repository, $this->_state['searchtype'])) 
		{				
			$criteria = $searchModuleManager->getSearchCriteria($repository, $this->_state['searchtype']);
			
			$assets =& $repository->getAssetsBySearch(
				$criteria,
				$this->_state['searchtype'],
				$searchProperties);
		} else if (isset($this->_state['selectedTypes']) && count($this->_state['selectedTypes'])) {
			$assets =& new MultiIteratorIterator($null = null);
			foreach (array_keys($this->_state['selectedTypes']) as $key) {
				$assets->addIterator($repository->getAssetsByType($this->_state['selectedTypes'][$key]));
			}
		} else if ($this->hasRootSearch()) {
			$criteria = NULL;
			$assets =& $repository->getAssetsBySearch(
				$criteria, 
				new HarmoniType("Repository","edu.middlebury.harmoni","RootAssets", ""), 
				$searchProperties);
		} 
		// Otherwise, just get all the assets
		else {
			$assets =& $repository->getAssets();
		}
		
		return $assets;
	}
	
	/**
	 * Answer true if this repository supports root search
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/15/06
	 */
	function hasRootSearch () {
		$repository =& $this->getRepository();
		// If the Repository supports searching of root assets, just get those
		$rootSearchType =& new HarmoniType("Repository","edu.middlebury.harmoni","RootAssets", "");
		$searchTypes =& $repository->getSearchTypes();
		while ($searchTypes->hasNext()) {
			if ($rootSearchType->isEqual( $searchTypes->next() ))
				return true;
		}
		
		return false;
	}
	
	/**
	 * Anser the display options GUI component
	 * 
	 * @param object ResultPrinter $resultPrinter
	 * @return object Component The GUI component for the display options
	 * @access public
	 * @since 5/11/06
	 */
	function &getDisplayOptions ( &$resultPrinter, $allowReordering = TRUE) {
		// view options
		ob_start();
		print "\n<div style='text-align: left'>";
		print "\n\t\t"._("Display")." ";
		
		print "\n\t<select name='".RequestContext::name("assets_per_page")."'";
		print " onchange='this.form.submit();'>";			
		for ($i = 1; $i < 20; $i++)
			$this->printSelectOption("assets_per_page", $_SESSION["assets_per_page"], $i);
		for ($i = 20; $i < 100; $i=$i+10)
			$this->printSelectOption("assets_per_page", $_SESSION["assets_per_page"], $i);
		for ($i = 100; $i <= 1000; $i=$i+100)
			$this->printSelectOption("assets_per_page", $_SESSION["assets_per_page"], $i);
		print "\n\t</select>";
		
		print "\n\t\t"._("per page, in")." ";
		
		
		print "\n\t<select name='".RequestContext::name("asset_columns")."'";
		print " onchange='this.form.submit();'>";
		for ($i = 1; $i < 20; $i++)
			$this->printSelectOption("columns", $_SESSION["asset_columns"], $i);
		print "\n\t</select>";
		
		print " "._("columns.");
		if ($allowReordering) {
			print " "._("Order by")." ";
			
			print "\n\t<select name='".RequestContext::name("asset_order")."'";
			print " onchange='this.form.submit();'>";
			$this->printSelectOption("asset_order", $_SESSION["asset_order"], 'DisplayName', _('Title'));
			$this->printSelectOption("asset_order", $_SESSION["asset_order"], 'Id', _('Id'));
			$this->printSelectOption("asset_order", $_SESSION["asset_order"], 'ModificationDate', _('Modification Date'));
			$this->printSelectOption("asset_order", $_SESSION["asset_order"], _('Creation Date'));
			print "\n\t</select>";
			
			print "\n\t<select name='".RequestContext::name("asset_order_direction")."'";
			print " onchange='this.form.submit();'>";
			$this->printSelectOption("asset_order_direction", $_SESSION["asset_order_direction"], 'ASC', _('Ascending'));
			$this->printSelectOption("asset_order_direction", $_SESSION["asset_order_direction"], 'DESC', _('Descending'));
			print "\n\t</select>";
		}
		
		// more display options
		$onChange = " onchange='this.form.action += \"&".$resultPrinter->startingNumberParam()."=".$resultPrinter->getStartingNumber()."\"; this.form.submit();'";
		print "\n\t<span";
		print " style='font-weight: bold; text-decoration: underline; cursor: pointer'";
		print " onclick='";
		print 'if (this.nextSibling.nextSibling.style.display=="none") {';
		print 		'this.nextSibling.nextSibling.style.display="block"; ';
		print 		'this.innerHTML="'._("less...").'";';
		print '} else {';
		print 		'this.nextSibling.nextSibling.style.display="none";';
		print 		'this.innerHTML="'._("more...").'";';
		print '}';
		print "'>"._("more...")."</span>";
		print "\n\t<div style='display: none'>";
		
		print _("Image Size:")." ";
		
		print "\n\t\t<select";
		print " name='".RequestContext::name("thumbnail_size")."'";
		print $onChange;
		print ">";
		$sizes = array(50, 100, 150, 200);
		foreach ($sizes as $size) {
			print "\n\t\t\t<option value='$size'";
			if ($_SESSION["thumbnail_size"] == $size)
				print " selected='selected'";
			print "'>".$size."px</option>";
		}
		print "\n\t\t</select> &nbsp;&nbsp;";
		
		print _("Show:")." ";
		
		print "\n\t\t&nbsp;&nbsp;<input type='checkbox'";
		print " name='".RequestContext::name("show_thumbnail")."'";
		print $onChange;
		print " value='true'";
		if ($_SESSION["show_thumbnail"] == 'true')
			print " checked='checked'";
		print "/> ";
		print _("Thumbnails,");
		
		print "\n\t\t&nbsp;&nbsp;<input type='checkbox'";
		print " name='".RequestContext::name("show_displayName")."'";
		print $onChange;
		print " value='true'";
		if ($_SESSION["show_displayName"] == 'true')
			print " checked='checked'";
		print "/> ";
		print _("Title,");
		
		print "\n\t\t&nbsp;&nbsp;<input type='checkbox'";
		print " name='".RequestContext::name("show_description")."'";
		print $onChange;
		print " value='true'";
		if ($_SESSION["show_description"] == 'true')
			print " checked='checked'";
		print "/> ";
		print _("Description,");
		
		print "\n\t\t&nbsp;&nbsp;<input type='checkbox'";
		print " name='".RequestContext::name("show_id")."'";
		print $onChange;
		print " value='true'";
		if ($_SESSION["show_id"] == 'true')
			print " checked='checked'";
		print "/> ";
		print _("Id,");
		
		print "\n\t\t&nbsp;&nbsp;<input type='checkbox'";
		print " name='".RequestContext::name("show_controls")."'";
		print $onChange;
		print " value='true'";
		if ($_SESSION["show_controls"] == 'true')
			print " checked='checked'";
		print "/> ";
		print _("Controls");
		
		
		
		print "\n\t</div>";
		print "</div>";
		
		$block =& new UnstyledBlock(ob_get_clean());
		return $block;
	}
	
	/**
	 * Print out a select list option
	 * 
	 * @param string $fieldname
	 * @param string $default
	 * @param string $value
	 * @param optional string $label
	 * @return void
	 * @access public
	 * @since 10/18/05
	 */
	function printSelectOption ( $fieldname, $default, $value, $label = null ) {
		print "\n\t\t<option value='".$value."'";
		if (RequestContext::value($fieldname) == $value
			|| (!RequestContext::value($fieldname)
				&& $value == $default)) 
		{
			print " selected='selected'";
		}
		print ">".(($label)?$label:$value)."</option>";
	}
}


// Callback function for printing Assets
function printAssetShort(& $asset, $params, $num) {
	$harmoni =& Harmoni::instance();
	$container =& new Container(new YLayout, BLOCK, EMPHASIZED_BLOCK);
	$fillContainerSC =& new StyleCollection("*.fillcontainer", "fillcontainer", "Fill Container", "Elements with this style will fill their container.");
	$fillContainerSC->addSP(new MinHeightSP("88%"));
	$container->addStyle($fillContainerSC);
	
	$centered =& new StyleCollection("*.centered", "centered", "Centered", "Centered Text");
	$centered->addSP(new TextAlignSP("center"));	
	
	$assetId =& $asset->getId();
	
	if ($_SESSION["show_thumbnail"] == 'true') {
		$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($asset);
		if ($thumbnailURL !== FALSE) {
			$xmlModule = 'collection';
			$xmlAssetIdString = $assetId->getIdString();
			$xmlStart = $num - 1;
			
			$thumbSize = $_SESSION["thumbnail_size"]."px";
	
			ob_start();
			print "\n<div style='height: $thumbSize; width: $thumbSize; margin: auto;'>";
			print "\n\t<a style='cursor: pointer;'";
			print " onclick='Javascript:window.open(";
			print '"'.VIEWER_URL."?&amp;source=";
			$params["asset_id"] = $xmlAssetIdString;
			print urlencode($harmoni->request->quickURL($xmlModule, "browse_outline_xml", $params));
			print '&amp;start='.$xmlStart.'", ';
	// 		print '"'.preg_replace("/[^a-z0-9]/i", '_', $assetId->getIdString()).'", ';
			print '"_blank", ';
			print '"toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=500"';
			print ")'>";
			print "\n\t\t<img src='$thumbnailURL' class='thumbnail' alt='Thumbnail Image' border='0' style='max-height: $thumbSize; max-width: $thumbSize;' />";
			print "\n\t</a>";
			print "\n</div>";
			$component =& new UnstyledBlock(ob_get_contents());
			$component->addStyle($centered);
			ob_end_clean();
			$container->add($component, "100%", null, CENTER, CENTER);
		}
	}
	
	ob_start();
	if ($_SESSION["show_displayName"] == 'true')
		print "\n\t<div style='font-weight: bold; height: 50px; overflow: auto;'>".htmlspecialchars($asset->getDisplayName())."</div>";
	if ($_SESSION["show_id"] == 'true')
		print "\n\t<div>"._("ID#").": ".$assetId->getIdString()."</div>";
	if ($_SESSION["show_description"] == 'true') {
		$description =& HtmlString::withValue($asset->getDescription());
		$description->trim(25);
		print  "\n\t<div style='font-size: smaller; height: 50px; overflow: auto;'>".$description->asString()."</div>";	
	}
	
	$component =& new UnstyledBlock(ob_get_contents());
	ob_end_clean();
	$container->add($component, "100%", null, LEFT, TOP);
	
	
	ob_start();
	print "<div style='margin-top: 5px; font-size: small;'>";
	if ($_SESSION["show_controls"] == 'true') {
		AssetPrinter::printAssetFunctionLinks($harmoni, $asset, NULL, $num, false);
		print " | ";
	}
	$authZ =& Services::getService("AuthZ");
	$idManager =& Services::getService("Id");
	$harmoni->request->startNamespace("AssetMultiEdit");
	print "<input type='checkbox'";
	print " name='".RequestContext::name("asset")."'";
	print " value='".$assetId->getIdString()."'";
	if (!$authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.modify"), $assetId))
		print " disabled='disabled'";
	print "/>";
	$harmoni->request->endNamespace();
	print "</div>";
	
	$container->add(new UnstyledBlock(ob_get_clean()), "100%", null, RIGHT, BOTTOM);
	
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