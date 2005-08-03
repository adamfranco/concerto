<?php


if (!isset($_SESSION['post_config_setup_complete'])) {

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
	}
	
	
	$_SESSION['post_config_setup_complete'] = TRUE;
}