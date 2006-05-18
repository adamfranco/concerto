<?php

require_once(HARMONI."GUIManager/Theme.class.php");

require_once(HARMONI."GUIManager/StyleCollection.class.php");
require_once(HARMONI."GUIManager/CornersStyleCollection.class.php");

require_once(HARMONI."GUIManager/StyleProperties/BackgroundColorSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/BackgroundImageSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/ColorSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/BorderSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/BorderTopSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/BorderRightSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/BorderBottomSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/BorderLeftSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/MarginSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/MarginLeftSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/PaddingSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/FontSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/FontFamilySP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/FontSizeSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/FontWeightSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/TextAlignSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/TextDecorationSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/DisplaySP.class.php");

/**
 * A simple theme with rounded boxes.
 *
 * @package harmoni.gui.themes
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class SimpleThemeBlack extends Theme {

	/**
	 * The constructor. All initialization and Theme customization is performed
	 * here.
	 * @access public
	 **/
	function SimpleThemeBlack() {
		$this->Theme("Simple Theme", "A simple theme with rounded boxes.");
		
		// =====================================================================
		// global Theme style
		$collection =& new StyleCollection("body", null, "Global Style", "Style settings affecting the overall look and feel.");
		$collection->addSP(new BackgroundColorSP("#000"));
		$collection->addSP(new ColorSP("#FFF"));
		//$collection->addSP(new FontFamilySP("Verdana"));
		$collection->addSP(new PaddingSP("0px"));
		$collection->addSP(new MarginSP("1px"));
		$this->addGlobalStyle($collection);

		$collection =& new StyleCollection("a", null, "Link Style", "Style settings affecting the look and feel of links.");
		$collection->addSP(new TextDecorationSP("underline"));
		$collection->addSP(new ColorSP("#FFF"));
// 		$collection->addSP(new FontWeightSP("bold"));
		$this->addGlobalStyle($collection);
// 
// 		$collection =& new StyleCollection("a:hover", null, "Link Hover Style", "Style settings affecting the look and feel of hover links.");
// 		$collection->addSP(new TextDecorationSP("underline"));
// 		$this->addGlobalStyle($collection);
// 
		// =====================================================================
		// Block 1 style
		$collection =& new StyleCollection("*.block1", "block1", "Block 1", "The main block where normally all of the page content goes in.");
// 		$collection->addSP(new BackgroundColorSP("#DDD"));
// 		$collection->addSP(new PaddingSP("10px"));
// 		$collection->addSP(new MarginSP("10px"));
		$this->addStyleForComponentType($collection, BLOCK, 1);
		
		// =====================================================================
		// Block 2 style
		$collection =& new CornersStyleCollection("*.block2", "block2", "Block 2", "A 2nd level block. Used for standard content");
		$collection->setBorderUrl("TopLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_TL.gif");
		$collection->setBorderUrl("TopRight", MYPATH."/themes/SimpleThemeBlack/images/corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_BL.gif");
		$collection->setBorderUrl("BottomRight", MYPATH."/themes/SimpleThemeBlack/images/corner_BR.gif");
		
		
		$collection->addSP(new BackgroundColorSP("#bbb"));
		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new BorderSP("1px", "solid", "#000"));
		$collection->addSP(new PaddingSP("10px"));
		$collection->addSP(new MarginSP("1px"));
		$collection->addSP(new TextAlignSP("left"));
		$this->addStyleForComponentType($collection, BLOCK, 2);

		$collection =& new StyleCollection("*.block2 a", "block2", "Block 2 Links", "Properties of links");
		$collection->addSP(new ColorSP("#000"));
		$this->addStyleForComponentType($collection, BLOCK, 2);
	
		// =====================================================================
		// Block 3 style
		$collection =& new CornersStyleCollection("*.block3", "block3", "Block 3", "A 3rd level block. Used for emphasized content such as Wizards.");
		$collection->setBorderUrl("TopLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_TL.gif");
		$collection->setBorderUrl("TopRight", MYPATH."/themes/SimpleThemeBlack/images/corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_BL.gif");
		$collection->setBorderUrl("BottomRight", MYPATH."/themes/SimpleThemeBlack/images/corner_BR.gif");
		
		
		$collection->addSP(new BackgroundColorSP("#333333"));
		$collection->addSP(new ColorSP("#FFF"));
