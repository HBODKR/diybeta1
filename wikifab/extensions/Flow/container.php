<?php

$c = new Flow\Container;

// MediaWiki
if ( defined( 'RUN_MAINTENANCE_IF_MAIN' ) ) {
	$c['user'] = new User;
} else {
	$c['user'] = isset( $GLOBALS['wgUser'] ) ? $GLOBALS['wgUser'] : new User;
}
$c['output'] = $GLOBALS['wgOut'];
$c['request'] = $GLOBALS['wgRequest'];
$c['memcache'] = function( $c ) {
	global $wgFlowUseMemcache, $wgMemc;

	if ( $wgFlowUseMemcache ) {
		return $wgMemc;
	} else {
		return new \HashBagOStuff();
	}
};
$c['cache.version'] = $GLOBALS['wgFlowCacheVersion'];

// This lets the index handle the initial query from HistoryPager,
// even when the UI limit is 500.  An extra item is requested
// so we know whether to link the pagination.
$c['history_index_limit'] = 501;

// 501 * OVERFETCH_FACTOR from HistoryQuery + 1
// Basically, this is so we can try to fetch enough extra to handle
// exclude_from_history without retrying.
$c['board_topic_history_post_index_limit'] = 682;

// Flow config
$c['flow_actions'] = function( $c ) {
	global $wgFlowActions;
	return new Flow\FlowActions( $wgFlowActions );
};

// Always returns the correct database for flow storage
$c['db.factory'] = function( $c ) {
	global $wgFlowDefaultWikiDb, $wgFlowCluster;
	return new Flow\DbFactory( $wgFlowDefaultWikiDb, $wgFlowCluster );
};

// Database Access Layer external from main implementation
$c['repository.tree'] = function( $c ) {
	return new Flow\Repository\TreeRepository(
		$c['db.factory'],
		$c['memcache.local_buffered']
	);
};

$c['url_generator'] = function( $c ) {
	return new Flow\UrlGenerator(
		$c['storage.workflow.mapper']
	);
};

$c['watched_items'] = function( $c ) {
	return new Flow\WatchedTopicItems(
		$c['user'],
		wfGetDB( DB_SLAVE, 'watchlist' )
	);
};

$c['link_batch'] = function() {
	return new LinkBatch;
};

$c['wiki_link_fixer'] = function( $c ) {
	return new Flow\Parsoid\Fixer\WikiLinkFixer( $c['link_batch'] );
};

$c['bad_image_remover'] = function( $c ) {
	return new Flow\Parsoid\Fixer\BadImageRemover( 'wfIsBadImage' );
};

$c['base_href_fixer'] = function( $c ) {
	global $wgArticlePath;

	return new Flow\Parsoid\Fixer\BaseHrefFixer( $wgArticlePath );
};

$c['ext_link_fixer'] = function ( $c ) {
	return new Flow\Parsoid\Fixer\ExtLinkFixer();
};

$c['content_fixer'] = function( $c ) {
	return new Flow\Parsoid\ContentFixer(
		$c['wiki_link_fixer'],
		$c['bad_image_remover'],
		$c['base_href_fixer'],
		$c['ext_link_fixer']
	);
};

$c['permissions'] = function( $c ) {
	return new Flow\RevisionActionPermissions( $c['flow_actions'], $c['user'] );
};

$c['lightncandy.template_dir'] = __DIR__ . '/handlebars';
$c['lightncandy'] = function( $c ) {
	global $wgFlowServerCompileTemplates;

	return new Flow\TemplateHelper(
		$c['lightncandy.template_dir'],
		$wgFlowServerCompileTemplates
	);
};

$c['templating'] = function( $c ) {
	return new Flow\Templating(
		$c['repository.username'],
		$c['url_generator'],
		$c['output'],
		$c['content_fixer'],
		$c['permissions']
	);
};

// New Storage Impl
use Flow\Data\BufferedCache;
use Flow\Data\Mapper\BasicObjectMapper;
use Flow\Data\Mapper\CachingObjectMapper;
use Flow\Data\Storage\BasicDbStorage;
use Flow\Data\Storage\TopicListStorage;
use Flow\Data\Storage\TopicListLastUpdatedStorage;
use Flow\Data\Storage\PostRevisionBoardHistoryStorage;
use Flow\Data\Storage\PostRevisionStorage;
use Flow\Data\Storage\HeaderRevisionStorage;
use Flow\Data\Storage\PostSummaryRevisionBoardHistoryStorage;
use Flow\Data\Storage\PostSummaryRevisionStorage;
use Flow\Data\Index\UniqueFeatureIndex;
use Flow\Data\Index\TopKIndex;
use Flow\Data\Index\TopicListTopKIndex;
use Flow\Data\Storage\PostRevisionTopicHistoryStorage;
use Flow\Data\Index\PostRevisionBoardHistoryIndex;
use Flow\Data\Index\PostRevisionTopicHistoryIndex;
use Flow\Data\Index\PostSummaryRevisionBoardHistoryIndex;
use Flow\Data\ObjectManager;
use Flow\Data\ObjectLocator;

// This currently never clears $this->bag, which makes it unusuable for long-running batch.
// Use 'memcache.non_local_buffered' for those instead.
$c['memcache.local_buffered'] = function( $c ) {
	global $wgFlowCacheTime;

	// This is the real buffered cached that will allow transactional-like cache.
	// It also caches all reads in-memory.
	$bufferedCache = new Flow\Data\BagOStuff\LocalBufferedBagOStuff( $c['memcache'] );
	// This is Flow's wrapper around it, to have a fixed cache expiry time
	return new BufferedCache( $bufferedCache, $wgFlowCacheTime );
};

