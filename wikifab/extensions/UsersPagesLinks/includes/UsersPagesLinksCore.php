<?php

namespace UsersPagesLinks;

/**
 * core opération for Users Pages Links
 *
 * @author Pierre Boutet
 */
class UsersPagesLinksCore  {


	/**
	 *	return an instance of UserWatchlistCore
	 *
	 * @return UsersWatchListCore
	 */
	public static function getInstance() {
		static $instance = null;
		if (!$instance) {
			$instance = new UsersPagesLinksCore();
		}
		return $instance;
	}

	/**
	 *
	 * @param User $user
	 * @param string $type
	 * @return string[]|boolean[]
	 */
	public function getUsersPagesLinks(\User $user, $type) {
		$list = array();
		$dbr = wfGetDB( DB_MASTER );

		$res = $dbr->select(
			'userspageslinks',
			array(
				'upl_page_namespace',
				'upl_page_title',
				'upl_page_id',
			), array(
				'upl_user_id' => $user->getId(),
				'upl_type' => $type,
			),
			__METHOD__
		);

		$results = array();
		if ( $res->numRows() > 0 ) {
			foreach ( $res as $row ) {
				$title = \Title::newFromText($row->upl_page_title, $row->upl_page_namespace);
				if ($title) {
					$results[] = $title;
				}
			}
			$res->free();
		}

		return $results;
	}


	/**
	 * returns Users linked to the given page (by type)
	 *
	 * @param \Title $page
	 * @param string $type
	 * @return string[]|boolean[]
	 */
	public function getPagesLinksUsers(\Title $page, $type) {
		$list = array();
		$dbr = wfGetDB( DB_MASTER );

		$res = $dbr->select(
			'userspageslinks',
			array(
				'upl_user_id',
			), array(
				'upl_page_namespace' => $page->getNamespace(),
				'upl_page_title' => $page->getBaseText(),
				'upl_type' => $type,
			),
			__METHOD__
		);
		$results = array();
		if ( $res->numRows() > 0 ) {
			foreach ( $res as $row ) {
				$results[] = \User::newFromId($row->upl_user_id  );
			}
			$res->free();
		}

		return $results;
	}

	public function getUserCounters($user) {
		global $wgUsersPagesLinksTypes;

		$dbr = wfGetDB( DB_MASTER );

		if ( !$user instanceof \User ) {
			$user = \User::newFromName($user);
		}
		$following = 0;
		$followers = 0;

		// get following counters :
		$res = $dbr->select (
				'userspageslinks',
				array (
						'count' => 'count(*)',
						'upl_type'
				),
				array (
						'upl_user_id' => $user->getId ()
				),
				__METHOD__,
				[
					'GROUP BY' => 'upl_type',
				]
		);
		$results = [];
		foreach ($wgUsersPagesLinksTypes as $type) {
			$results[$type] = 0;
		}

		if ( $res->numRows() > 0 ) {
			foreach ( $res as $row ) {
				$results[$row->upl_type] = $row->count ;
			}
			$res->free();
		}
		return $results;
	}

	public function getPageCounters(\Title $page) {
		global $wgUsersPagesLinksTypes;

		$dbr = wfGetDB( DB_MASTER );

		// get following counters :
		$res = $dbr->select (
				'userspageslinks',
				array (
						'count' => 'count(*)',
						'upl_type'
				),
				array (
						'upl_page_namespace' => $page->getNamespace(),
						'upl_page_title' => $page->getBaseText(),
				),
				__METHOD__,
				[
					'GROUP BY' => 'upl_type',
				]
		);
		$results = [];
		foreach ($wgUsersPagesLinksTypes as $type) {
			$results[$type] = 0;
		}
		if ( $res->numRows() > 0 ) {
			foreach ( $res as $row ) {
				$results[$row->upl_type] = $row->count ;
			}
			$res->free();
		}
		return $results;
	}

	/**
	 * get list of links type betwen the user and the page given
	 * @param string|\User $user
	 * @param \Title $page
	 * @return string[]
	 */
	public function getUserPageLinks($user, \Title $page) {

		$dbr = wfGetDB( DB_MASTER );
		$userId = $user->getId();

		if ( !$user instanceof \User ) {
			$user = \User::newFromName($user);
		}

		// get following counters :
		$res = $dbr->select (
				'userspageslinks',
				array (
						'upl_type'
				),
				array (
						'upl_user_id' => $user->getId (),
						'upl_page_namespace' => $page->getNamespace(),
						'upl_page_title' => $page->getBaseText(),
						//'upl_page_id',
				),
				__METHOD__
				);

		$results = [];
		if ( $res->numRows() > 0 ) {
			foreach ( $res as $row ) {
				$results[] = $row->upl_type;
			}
			$res->free();
		}
		return $results;
	}

	/**
	 * return true if user a link for this page and type
	 *
	 * @param \User $user
	 * @param \Title $page
	 * @param string $type
	 * @return boolean
	 */
	public function hasLink(\User $user, \Title $page, $type) {
		$links = $this->getUserPageLinks($user, $page);
		if(in_array($type, $links)) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param \User $user
	 * @param \Title $page
	 * @param string $type
	 * @return boolean
	 */
	public function addLink( \User $user, \Title $page, $type ) {
		global $wgUsersPagesLinksTypes;

		$dbw = wfGetDB( DB_MASTER );

		if ( !$user instanceof \User ) {
			$user = \User::newFromName($user);
		}
		if( ! in_array($type, $wgUsersPagesLinksTypes)) {
			trigger_error('Bad Type for user links : ' . $type);
			return false;
		}

		if ( $user instanceof \User ) {
			$rows[] = array(
				'upl_user_id' => $user->getId (),
				'upl_page_namespace' => $page->getNamespace(),
				'upl_page_title' => $page->getBaseText(),
				'upl_page_id'=> $page->getArticleID(),
				'upl_type' => $type,
			);

			if (\Hooks::run( 'UsersPagesLinks-beforeCreate', [ $user, $page, $type ] )) {
				$dbw->insert( 'userspageslinks', $rows, __METHOD__, 'IGNORE' );
				$wikiPage = new \WikiPage($page);
				$wikiPage->doPurge();
				return true;
			}
		}

		return false;
	}

	/**
	 * Remove a list of titles from a user's userswatchlist
	 *
	 * $titles can be an array of strings or \User objects; the former
	 * is preferred, since User are very memory-heavy
	 *
	 * @param User $user
	 * @param array $users Array of strings, or User objects
	 */
	public function removeLink( \User $user, \Title $page, $type ) {
		$dbw = wfGetDB( DB_MASTER );
		if ( !$user instanceof \User ) {
			$user = \User::newFromName($user);
		}

		if ( $user instanceof \User ) {
			$dbw->delete(
				'userspageslinks',
				array(
					'upl_user_id' => $user->getId (),
					'upl_page_namespace' => $page->getNamespace(),
					'upl_page_title' => $page->getBaseText(),
					'upl_type' => $type
				),
				__METHOD__
			);
			$wikiPage = new \WikiPage($page);
			$wikiPage->doPurge();
			return true;
		}
		return false;
	}
}
