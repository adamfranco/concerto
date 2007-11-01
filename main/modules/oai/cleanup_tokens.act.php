<?php
/**
 * @since 10/30/07
 * @package concerto.oai
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This action cleans up the OAI resumption tokens
 * 
 * @since 10/30/07
 * @package concerto.oai
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class cleanup_tokensAction
	extends Action
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 10/30/07
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 10/30/07
	 */
	function execute () {
		if (RequestContext::value('help') || RequestContext::value('h') || RequestContext::value('?'))
			throw new HelpRequestedException(
"This is a command line script that will clean up old OAI resumption tokens. 
It takes no arguments or parameters.
");
		
		$harmoni = Harmoni::instance();
		$config = $harmoni->getAttachedData('OAI_CONFIG');
		$tokenDir = $config->getProperty('OAI_TOKEN_DIR');
		
		// go through the token directory
		if (file_exists($tokenDir)) {
			if ($handle = opendir($tokenDir)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						$path = $tokenDir . DIRECTORY_SEPARATOR . $file;
						// Delete tokens older than 1 hour
						if (filectime($path) < (time() - 3600)) {
							unlink($path);
						}
					}
				}
				closedir($handle);
			}
		}
	}
}

?>