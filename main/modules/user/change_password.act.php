<?php

/**
 * @package polyphony.modules.user
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * This file will allow the user to change their ConcertoDB password.
 *
 * @since 10/24/05 
 * @author Christopher W. Shubert
 * 
 * @package polyphony.modules.user
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class change_passwordAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Change Your ConcertoDB Password");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$authN =& Services::getService("AuthN");
		
		$dbAuthType =& new Type ("Authentication", "edu.middlebury.harmoni",
			"Concerto DB");
		
		$centerPane =& $this->getActionRows();
		
		$id =& $authN->getUserId($dbAuthType);
		$cacheName = 'change_password_wizard_'.$id->getIdString();
		
		$this->runWizard($cacheName, $centerPane);
	}
	
	/**
	 * creates the wizard
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 10/24/05
	 */
	function &createWizard() {
		$wizard =& SimpleWizard::withText(
			"\n<h2>"._("Old Password")."</h2>".
			"\n<br \>[[old_password]]".
			"\n<h2>"._("New Password")."</h2>".
			"\n"._("Please enter your new password twice").
			"\n<br />[[new_password]]".
			"\n<br />[[n_p_again]]".
			"<table width='100%' border='0' style='margin-top:20px' >\n".
			"<tr>\n".
			"</td>\n".
			"<td align='left' width='50%'>\n".
			"[[_cancel]]".
			"<td align='right' width='50%'>\n".
			"[[_save]]".
			"</td></tr></table>");
			
		$pass1 =& $wizard->addComponent("old_password", new WPasswordField());
		$pass2 =& $wizard->addComponent("new_password", new WPasswordField());
		$pass3 =& $wizard->addComponent("n_p_again", new WPasswordField());
		
		$save =& $wizard->addComponent("_save", 
			WSaveButton::withLabel("Change Password"));
		$cancel =& $wizard->addComponent("_cancel", new WCancelButton());

		return $wizard;
	}
	
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 10/24/05
	 */
	function saveWizard ($cacheName) {
		$harmoni =& Harmoni::Instance();
		$authN =& Services::getService("AuthN");
		$tokenM =& Services::getService("AgentTokenMapping");
		$wizard =& $this->getWizard($cacheName);
		
		$properties =& $wizard->getAllValues();
		
		$dbAuthType =& new Type ("Authentication", "edu.middlebury.harmoni",
			"Concerto DB");		
		$id =& $authN->getUserId($dbAuthType);
		$it =& $tokenM->getMappingsForAgentId($id);
		
		while ($it->hasNext()) {
			$mapping =& $it->next();
			
			if ($mapping->getAuthenticationType() == $dbAuthType)
				$tokens =& $mapping->getTokens();
		}
		if (isset($tokens)) {
			$uname = $tokens->getUsername();
			if ($properties['new_password'] == $properties['n_p_again']) {
				$authNMethodManager =&
					Services::getService("AuthNMethodManager");
				$dbAuthMethod =&
					$authNMethodManager->getAuthNMethodForType($dbAuthType);

				$t_array = array("username" => $uname, 
					"password" => $properties['new_password']);
				$authNTokens =& $dbAuthMethod->createTokens($t_array);
				// Add it to the system
				if ($dbAuthMethod->supportsTokenUpdates()) {
					$dbAuthMethod->updateTokens($tokens, $authNTokens);
					$harmoni->request->startNamespace("harmoni-authentication");
					$harmoni->request->set("username", $uname);
					$harmoni->request->set("password", 
						$properties['new_password']);
					$harmoni->request->endNamespace();
					$authN->authenticateUser($dbAuthType);
					return TRUE;
				}
			}		
		 } else {
		 	print "Sorry, something didn't work, please try again";
			return FALSE;
		}
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/05
	 */
	function getReturnUrl () {
		$harmoni =& Harmoni::instance();
		
		return $harmoni->request->quickURL("user", "main");
	}

}
?>