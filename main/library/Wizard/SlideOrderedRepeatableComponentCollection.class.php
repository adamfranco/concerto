<?php
/**
 * @since Aug 1, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WOrderedRepeatableComponentCollection.class.php");

/**
 * This component allows for the creation of ordered repeatable components or groups of components. 
 * 
 * @since Aug 1, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

class SlideOrderedRepeatableComponentCollection 
	extends WOrderedRepeatableComponentCollection 
{
    
    function SlideOrderedRepeatableComponentCollection() {
    	parent::WOrderedRepeatableComponentCollection();
    	$this->_addButton->setLabel(_("Add a Text-Slide"));
    	$this->_addFromBasketButton =& WEventButton::withLabel(dgettext("polyphony", "Create Slides from Assets in Basket"));
    	$this->_addFromBasketButton->setParent($this);
    }
    
	/**
	 * Adds a collection of {@link WizardComponent}s indexed by field name to the list of collections.
	 * This is useful when pre-populating the list with old/previous values.
	 * @param ref array $collection Indexed by field name.
	 * @access public
	 * @return void
	 */
	function addCollection (&$collection) {
		// @todo - make sure that the correct fields/classes are represented
		$collection["_remove"] =& WEventButton::withLabel(dgettext("polyphony", "Remove"));
		$collection["_remove"]->setParent($this);
		$collection["_remove"]->setOnClick("ignoreValidation(this.form);");
		$collection["_moveup"] =& WEventButton::withLabel(dgettext("polyphony", "Move Up"));
		$collection["_moveup"]->setParent($this);
		$collection["_movedown"] =& WEventButton::withLabel(dgettext("polyphony", "Move Down"));
		$collection["_movedown"]->setParent($this);
		
		// The AssetComponent
		$collection['_asset'] =& new AssetComponent;
		$collection['_asset']->setParent($this);
		$collection['_asset']->setId($collection['assetId']);
		unset($collection['assetId']);
		
		$this->_collections[$this->_nextId] =& $collection;
		$idManager =& Services::getService("Id");
		$this->_orderedSet->addItem($idManager->getId(strval($this->_nextId)));
		$this->_nextId++;
		$this->_num++;
	}
	
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 */
	function update ($fieldName) {
		$idManager =& Services::getService("Id");
		$ok = parent::update($fieldName);
		
		// then, check if any "buttons" or anything were pressed to add/remove elements
		$this->_addFromBasketButton->update($fieldName."_addfrombasket");
		if ($this->_addFromBasketButton->getAllValues()) {
			$basket =& BasketManager::getBasket();
			$basket->reset();
			while ($basket->hasNext()) {
				$assetId =& $basket->next();
				$element =& $this->_addElement();
				$element['_asset'] =& new AssetComponent;
				$element['_asset']->setParent($this);
				$element['_asset']->setId($assetId);
			}
			$basket->removeAllItems();
		}
		
		return $ok;
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {
		// check if we have min/max values that are appropriate, etc.
		if ($this->_num < $this->_min) $this->_num = $this->_min;
		if ($this->_max != -1 && $this->_num > $this->_max) $this->_num = $this->_max;
		$this->_ensureNumber($this->_num);
		
		ob_start();
		
		$includeAdd = !($this->_num == $this->_max);
		$includeRemove = !($this->_num == $this->_min);

		print "<table width='100%' border='0' cellspacing='0' cellpadding='2'>\n";
		
		$this->_orderedSet->reset();
		while ($this->_orderedSet->hasNext()) {
			$collectionId =& $this->_orderedSet->next();
			$key = $collectionId->getIdString();
			
			$this->_collections[$key]["_remove"]->setEnabled($includeRemove);
			print "<tr><td valign='top' style='border-bottom: 1px solid #555;'>";
			
			print $this->_collections[$key]["_remove"]->getMarkup($fieldName."_".$key."__remove");
			if ($this->_orderedSet->getPosition($collectionId) > 0)
				print "\n<br/>".$this->_collections[$key]["_moveup"]->getMarkup($fieldName."_".$key."__moveup");
			if ($this->_orderedSet->hasNext())
				print "\n<br/>".$this->_collections[$key]["_movedown"]->getMarkup($fieldName."_".$key."__movedown");
			
			print "</td><td style='border-bottom: 1px solid #555;'>";
			
			print Wizard::parseText($this->_text, $this->_collections[$key], $fieldName."_".$key."_");
			
			print "</td><td style='border-bottom: 1px solid #555;'>";
			
			if (isset($this->_collections[$key]['_asset'])) {
				print $this->_collections[$key]['_asset']->getMarkup($fieldName."_".$key."__asset");
			}
			
			print "</td></tr>\n";
		}
		
		$this->_addButton->setEnabled($includeAdd);
		print "<tr><td colspan='2'>".$this->_addButton->getMarkup($fieldName."_add")."</td></tr>\n";
		print "<tr><td colspan='2'>".$this->_addFromBasketButton->getMarkup($fieldName."_addfrombasket")."</td></tr>\n";
		print "</table>\n";
		
		$m = ob_get_contents();
		ob_end_clean();
		return $m;
	}
    
}
?>