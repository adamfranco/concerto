<?php
/**
 * @since 8/9/06
 * @package concerto.modules.collections
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/modules/collection/rss_latest.act.php");

/**
 * Build an RSS feed of the most recently Added (or modified) Assets across all
 * of Concerto
 * 
 * @since 8/9/06
 * @package concerto.modules.collections
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class rss_all_latestAction
	extends rss_latestAction
{

	/**
	 * @var integer $_numInFeed;  
	 * @access private
	 * @since 8/9/06
	 */
	var $_numInFeed = 20;
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/8/06
	 */
	function isExecutionAuthorized () {
		if ($this->isAuthenticated())
			return true;
		else
			return false;
	}
	
	/**
	 * Build the rss feed
	 * 
	 * @return void
	 * @access public
	 * @since 8/8/06
	 */
	function buildFeed () {
		$harmoni =& Harmoni::instance();
		$repositoryManager =& Services::getService('Repository');
 		
 		if (RequestContext::value('order') == 'modification') {
 			$this->setTitle(_("All of Concerto")." - "._("Recently Changed Assets"));
 			$this->setDescription(_("A feed of the most recently changed Assets across all of Concerto."));
 		} else {
	 		$this->setTitle(_("All of Concerto")." - "._("Newest Assets"));
	 		$this->setDescription(_("A feed of the most recently added Assets across all of Concerto."));
	 	}
	 	
 		$this->setLink($harmoni->request->quickURL('collections', 'namebrowse'));
		
		$assets =& $this->getAssets();
		$i = 0;
		while ($assets->hasNext() && $i < $this->_numInFeed) {
			$this->addItem($this->getAssetItem($assets->next()));
			$i++;
		}
	}
	
	/**
	 * Answer an iterator of the latest assets.
	 * 
	 * @return object Iterator
	 * @access public
	 * @since 8/8/06
	 */
	function &getAssets () {
		$authZManager =& Services::getService("AuthZ");
 		$idManager =& Services::getService("IdManager");
		
		$assetsByDate = array();
		
		$repositoryManager =& Services::getService('Repository');
		$repositories =& $repositoryManager->getRepositories();
		$exhibitionRepositoryType =& new Type ('System Repositories', 
											'edu.middlebury.concerto', 'Exhibitions');
		while($repositories->hasNext()) {
			$repository =& $repositories->next();
			
			if (!$exhibitionRepositoryType->isEqual($repository->getType())
				&& $authZManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view"),
					$repository->getId()))
			{
				$assets =& parent::getAssets($repository);
				$i = 0;
				while ($assets->hasNext() && $i < $this->_numInFeed) {
					$asset =& $assets->next();
					$assetId =& $asset->getId();
					if (RequestContext::value('order') == 'modification')
						$date =& $asset->getModificationDate();
					else
						$date =& $asset->getCreationDate();
					
					$j = 0;
					while (isset($assetsByDate[$date->asString()." ".$j]))
						$j++;
					$assetsByDate[$date->asString()." ".$j] =& $asset;
					$i++;
				}
			}
		}
		
		krsort($assetsByDate);
		
		$iterator =& new HarmoniIterator($assetsByDate);
		return $iterator;
	}
	
	/**
	 * Add an Asset to the feed
	 * 
	 * @param object Asset $asset
	 * @return object RSSItem
	 * @access public
	 * @since 8/9/06
	 */
	function &getAssetItem (&$asset) {
		$item =& parent::getAssetItem($asset);
		$repository =& $asset->getRepository();
		$item->setDescription(str_replace('<dl>', "<dl>"
				."\n\t\t<dt style='font-weight: bold'>"._("Collection: ")."</dt>"
				."\n\t\t<dd>".$repository->getDisplayName()."</dd>",
			$item->getDescription()));
		
		$item->prependCategory($repository->getDisplayName());
		return $item;
	}
}

?>