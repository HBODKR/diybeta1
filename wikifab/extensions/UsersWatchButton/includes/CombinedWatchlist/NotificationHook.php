<?php

namespace CombinedWatchlist;

class NotificationHook {

	public static function onExtensionLoad() {
		global $wgHooks;
		//$wgHooks['RecentChange_save'][] = 'PersonalisedEmailNotificationHook::onRecentChange_save';

	}

	public static function onEchoGetDefaultNotifiedUsers(\EchoEvent $event, &$users ) {
		switch ( $event->getType() ) {
			case 'combinedwatchlist':

				$title = $event->getTitle();
				$extra = $event->getExtra();
				$agentId = $event->getAgent()->getId();

				if (  $event->getTitle()->getNamespace() != NS_MAIN) {
					// we do not notify people on file upload
					break;
				}


				// get users watching getFollowersIds($userId);
				$followingUserUsersIds = \UsersWatchListCore::getInstance()->getFollowersIds($extra['rc_user']);

				// get user watching page :
				$followingPageUsersIds = WatchlistModelConnector::getInstance()->getUsersWatchingPage($title);

				//$event->setExtra( 'followingUserUsersIds', $followingUserUsersIds );
				//$event->setExtra( 'followingPageUserdIds', $followingPageUsersIds );

				foreach ($followingUserUsersIds as $recipientId) {
					if($recipientId == $agentId) {
						continue;
					}
					$users[$recipientId] = \User::newFromId( $recipientId );
				}
				foreach ($followingPageUsersIds as $recipientId) {
					if($recipientId == $agentId) {
						continue;
					}
					$users[$recipientId] = \User::newFromId( $recipientId );
				}
				break;
		}
		return true;
	}

	public static function onEchoGetBundleRules( $event, &$bundleString ) {
		switch ( $event->getType() ) {
			case 'combinedwatchlist':
				$bundleString = 'combinedwatchlist';
				if ( $event->getTitle() ) {
					$bundleString .= '-' . $event->getTitle()->getNamespace() . '-' . $event->getTitle()->getDBkey();
				}
				if ( $event->getAgent()->getId() ) {
					$bundleString .= '-' . $event->getAgent()->getId();
				}
				break;
		}
	}

	/**
	 * hook on recent_change_save
	 *
	 * @param RecentChange $recentChange
	 * @return boolean
	 */
	public static function onRecentChange_save(\RecentChange $rc) {

		$result = \EchoEvent::create( array(
				'type' => 'combinedwatchlist',
				'title' => $rc->getTitle(),
				'extra' => array(
						'rc_id' => $rc->getAttribute('rc_id'),
						'rc_timestamp' => $rc->getAttribute('rc_timestamp'),
						'rc_user' => $rc->getAttribute('rc_user'),
						'rc_type' => $rc->getAttribute('rc_type'), // New page or edit
						'rc_source' => $rc->getAttribute('rc_source'), // New page or edit
						'rc_comment' => $rc->getAttribute('rc_comment'),
						'rc_title' => $rc->getAttribute('rc_title'),
						'rc_minor' => $rc->getAttribute('rc_minor'),
						'rc_cur_id' => $rc->getAttribute('rc_cur_id'),
						'rc_type' => $rc->getAttribute('rc_type'),
						'rc_this_oldid' => $rc->getAttribute('rc_this_oldid'),
						'rc_last_oldid' => $rc->getAttribute('rc_last_oldid'),
				),
				'agent' => $rc->getPerformer(),
		) );

	}
	/*
	public static function onBeforeCreateEchoEvent( &$notifications, &$notificationCategories, &$icons ) {
		// You can use either a path or a url, but not both.
		// The value of 'path' is relative to $wgExtensionAssetsPath.
		//
		// The value of 'url' should be a URL.

		//adding a custom icon
		$icons+=array(
				'myext-customicon' => array(
						'path' => "my/icon/path/customicon.png"
				),
				'myext-anothericon' => array(
						'url' => 'http://www.example.org/images/anothericon.png'
				)
		);
		// register some echo events
		return true;
	}*/

	public static function onParserFirstCallInit() {

		$rc = \RecentChange::newFromId(4077);

		//self::onRecentChange_save($rc);
	}


}

/*

Hooks::run( 'AbortEmailNotification', [ $editor, $title, $this ] ) ) {
					# @todo FIXME: This would be better as an extension hook
					*/