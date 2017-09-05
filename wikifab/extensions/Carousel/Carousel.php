<?php
# Alert the user that this is not a valid access point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	exit( 1 );
}

$dir = dirname( __FILE__ );
 
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'Carousel',
	'descriptionmsg' => 'carousel-desc',
	'version' => '1.0',
	'author' => array( 'Pierre Boutet' ),
	'url' => 'https://www.wikifab.org'
);


$wgHooks['ParserFirstCallInit'][] = 'wfCarouselFunctions';
//$wgHooks['ParserAfterTidy'][] = 'wfCarouselFunctions';

# Parser function to insert a link changing a tab.
function wfCarouselFunctions( $parser ) {
	$parser->setFunctionHook( 'carousel', array('Carousel', 'addParser' ));
	//$parser->setFunctionTagHook('displayTutorialsList', array('Carousel', 'addSampleParser' ), array());
	return true;
}
require_once(__DIR__ . "/includes/Carousel.php");

$wgAutoloadClasses['Carousel'] = __DIR__ . "/includes/Carousel.php";
//$wgMessagesDirs['Carousel'][] = __DIR__ . "/i18n"; 

// Allow translation of the parser function name
$wgExtensionMessagesFiles['Carousel'] = __DIR__ . '/Carousel.i18n.php';
