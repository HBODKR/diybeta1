<?php
# Alert the user that this is not a valid access point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	exit( 1 );
}

$dir = dirname( __FILE__ );

$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'Users Watch Button',
	'descriptionmsg' => 'userswatchbutton-desc',
	'version' => '1.0',
	'author' => array( 'Pierre Boutet' ),
	'url' => 'https://www.wikifab.org'
);

$wgResourceModules['ext.userswatchbutton.js'] = array(
		'scripts' => 'userswatchbutton.js',
		'styles' => array(),
		'messages' => array(
		),
		'dependencies' => array(
		),
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '',
		'remoteExtPath' => 'UsersWatchButton',
);

$wgResourceModules['ext.userswatchbutton.icons'] = array(
		'styles' => array(),
		'messages' => array(
		),
		'dependencies' => array(
		),
		'position' => 'top',
		'localBasePath' => __DIR__ . '/icons',
		'remoteExtPath' => 'UsersWatchButton/icons',
);


$wgHooks['ParserFirstCallInit'][] = 'userswatchbuttonFunctions';
$wgHooks['BeforePageDisplay'][] = 'UsersWatchButton::BeforePageDisplay';

// hooks for notifications with Echo :
$wgHooks['ParserFirstCallInit'][] = 'CombinedWatchlist\\NotificationHook::onParserFirstCallInit';
$wgHooks['RecentChange_save'][] = 'CombinedWatchlist\\NotificationHook::onRecentChange_save';
$wgHooks['EchoGetDefaultNotifiedUsers'][] = 'CombinedWatchlist\\NotificationHook::onEchoGetDefaultNotifiedUsers';
$wgHooks['EchoGetBundleRules'][] = 'CombinedWatchlist\\NotificationHook::onEchoGetBundleRules';
$wgHooks['EchoGetDefaultNotifiedUsers'][] = 'NewFollowerNotif\\NotificationHook::onEchoGetDefaultNotifiedUsers';
$wgHooks['EchoGetBundleRules'][] = 'NewFollowerNotif\\NotificationHook::onEchoGetBundleRules';
$wgHooks['UsersWatchList-newFollower'][] = 'NewFollowerNotif\\NotificationHook::onNewFollower';
$wgHooks['BeforeCreateEchoEvent'][] = 'NewFollowerNotif\\NotificationHook::onBeforeCreateEchoEvent';

# Parser function to insert a link changing a tab.
function userswatchbuttonFunctions( $parser ) {
	$parser->setFunctionHook( 'userswatchbutton', array('UsersWatchButton', 'addParser' ));
	//$parser->setFunctionTagHook('displayTutorialsList', array('WfAuthorDiv', 'addSampleParser' ), array());
	return true;
}


//require_once(__DIR__ . "/includes/UsersWatchButton.php");

$wgAutoloadClasses['UsersWatchButton'] = __DIR__ . "/includes/UsersWatchButton.php";
$wgAutoloadClasses['CombinedWatchlist\\EventFormatter'] = __DIR__ . "/includes/CombinedWatchlist/EventFormatter.php";
$wgAutoloadClasses['CombinedWatchlist\\NotificationPresentationModel'] = __DIR__ . "/includes/CombinedWatchlist/NotificationPresentationModel.php";
$wgAutoloadClasses['CombinedWatchlist\\NotificationHook'] = __DIR__ . "/includes/CombinedWatchlist/NotificationHook.php";
$wgAutoloadClasses['CombinedWatchlist\\WatchlistModelConnector'] = __DIR__ . "/includes/CombinedWatchlist/WatchlistModelConnector.php";
$wgAutoloadClasses['NewFollowerNotif\\NotificationPresentationModel'] = __DIR__ . "/includes/NewFollowerNotif/NotificationPresentationModel.php";
$wgAutoloadClasses['NewFollowerNotif\\NotificationHook'] = __DIR__ . "/includes/NewFollowerNotif/NotificationHook.php";
$wgMessagesDirs['UsersWatchButton'][] = __DIR__ . "/i18n";

// Allow translation of the parser function name
$wgExtensionMessagesFiles['UsersWatchButtonMagic'] = __DIR__ . '/UsersWatchButton.i18n.php';


// connection with the 'Notification' extension ('Echo')
// Notification on watchlist
$wgEchoNotificationCategories['combinedwatchlist'] = array(
		'priority' => 3,
		'tooltip' => 'echo-pref-tooltip-combinedwatchlist',
);
$wgEchoNotifications['combinedwatchlist'] = array(
		'category' => 'combinedwatchlist',
		//'bundle' => array( 'web' => true, 'email' => true ),
		'formatter-class' => 'CombinedWatchlist\\EventFormatter',
		'presentation-model' => 'CombinedWatchlist\\NotificationPresentationModel',
		'title-message' => 'combinewatchlist-notif-title-message',
		'email-subject-message' => 'combinewatchlist-notif-email-subject-message',
		'email-body-batch-message' => 'combinewatchlist-notif-email-body-batch-message',
		'title-message' => 'combinewatchlist-notif-title-message',
		'section' => 'message', // 'message' or 'alert'
		'bundle' => array( 'web' => true, 'email' => true )
);

// Notification new follower
$wgEchoNotificationCategories['newfollower'] = array(
		'priority' => 3,
		'tooltip' => 'echo-pref-tooltip-newfollower',
);
$wgEchoNotifications['newfollower'] = array(
		'category' => 'newfollower',
		//'bundle' => array( 'web' => true, 'email' => true ),
		'formatter-class' => 'EchoBasicFormatter',
		'presentation-model' => 'NewFollowerNotif\\NotificationPresentationModel',
		'title-message' => 'newfollower-notif-title-message',
		'email-subject-message' => 'newfollower-notif-email-subject-message',
		'email-body-batch-message' => 'newfollower-notif-email-body-batch-message',
		'title-message' => 'newfollower-notif-title-message',
		'section' => 'message', // 'message' or 'alert'
);

//default email notification period
// -1  : no email
// 0 : instant email
// 1 : every day
// 7 : every weeks
$wgDefaultUserOptions['echo-email-frequency'] = 1;

// Echo notification subscription preference
$wgDefaultUserOptions['echo-subscriptions-web-combinedwatchlist'] = true;
$wgDefaultUserOptions['echo-subscriptions-email-combinedwatchlist'] = true;
$wgDefaultUserOptions['echo-subscriptions-web-newfollower'] = true;
$wgDefaultUserOptions['echo-subscriptions-email-newfollower'] = true;


