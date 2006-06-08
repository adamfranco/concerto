<?php

/**
 * Run some post-configuration setup.
 *
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

if (!isset($_SESSION['post_config_setup_complete'])) {

	// Exhibition Repository
	$repositoryManager =& Services::getService("Repository");
	$idManager =& Services::getService("Id");
	$exhibitionRepositoryId =& $idManager->getId("edu.middlebury.concerto.exhibition_repository");

	$repositories =& $repositoryManager->getRepositories();
	$exhibitionRepositoryExists = FALSE;
	while ($repositories->hasNext()) {
		$repository =& $repositories->next();
		if ($exhibitionRepositoryId->isEqual($repository->getId())) {
			$exhibitionRepositoryExists = TRUE;
			break;
		}
	}
	
	if (!$exhibitionRepositoryExists) {

		$exhibitionRepositoryType =& new Type (
						'System Repositories', 
						'edu.middlebury.concerto', 
						'Exhibitions',
						'A Repository for holding Exhibitions, their Slide-Shows and Slides');
		$repository =& $repositoryManager->createRepository(
								  "All Exhibitions",
								  "This is a Repository that holds all of the Exhibitions in Concerto.",
								  $exhibitionRepositoryType,
								  $exhibitionRepositoryId);


		$slideSchemaId =& $idManager->getId("edu.middlebury.concerto.slide_record_structure");
		$slideSchema =& $repository->createRecordStructure(
							"Slide Schema", 
							"This is the schema used for exhibition slides.", 
							"text/plain", 
							"", 
							$slideSchemaId);
		$slideSchema->createPartStructure(
							"target id", 
							"The Id of the asset that this slide is referencing.", 
							new HarmoniType("Repository", "edu.middlebury.harmoni", "string"), 
							false, 
							false, 
							false,
							$idManager->getId("edu.middlebury.concerto.slide_record_structure.target_id"));
		$slideSchema->createPartStructure(
							"text position", 
							"The location of any text presented in the slide. (bottom, top, left, right)", 
							new HarmoniType("Repository", "edu.middlebury.harmoni", "string"), 
							false, 
							false, 
							false,
							$idManager->getId("edu.middlebury.concerto.slide_record_structure.text_position"));
		$slideSchema->createPartStructure(
							"display metadata", 
							"Whether or not to display the metadata of the associated asset referenced by target id.", 
							new HarmoniType("Repository", "edu.middlebury.harmoni", "boolean"), 
							false, 
							false, 
							false,
							$idManager->getId("edu.middlebury.concerto.slide_record_structure.display_metadata"));


	}
	
	$authZManager =& Services::getService("AuthZ");
	$idManager =& Services::getService("Id");
	$type =& new Type ("Authorization", "edu.middlebury.harmoni", "RecordStructures", "Functions for managing RecordStructures (a.k.a Schemas).");
	$qualifierHierarchyId = $idManager->getId("edu.middlebury.authorization.hierarchy");
	
	$functions =& $authZManager->getFunctions($type);
	if (!$functions->hasNext()) {
		$id =& $idManager->getId("edu.middlebury.authorization.modify_rec_struct");
		$function =& $authZManager->createFunction($id, "Modify RecordStructures", "Modify RecordStructures (a.k.a. Schemas) in a Repository (a.k.a. Collection).", $type, $qualifierHierarchyId);
		
		$id =& $idManager->getId("edu.middlebury.authorization.delete_rec_struct");
		$function =& $authZManager->createFunction($id, "Delete RecordStructures", "Delete RecordStructures (a.k.a. Schemas) in a Repository (a.k.a. Collection).", $type, $qualifierHierarchyId);
		
		$id =& $idManager->getId("edu.middlebury.authorization.convert_rec_struct");
		$function =& $authZManager->createFunction($id, "Convert RecordStructures", "Convert the data types/properties of RecordStructures (a.k.a. Schemas) in a Repository (a.k.a. Collection).", $type, $qualifierHierarchyId);
		
		$id =& $idManager->getId("edu.middlebury.authorization.modify_authority_list");
		$function =& $authZManager->createFunction($id, "Modify Authority List", "Modify the values that appear in the Authority Lists of a Repository (a.k.a. Collection).", $type, $qualifierHierarchyId);
	}
	
	$_SESSION['post_config_setup_complete'] = TRUE;
}