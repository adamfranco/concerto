<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Browse Collections By Name")));
$actionRows->addComponent($introHeader);

$text = "";
$text .= "<p>";
$text .= _("Below are listed the availible <em>Collections</em>, organized by type, then name.");
$text .= "</p>\n<p>";
$text .= _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
$text .= "</p>";

$introText =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$introText->addComponent(new Content($text));
$actionRows->addComponent($introText);

$drManager =& Services::getService("DR");

// Get all the types
$types =& $drManager->getDigitalRepositoryTypes();
// put the drs into an array and order them.
$typeArray = array();
while($types->hasNext()) {
	$type =& $types->next();
	$typeArray[$type->getDomain()." ".$type->getAuthority()." ".$type->getKeyword()] =& $type;
}
ksort($typeArray);

$text = "\n<ul>";
if (count($typeArray)) {
	foreach (array_keys($typeArray) as $typeName) {
		$type =& $typeArray[$typeName];
		
		$text .= "\n<li>";
		$text .= "\n\t<strong>".$type->getDomain()."::".$type->getAuthority()."::".$type->getKeyword()."</strong>";
	
		// Get the DRs
		$allDRs =& $drManager->getDigitalRepositoriesByType($type);
		
		// put the drs into an array and order them.
		// @todo, do authorization checking
		$drArray = array();
		while($allDRs->hasNext()) {
			$dr =& $allDRs->next();
			$drArray[$dr->getDisplayName()] =& $dr;
		}
		ksort($drArray);
		
		// Print out the DRs
		$text .= "\n<ul>";
		if (count($drArray)) {
			foreach (array_keys($drArray) as $name) {
				$dr =& $drArray[$name];
				$drId =& $dr->getId();
				$drType =& $dr->getType();
				
				$text .= "\n<li>";
				$text .= "\n\t<strong>".$dr->getDisplayName()."</strong> - "._("ID#").": ".
						$drId->getIdString();
				$text .= "<br><em>".$dr->getDescription()."</em>";
		//		$text .= "<div style='font-size: smaller'><br>";
		//		$text .= $drType->getDomain() ."::". $drType->getAuthority() ."::". $drType->getKeyword() ."<br><em>".$drType->getDescription()."</em></span>";
				
				// @todo User AuthZ to decide if we should print links.
				$text .= "<br>";
				$links = array();
				
				$links[] = "<a href='".MYURL."/collection/browse/".$drId->getIdString()."/'>";
				$links[count($links) - 1] .= _("browse")."</a>";
				
				$links[] = "<a href='".MYURL."/collection/search/".$drId->getIdString()."/'>";
				$links[count($links) - 1] .= _("search")."</a>";
				
				$links[] = "<a href='".MYURL."/collection/edit/".$drId->getIdString()."/'>";
				$links[count($links) - 1] .= _("edit")."</a>";
				
				$text .= implode(" | ", $links);
				$text .= "\n<br></li>";
			}
			
		} else {
			$text .= "\n<li>\n\t"._("No <em>Collections</em> are availible for this type.")."\n</li>";
		}
		$text .= "\n</ul>";
		$text .= "\n<br></li>";
	}
} else {
	$text .= "\n<li>\n\t"._("No <em>Collections</em> are availible.")."\n</li>";
}
$text .= "\n</ul>";

$drBlock =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$drBlock->addComponent(new Content($text));
$actionRows->addComponent($drBlock);


// return the main layout.
return $mainScreen;