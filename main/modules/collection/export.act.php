<?php
/**
 * @since 9/27/05
 * @package concerto.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/Exporter/XMLRepositoryExporter.class.php");
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * This is the export action for a collection
 * 
 * @since 9/27/05
 * @package concerto.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class exportAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 9/27/05
	 */
	function isAuthorizedToExecute () {
		$harmoni =& Harmoni::instance();
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");

		$harmoni->request->startNamespace('export');

		$return = $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"),
			$idManager->getId(RequestContext::value('collection_id')));

		$harmoni->request->endNamespace();
		
		// Check that the user can create an asset here.
		return $return;
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 9/27/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to export this <em>Collection</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 9/28/05
	 */
	function getHeadingText () {
		$harmoni =& Harmoni::Instance();
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		
		$harmoni->request->startNamespace('export');

		$repository =& $repositoryManager->getRepository(
				$idManager->getId($harmoni->request->get("collection_id")));
		
		$harmoni->request->endNamespace();

		return _("Export the <em>".$repository->getDisplayName()."</em> Collection out of <em>Concerto</em>");
	}

	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 9/27/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::Instance();
		$harmoni->request->startNamespace('export');
		$harmoni->request->passthrough('collection_id');

		$centerPane =& $this->getActionRows();

		$cacheName = 'export_collection_wizard_'.
			$harmoni->request->get('collection_id');
		
		$this->runWizard ( $cacheName, $centerPane );
		$harmoni->request->endNamespace();
	}
		
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 9/28/05
	 */
	function &createWizard () {
		$harmoni =& Harmoni::Instance();
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		

		$repository =& $repositoryManager->getRepository(
				$idManager->getId($harmoni->request->get("collection_id")));
				
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleWizard::withText(
			"\n<h3>"._("Click <em>Export</em> to Export the <em>").
			$repository->getDisplayName().
			_("</em> Collection out of <em>Concerto</em>")."</h3>".
			"\n<br/>"._("The current content of <em>").
			$repository->getDisplayName().
			_("</em> will be exported and presented as an archive for download.  Once the archive is downloaded click <em>Cancel</em> to go back.").
			"\n<br/><h3>"._("Archive:")."</h3>".
			"<table border='0' style='margin-top:20px' >\n" .
			"\n<tr><td>"._("Archive Name: ")."</td>".
			"<td>[[filepath]]</td></tr>".
			"\n<tr><td>"._("Compression: ")."</td>".
			"<td>[[compression]]</td></tr>".
			"<tr>\n" .
			"<td align='left'>\n" .
			"[[_cancel]]".
			"</td>\n" .
			"<td align='right'>\n" .
			"[[_save]]".
			"</td></tr></table>");

		
		// Create the properties.
		$fileNameProp =& $wizard->addComponent("filepath", new WTextField());
//		$fileNameProp->setErrorText("<nobr>"._("The archive name must not have an extension")."</nobr>");
// 		$fileNameProp->setErrorRule(new WECRegex("\."));
		
 		$type =& $wizard->addComponent("compression", new WSelectList());
 		$type->setValue(".tar.gz");
 		$type->addOption(".tar.gz", _("gzip"));
//  		$type->addOption(".zip", _("zip"));
//  		$type->addOption(".bz2", _("bz2"));

		$save =& $wizard->addComponent("_save", 
			WSaveButton::withLabel("Export"));
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
	 * @since 4/28/05
	 */
	function saveWizard ( $cacheName ) {
		$harmoni =& Harmoni::Instance();
		$idManager =& Services::getService("Id");

		$wizard =& $this->getWizard($cacheName);
				
		$properties =& $wizard->getAllValues();
		
		$repositoryId =& $idManager->getId(
			RequestContext::value('collection_id'));
		
		$exporter =& XMLRepositoryExporter::withCompression(
			$properties['compression']);

		$file = $exporter->export($repositoryId);

		shell_exec('cd '.str_replace(":", "\:", $file).";".
		'tar -czf /tmp/'.$properties['filepath'].$properties['compression'].
		" *");
		

		header("Content-type: application/x-gzip");
		header('Content-Disposition: attachment; filename="'.
			$properties['filepath'].$properties['compression'].'"');

		print file_get_contents(
			"/tmp/".$properties['filepath'].$properties['compression']);
		exit();

		return TRUE;
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		$harmoni =& Harmoni::instance();
		
		$harmoni->request->forget('collection_id');
		
		return $harmoni->request->quickURL("collections", "namebrowse");
	}
}

?>