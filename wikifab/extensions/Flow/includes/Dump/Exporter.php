<?php

namespace Flow\Dump;

use BatchRowIterator;
use DatabaseBase;
use Exception;
use Flow\Collection\PostSummaryCollection;
use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\Workflow;
use Flow\RevisionActionPermissions;
use Flow\Search\Iterators\AbstractIterator;
use Flow\Search\Iterators\HeaderIterator;
use Flow\Search\Iterators\TopicIterator;
use ReflectionProperty;
use TimestampException;
use User;
use WikiExporter;
use Xml;

class Exporter extends WikiExporter {
	/**
	 * Map of [db column name => xml attribute name]
	 *
	 * @var array
	 */
	public static $map = array(
		'rev_id' => 'id',
		'rev_user_id' => 'userid',
		'rev_user_ip' => 'userip',
		'rev_user_wiki' => 'userwiki',
		'rev_parent_id' => 'parentid',
		'rev_change_type' => 'changetype',
		'rev_type' => 'type',
		'rev_type_id' => 'typeid',
		'rev_content' => 'content',
		'rev_content_url' => 'contenturl',
		'rev_flags' => 'flags',
		'rev_mod_state' => 'modstate',
		'rev_mod_user_id' => 'moduserid',
		'rev_mod_user_ip' => 'moduserip',
		'rev_mod_user_wiki' => 'moduserwiki',
		'rev_mod_timestamp' => 'modtimestamp',
		'rev_mod_reason' => 'modreason',
		'rev_last_edit_id' => 'lasteditid',
		'rev_edit_user_id' => 'edituserid',
		'rev_edit_user_ip' => 'edituserip',
		'rev_edit_user_wiki' => 'edituserwiki',
		'rev_content_length' => 'contentlength',
		'rev_previous_content_length' => 'previouscontentlength',

		'tree_parent_id' => 'treeparentid',
		'tree_rev_descendant_id' => 'treedescendantid',
		'tree_rev_id' => 'treerevid',
		'tree_orig_user_id' => 'treeoriguserid',
		'tree_orig_user_ip' => 'treeoriguserip',
		'tree_orig_user_wiki' => 'treeoriguserwiki',
	);

	/**
	   @var ReflectionProperty $prevRevisionProperty Previous revision property
	*/
	protected $prevRevisionProperty;

	/**
	   @var ReflectionProperty $changeTypeProperty Change type property
	*/
	protected $changeTypeProperty;

	/**
	 * {@inheritDoc}
	 */
	function __construct( $db, $history = WikiExporter::CURRENT,
		$buffer = WikiExporter::BUFFER, $text = WikiExporter::TEXT ) {

		parent::__construct( $db, $history, $buffer, $text );
		$this->prevRevisionProperty = new ReflectionProperty( 'Flow\Model\AbstractRevision', 'prevRevision' );
		$this->prevRevisionProperty->setAccessible( true );

		$this->changeTypeProperty = new ReflectionProperty( 'Flow\Model\AbstractRevision', 'changeType' );
		$this->changeTypeProperty->setAccessible( true );
	}

	public static function schemaVersion() {
		/*
		 * Be sure to also update the schema/namespace on mediawiki.org when
		 * making any changes:
		 * @see https://gerrit.wikimedia.org/r/#/c/281640/
		 */
		return '1.0';
	}

	public function openStream() {
		global $wgLanguageCode;
		$version = static::schemaVersion();

		$output = Xml::openElement(
			'mediawiki',
			array(
				'xmlns' => "http://www.mediawiki.org/xml/flow-$version/",
				'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
				'xsi:schemaLocation' => "http://www.mediawiki.org/xml/flow-$version/ http://www.mediawiki.org/xml/flow-$version.xsd",
				'version' => $version,
				'xml:lang' => $wgLanguageCode
			)
		) . "\n";
		$this->sink->write( $output );
	}

	/**
	 * @param string[]|null $pages Array of DB-prefixed page titles
	 * @param int|null $startId page_id to start from (inclusive)
	 * @param int|null $endId page_id to end (exclusive)
	 * @return BatchRowIterator
	 */
	public function getWorkflowIterator( array $pages = null, $startId = null, $endId = null ) {
		/** @var DatabaseBase $dbr */
		$dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );

		$iterator = new BatchRowIterator( $dbr, 'flow_workflow', 'workflow_id', 300 );
		$iterator->setFetchColumns( array( '*' ) );
		$iterator->addConditions( array( 'workflow_wiki' => wfWikiID() ) );
		$iterator->addConditions( array( 'workflow_type' => 'discussion' ) );

