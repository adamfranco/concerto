<?php

/**
 * Set up the CourseManagementManager
 *
 * USAGE: Copy this file to coursemanagament.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
// :: Set up the CourseManagementManager ::
	$courseManagamentHierarchyId = "edu.middlebury.authorization.hierarchy";
	$configuration =& new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('hierarchy_id', $courseManagamentHierarchyId);
	Services::startManagerAsService("CourseManagementManager", $context, $configuration);
	