// 		$collection->addSP(new BorderSP("1px", "solid", "#000"));
		$collection->addSP(new PaddingSP("10px"));
		$collection->addSP(new MarginSP("1px"));
		$collection->addSP(new TextAlignSP("left"));		
		$this->addStyleForComponentType($collection, BLOCK, 3);
		
// 		$collection =& new StyleCollection("*.block3 .thumbnail", "block3", "Block 4 Links", "Properties of links");
// 		$collection->addSP(new BorderSP("2px", "solid", "#000"));
// 		$this->addStyleForComponentType($collection, BLOCK, 3);
		
		
		
		// =====================================================================
		// Block 4 style
		$collection =& new CornersStyleCollection("*.block4", "block4", "Block 4", "A 4th level block. Used for alerts and highlit dialog boxes.");
		$collection->setBorderUrl("TopLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_TL.gif");
		$collection->setBorderUrl("TopRight", MYPATH."/themes/SimpleThemeBlack/images/corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_BL.gif");
		$collection->setBorderUrl("BottomRight", MYPATH."/themes/SimpleThemeBlack/images/corner_BR.gif");
		
		
		$collection->addSP(new BackgroundColorSP("#eeeeee"));
		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new BorderSP("1px", "solid", "#000"));
		$collection->addSP(new PaddingSP("10px"));
		$collection->addSP(new MarginSP("1px"));
		$collection->addSP(new TextAlignSP("left"));
		$this->addStyleForComponentType($collection, BLOCK, 4);
		
		$collection =& new StyleCollection("*.block4 a", "block4", "Block 4 Links", "Properties of links");
		$collection->addSP(new ColorSP("#000"));
		$this->addStyleForComponentType($collection, BLOCK, 4);
		
		
		// =====================================================================
		// Heading 1 style
		$collection =& new CornersStyleCollection("*.heading1", "heading1", "Heading 1", "A 1st level heading.");
		$collection->setBorderUrl("TopLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_TL.gif");
		$collection->setBorderUrl("TopRight", MYPATH."/themes/SimpleThemeBlack/images/corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_BL.gif");
		$collection->setBorderUrl("BottomRight", MYPATH."/themes/SimpleThemeBlack/images/corner_BR.gif");
		
		
		$collection->addSP(new BackgroundColorSP("#777"));
// 		$collection->addSP(new BorderSP("1px", "solid", "#000"));
		$collection->addSP(new PaddingSP("10px"));
		$collection->addSP(new MarginSP("1px"));
		$collection->addSP(new TextAlignSP("left"));		
		$collection->addSP(new ColorSP("#fff"));
		$collection->addSP(new FontSizeSP("175%"));
		$this->addStyleForComponentType($collection, HEADING, 1);

		
		// =====================================================================
		// Heading 2 style
		$collection =& new CornersStyleCollection("*.heading2", "heading2", "Heading 2", "A 2nd level heading.");
		$collection->setBorderUrl("TopLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_TL.gif");
		$collection->setBorderUrl("TopRight", MYPATH."/themes/SimpleThemeBlack/images/corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_BL.gif");
		$collection->setBorderUrl("BottomRight", MYPATH."/themes/SimpleThemeBlack/images/corner_BR.gif");
		
		
		$collection->addSP(new BackgroundColorSP("#ddd"));
// 		$collection->addSP(new BorderSP("1px", "solid", "#000"));
		$collection->addSP(new PaddingSP("10px"));
		$collection->addSP(new MarginSP("1px"));
		$collection->addSP(new TextAlignSP("left"));		
		$collection->addSP(new ColorSP("#fff"));
		$collection->addSP(new FontSizeSP("125%"));
		$this->addStyleForComponentType($collection, HEADING, 2);


		// =====================================================================
		// Header 1 style
		$collection =& new CornersStyleCollection("*.header1", "header1", "Header 1", "A 1st level header.");
		$collection->setBorderUrl("TopLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_TL.gif");
		$collection->setBorderUrl("TopRight", MYPATH."/themes/SimpleThemeBlack/images/corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_BL.gif");
		$collection->setBorderUrl("BottomRight", MYPATH."/themes/SimpleThemeBlack/images/corner_BR.gif");
		
		
		$collection->addSP(new BackgroundColorSP("#777"));
// 		$collection->addSP(new BorderSP("1px", "solid", "#000"));
		$collection->addSP(new PaddingSP("10px"));
		$collection->addSP(new MarginSP("1px"));
		$collection->addSP(new TextAlignSP("left"));		
		$collection->addSP(new ColorSP("#fff"));
