<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/modules/collection/browse.act.php");
require_once(HARMONI."GUIManager/StyleProperties/MinHeightSP.class.php");
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");

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
class browseAssetAction 
	extends browseAction
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
		return ($authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.access"), 
					$this->getAssetId())
				|| $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view"), 
					$this->getAssetId()));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to access this <em>Asset</em>.");
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
		return _("Browsing")." <em>".$asset->getDisplayName()."</em> ";
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
		$this->_state =& $this->getState($this->getAssetId());
				
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
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$this->init();
		
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		
		$asset =& $this->getAsset();
		$assetId =& $asset->getId();

		// function links
		ob_start();
		AssetPrinter::printAssetFunctionLinks($harmoni, $asset);
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, CENTER, CENTER);
		
		ob_start();
		print "\n<table width='100%'>\n<tr><td style='text-align: left; vertical-align: top'>";				

		print "\n\t<dl>";		
		if ($asset->getDisplayName()) {
			print "\n\t\t<dt style='font-weight: bold;'>"._("Title:")."</dt>";
			print "\n\t\t<dd>".$asset->getDisplayName()."</dd>";
		}
		
		if ($asset->getDescription()) {
			$description =& HtmlString::withValue($asset->getDescription());
			$description->clean();
			print "\n\t\t<dt style='font-weight: bold;'>"._("Description:")."</dt>";
			print "\n\t\t<dd>".$description->asString()."</dd>";
		}
		
		print  "\n\t\t<dt style='font-weight: bold;'>";
		print _("ID#");
		print ":</dt>\n\t\t<dd >";
		print $assetId->getIdString();
		print "</dd>";
		
		print  "\n\t\t<dt style='font-weight: bold;'>";
		print _("Type");
		print ":</dt>\n\t\t<dd >";
		print Type::typeToString($asset->getAssetType());
		print "</dd>";
		
		$date = $asset->getModificationDate();
		print  "\n\t\t<dt style='font-weight: bold;'>";
		print _("Modification Date");
		print ":</dt>\n\t\t<dd >";
		print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
		print "</dd>";
		
		$date = $asset->getCreationDate();
		print  "\n\t\t<dt style='font-weight: bold;'>";
		print _("Creation Date");
		print ":</dt>\n\t\t<dd >";
		print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
		print "</dd>";
	
		if(is_object($asset->getEffectiveDate())) {
			$date = $asset->getEffectiveDate();
			print  "\n\t\t<dt style='font-weight: bold;'>";
			print _("Effective Date");
			print ":</dt>\n\t\t<dd >";
			print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
			print "</dd>";
		}
		
		if(is_object($asset->getExpirationDate())) {
			$date = $asset->getExpirationDate();
			print  "\n\t\t<dt style='font-weight: bold;'>";
			print _("Expiration Date");
			print ":</dt>\n\t\t<dd >";
			print $date->monthName()." ".$date->dayOfMonth().", ".$date->year()." ".$date->hmsString()." ".$date->timeZoneAbbreviation();
			print "</dd>";
		}
		print "\n\t</dl>";
		
		
		print "\n</td><td style='text-align: right; vertical-align: top'>";
		
		
		$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($assetId);
		if ($thumbnailURL !== FALSE) {
			print "\n\t\t<img src='$thumbnailURL' alt='Thumbnail Image' align='right' class='thumbnail_image' style='margin-bottom: 5px;' />";
		}
		
		// Add the tagging manager script to the header
		$outputHandler =& $harmoni->getOutputHandler();
		$outputHandler->setHead($outputHandler->getHead()
			."\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."javascript/Tagger.js'></script>"
			."\n\t\t<link rel='stylesheet' type='text/css' href='".POLYPHONY_PATH."javascript/Tagger.css' />");
		
		// Tags
		print "\n\t<div style='font-weight: bold; margin-bottom: 10px; text-align: left; clear: both;'>"._("Tags given to this Asset: ")."</div>";
		print "\n\t<div style=' text-align: justify;'>";
		print TagAction::getTagCloudForItem(TaggedItem::forId($assetId, 'concerto'), 'view');
		print "\n\t</div>";
		
		print "\n</td></tr></table>";
// 		print "\n\t<hr/>";
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		
		$searchBar =& new Container(new YLayout(), BLOCK, STANDARD_BLOCK);
		$actionRows->add($searchBar, "100%", null, CENTER, CENTER);
		
		
		//***********************************
		// Get the assets to display
		//***********************************
		$assets =& $asset->getAssets();
		
		$tmpAssets = array();
		while ($assets->hasNext()) {
			$asset =& $assets->next();
			switch($_SESSION["asset_order"]) {
				case 'DisplayName':
					$tmpAssets[$asset->getDisplayName()] =& $asset;
					break;
				case 'Id':
					$id =& $asset->getId();
					$tmpAssets[$id->getIdString()] =& $asset;
					break;
				case 'ModificationDate':
					$date =& $asset->getModificationDate();
					$tmpAssets[$date->asString()] =& $asset;
					break;
				case 'CreationDate':
					$date =& $asset->getCreationDate();
					$tmpAssets[$date->asString()] =& $asset;
					break;
				default:
					$tmpAssets[] =& $asset;
			}
		}
		
		if ($_SESSION["asset_order_direction"] == 'ASC')
			ksort($tmpAssets);
		else
			krsort($tmpAssets);
		
		//***********************************
		// print the results
		//***********************************
		$resultPrinter =& new ArrayResultPrinter($tmpAssets, 
									$_SESSION["asset_columns"], 
									$_SESSION["assets_per_page"], 
									"printAssetShort", $this->getParams());
		$resultPrinter->setStartingNumber($this->_state['startingNumber']);
		
		$resultLayout =& $resultPrinter->getLayout("canView");
		$resultLayout->setPreHTML("<form id='AssetMultiEditForm' name='AssetMultiEditForm' action='' method='post'>");
		$resultLayout->setPostHTML("</form>");
		
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
		
		
		
		/*********************************************************
		 * Display options
		 *********************************************************/
		$currentUrl =& $harmoni->request->mkURL();	
		$searchBar->setPreHTML(
			"\n<form action='".$currentUrl->write()."' method='post'>
	<input type='hidden' name='".RequestContext::name('form_submitted')."' value='true'/>");
		$searchBar->setPostHTML("\n</form>");
		
		ob_start();
		print  "\n\t<strong>"._("Child Assets").":</strong>";
		$searchBar->add(new UnstyledBlock(ob_get_clean()), null, null, LEFT, TOP);
		
		$searchBar->add($this->getDisplayOptions($resultPrinter), null, null, LEFT, TOP);
	}
}