<?php

/**
 * The main configuration file.
 *
 * USAGE: Copy this file to harmoni.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

// :: set up the $harmoni object :: 
	$harmoni->config->set("defaultModule","home");
	$harmoni->config->set("defaultAction","welcome");
	$harmoni->config->set("sessionName","PHPSESSID");
	$harmoni->config->set("sessionUseCookies",true);
	$harmoni->config->set("sessionCookiePath","/");
	$harmoni->config->set("sessionCookieDomain","middlebury.edu");
	
	// tell harmoni to post-process all actions with this specified action.
	// the action takes the result from previous actions and builds a display
	// screen from it.
	
	// until polyphony has been updated to use this functionality, we must ignore
	// it.
	$postProcessIgnoreList = array(
									"language.*",
									"repository.*",
									
									// and our special no-theme action
									"example.no_theme"
								);
	
	$harmoni->setPostProcessAction("window.display", $postProcessIgnoreList);
	
	$context =& new OsidContext;
	$context->assignContext('harmoni', $harmoni);