<?php

namespace NewFollowerNotif;

class NotificationHook {

	public static function onBeforeCreateEchoEvent( &$notifications, &$notificationCategories, &$icons ) {

		//adding a custom icon
		$icons += array(
				'user-add' => array(
						'path' => "UsersWatchButton/icons/user-add.svg"
				)
		);
		// register some echo events
		return true;
	}

	public static function onNewFollower( \User $user, $userToWatch ) {

		$username = $user->getRealName();
		if( !$username ) {
			$username = $user->getName();
		}

		$result = \EchoEvent::create( array(
				'type' => 'newfollower',
				'title' => $user->getUserPage(),
				'extra' => array(
						'followed_user_id' => $userToWatch->getId(),
						'follower_id' => $user->getId(),
						'follower_name' => $username,
				),
				'agent' => $user,
		) );

	}

	public static function onEchoGetDefaultNotifiedUsers(\EchoEvent $event, &$users ) {
		switch ( $event->getType() ) {
			case 'newfollower':

				$title = $event->getTitle();
				$extra = $event->getExtra();
				$followedUser = \User::newFromId( $extra['followed_user_id'] );

				$users[$extra['followed_user_id']] = $followedUser;
				break;
		}
		return true;
	}

	public static function onEchoGetBundleRules( $event, &$bundleString ) {
		switch ( $event->getType() ) {
			case 'newfollower':
				$bundleString = 'newfollower';
				$extra = $event->getExtra();
				if ( $extra['followed_user_id'] ) {
					$bundleString .= '-' . $extra['followed_user_id'];
				}
				break;
		}
	}

}