$c['memcache.non_local_buffered'] = function( $c ) {
	global $wgFlowCacheTime;

	// This is the real buffered cached that will allow transactional-like cache
	$bufferedCache = new Flow\Data\BagOStuff\BufferedBagOStuff( $c['memcache'] );

	// This is Flow's wrapper around it, to have a fixed cache expiry time
	return new BufferedCache( $bufferedCache, $wgFlowCacheTime );
};

// Batched username loader
$c['repository.username.query'] = function( $c ) {
	return new Flow\Repository\UserName\TwoStepUserNameQuery(
		$c['db.factory']
	);
};
$c['repository.username'] = function( $c ) {
	return new Flow\Repository\UserNameBatch(
		$c['repository.username.query']
	);
};
$c['collection.cache'] = function( $c ) {
	return new Flow\Collection\CollectionCache();
};
// Individual workflow instances
$c['storage.workflow.class'] = 'Flow\Model\Workflow';
$c['storage.workflow.table'] = 'flow_workflow';
$c['storage.workflow.primary_key'] = array( 'workflow_id' );
$c['storage.workflow.mapper'] = function( $c ) {
	return CachingObjectMapper::model(
		$c['storage.workflow.class'],
		$c['storage.workflow.primary_key']
	);
};
$c['storage.workflow.backend'] = function( $c ) {
	return new BasicDbStorage(
		$c['db.factory'],
		$c['storage.workflow.table'],
		$c['storage.workflow.primary_key']
	);
};
$c['storage.workflow.indexes.primary'] = function( $c ) {
	return new UniqueFeatureIndex(
		$c['memcache.local_buffered'],
		$c['storage.workflow.backend'],
		$c['storage.workflow.mapper'],
		'flow_workflow:v2:pk',
		$c['storage.workflow.primary_key']
	);
};
$c['storage.workflow.indexes.title_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.local_buffered'],
		$c['storage.workflow.backend'],
		$c['storage.workflow.mapper'],
		'flow_workflow:title:v2:',
		array( 'workflow_wiki', 'workflow_namespace', 'workflow_title_text', 'workflow_type' ),
		array(
			'shallow' => $c['storage.workflow.indexes.primary'],
			'limit' => 1,
			'sort' => 'workflow_id'
		)
	);
};
$c['storage.workflow.indexes'] = function( $c ) {
	return array(
		$c['storage.workflow.indexes.primary'],
		$c['storage.workflow.indexes.title_lookup']
	);
};
$c['storage.workflow.listeners.topiclist'] = function( $c ) {
	return new Flow\Data\Listener\WorkflowTopicListListener(
		$c['storage.topic_list'],
		$c['storage.topic_list.indexes.last_updated']
	);
};
$c['storage.workflow.listeners'] = function( $c ) {
	return array(
		'listener.topicpagecreation' => $c['listener.topicpagecreation'],

		// The storage.topic_list.indexes are primarily for TopicListEntry insertions, but they
		// also listen for discussion workflow insertions so they can initialize for new boards.
		'storage.topic_list.indexes.reverse_lookup' => $c['storage.topic_list.indexes.reverse_lookup'],
		'storage.topic_list.indexes.last_updated' => $c['storage.topic_list.indexes.last_updated'],

		'storage.workflow.listeners.topiclist' => $c['storage.workflow.listeners.topiclist'],
	);
};
$c['storage.workflow'] = function( $c ) {
	return new ObjectManager(
		$c['storage.workflow.mapper'],
		$c['storage.workflow.backend'],
		$c['db.factory'],
		$c['storage.workflow.indexes'],
		$c['storage.workflow.listeners']
	);
};
$c['listener.recentchanges'] = function( $c ) {
	// Recent change listeners go out to external services and
	// as such must only be run after the transaction is commited.
	return new Flow\Data\Listener\DeferredInsertLifecycleHandler(
		$c['deferred_queue'],
		new Flow\Data\Listener\RecentChangesListener(
			$c['flow_actions'],
			$c['repository.username'],
			new Flow\Data\Utils\RecentChangeFactory,
			$c['formatter.irclineurl']
		)
	);
};
$c['listener.topicpagecreation'] = function( $c ) {
	return new Flow\Data\Listener\TopicPageCreationListener(
		$c['occupation_controller'],
		$c['deferred_queue']
	);
};
$c['listeners.notification'] = function( $c ) {
	// Defer notifications triggering till end of request so we could get
	// article_id in the case of a new topic
	return new Flow\Data\Listener\DeferredInsertLifecycleHandler(
		$c['deferred_queue'],
		new Flow\Data\Listener\NotificationListener(
			$c['controller.notification']
		)
	);
};

$c['storage.post_board_history.backend'] = function( $c ) {
	return new PostRevisionBoardHistoryStorage( $c['db.factory'] );
};
$c['storage.post_board_history.indexes.primary'] = function( $c ) {
	return new PostRevisionBoardHistoryIndex(
		$c['memcache.local_buffered'],
		// backend storage
		$c['storage.post_board_history.backend'],
		// data mapper
		$c['storage.post.mapper'],
		// key prefix
		'flow_revision:topic_list_history:post:v2',
		// primary key
		array( 'topic_list_id' ),
		// index options
		array(
			'limit' => $c['board_topic_history_post_index_limit'],
			'sort' => 'rev_id',
			'order' => 'DESC'
		),
		$c['storage.topic_list']
	);
};