// 		$collection->addSP(new FontSizeSP("200%"));
		$this->addStyleForComponentType($collection, HEADER, 1);


		// =====================================================================
		// Footer 1 style
		$collection =& new CornersStyleCollection("*.footer1", "footer1", "Footer 1", "A 1st level footer.");
		$collection->setBorderUrl("TopLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_TL.gif");
		$collection->setBorderUrl("TopRight", MYPATH."/themes/SimpleThemeBlack/images/corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_BL.gif");
		$collection->setBorderUrl("BottomRight", MYPATH."/themes/SimpleThemeBlack/images/corner_BR.gif");
		
		$collection->addSP(new BackgroundColorSP("#777"));
// 		$collection->addSP(new BorderSP("1px", "solid", "#000"));
		$collection->addSP(new PaddingSP("10px"));
		$collection->addSP(new MarginSP("1px"));
		$collection->addSP(new TextAlignSP("right"));
		
		$collection->addSP(new ColorSP("#fff"));
		$collection->addSP(new FontSizeSP("75%"));
		$this->addStyleForComponentType($collection, FOOTER, 1);

		
		// =====================================================================
		// Menu 1 style
		$collection =& new CornersStyleCollection("*.menu1", "menu1", "Menu 1", "A 1st level menu.");
		$collection->setBorderUrl("TopLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_TL.gif");
		$collection->setBorderUrl("TopRight", MYPATH."/themes/SimpleThemeBlack/images/corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleThemeBlack/images/corner_BL.gif");
		$collection->setBorderUrl("BottomRight", MYPATH."/themes/SimpleThemeBlack/images/corner_BR.gif");
		
		$collection->addSP(new BackgroundColorSP("#eeeeee"));
		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new BorderSP("1px", "solid", "#000"));
		$collection->addSP(new PaddingSP("10px"));
		$collection->addSP(new MarginSP("1px"));
		$collection->addSP(new TextAlignSP("left"));
		$this->addStyleForComponentType($collection, MENU, 1);
		
		// =====================================================================
		// Menu Heading 1 style
		$collection =& new StyleCollection("*.menuHeading1", "menuHeading1", "Menu Heading 1", "A 1st level menu heading.");
		$collection->addSP(new DisplaySP("block"));
		$collection->addSP(new BackgroundColorSP("#eeeeee"));
		$collection->addSP(new PaddingSP("5px"));
		//$collection->addSP(new FontWeightSP("bold"));
		$this->addStyleForComponentType($collection, MENU_ITEM_HEADING, 1);
		
		// =====================================================================
		// Menu Unselected Link 1 style
		$collection =& new StyleCollection("*.menuLink1_unselected a", "menuLink1_unselected", "Unselected Menu Link 1", "A 1st level unselected menu link.");
		$collection->addSP(new DisplaySP("block"));
		$collection->addSP(new BackgroundColorSP("#eeeeee"));
 		$collection->addSP(new ColorSP("#000"));
		$collection->addSP(new PaddingSP("5px"));
		$collection->addSP(new FontSizeSP("larger"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 1);
		
		$collection =& new StyleCollection("*.menuLink1_hover a:hover", "menuLink1_hover", "Menu Link 1 Hover", "A 1st level menu link hover behavior.");
		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 1);
		
		// =====================================================================
		// Menu Selected Link 1 style
		$collection =& new StyleCollection("*.menuLink1_selected a", "menuLink1_selected", "Selected Menu Link 1", "A 1st level selected menu link.");
		$collection->addSP(new DisplaySP("block"));
		$collection->addSP(new BackgroundColorSP("#ccc"));
 		$collection->addSP(new ColorSP("#000"));
		$collection->addSP(new PaddingSP("5px"));
		$collection->addSP(new FontSizeSP("larger"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 1);
		
		// =====================================================================
		// Menu Unselected Link 2 style
		$collection =& new StyleCollection("*.menuLink2_unselected a", "menuLink2_unselected", "Unselected Menu Link ", "A 2nd level unselected menu link.");
		$collection->addSP(new DisplaySP("block"));
		$collection->addSP(new BackgroundColorSP("#eeeeee"));
 		$collection->addSP(new ColorSP("#000"));
		$collection->addSP(new PaddingSP("5px"));
		$collection->addSP(new MarginLeftSP("10px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 2);
		
		$collection =& new StyleCollection("*.menuLink2_hover a:hover", "menuLink2_hover", "Menu Link 2 Hover", "A 2nd level menu link hover behavior.");
		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 2);
		
		// =====================================================================
		// Menu Selected Link 2 style
		$collection =& new StyleCollection("*.menuLink2_selected a", "menuLink2_selected", "Selected Menu Link 2", "A 2nd level selected menu link.");
		$collection->addSP(new DisplaySP("block"));
		$collection->addSP(new BackgroundColorSP("#ccc"));
 		$collection->addSP(new ColorSP("#000"));
		$collection->addSP(new PaddingSP("5px"));
		$collection->addSP(new MarginLeftSP("10px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 2);
		
		// =====================================================================
		// Menu Unselected Link 3 style
		$collection =& new StyleCollection("*.menuLink3_unselected a", "menuLink3_unselected", "Unselected Menu Link ", "A 3nd level unselected menu link.");
		$collection->addSP(new DisplaySP("block"));
		$collection->addSP(new BackgroundColorSP("#eeeeee"));
 		$collection->addSP(new ColorSP("#000"));
		$collection->addSP(new PaddingSP("5px"));
		$collection->addSP(new MarginLeftSP("20px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 3);
		
		$collection =& new StyleCollection("*.menuLink3_hover a:hover", "menuLink3_hover", "Menu Link 3 Hover", "A 3nd level menu link hover behavior.");
		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 3);
		
		// =====================================================================
		// Menu Selected Link 3 style
		$collection =& new StyleCollection("*.menuLink3_selected a", "menuLink3_selected", "Selected Menu Link 3", "A 3nd level selected menu link.");
		$collection->addSP(new DisplaySP("block"));
		$collection->addSP(new BackgroundColorSP("#ccc"));
 		$collection->addSP(new ColorSP("#000"));
		$collection->addSP(new PaddingSP("5px"));
		$collection->addSP(new MarginLeftSP("20px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 3);
		
		// =====================================================================
		// Menu Unselected Link 4 style
		$collection =& new StyleCollection("*.menuLink4_unselected a", "menuLink4_unselected", "Unselected Menu Link ", "A 4nd level unselected menu link.");
		$collection->addSP(new DisplaySP("block"));
		$collection->addSP(new BackgroundColorSP("#eeeeee"));
 		$collection->addSP(new ColorSP("#000"));
		$collection->addSP(new PaddingSP("5px"));
		$collection->addSP(new MarginLeftSP("30px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 4);
		
		$collection =& new StyleCollection("*.menuLink4_hover a:hover", "menuLink4_hover", "Menu Link 4 Hover", "A 4nd level menu link hover behavior.");
		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 4);
		
		// =====================================================================
		// Menu Selected Link 4 style
		$collection =& new StyleCollection("*.menuLink4_selected a", "menuLink4_selected", "Selected Menu Link 4", "A 4nd level selected menu link.");
		$collection->addSP(new DisplaySP("block"));
		$collection->addSP(new BackgroundColorSP("#ccc"));
 		$collection->addSP(new ColorSP("#000"));
		$collection->addSP(new PaddingSP("5px"));
		$collection->addSP(new MarginLeftSP("30px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 4);
		
		// =====================================================================
		// Menu Unselected Link 5 style
		$collection =& new StyleCollection("*.menuLink5_unselected a", "menuLink5_unselected", "Unselected Menu Link ", "A 5nd level unselected menu link.");
		$collection->addSP(new DisplaySP("block"));
		$collection->addSP(new BackgroundColorSP("#eeeeee"));
 		$collection->addSP(new ColorSP("#000"));
		$collection->addSP(new PaddingSP("5px"));
		$collection->addSP(new MarginLeftSP("40px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 5);
		
		$collection =& new StyleCollection("*.menuLink5_hover a:hover", "menuLink5_hover", "Menu Link 5 Hover", "A 5nd level menu link hover behavior.");
		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 5);
		
		// =====================================================================
		// Menu Selected Link 5 style
		$collection =& new StyleCollection("*.menuLink5_selected a", "menuLink5_selected", "Selected Menu Link 5", "A 5nd level selected menu link.");
		$collection->addSP(new DisplaySP("block"));
		$collection->addSP(new BackgroundColorSP("#ccc"));
 		$collection->addSP(new ColorSP("#000"));
		$collection->addSP(new PaddingSP("5px"));
		$collection->addSP(new MarginLeftSP("40px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 5);
	}


}

?>