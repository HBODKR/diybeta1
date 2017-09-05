<?php
namespace CombinedWatchlist;

use \Title;

class WatchlistModelConnector  {

	/**
	 *
	 * @return WatchlistModelConnector
	 */
	public static  function getInstance() {
		return new WatchlistModelConnector();
	}

	/**
	 * get list of users Id who are watching the given page
	 *
	 * @param Title $page
	 * @return int[]
	 */
	public function getUsersWatchingPage(Title $page) {
		$dbr = wfGetDB( DB_MASTER );

		$res = $dbr->select(
				array( 'watchlist' ),
				array( 'wl_user' ),
				array(
						'wl_title' => $page->getDBkey(),
						'wl_namespace' => $page->getNamespace()
				),
				__METHOD__,
				array(),
				array()
		);
		$users = array();
		if ( $res->numRows() > 0 ) {
			foreach ( $res as $row ) {
				$users[] = $row->wl_user ;
			}
			$res->free();
		}

		return $users;
	}
}