$c['storage.post_board_history.indexes'] = function( $c ) {
	return array( $c['storage.post_board_history.indexes.primary'] );
};

$c['storage.post_board_history'] = function( $c ) {
	return new ObjectLocator(
		$c['storage.post.mapper'],
		$c['storage.post_board_history.backend'],
		$c['db.factory'],
		$c['storage.post_board_history.indexes']
	);
};

$c['storage.post_summary_board_history.backend'] = function( $c ) {
	return new PostSummaryRevisionBoardHistoryStorage( $c['db.factory'] );
};
$c['storage.post_summary_board_history.indexes.primary'] = function( $c ) {
	return new PostSummaryRevisionBoardHistoryIndex(
		$c['memcache.local_buffered'],
		// backend storage
		$c['storage.post_summary_board_history.backend'],
		// data mapper
		$c['storage.post_summary.mapper'],
		// key prefix
		'flow_revision:topic_list_history:post_summary:v2',
		// primary key
		array( 'topic_list_id' ),
		// index options
		array(
			'limit' => $c['history_index_limit'],
			'sort' => 'rev_id',
			'order' => 'DESC'
		),
		$c['storage.topic_list']
	);
};

$c['storage.post_summary_board_history.indexes'] = function( $c ) {
	return array( $c['storage.post_summary_board_history.indexes.primary'] );
};

$c['storage.post_summary_board_history'] = function( $c ) {
	return new ObjectLocator(
		$c['storage.post_summary.mapper'],
		$c['storage.post_summary_board_history.backend'],
		$c['db.factory'],
		$c['storage.post_summary_board_history.indexes']
	);
};

$c['storage.header.listeners.username'] = function( $c ) {
	return new Flow\Data\Listener\UserNameListener(
		$c['repository.username'],
		array(
			'rev_user_id' => 'rev_user_wiki',
			'rev_mod_user_id' => 'rev_mod_user_wiki',
			'rev_edit_user_id' => 'rev_edit_user_wiki'
		)
	);
};
$c['storage.header.listeners'] = function( $c ) {
	return array(
		'reference.recorder' => $c['reference.recorder'],
		'storage.header.listeners.username' => $c['storage.header.listeners.username'],
		'listeners.notification' => $c['listeners.notification'],
		'listener.recentchanges' => $c['listener.recentchanges'],
		'listener.editcount' => $c['listener.editcount'],
	);
};
$c['storage.header.primary_key'] = array( 'rev_id' );
$c['storage.header.mapper'] = function( $c ) {
	return CachingObjectMapper::model( 'Flow\\Model\\Header', array( 'rev_id' ) );
};
$c['storage.header.backend'] = function( $c ) {
	global $wgFlowExternalStore;
	return new HeaderRevisionStorage(
		$c['db.factory'],
		$wgFlowExternalStore
	);

};
$c['storage.header.indexes.primary'] = function( $c ) {
	return new UniqueFeatureIndex(
		$c['memcache.local_buffered'],
		$c['storage.header.backend'],
		$c['storage.header.mapper'],
		'flow_header:v2:pk',
		$c['storage.header.primary_key']
	);
};
$c['storage.header.indexes.header_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.local_buffered'],
		$c['storage.header.backend'],
		$c['storage.header.mapper'],
		'flow_header:workflow:v3',
		array( 'rev_type_id' ),
		array(
			'limit' => $c['history_index_limit'],
			'sort' => 'rev_id',
			'order' => 'DESC',
			'shallow' => $c['storage.header.indexes.primary'],
			'create' => function( array $row ) {
				return $row['rev_parent_id'] === null;
			},
		)
	);
};
$c['storage.header.indexes'] = function( $c ) {
	return array(
		$c['storage.header.indexes.primary'],
		$c['storage.header.indexes.header_lookup']
	);
};
$c['storage.header'] = function( $c ) {
	return new ObjectManager(
		$c['storage.header.mapper'],
		$c['storage.header.backend'],
		$c['db.factory'],
		$c['storage.header.indexes'],
		$c['storage.header.listeners']
	);
};

