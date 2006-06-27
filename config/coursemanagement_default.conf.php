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
$configuration =& new ConfigurationProperties;
$configuration->addProperty('database_index', $dbID);

$courseManagamentHierarchyId = "edu.middlebury.authorization.hierarchy";
$courseManagementId ="edu.middlebury.coursemanagement";
$canonicalCoursesId="edu.middlebury.coursemanagement.canonicalcourses";
$courseGroupsId ="edu.middlebury.coursemanagement.coursegroups";

$configuration->addProperty('hierarchy_id', $courseManagamentHierarchyId);
$configuration->addProperty('course_management_id', $courseManagementId);
$configuration->addProperty('canonical_courses_id', $canonicalCoursesId);
$configuration->addProperty('course_groups_id', $courseGroupsId);

Services::startManagerAsService("CourseManagementManager", $context, $configuration);
