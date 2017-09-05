<?php
namespace GroupsPage;

/**
 * core opération for GroupsPages
 *
 * @author Pierre Boutet
 */
class GroupsPageCore  {


	/**
	 *	return an instance of GroupsPageCore
	 *
	 * @return GroupsPageCore
	 */
	public static function getInstance() {
		static $instance = null;
		if (!$instance) {
			$instance = new GroupsPageCore();
		}
		return $instance;
	}

	/**
	 * get the list of titles belonging to the given page
	 * and return an array of Title
	 *
	 * @param \Title $grouppage
	 * @return array
	 */
	public function getMemberPages(\Title $grouppage) {
		$list = array();
		$dbr = wfGetDB( DB_SLAVE );

		$res = $dbr->select(
			'pagesbelonging',
			array(
				'pb_child_page_id'
			), array(
				'pb_parent_page_id' => $grouppage->getArticleID(),
			),
			__METHOD__
		);

		$pages = array();
		if ( $res->numRows() > 0 ) {
			foreach ( $res as $row ) {
				$pages[] = \Title::newFromID(  $row->pb_child_page_id );
			}
			$res->free();
		}

		return $pages;
	}

	/**
	 * get number of pages belonging to the given one
	 * @param \Title $page
	 * @return number
	 */
	public function getGroupNbPages(\Title $page) {

		$dbr = wfGetDB( DB_SLAVE );

		if ( !$user instanceof User ) {
			$user = User::newFromName($user);
		}
		$result = 0;

		// get following counters :
		$res = $dbr->select(
				'pagesbelonging',
				array(
						'count' => 'count(*)'
				), array(
						'pb_parent_page_id' => $page->getArticleID(),
				),
				__METHOD__
				);
		if ( $res->numRows() > 0 ) {
			foreach ( $res as $row ) {
				$result = $row->count ;
			}
			$res->free();
		}
		return $result;
	}

	/**
	 * get list of page wich belong this one
	 * and return an array of Title
	 *
	 * @param \Title $page
	 * @return array
	 */
	public function getGroupsPages(\Title $page) {
		$list = array();
		$dbr = wfGetDB( DB_MASTER );

		$res = $dbr->select(
			'pagesbelonging',
			array(
				'pb_parent_page_id'
			), array(
				'pb_child_page_id' => $page->getArticleID(),
			),
			__METHOD__
		);

		$pages = array();
		if ( $res->numRows() > 0 ) {
			foreach ( $res as $row ) {
				$pages[] = \Title::newFromID(  $row->pb_parent_page_id );
			}
			$res->free();
		}

		return $pages;
	}


	/**
	 * Add a list of page to a group page
	 *
	 * $pages can be an array of Title Object;
	 *
	 * @param \Title $gouppage
	 * @param array $users Array of strings, or Title objects
	 */
	public function addPagesToGroup( \Title $gouppage, $pages ) {
		$dbw = wfGetDB( DB_MASTER );
		$rows = array();

		$added = array();

		foreach ( $pages as $page ) {

			if ( $page instanceof \Title && $page->getArticleID()) {
				$rows[] = array(
					'pb_parent_page_id' => $gouppage->getArticleID(),
					'pb_child_page_id' => $page->getArticleID()
				);
				$added[] = $page;

				\Hooks::run( 'groupspages-newpageingroup', [ $gouppage, $page ] );
			}
		}

		$dbw->insert( 'pagesbelonging', $rows, __METHOD__, 'IGNORE' );
		return $added;
	}

	/**
	 * Remove a list of titles from a group page
	 *
	 *
	 * @param \Title $gouppage
	 * @param array $pages Array of Title
	 */
	public function removePagesFromGroup(  \Title $gouppage, $pages ) {
		$dbw = wfGetDB( DB_MASTER );
		foreach ( $pages as $page ) {

			if ( $page instanceof \Title && $page->getArticleID()) {
				$dbw->delete(
					'pagesbelonging',
					array(
						'pb_parent_page_id' => $gouppage->getArticleID(),
						'pb_child_page_id' =>  $page->getArticleID()
					),
					__METHOD__
				);
			}
		}
		return true;
	}
}
