<?php
/**
 * @package concerto.printers
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/**
 * A static printer class for printing common repository info
 *
 * @package concerto.printers
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

class RepositoryPrinter {
		
	/**
	 * Die constructor for static class
	 */
	function RepositoryPrinter () {
		die("Static class RepositoryPrinter can not be instantiated.");
	}
	
	/**
	 * Print links for the various functions that are possible to do with this
	 * Repository.
	 * 
	 * @param object Repository $repository The Repository to print the links for.
	 * @return void
	 * @access public
	 * @date 8/6/04
	 */
	function printRepositoryFunctionLinks (& $harmoni, & $repository) {		
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$repositoryId =& $repository->getId();
		
		$links = array();
		
		$actionString = $harmoni->getCurrentAction();
		$url =& $harmoni->request->mkURL();	
		$url->setValue("collection_id", $repositoryId->getIdString());
		
		// Authorization Icon
		print AuthZPrinter::getAZIcon($repositoryId);
		print " &nbsp; ";
		
	//===== Browse Link =====//
		if ($authZ->isUserAuthorizedBelow(
				$idManager->getId("edu.middlebury.authorization.view"),
				$repositoryId)) 
		{
			if ($actionString != "collection.browse") {
				$url->setModuleAction("collection", "browse");
				$links[] = "<a href='".$url->write()."'>";
				$links[count($links) - 1] .= _("Browse")."</a>";
			} else {
				$links[] = _("Browse");
			}
	//===== TypeBrowse Link =====//
// 			if ($actionString != "collection.typebrowse") {
// 				$url->setModuleAction("collection", "typebrowse");
// 				$links[] = "<a href='".$url->write()."'>";
// 				$links[count($links) - 1] .= _("Browse by Type")."</a>";
// 			} else {
// 				$links[] = _("Browse by Type");
// 			}
//	//===== Search Link =====//
// 			if ($actionString != "collection.search") {
// 				$url->setModuleAction("collection", "search");
// 				$links[] = "<a href='".$url->write()."'>";
// 				$links[count($links) - 1] .= _("Search")."</a>";
// 			} else {
// 				$links[] = _("search");
// 			}
		}
		
	 //===== Add Link =====//
	 	if ($authZ->isUserAuthorized(
	 			$idManager->getId("edu.middlebury.authorization.add_children"), 
	 			$repositoryId)) {
			$url->setModuleAction("asset", "add");
			$links[] = "<a href='".$url->write()."'>";
			$links[count($links) - 1] .= _("Add")."</a>";
	//===== Import Link =====//
			$harmoni->request->startNamespace("import");
			$links[] = "<a href='".$harmoni->request->quickURL(
				"collection", "import",
				array("collection_id" => $repositoryId->getIdString()))."'>";
			$links[count($links) - 1] .= _("Import")."</a>";
			$harmoni->request->endNamespace();
		}
			
	//===== Export Link =====//
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$repositoryId)) {
			$harmoni->request->startNamespace('export');
			$links[] = "<a href='".$harmoni->request->quickURL(
				"collection", "export",
				array("collection_id" => $repositoryId->getIdString()))."'>";
			$links[count($links) - 1] .= _("Export")."</a>";
			$harmoni->request->endNamespace();
		}
	//===== Edit Link =====//
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.modify"), 
				$repositoryId)) 
		{
		
			$params = array("collection_id" => $repositoryId->getIdString(),
							RequestContext::name("starting_number") => RequestContext::value("starting_number"),
							RequestContext::name("limit_by") => RequestContext::value("limit_by"),
							RequestContext::name("type") => RequestContext::value("type"),
							RequestContext::name("searchtype") => RequestContext::value("searchtype"));			
			if (RequestContext::value("searchtype")) {
				$searchModuleManager =& Services::getService("RepositorySearchModules");
				foreach ($searchModuleManager->getCurrentValues(Type::fromString(RequestContext::value("searchtype"))) as $key => $value) {
					$params[$key] = $value;
				}
			}		
			$harmoni->history->markReturnURL("concerto/collection/edit-return",
				$harmoni->request->mkURL(null, null, $params));
		
			$url->setModuleAction("collection", "edit");
			$links[] = "<a href='".$url->write()."'>";
			$links[count($links) - 1] .= _("Edit")."</a>";
			
		}
		
	//===== Edit Schemas =====//
		// Check that the user can modify Schemas for this collection
		$recStructFunctions =& $authZ->getFunctions(
								new Type (	"Authorization", 
											"edu.middlebury.harmoni", 
											"RecordStructures"));
		$canEditStructures = false;
		while ($recStructFunctions->hasNext()) {
			$function =& $recStructFunctions->next();
			if ($authZ->isUserAuthorized($function->getId(), $repositoryId))
				$canEditStructures = true;
			if ($authZ->isUserAuthorized($function->getId(), 
				$idManager->getId("edu.middlebury.authorization.root")))
			{
				$canEditStructures = true;
			}
		}
		
		if ($canEditStructures) {
			// Schema Editing
			ob_start();
			print "\n<select";
			print " onchange=\"var url = '";
			print $harmoni->request->quickURL(
				"schema", "edit", array(
					"collection_id" => $repositoryId->getIdString(),
					"recordstructure_id" => "XXXXXXXX"));
			print "'; ";
			print "url = url.replace(/XXXXXXXX/, this.value); ";
			print "url = url.urlDecodeAmpersands(); ";
			print "if (this.value) { ";
			print 		"window.location = url; ";
			print "} ";
			print "\">";
			print "\n\t<option value=''>"._("Edit Schema...")."</option>";
			
			$localStructures = '';
			$globalStructures = '';
			$fileId = $idManager->getId("FILE");
			$remoteFileId = $idManager->getId("REMOTE_FILE");
			
			$setManager =& Services::getService("Sets");
			$set =& $setManager->getPersistentSet($repositoryId);
			$set->reset();
			while ($set->hasNext()) {
				$recStructId =& $set->next();
				if (!$recStructId->isEqual($fileId) && !$recStructId->isEqual($remoteFileId)) {
					ob_start();
					$recStruct =& $repository->getRecordStructure($recStructId);
						$harmoni->history->markReturnURL(
								"concerto/schema/edit-return/".$recStructId->getIdString());
						print "\n\t\t<option value='".$recStructId->getIdString()."'";
						print ">";
						print $recStruct->getDisplayName();
						print "</option>";
					if (preg_match("/^Repository::.+$/i", $recStructId->getIdString())) {
						$localStructures .= ob_get_clean();
					} else {
						$globalStructures .= ob_get_clean();
					}
				}
			}
			print "\n\t<optgroup label='"._("Local Schemas")."'>";
			print $localStructures;
			print "\n\t</optgroup>";
			print "\n\t<optgroup label='"._("Global Schemas")."'>";
			print $globalStructures;
			print "\n\t</optgroup>";
			print "\n</select>";
			
			
			$links[] = ob_get_clean();
		}
	//===== Delete Link =====//
		if ($authZ->iSUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.delete"),
				$repositoryId))
		{
			ob_start();
			print "<a href='Javascript:deleteRepository(\"".
				$repositoryId->getIdString()."\", \"".
				$harmoni->request->quickURL("collection", "delete",
				array("collection_id" => $repositoryId->getIdString())).
				"\");'>";
			print _("Delete")."</a>";
			$links[] = ob_get_contents();
			ob_end_clean();
			
			print "\n<script type='text/javascript'>\n//<![CDATA[";
			print "\n	function deleteRepository(repositoryId, url) {";
			print "\n		if (confirm(\""._("Are you sure you want to delete this Collection and all of its Assets?")."\") && confirm(\""._("Clicking OK will permenantly delete this Collection and all of the Assets in it.\\nAre you REALLY sure you want to delete this Collection and all of its Assets?")."\")) {";
			print "\n			window.location = url;";
			print "\n		}";
			print "\n	}";
			print "\n//]]>\n</script>\n";
		}
	
		print  implode("\n\t | ", $links);
	}
}
?>