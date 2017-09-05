<?php

namespace Flow\Data\Storage;

use Flow\DbFactory;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;

/**
 * SQL storage and query for PostRevision instances
 */
class PostRevisionStorage extends RevisionStorage {
	/**
	 * @var TreeRepository
	 */
	protected $treeRepo;

	/**
	 * @param DbFactory $dbFactory
	 * @param array|false $externalStore List of external store servers available for insert
	 *  or false to disable. See $wgFlowExternalStore.
	 * @param TreeRepository $treeRepo
	 */
	public function __construct( DbFactory $dbFactory, $externalStore, TreeRepository $treeRepo ) {
		parent::__construct( $dbFactory, $externalStore );
		$this->treeRepo = $treeRepo;
	}

	protected function joinTable() {
		return 'flow_tree_revision';
	}

	protected function joinField() {
		return 'tree_rev_id';
	}

	protected function getRevType() {
		return 'post';
	}

	protected function insertRelated( array $rows ) {
		if ( ! is_array( reset( $rows ) ) ) {
			$rows = array( $rows );
		}

		$trees = array();
		foreach( $rows as $key => $row ) {
			$trees[$key] = $this->splitUpdate( $row, 'tree' );
		}

		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->insert(
			$this->joinTable(),
			$this->preprocessNestedSqlArray( $trees ),
			__METHOD__
		);

		// If this is a brand new root revision it needs to be added to the tree
		// If it has a rev_parent_id then its already a part of the tree
		if ( $res ) {
			foreach( $rows as $row ) {
				if ( $row['rev_parent_id'] === null ) {
					$res = $res && $this->treeRepo->insert(
						UUID::create( $row['tree_rev_descendant_id'] ),
						UUID::create( $row['tree_parent_id'] )
					);
				}
			}
		}

		if ( !$res ) {
			return array();
		}

		return $rows;
	}

	// Topic split will primarily be done through the TreeRepository directly,  but
	// we will need to accept updates to the denormalized tree_parent_id field for
	// the new root post
	protected function updateRelated( array $changes, array $old ) {
		$treeChanges = $this->splitUpdate( $changes, 'tree' );

		// no changes to be performed
		if ( !$treeChanges ) {
			return $changes;
		}

		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->update(
			$this->joinTable(),
			$this->preprocessSqlArray( $treeChanges ),
			array( 'tree_rev_id' => $old['tree_rev_id'] ),
			__METHOD__
		);

		if ( !$res ) {
			return array();
		}

		return $changes;
	}

	// this doesn't delete the whole post, it just deletes the revision.
	// The post will *always* exist in the tree structure, its just a tree
	// and we aren't going to re-parent its children;
	protected function removeRelated( array $row ) {
		return $this->dbFactory->getDB( DB_MASTER )->delete(
			$this->joinTable(),
			$this->preprocessSqlArray( array( $this->joinField() => $row['rev_id'] ) )
		);
	}
}
