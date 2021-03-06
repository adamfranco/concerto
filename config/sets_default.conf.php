<?php

/**
 * Set up the SetsManager
 *
 * USAGE: Copy this file to sets.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
// :: Set up the Sets Manager ::
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	Services::startManagerAsService("SetManager", $context, $configuration);