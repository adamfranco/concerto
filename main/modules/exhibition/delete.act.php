<?php
/**
 * @package concerto.modules.exhibition
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General \
Public License (GPL)
*
* @version $Id$
*/
require_once(MYDIR."/main/library/abstractActions/AssetAction\
.class.php");

class deleteAction extends AssetAction
{

    /**
     * Check Authorizations
     *
     * @return boolean
     * @access public
     * @since 4/26/05
     */
    function isAuthorizedToExecute () {
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
				 $idManager->getId("edu.middlebury.authorization.modify"),
				 $this->getAssetId());
    }

    /**
     * Return the "unauthorized" string to pring
     *
     * @return string
     * @access public
     * @since 4/26/05
     */
    function getUnauthorizedMessage () {
      return _("You are not authorized to edit this<em>Exhibition</em>.");
    }
    
    /**
     * Return the URL that this action should return to when completed.
     *
     * @return string
     * @access public
     * @since 4/28/05
     */
    function getReturnUrl () {
      $assetId =& $this->getAssetId();
      $repositoryId =& $this->getRepositoryId();
      $repository =& $this->getRepository($repositoryId);
      $repository->deleteAsset($assetId);

      return MYURL."/exhibition/browse/".$repositoryId->getIdString()."/";
    }
  
}


