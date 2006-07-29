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
$courseManagamentRootId = "edu.middlebury.authorization.root";
$courseManagementId ="edu.middlebury.coursemanagement";
$canonicalCoursesId="edu.middlebury.coursemanagement.canonicalcourses";
$courseGroupsId ="edu.middlebury.coursemanagement.coursegroups";

$configuration->addProperty('hierarchy_id', $courseManagamentHierarchyId);
$configuration->addProperty('root_id', $courseManagamentRootId);
$configuration->addProperty('course_management_id', $courseManagementId);
$configuration->addProperty('canonical_courses_id', $canonicalCoursesId);
$configuration->addProperty('course_groups_id', $courseGroupsId);



for($year=2004; $year<2008; $year++){
	

	
	$array = array();
	$array['name'] = "Winter ".$year;
	$array['start'] = Timestamp::fromString($year."-01-01T00:00:00"); 
	$array['end'] = Timestamp::fromString($year."-02-01T00:00:00"); 
	$array['type'] = new Type("TermType","edu.middlebury","Winter");
	$terms[] = $array;
	
	$array = array();
	$array['name'] = "Spring ".$year;
	$array['start'] = Timestamp::fromString($year."-02-01T00:00:00"); 
	$array['end'] = Timestamp::fromString($year."-06-01T00:00:00"); 
	$array['type'] = new Type("TermType","edu.middlebury","Spring");
	$terms[] = $array;
	
	$array = array();
	$array['name'] = "Summer ".$year;
	$array['start'] = Timestamp::fromString($year."-06-01T00:00:00"); 
	$array['end'] = Timestamp::fromString($year."-09-01T00:00:00"); 
	$array['type'] = new Type("TermType","edu.middlebury","Summer");
	$terms[] = $array;
	
	$array = array();
	$array['name'] = "Fall ".$year;
	$array['start'] = Timestamp::fromString($year."-09-01T00:00:00"); 
	$array['end'] = Timestamp::fromString(($year+1)."-01-01T00:00:00"); 
	$array['type'] = new Type("TermType","edu.middlebury","Fall");
	$terms[] = $array;
	
}

$configuration->addProperty('terms_to_add', $terms);


Services::startManagerAsService("CourseManagementManager", $context, $configuration);

