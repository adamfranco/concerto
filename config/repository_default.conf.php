<?php

/**
 * Set up the RepositoryManager
 *
 * USAGE: Copy this file to repository.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
// :: Set up the RepositoryManager ::
	$repositoryHierarchyId = "edu.middlebury.authorization.hierarchy";
	define('REPOSITORY_ROOT_ID', "edu.middlebury.repositories_root");
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('hierarchy_id', $repositoryHierarchyId);
	$configuration->addProperty('default_parent_id', $arg1 = REPOSITORY_ROOT_ID);
	$configuration->addProperty('version_control_all', $arg2 = TRUE);
	$configuration->addProperty('use_filesystem_for_files', $arg3 = FALSE);
// 	$configuration->addProperty('file_data_path', $arg4 = MYPATH."/../concerto_data");
	Services::startManagerAsService("RepositoryManager", $context, $configuration);
	unset($arg0, $arg1, $arg2, $arg3, $arg4);