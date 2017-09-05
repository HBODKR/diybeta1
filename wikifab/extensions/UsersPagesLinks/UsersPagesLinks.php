<?php

$wgExtensionCredits['api'][] = array(
		'path' => __FILE__,
		'name' => 'UsersPagesLinks API',
		'description' => 'API to add link between user and pages',
		'version' => 1,
		'author' => 'Pierre Boutet',

		/* The URL to a wiki page/web page with information about the extension,
		 which will appear on Special:Version. */
		//'url' => 'https://www.mediawiki.org/wiki/API:Extensions',

);

$wgResourceModules['ext.userspageslinks.js'] = array(
		'scripts' => 'userspageslinksbutton.js',
		'styles' => array('userspageslinksbutton.css'),
		'messages' => array(
		),
		'dependencies' => array(
		),
		'position' => 'top',
		'localBasePath' => __DIR__ . '/module',
		'remoteExtPath' => 'UsersPagesLinks/module',
);


$wgAutoloadClasses['UsersPagesLinks\\UsersPagesLinks'] = __DIR__ . "/includes/UsersPagesLinks.php";
$wgAutoloadClasses['UsersPagesLinks\\UsersPagesLinksCore'] = __DIR__ . "/includes/UsersPagesLinksCore.php";
$wgAutoloadClasses['UsersPagesLinks\\SpecialEditUsersWatchList'] = __DIR__ . "/includes/SpecialEditUsersWatchList.php";
$wgAutoloadClasses['UsersPagesLinks\\ApiUsersPagesLinks'] = __DIR__ . "/includes/ApiUsersPagesLinks.php";
$wgAutoloadClasses['UsersPagesLinks\\Buttons'] = __DIR__ . "/includes/Buttons.php";

$wgHooks['LoadExtensionSchemaUpdates'][] = 'UsersPagesLinks\\UsersPagesLinks::onLoadExtensionSchemaUpdates';
$wgHooks['ParserFirstCallInit'][] = "UsersPagesLinks\\Buttons::onParserFirstCallInit";
$wgHooks['SkinTemplateNavigation'][] = "UsersPagesLinks\\Buttons::onSkinTemplateNavigation";
$wgHooks['BeforePageDisplay'][] = "UsersPagesLinks\\Buttons::onBeforePageDisplay";


$wgExtensionCredits['specialpage'][] = array(
		'path' => __FILE__,
		'name' => 'UsersWatchList',
		'author' => 'Pierre Boutet',
		'description' => "View and edit users watch list.",
		'descriptionmsg' => 'userswatchlist-desc',
		'version' => '0.2.0',
);

$wgMessagesDirs['UsersPagesLinks'] = __DIR__ . "/i18n";

$wgExtensionMessagesFiles['UsersPagesLinksMagicWords'] = __DIR__ . '/UsersPagesLinks.i18n.php';


$wgAPIModules['userspageslinks'] = 'UsersPagesLinks\\ApiUsersPagesLinks';

$wgUsersPagesLinksTypes = [
		'star',
		'ididit',
		'member'
];

$wgUsersPagesLinksTypesUndoLabelsKey = [
		'member' => 'userspageslinks-unmember'
];

$wgUsersPagesLinksFoNamespaces = [
		NS_MAIN  => [
				'ididit',
				'star'
		]
];
if(defined("NS_GROUP")) {
	$wgUsersPagesLinksFoNamespaces [NS_GROUP] = [
		'member'
	];
}