$c['storage.post_summary.class'] = 'Flow\Model\PostSummary';
$c['storage.post_summary.primary_key'] = array( 'rev_id' );
$c['storage.post_summary.mapper'] = function( $c ) {
	return CachingObjectMapper::model(
		$c['storage.post_summary.class'],
		$c['storage.post_summary.primary_key']
	);
};
$c['storage.post_summary.listeners.username'] = function( $c ) {
	return new Flow\Data\Listener\UserNameListener(
		$c['repository.username'],
		array(
			'rev_user_id' => 'rev_user_wiki',
			'rev_mod_user_id' => 'rev_mod_user_wiki',
			'rev_edit_user_id' => 'rev_edit_user_wiki'
		)
	);
};
$c['storage.post_summary.listeners'] = function( $c ) {
	return array(
		'listener.recentchanges' => $c['listener.recentchanges'],
		'storage.post_summary.listeners.username' => $c['storage.post_summary.listeners.username'],
		'listeners.notification' => $c['listeners.notification'],
		'storage.post_summary_board_history.indexes.primary' => $c['storage.post_summary_board_history.indexes.primary'],
		'listener.editcount' => $c['listener.editcount'],
		'reference.recorder' => $c['reference.recorder'],
	);
};
$c['storage.post_summary.backend'] = function( $c ) {
	global $wgFlowExternalStore;
	return new PostSummaryRevisionStorage(
		$c['db.factory'],
		$wgFlowExternalStore
	);
};
$c['storage.post_summary.indexes.primary'] = function( $c ) {
	return new UniqueFeatureIndex(
		$c['memcache.local_buffered'],
		$c['storage.post_summary.backend'],
		$c['storage.post_summary.mapper'],
		'flow_post_summary:v2:pk',
		$c['storage.post_summary.primary_key']
	);
};
$c['storage.post_summary.indexes.topic_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.local_buffered'],
		$c['storage.post_summary.backend'],
		$c['storage.post_summary.mapper'],
		'flow_post_summary:workflow:v3',
		array( 'rev_type_id' ),
		array(
			'limit' => $c['history_index_limit'],
			'sort' => 'rev_id',
			'order' => 'DESC',
			'shallow' => $c['storage.post_summary.indexes.primary'],
			'create' => function( array $row ) {
				return $row['rev_parent_id'] === null;
			},
		)
	);
};
$c['storage.post_summary.indexes'] = function( $c ) {
	return array(
		$c['storage.post_summary.indexes.primary'],
		$c['storage.post_summary.indexes.topic_lookup'],
	);
};
$c['storage.post_summary'] = function( $c ) {
	return new ObjectManager(
		$c['storage.post_summary.mapper'],
		$c['storage.post_summary.backend'],
		$c['db.factory'],
		$c['storage.post_summary.indexes'],
		$c['storage.post_summary.listeners']
	);
};

$c['storage.topic_list.class'] = 'Flow\Model\TopicListEntry';
$c['storage.topic_list.table'] = 'flow_topic_list';
$c['storage.topic_list.primary_key'] = array( 'topic_list_id', 'topic_id' );
$c['storage.topic_list.indexes.last_updated.backend'] = function( $c ) {
	return new TopicListLastUpdatedStorage(
		$c['db.factory'],
		$c['storage.topic_list.table'],
		$c['storage.topic_list.primary_key']
	);
};
$c['storage.topic_list.mapper'] = function( $c ) {
	// Must be BasicObjectMapper, due to variance in when
	// we have workflow_last_update_timestamp
	return BasicObjectMapper::model(
		$c['storage.topic_list.class'],
		$c['storage.topic_list.primary_key']
	);
};
$c['storage.topic_list.backend'] = function( $c ) {
	return new TopicListStorage(
		// factory and table
		$c['db.factory'],
		$c['storage.topic_list.table'],
		$c['storage.topic_list.primary_key']
	);
};
// Lookup from topic_id to its owning board id
$c['storage.topic_list.indexes.primary'] = function( $c ) {
	return new UniqueFeatureIndex(
		$c['memcache.local_buffered'],
		$c['storage.topic_list.backend'],
		$c['storage.topic_list.mapper'],
		'flow_topic_list:topic',
		array( 'topic_id' )
	);
};