		if ( $pages ) {
			$pageConds = array();
			foreach ( $pages as $page ) {
				$title = \Title::newFromDBkey( $page );
				$pageConds[] = $dbr->makeList(
					array(
						'workflow_namespace' => $title->getNamespace(),
						'workflow_title_text' => $title->getDBkey()
					),
					LIST_AND
				);
			}

			$iterator->addConditions( array( $dbr->makeList( $pageConds, LIST_OR ) ) );
		}
		if ( $startId ) {
			$iterator->addConditions( array( 'workflow_page_id >= ' . $dbr->addQuotes( $startId ) ) );
		}
		if ( $endId ) {
			$iterator->addConditions( array( 'workflow_page_id < ' . $dbr->addQuotes( $endId ) ) );
		}

		return $iterator;
	}

	/**
	 * @param BatchRowIterator $workflowIterator
	 * @throws Exception
	 * @throws TimestampException
	 * @throws \Flow\Exception\InvalidInputException
	 */
	public function dump( BatchRowIterator $workflowIterator ) {
		foreach ( $workflowIterator as $rows ) {
			foreach ( $rows as $row ) {
				$workflow = Workflow::fromStorageRow( (array) $row );

				$headerIterator = Container::get( 'search.index.iterators.header' );
				$topicIterator = Container::get( 'search.index.iterators.topic' );
				/** @var AbstractIterator $iterator */
				foreach ( array( $headerIterator, $topicIterator ) as $iterator ) {
					$iterator->setPage( $row->workflow_page_id );
				}

				$this->formatWorkflow( $workflow, $headerIterator, $topicIterator );
			}
		}
	}

	protected function formatWorkflow( Workflow $workflow, HeaderIterator $headerIterator, TopicIterator $topicIterator ) {
		if ( $workflow->isDeleted() ) {
			return;
		}

		$output = Xml::openElement( 'board', array(
			'id' => $workflow->getId()->getAlphadecimal(),
			'title' => $workflow->getOwnerTitle()->getPrefixedDBkey(),
		) ) . "\n";
		$this->sink->write( $output );

		foreach ( $headerIterator as $revision ) {
			/** @var Header $revision */
			$this->formatHeader( $revision );
		}
		foreach ( $topicIterator as $revision ) {
			/** @var PostRevision $revision */
			$this->formatTopic( $revision );
		}

		$output = Xml::closeElement( 'board' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatTopic( PostRevision $revision ) {
		if ( !$this->isAllowed( $revision ) ) {
			return;
		}

		$output = Xml::openElement( 'topic', array(
			'id' => $revision->getCollectionId()->getAlphadecimal(),
		) ) . "\n";
		$this->sink->write( $output );

		$this->formatPost( $revision );

		// find summary for this topic & add it as revision
		$summaryCollection = PostSummaryCollection::newFromId( $revision->getCollectionId() );
		try {
			/** @var PostSummary $summary */
			$summary = $summaryCollection->getLastRevision();
			$this->formatSummary( $summary );
		} catch ( \Exception $e ) {
			// no summary - that's ok!
		}

		$output = Xml::closeElement( 'topic' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatHeader( Header $revision ) {
		if ( !$this->isAllowed( $revision ) ) {
			return;
		}

		$output = Xml::openElement( 'description', array(
			'id' => $revision->getCollectionId()->getAlphadecimal()
		) ) . "\n";
		$this->sink->write( $output );

		$this->formatRevisions( $revision );

		$output = Xml::closeElement( 'description' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatPost( PostRevision $revision ) {
		if ( !$this->isAllowed( $revision ) ) {
			return;
		}

		$output = Xml::openElement( 'post', array(
			'id' => $revision->getCollectionId()->getAlphadecimal()
		) ) . "\n";
		$this->sink->write( $output );

		$this->formatRevisions( $revision );

		if ( $revision->getChildren() ) {
			$output = Xml::openElement( 'children' ) . "\n";
			$this->sink->write( $output );

			foreach ( $revision->getChildren() as $child ) {
				$this->formatPost( $child );
			}

			$output = Xml::closeElement( 'children' ) . "\n";
			$this->sink->write( $output );
		}

		$output = Xml::closeElement( 'post' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatSummary( PostSummary $revision ) {
		if ( !$this->isAllowed( $revision ) ) {
			return;
		}

		$output = Xml::openElement( 'summary', array(
			'id' => $revision->getCollectionId()->getAlphadecimal()
		) ) . "\n";
		$this->sink->write( $output );

		$this->formatRevisions( $revision );

		$output = Xml::closeElement( 'summary' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatRevisions( AbstractRevision $revision ) {
		$output = Xml::openElement( 'revisions' ) . "\n";
		$this->sink->write( $output );

		$collection = $revision->getCollection();
		if ( $this->history === WikiExporter::FULL ) {
			/** @var AbstractRevision[] $revisions */
			$revisions = array_reverse( $collection->getAllRevisions() );
			$prevId = null;

			foreach ( $revisions as $revision ) {
				if ( $this->isAllowed( $revision ) ) {
					if ( $prevId !== null ) {
						// override parent id: this is used to get rid of gaps
						// that are caused by moderated items, where the
						// revision tree would be incorrect
						$this->prevRevisionProperty->setValue( $revision, $prevId );

						// Since $prevId is set, we know
						// there was a gap, and the original
						// hide-topic/delete-topic/suppress-topic
						// was removed. Since that is used for
						// listeners in FlowActions.php, we replace
						// restore-topic with edit-title and make a
						// null edit (we don't do null edits in the
						// normal application flow, but this
						// provides a way to replace restore).
						$oldChangeType = $revision->getChangeType();

						if ( $oldChangeType === 'restore-topic' ) {
							$this->changeTypeProperty->setValue( $revision, 'edit-title' );
						}

						if ( $oldChangeType === 'restore-post' ) {
							$this->changeTypeProperty->setValue( $revision, 'edit-post' );
						}

						$prevId = null;
					}
					$this->formatRevision( $revision );
				} elseif ( $prevId === null ) {
					// if revision can't be dumped, store its parent id so we
					// can re-apply it to the next one that can be displayed, so
					// we don't have gaps
					$prevId = $revision->getPrevRevisionId();
				}
			}
		} elseif ( $this->history === WikiExporter::CURRENT ) {
			$first = $collection->getFirstRevision();

			// storing only last revision won't work (it'll reference non-existing
			// parents): we'll construct a bogus revision with most of the original
			// metadata, but with the current content & id (= timestamp)
			$first = $first->toStorageRow( $first );
			$last = $revision->toStorageRow( $revision );
			$first['rev_id'] = $last['rev_id'];
			$first['rev_content'] = $last['rev_content'];
			$first['rev_flags'] = $last['rev_flags'];
			if ( isset( $first['tree_rev_id'] ) ) {
				// PostRevision-only: tree_rev_id must match rev_id
				$first['tree_rev_id'] = $first['rev_id'];
			}

			// clear buffered cache, to make sure it doesn't serve the existing (already
			// loaded) revision when trying to turn our bogus mixed data into a revision
			/** @var ManagerGroup $storage */
			$storage = Container::get( 'storage' );
			$storage->clear();

			$mix = $revision->fromStorageRow( $first );

			$this->formatRevision( $mix );
		}

		$output = Xml::closeElement( 'revisions' ) . "\n";
		$this->sink->write( $output );
	}

	protected function formatRevision( AbstractRevision $revision ) {
		if ( !$this->isAllowed( $revision ) ) {
			return;
		}

		$attribs = $revision->toStorageRow( $revision );

		// make sure there are no leftover key columns (unknown to $attribs)
		$keys = array_intersect_key(static::$map, $attribs );
		// now make sure $values columns are in the same order as $keys are
		// (array_merge) and there are no leftover columns (array_intersect_key)
		$values = array_intersect_key( array_merge( $keys, $attribs ), $keys );
		// combine them
		$attribs = array_combine( $keys, $values );
		// and get rid of columns with null values
		$attribs = array_filter( $attribs, function ( $value ) {
			return $value !== null;
		} );

		// references to external store etc. are useless; we'll include the real
		// content as node text
		unset($attribs['content'], $attribs['contenturl']);
		$format = $revision->getContentFormat();
		$attribs['flags'] = 'utf-8,' . $format;

		$output = Xml::element(
			'revision',
			$attribs,
			$revision->getContent( $format )
		) . "\n";
		$this->sink->write( $output );
	}

	/**
	 * Test if anon users are allowed to view a particular revision.
	 *
	 * @param AbstractRevision $revision
	 * @return bool
	 */
	protected function isAllowed( AbstractRevision $revision ) {
		$user = User::newFromId( 0 );
		$actions = Container::get( 'flow_actions' );
		$permissions = new RevisionActionPermissions( $actions, $user );

		return $permissions->isAllowed( $revision, 'view' );
	}
}
