<?php
# Alert the user that this is not a valid access point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	exit( 1 );
}

$dir = dirname( __FILE__ );
 
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'Wf Sample display',
	'descriptionmsg' => 'wfsampledisplay-desc',
	'version' => '1.0',
	'author' => array( 'Pierre Boutet' ),
	'url' => 'https://www.wikifab.org'
);


$wgHooks['ParserFirstCallInit'][] = 'WfSampleDisplayParserFunctions';

# Parser function to insert a link changing a tab.
function wfSampleDisplayParserFunctions( $parser ) {
	$parser->setFunctionHook( 'displayTutorialsList', array('WfSampleDisplay', 'addSampleParser' ));
	//$parser->setFunctionTagHook('displayTutorialsList', array('WfSampleDisplay', 'addSampleParser' ), array());
	return true;
}

$wgAutoloadClasses['WfSampleDisplay'] = __DIR__ . "/includes/WfSampleDisplay.php";
$wgMessagesDirs['WfSampleDisplay'][] = __DIR__ . "/i18n"; 

// Allow translation of the parser function name
$wgExtensionMessagesFiles['WfSampleDisplay'] = __DIR__ . '/WfSampleDisplay.i18n.php';