// Lookup from board to contained topics
/// In reverse order by topic_id
$c['storage.topic_list.indexes.reverse_lookup'] = function( $c ) {
	return new TopicListTopKIndex(
		$c['memcache.local_buffered'],
		$c['storage.topic_list.backend'],
		$c['storage.topic_list.mapper'],
		'flow_topic_list:list',
		array( 'topic_list_id' ),
		array( 'sort' => 'topic_id' )
	);
};
/// In reverse order by topic last_updated
$c['storage.topic_list.indexes.last_updated'] = function( $c ) {
	return new TopicListTopKIndex(
		$c['memcache.local_buffered'],
		$c['storage.topic_list.indexes.last_updated.backend'],
		$c['storage.topic_list.mapper'],
		'flow_topic_list_last_updated:list',
		array( 'topic_list_id' ),
		array(
			'sort' => 'workflow_last_update_timestamp',
			'order' => 'desc'
		)
	);
};
$c['storage.topic_list.indexes'] = function( $c ) {
	return array(
		$c['storage.topic_list.indexes.primary'],
		$c['storage.topic_list.indexes.reverse_lookup'],
		$c['storage.topic_list.indexes.last_updated'],
	);
};
$c['storage.topic_list'] = function( $c ) {
	return new ObjectManager(
		$c['storage.topic_list.mapper'],
		$c['storage.topic_list.backend'],
		$c['db.factory'],
		$c['storage.topic_list.indexes']
	);
};
$c['storage.post.class'] = 'Flow\Model\PostRevision';
$c['storage.post.primary_key'] = array( 'rev_id' );
$c['storage.post.mapper'] = function( $c ) {
	return CachingObjectMapper::model(
		$c['storage.post.class'],
		$c['storage.post.primary_key']
	);
};
$c['storage.post.backend'] = function( $c ) {
	global $wgFlowExternalStore;
	return new PostRevisionStorage(
		$c['db.factory'],
		$wgFlowExternalStore,
		$c['repository.tree']
	);
};
$c['storage.post.listeners.moderation_logging'] = function( $c ) {
	return new Flow\Data\Listener\ModerationLoggingListener(
		$c['logger.moderation']
	);
};
$c['storage.post.listeners.username'] = function( $c ) {
	return new Flow\Data\Listener\UserNameListener(
		$c['repository.username'],
		array(
			'rev_user_id' => 'rev_user_wiki',
			'rev_mod_user_id' => 'rev_mod_user_wiki',
			'rev_edit_user_id' => 'rev_edit_user_wiki',
			'tree_orig_user_id' => 'tree_orig_user_wiki'
		)
	);
};
$c['storage.post.listeners.watch_topic'] = function( $c ) {
	// Auto-subscribe users to the topic after performing specific actions
	return new Flow\Data\Listener\ImmediateWatchTopicListener(
		$c['watched_items']
	);
};
$c['storage.post.listeners'] = function( $c ) {
	return array(
		'reference.recorder' => $c['reference.recorder'],
		'collection.cache' => $c['collection.cache'],
		'storage.post.listeners.username' => $c['storage.post.listeners.username'],
		'storage.post.listeners.watch_topic' => $c['storage.post.listeners.watch_topic'],
		'listeners.notification' => $c['listeners.notification'],
		'storage.post.listeners.moderation_logging' => $c['storage.post.listeners.moderation_logging'],
		'listener.recentchanges' => $c['listener.recentchanges'],
		'listener.editcount' => $c['listener.editcount'],
		'storage.post_board_history.indexes.primary' => $c['storage.post_board_history.indexes.primary'],
	);
};
$c['storage.post.indexes.primary'] = function( $c ) {
	return new UniqueFeatureIndex(
		$c['memcache.local_buffered'],
		$c['storage.post.backend'],
		$c['storage.post.mapper'],
		'flow_revision:v4:pk',
		$c['storage.post.primary_key']
	);
};
// Each bucket holds a list of revisions in a single post
$c['storage.post.indexes.post_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.local_buffered'],
		$c['storage.post.backend'],
		$c['storage.post.mapper'],
		'flow_revision:descendant',
		array( 'rev_type_id' ),
		array(
			'limit' => 100,
			'sort' => 'rev_id',
			'order' => 'DESC',
			'shallow' => $c['storage.post.indexes.primary'],
			'create' => function( array $row ) {
				// return true to create instead of merge index
				return $row['rev_parent_id'] === null;
			},
		)
	);
};
$c['storage.post.indexes'] = function( $c ) {
	return array(
		$c['storage.post.indexes.primary'],
		$c['storage.post.indexes.post_lookup'],
		$c['storage.post_topic_history.indexes.topic_lookup']
	);
};
$c['storage.post'] = function( $c ) {
	return new ObjectManager(
		$c['storage.post.mapper'],
		$c['storage.post.backend'],
		$c['db.factory'],
		$c['storage.post.indexes'],
		$c['storage.post.listeners']
	);
};

$c['storage.post_topic_history.backend'] = function( $c ) {
	return new PostRevisionTopicHistoryStorage(
		$c['storage.post.backend'],
		$c['repository.tree']
	);
};

$c['storage.post_topic_history.indexes.topic_lookup'] = function( $c ) {
	return new PostRevisionTopicHistoryIndex(
		$c['memcache.local_buffered'],
		$c['storage.post_topic_history.backend'],
		$c['storage.post.mapper'],
		'flow_revision:topic_history:post:v2',
		array( 'topic_root_id' ),
		array(
			'limit' => $c['board_topic_history_post_index_limit'],
			'sort' => 'rev_id',
			'order' => 'DESC',
			// Why does topic history have a shallow compactor, but not board history?
			'shallow' => $c['storage.post.indexes.primary'],
			'create' => function( array $row ) {
				// only create new indexes for new topics, so it has to be
				// of type 'post' and have no parent post & revision
				if ( $row['rev_type'] !== 'post' ) {
					return false;
				}
				return $row['tree_parent_id'] === null && $row['rev_parent_id'] === null;
			},
		)
	);
};


$c['storage.post_topic_history.indexes'] = function( $c ) {
	return array(
		$c['storage.post_topic_history.indexes.topic_lookup'],
	);
};

$c['storage.post_topic_history'] = function( $c ) {
	return new ObjectLocator(
		$c['storage.post.mapper'],
		$c['storage.post_topic_history.backend'],
		$c['db.factory'],
		$c['storage.post_topic_history.indexes']
	);
};

$c['storage.manager_list'] = function( $c ) {
	return array(
		'Flow\\Model\\Workflow' => 'storage.workflow',
		'Workflow' => 'storage.workflow',

		'Flow\\Model\\PostRevision' => 'storage.post',
		'PostRevision' => 'storage.post',
		'post' => 'storage.post',

		'Flow\\Model\\PostSummary' => 'storage.post_summary',
		'PostSummary' => 'storage.post_summary',
		'post-summary' => 'storage.post_summary',

		'Flow\\Model\\TopicListEntry' => 'storage.topic_list',
		'TopicListEntry' => 'storage.topic_list',

		'Flow\\Model\\Header' => 'storage.header',
		'Header' => 'storage.header',
		'header' => 'storage.header',

		'PostRevisionBoardHistoryEntry' => 'storage.post_board_history',

		'PostSummaryBoardHistoryEntry' => 'storage.post_summary_board_history',

		'PostRevisionTopicHistoryEntry' => 'storage.post_topic_history',

		'Flow\\Model\\WikiReference' => 'storage.wiki_reference',
		'WikiReference' => 'storage.wiki_reference',

		'Flow\\Model\\URLReference' => 'storage.url_reference',
		'URLReference' => 'storage.url_reference',
	);
};
$c['storage'] = function( $c ) {
	return new \Flow\Data\ManagerGroup(
		$c,
		$c['storage.manager_list']
	);
};
$c['loader.root_post'] = function( $c ) {
	return new \Flow\Repository\RootPostLoader(
		$c['storage'],
		$c['repository.tree']
	);
};

