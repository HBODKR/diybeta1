<?php

$wgResourceModules['ext.groupspage.js'] = array(
		'scripts' => 'groupspagebutton.js',
		'styles' => array('groupspagebutton.css'),
		'messages' => array(
		),
		'dependencies' => array(
		),
		'position' => 'top',
		'localBasePath' => __DIR__ . '/js',
		'remoteExtPath' => 'GroupsPage/js',
);

// define namespaces :
define("NS_GROUP", 220);
define("NS_GROUP_TALK", 221);
define("NS_GROUP_BELONGING", 222);

// Add namespaces.
$wgExtraNamespaces[NS_GROUP] = "Group";
$wgExtraNamespaces[NS_GROUP_TALK] = "Group_talk";

// group belonging is a namespace for pages destinated to link on page to many other
// (not necessarely a group page)
// not used yet
$wgExtraNamespaces[NS_GROUP_BELONGING] = "Group_belonging";

$wgUFAllowedNamespaces[NS_GROUP] = true;


$wgAutoloadClasses['GroupsPage\\SpecialExploreGroups'] = __DIR__ . '/includes/SpecialExploreGroups.php';
$wgAutoloadClasses['GroupsPage\\Hook'] = __DIR__ . '/includes/Hook.php';
$wgAutoloadClasses['GroupsPage\\ApiGroupsPage'] = __DIR__ . '/includes/ApiGroupsPage.php';
$wgAutoloadClasses['GroupsPage\\GroupsPageCore'] = __DIR__ . '/includes/GroupsPageCore.php';
$wgAutoloadClasses['GroupsPage\\Buttons'] = __DIR__ . "/includes/Buttons.php";
$wgAutoloadClasses['GroupsPage\\ExploreGroupsTag'] = __DIR__ . "/includes/ExploreGroupsTag.php";

$wgSpecialPages['ExploreGroups'] = 'GroupsPage\\SpecialExploreGroups';


$wgHooks['LoadExtensionSchemaUpdates'][] = 'GroupsPage\\Hook::onLoadExtensionSchemaUpdates';
$wgHooks['ParserFirstCallInit'][] = 'GroupsPage\\Hook::onParserFirstCallInit';
$wgHooks['SkinTemplateNavigation'][] = "GroupsPage\\Buttons::onSkinTemplateNavigation";
$wgHooks['BeforePageDisplay'][] = "GroupsPage\\Buttons::onBeforePageDisplay";


$wgAPIModules['goupspage'] = 'GroupsPage\\ApiGroupsPage';


$GLOBALS['wgMessagesDirs']['GroupsPageGeneral'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['GroupsPage'] = __DIR__ . '/GroupsPage.i18n.php';

$wgGroupsPagesNamespacesEnabled = [
		NS_MAIN
];
