<?php

/**
 * Set up the LanguageLocalization system
 *
 * 
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
// :: Set up language directories ::
	$configuration =& new ConfigurationProperties;
	$configuration->addProperty('default_language', $arg0 = 'en_US');
	$configuration->addProperty('applications', $arg1 = array (
		'concerto' => MYDIR.'/main/languages',
		'polyphony'=> POLYPHONY.'/main/languages'
	));
	unset ($arg0, $arg1);
	Services::startManagerAsService("LanguageManager", $context, $configuration);