// Queue of callbacks to run by DeferredUpdates, but only
// on successfull commit
$c['deferred_queue'] = function( $c ) {
	return new SplQueue;
};

$c['submission_handler'] = function( $c ) {
	return new Flow\SubmissionHandler(
		$c['storage'],
		$c['db.factory'],
		$c['memcache.local_buffered'],
		$c['deferred_queue']
	);
};
$c['factory.block'] = function( $c ) {
	return new Flow\BlockFactory(
		$c['storage'],
		$c['loader.root_post']
	);
};
$c['factory.loader.workflow'] = function( $c ) {
	return new Flow\WorkflowLoaderFactory(
		$c['storage'],
		$c['factory.block'],
		$c['submission_handler']
	);
};
// Initialized in FlowHooks to facilitate only loading the flow container
// when flow is specifically requested to run. Extension initialization
// must always happen before calling flow code.
$c['occupation_controller'] = FlowHooks::getOccupationController();

$c['controller.notification'] = function( $c ) {
	global $wgContLang;
	return new Flow\NotificationController( $wgContLang, $c['repository.tree'] );
};

// Initialized in FlowHooks to faciliate only loading the flow container
// when flow is specifically requested to run. Extension initialization
// must always happen before calling flow code.
$c['controller.abusefilter'] = FlowHooks::getAbuseFilter();

$c['controller.spamregex'] = function( $c ) {
	return new Flow\SpamFilter\SpamRegex;
};

$c['controller.spamblacklist'] = function( $c ) {
	return new Flow\SpamFilter\SpamBlacklist;
};

$c['controller.confirmedit'] = function( $c ) {
	return new Flow\SpamFilter\ConfirmEdit;
};

$c['controller.contentlength'] = function( $c ) {
	global $wgMaxArticleSize;

	// wgMaxArticleSize is in kilobytes,
	// whereas this really is characters (it uses
	// mb_strlen), so it's not the exact same limit.
	$maxCharCount = $wgMaxArticleSize * 1024;

	return new Flow\SpamFilter\ContentLengthFilter( $maxCharCount );
};

$c['controller.ratelimits'] = function( $c ) {
	return new Flow\SpamFilter\RateLimits;
};

$c['controller.spamfilter'] = function( $c ) {
	return new Flow\SpamFilter\Controller(
		$c['controller.contentlength'],
		$c['controller.spamregex'],
		$c['controller.ratelimits'],
		$c['controller.spamblacklist'],
		$c['controller.abusefilter'],
		$c['controller.confirmedit']
	);
};

$c['query.categoryviewer'] = function( $c ) {
	return new Flow\Formatter\CategoryViewerQuery(
		$c['storage'],
		$c['repository.tree']
	);
};
$c['formatter.categoryviewer'] = function( $c ) {
	return new Flow\Formatter\CategoryViewerFormatter(
		$c['permissions']
	);
};
$c['query.singlepost'] = function( $c ) {
	return new Flow\Formatter\SinglePostQuery(
		$c['storage'],
		$c['repository.tree']
	);
};
$c['query.checkuser'] = function( $c ) {
	return new Flow\Formatter\CheckUserQuery(
		$c['storage'],
		$c['repository.tree']
	);
};

$c['formatter.irclineurl'] = function( $c ) {
	return new Flow\Formatter\IRCLineUrlFormatter(
		$c['permissions'],
		$c['formatter.revision']
	);
};

$c['formatter.checkuser'] = function( $c ) {
	return new Flow\Formatter\CheckUserFormatter(
		$c['permissions'],
		$c['formatter.revision']
	);
};
$c['formatter.revisionview'] = function( $c ) {
	return new Flow\Formatter\RevisionViewFormatter(
		$c['url_generator'],
		$c['formatter.revision']
	);
};
$c['formatter.revision.diff.view'] = function( $c ) {
	return new Flow\Formatter\RevisionDiffViewFormatter(
		$c['formatter.revisionview'],
		$c['url_generator']
	);
};
$c['query.topiclist'] = function( $c ) {
	return new Flow\Formatter\TopicListQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['permissions'],
		$c['watched_items']
	);
};
$c['query.topic.history'] = function( $c ) {
	return new Flow\Formatter\TopicHistoryQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
};
$c['query.post.history'] = function( $c ) {
	return new Flow\Formatter\PostHistoryQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
};
$c['query.changeslist'] = function( $c ) {
	$query = new Flow\Formatter\ChangesListQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
	$query->setExtendWatchlist( $c['user']->getOption( 'extendwatchlist' ) );

	return $query;
};
$c['query.postsummary'] = function( $c ) {
	return new Flow\Formatter\PostSummaryQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
};
$c['query.header.view'] = function( $c ) {
	return new Flow\Formatter\HeaderViewQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['permissions']
	);
};
$c['query.post.view'] = function( $c ) {
	return new Flow\Formatter\PostViewQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['permissions']
	);
};
$c['query.postsummary.view'] = function( $c ) {
	return new Flow\Formatter\PostSummaryViewQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['permissions']
	);
};
$c['formatter.changeslist'] = function( $c ) {
	return new Flow\Formatter\ChangesListFormatter(
		$c['permissions'],
		$c['formatter.revision']
	);
};

