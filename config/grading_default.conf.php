<?php

/**
* Set up the GradingManager
*
* USAGE: Copy this file to grading.conf.php to set custom values.
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
Services::startManagerAsService("GradingManager", $context, $configuration);
