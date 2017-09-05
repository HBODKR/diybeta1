<?php
/**
 * class for display author info div
 *
 * @file
 * @ingroup Extensions
 *
 * @author Pierre Boutet
 */

class UsersWatchButton {

	private static $usersWatchListCore = null;


	public static function getHtml($user) {
		global $wgUser;

		$usersWatchListCore = new UsersWatchListCore();

		$isFollowing = $usersWatchListCore->getUserIsFollowing($wgUser, $user);

		$class = $isFollowing ? ' followed' : ' unfollowed';
		$styleWatch = $isFollowing ? ' style="display:none"' : '';
		$styleUnWatch = $isFollowing ? '' : ' style="display:none"' ;


		$out = '<a class="UsersWatchButton' . $class . '" data-user="'. $user . '" '.$styleWatch.'>
			  <button class="btn btn-sm btn-message"><i class="fa fa-user-plus"></i> '.wfMessage( 'userswatchbutton-button-text' )->escaped() . '</button>
			  </a>';
		$out.= '<a class="UsersUnWatchButton' . $class . '" data-user="'. $user . '" '.$styleUnWatch.'>
			  <button class="btn btn-sm btn-message"><i class="fa fa-user-times"></i> '.wfMessage( 'userswatchbutton-unwatchbutton-text' )->escaped() . '</button>
			  </a>';

		return $out;
	}

	private static function formatUsersList($users) {
		$out = '<div class="followUserList row">';

		foreach ($users as $followedUser) {
			$out .= '<div class="col-md-4 col-sm-6 col-xs-12 followUserListcard">';
			$data = [];

			$data['id'] = $followedUser->getId();
			$data['url'] = $followedUser->getUserPage()->getLinkURL();
			$avatar = new wAvatar( $data['id'], 'ml' );
			$data['avatar'] = $avatar->getAvatarURL();
			$data['name'] = $followedUser->getRealName();
			if ( ! $data['name']) {
				$data['name'] = $followedUser->getName();
			}

			$out .= '<a href="'.$data['url'].'">';
			$out .= '<div class="avatar">' . $data['avatar'] . '</div>';
			$out .= '<span class="name">' . $data['name'] . '</span>';
			$out .= '</a>';

			$out .= '</div>';
		}
		$out .= '</div>';
		return $out;

	}

	public static function getFollowers(User $user) {

		$usersWatchListCore = new UsersWatchListCore();

		$followedUsers = $usersWatchListCore->getUsersFollowersInfo($user);

		return self::formatUsersList($followedUsers);

	}

	public static function getFollowing(User $user) {

		$usersWatchListCore = new UsersWatchListCore();

		$followedUsers = $usersWatchListCore->getUsersWatchListInfo($user);

		return self::formatUsersList($followedUsers);
	}

	public static function getUsersCounters($user) {

		$usersWatchListCore = new UsersWatchListCore();

		$counters = $usersWatchListCore->getUserCounters($user);

		$out = '<div class="users-watch-counters">';
		// followers counters :
		$out .='<a onclick="$(\'#tab-followers a\').click()" href="#followers" aria-controls="followers" role="tab" data-toggle="tab" >';
		//$out .='<a href="#" class="vcard-stat">';
		$out .='<strong class="uwc-counter">' . $counters['followers'] . '</strong> ';
		$out .='<span class="uwc-label">'.wfMessage( 'userswatchbutton-followers' )->escaped() . '</span>';
		$out .='</a>';
		// following counters :
		$out .='<a onclick="$(\'#tab-following a\').click()" href="#following" aria-controls="following" role="tab" data-toggle="tab">';
		//$out .='<a href="#" class="vcard-stat">';
		$out .='<strong class="uwc-counter">' . $counters['following'] . '</strong> ';
		$out .='<span class="uwc-label">'.wfMessage( 'userswatchbutton-following' )->escaped() . '</span>';
		$out .='</a>';
		$out .= '</div>';
		return $out;
	}

	public static function beforePageDisplay( $out ) {
		$out->addModules( 'ext.userswatchbutton.js' );
	}

	public static function addParser( $input, $type = 'top', $number = 4 ) {

		$out = '<button class="userswatchbutton">';
		$out .= '</button>';


		return array( $out, 'noparse' => true, 'isHTML' => true );
	}

}