$c['query.contributions'] = function( $c ) {
	return new Flow\Formatter\ContributionsQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['memcache'],
		$c['db.factory'],
		$c['flow_actions']
	);
};
$c['formatter.contributions'] = function( $c ) {
	return new Flow\Formatter\ContributionsFormatter(
		$c['permissions'],
		$c['formatter.revision']
	);
};
$c['formatter.contributions.feeditem'] = function( $c ) {
	return new Flow\Formatter\FeedItemFormatter(
		$c['permissions'],
		$c['formatter.revision']
	);
};
$c['query.board.history'] = function( $c ) {
	return new Flow\Formatter\BoardHistoryQuery(
		$c['storage'],
		$c['repository.tree'],
		$c['flow_actions']
	);
};

// The RevisionFormatter holds internal state like
// contentType of output and if it should include history
// properties.  To prevent different code using the formatter
// from causing problems return a new RevisionFormatter every
// time it is requested.
$c['formatter.revision'] = $c->factory( function( $c ) {
	global $wgFlowMaxThreadingDepth;

	return new Flow\Formatter\RevisionFormatter(
		$c['permissions'],
		$c['templating'],
		$c['repository.username'],
		$wgFlowMaxThreadingDepth
	);
} );
$c['formatter.topiclist'] = function( $c ) {
	return new Flow\Formatter\TopicListFormatter(
		$c['url_generator'],
		$c['formatter.revision']
	);
};
$c['formatter.topiclist.toc'] = function ( $c ) {
	return new Flow\Formatter\TocTopicListFormatter(
		$c['templating']
	);
};
$c['formatter.topic'] = function( $c ) {
	return new Flow\Formatter\TopicFormatter(
		$c['url_generator'],
		$c['formatter.revision']
	);
};
$c['search.connection'] = function( $c ) {
	if ( defined( 'MW_PHPUNIT_TEST' ) && !class_exists( 'ElasticaConnection' ) ) {
		/*
		 * ContainerTest::testInstantiateAll instantiates everything
		 * in container and doublechecks it's not null.
		 * Flow runs on Jenkins don't currently load Extension:Elastica,
		 * which is required to be able to construct this object.
		 * Because search is not currently in use, let's not add the
		 * dependency in Jenkins and just return a bogus value to not
		 * make the test fail ;)
		 */
		return 'not-supported';
	}

	global $wgFlowSearchServers, $wgFlowSearchConnectionAttempts;
	return new Flow\Search\Connection( $wgFlowSearchServers, $wgFlowSearchConnectionAttempts );
};
$c['search.index.iterators.header'] = function( $c ) {
	return new \Flow\Search\Iterators\HeaderIterator( $c['db.factory'] );
};
$c['search.index.iterators.topic'] = function( $c ) {
	return new \Flow\Search\Iterators\TopicIterator( $c['db.factory'], $c['loader.root_post'] );
};
$c['search.index.updaters'] = function( $c ) {
	// permissions for anon user
	$anonPermissions = new Flow\RevisionActionPermissions( $c['flow_actions'], new User );
	return array(
		'topic' => new \Flow\Search\Updaters\TopicUpdater( $c['search.index.iterators.topic'], $anonPermissions, $c['loader.root_post'] ),
		'header' => new \Flow\Search\Updaters\HeaderUpdater( $c['search.index.iterators.header'], $anonPermissions )
	);
};

$c['logger.moderation'] = function( $c ) {
	return new Flow\Log\ModerationLogger(
		$c['flow_actions']
	);
};

$c['storage.wiki_reference.class'] = 'Flow\Model\WikiReference';
$c['storage.wiki_reference.table'] = 'flow_wiki_ref';
$c['storage.wiki_reference.primary_key'] = function ( $c ) {
	return array(
		'ref_src_wiki',
		'ref_src_namespace',
		'ref_src_title',
		'ref_src_object_id',
		'ref_type',
		'ref_target_namespace',
		'ref_target_title'
	);
};
$c['storage.wiki_reference.mapper'] = function( $c ) {
	return BasicObjectMapper::model(
		$c['storage.wiki_reference.class']
	);
};
$c['storage.wiki_reference.backend'] = function( $c ) {
	return new BasicDbStorage(
		$c['db.factory'],
		$c['storage.wiki_reference.table'],
		$c['storage.wiki_reference.primary_key']
	);
};
$c['storage.wiki_reference.indexes.source_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.local_buffered'],
		$c['storage.wiki_reference.backend'],
		$c['storage.wiki_reference.mapper'],
		'flow_ref:wiki:by-source:v3',
		array(
			'ref_src_wiki',
			'ref_src_namespace',
			'ref_src_title',
		),
		array(
			'order' => 'ASC',
			'sort' => 'ref_src_object_id',
		)
	);
};
$c['storage.wiki_reference.indexes.revision_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.local_buffered'],
		$c['storage.wiki_reference.backend'],
		$c['storage.wiki_reference.mapper'],
		'flow_ref:wiki:by-revision:v3',
		array(
			'ref_src_wiki',
			'ref_src_object_type',
			'ref_src_object_id',
		),
		array(
			'order' => 'ASC',
			'sort' => array( 'ref_target_namespace', 'ref_target_title' ),
		)
	);
};
$c['storage.wiki_reference.indexes'] = function( $c ) {
	return array(
		$c['storage.wiki_reference.indexes.source_lookup'],
		$c['storage.wiki_reference.indexes.revision_lookup'],
	);
};
$c['storage.wiki_reference'] = function( $c ) {
	return new ObjectManager(
		$c['storage.wiki_reference.mapper'],
		$c['storage.wiki_reference.backend'],
		$c['db.factory'],
		$c['storage.wiki_reference.indexes'],
		array()
	);
};
$c['storage.url_reference.class'] = 'Flow\Model\URLReference';
$c['storage.url_reference.table'] = 'flow_ext_ref';
$c['storage.url_reference.primary_key'] = function ( $c ) {
	return array(
		'ref_src_wiki',
		'ref_src_namespace',
		'ref_src_title',
		'ref_src_object_id',
		'ref_type',
		'ref_target',
	);
};

