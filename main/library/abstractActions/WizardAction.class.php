<?php
/**
 * @since 4/28/05
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
 
 require_once(dirname(__FILE__)."/Action.class.php");

/**
 * This class is an abstract class that provides a structure for building actions
 * that contain Wizards. Decendent actions are not required to contain Wizards,
 * though if they do, they should implement the following methods:
 * 		- {@link saveWizard()}
 *		- {@link createWizard()}
 *		- {@link getReturnUrl()}
 * 
 * To run the entire wizard execution sequence, only {@link runWizard()} needs
 * to be called. Example:
 * <code>
 * <?php
 *	...
 *	
 *	&#109;**
 *	 * Build the content for this action
 *	 * 
 *	 * @return boolean
 *	 * @access public
 *	 * @since 4/26/05
 *	 *&#109;
 *	function buildContent () {
 *		$centerPane =& $this->getCenterPane();
 *		$assetId =& $this->getAssetId();
 *		$cacheName = 'edit_asset_wizard_'.$assetId->getIdString();
 *		
 *		$this->runWizard ( $cacheName, $centerPane );
 *	}
 *	
 *	...
 *	?>
 *	</code>
 *
 * @since 4/28/05
 * @package <##>
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class WizardAction 
	extends Action
{
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 4/28/05
	 */
	function &createWizard () {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridded in child classes."));
	}
	
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 4/28/05
	 */
	function saveWizard ( $cacheName ) {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridded in child classes."));
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridded in child classes."));
	}
	
	/**
	 * Cancel from this Wizard. This will tear down the wizard and return us
	 * to our returnUrl as specified by {@link getReturnUrl()}.
	 * 
	 * @param string $cacheName
	 * @return void
	 * @access public
	 * @since 4/28/05
	 */
	function cancelWizard ( $cacheName ) {
		$this->closeWizard($cacheName);
		header("Location: ".$this->getReturnUrl());
	}
	
	/**
	 * Close the Wizard. This will tear down the Wizard.
	 * 
	 * @param string $cacheName
	 * @return void
	 * @access public
	 * @since 4/28/05
	 */
	function closeWizard ( $cacheName ) {
		$wizard =& $this->getWizard($cacheName);
		$wizard = NULL;
		unset ($_SESSION[$cacheName]);
		unset ($wizard);
	}
	
	/**
	 * Run this Action's wizard and add it to the specified container. Cache
	 * this Action's wizard with the specified cacheName.
	 *
	 * This is the only method that an Action needs to call to run itself, see
	 * {@link editAction::buildContent()} for an example:
	 * <code>
	 * <?php
	 *	...
	 *	
	 *	&#109;**
	 *	 * Build the content for this action
	 *	 * 
	 *	 * @return boolean
	 *	 * @access public
	 *	 * @since 4/26/05
	 *	 *&#109;
	 *	function buildContent () {
	 *		$centerPane =& $this->getCenterPane();
	 *		$assetId =& $this->getAssetId();
	 *		$cacheName = 'edit_asset_wizard_'.$assetId->getIdString();
	 *		
	 *		$this->runWizard ( $cacheName, $centerPane );
	 *	}
	 *	
	 *	...
	 *	?>
	 *	</code>
	 * 
	 * @param string $cacheName The name to cache this Action's Wizard with.
	 * @param object Container $container The container to put the Wizard's layout in.
	 * @return void
	 * @access public
	 * @since 4/28/05
	 */
	function runWizard ( $cacheName, &$container) {
		$wizard =& $this->getWizard($cacheName);
		$harmoni =& Harmoni::instance();
		
		if ($wizard->isSaveRequested()) {		
			if ($this->saveWizard($cacheName))
				$this->cancelWizard($cacheName);
		} 
		else if ($wizard->isCancelRequested()) {
			$this->cancelWizard($cacheName);	
		}
		
		$container->add($wizard->getLayout($harmoni), null, null, CENTER, CENTER);
	}
	
	/**
	 * Build and/or return our Wizard. Handle caching of it in the SESSION under
	 * the specified name.
	 * 
	 * @param string $cacheName
	 * @return object Wizard
	 * @access public
	 * @since 4/28/05
	 */
	function &getWizard ( $cacheName ) {
		// Create the wizard if it doesn't exist.
		 if (!$_SESSION[$cacheName]) {
		 	$wizard =& $this->createWizard();
		 	$_SESSION[$cacheName] =& $wizard;
		 }
		 
		 return $_SESSION[$cacheName];
	}	
}

?>