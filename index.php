<?

// Define a Constant reference to this application directory.
define("MYDIR",dirname(__FILE__));
define("MYPATH",str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(__FILE__)));
define("MYURL",str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(__FILE__))."/index.php");

define("OKI_VERSION", 2);
define("LOAD_THEMES", false);
define("LOAD_GUI", true);
define("LOAD_AUTHENTICATION", false);

/******************************************************************************
 * Include Harmoni - required
 ******************************************************************************/
require_once "../harmoni/harmoni.inc.php";

/******************************************************************************
 * Include Polyphony
 ******************************************************************************/
require_once "../polyphony/polyphony.inc.php";

/******************************************************************************
 * Include our libraries
 ******************************************************************************/
require_once "main/library/ConcertoMenuGenerator.class.php";
require_once "main/library/printers/AssetPrinter.static.php";
require_once "main/library/printers/RepositoryPrinter.static.php";

/******************************************************************************
 * Include any theme classes we want to use. They need to be included prior
 * to starting the session so that they can be restored properly.
 ******************************************************************************/
require_once(HARMONI."GUIManager/Themes/SimpleLinesTheme.class.php");
require_once(HARMONI."GUIManager/GUIManager.class.php");



/******************************************************************************
 * Start the session so that we can use the session for storage.
 ******************************************************************************/
$harmoni->startSession();
//printpre($_SESSION);

/******************************************************************************
 * Include our configs
 ******************************************************************************/
require_once "config/harmoni.inc.php";


/******************************************************************************
 * 	Execute our actions
 ******************************************************************************/

$harmoni->execute();
// printpre($_SESSION);
// debug::output(session_id());
// Debug::printAll();

?>