$c['storage.url_reference.mapper'] = function( $c ) {
	return BasicObjectMapper::model(
		$c['storage.url_reference.class']
	);
};
$c['storage.url_reference.backend'] = function( $c ) {
	return new BasicDbStorage(
		// factory and table
		$c['db.factory'],
		$c['storage.url_reference.table'],
		$c['storage.url_reference.primary_key']
	);
};

$c['storage.url_reference.indexes.source_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.local_buffered'],
		$c['storage.url_reference.backend'],
		$c['storage.url_reference.mapper'],
		'flow_ref:url:by-source:v3',
		array(
			'ref_src_wiki',
			'ref_src_namespace',
			'ref_src_title',
		),
		array(
			'order' => 'ASC',
			'sort' => 'ref_src_object_id',
		)
	);
};
$c['storage.url_reference.indexes.revision_lookup'] = function( $c ) {
	return new TopKIndex(
		$c['memcache.local_buffered'],
		$c['storage.url_reference.backend'],
		$c['storage.url_reference.mapper'],
		'flow_ref:url:by-revision:v3',
		array(
			'ref_src_wiki',
			'ref_src_object_type',
			'ref_src_object_id',
		),
		array(
			'order' => 'ASC',
			'sort' => array( 'ref_target' ),
		)
	);
};
$c['storage.url_reference.indexes'] = function( $c ) {
	return array(
		$c['storage.url_reference.indexes.source_lookup'],
		$c['storage.url_reference.indexes.revision_lookup'],
	);
};
$c['storage.url_reference'] = function( $c ) {
	return new ObjectManager(
		$c['storage.url_reference.mapper'],
		$c['storage.url_reference.backend'],
		$c['db.factory'],
		$c['storage.url_reference.indexes'],
		array()
	);
};

$c['reference.updater.links-tables'] = function( $c ) {
	return new Flow\LinksTableUpdater( $c['storage'] );
};

$c['reference.clarifier'] = function( $c ) {
	return new Flow\ReferenceClarifier( $c['storage'], $c['url_generator'] );
};

$c['reference.extractor'] = function( $c ) {
	$default = array(
		new Flow\Parsoid\Extractor\ImageExtractor,
		new Flow\Parsoid\Extractor\PlaceholderExtractor,
		new Flow\Parsoid\Extractor\WikiLinkExtractor,
		new Flow\Parsoid\Extractor\ExtLinkExtractor,
		new Flow\Parsoid\Extractor\TransclusionExtractor,
	);
	$extractors = array(
		'header' => $default,
		'post-summary' => $default,
		'post' => $default,
	);
	// In addition to the defaults header and summaries collect
	// the related categories.
	$extractors['header'][] = $extractors['post-summary'][] = new Flow\Parsoid\Extractor\CategoryExtractor;

	return new Flow\Parsoid\ReferenceExtractor( $extractors );
};

$c['reference.recorder'] = function( $c ) {
	return new Flow\Data\Listener\ReferenceRecorder(
		$c['reference.extractor'],
		$c['reference.updater.links-tables'],
		$c['storage'],
		$c['repository.tree'],
		$c['deferred_queue']
	);
};

$c['user_merger'] = function( $c ) {
	return new Flow\Data\Utils\UserMerger(
		$c['db.factory'],
		$c['storage']
	);
};

$c['importer'] = function( $c ) {
	$importer = new Flow\Import\Importer(
		$c['storage'],
		$c['factory.loader.workflow'],
		$c['memcache.local_buffered'],
		$c['db.factory'],
		$c['deferred_queue'],
		$c['occupation_controller']
	);

	$importer->addPostprocessor( new Flow\Import\Postprocessor\SpecialLogTopic(
		$c['occupation_controller']->getTalkpageManager()
	) );

	return $importer;
};

$c['listener.editcount'] = function( $c ) {
	return new \Flow\Data\Listener\EditCountListener( $c['flow_actions'] );
};

$c['formatter.undoedit'] = function( $c ) {
	return new Flow\Formatter\RevisionUndoViewFormatter(
		$c['formatter.revisionview']
	);
};

$c['board_mover'] = function( $c ) {
	return new Flow\BoardMover(
		$c['db.factory'],
		$c['memcache.local_buffered'],
		$c['storage'],
		$c['occupation_controller']->getTalkpageManager()
	);
};

$c['parser'] = function() {
	global $wgParser;
	return $wgParser;
};

$c['default_logger'] = function() {
	return MediaWiki\Logger\LoggerFactory::getInstance( 'Flow' );
};

return $c;
