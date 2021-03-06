<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Model\PostRevision;
use Flow\Model\UserTuple;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\NotificationController;
use EchoNotificationController;
use User;

/**
 * @group Flow
 */
class NotifiedUsersTest extends PostRevisionTestCase {
	protected $tablesUsed = array(
		'echo_event',
		'echo_notification',
		'flow_revision',
		'flow_topic_list',
		'flow_tree_node',
		'flow_tree_revision',
		'flow_workflow',
		'page',
		'revision',
		'text',
	);

	protected function setUp() {
		parent::setUp();

		if ( !class_exists( 'EchoEvent' ) ) {
			$this->markTestSkipped();
			return;
		}
	}

	public function testWatchingTopic() {
		$data = $this->getTestData();
		if ( !$data ) {
			$this->markTestSkipped();
			return;
		}

		/** @var User $user */
		$user = $data['user'];
		$user->addWatch( $data['topicWorkflow']->getArticleTitle() );

		$events = $data['notificationController']->notifyPostChange( 'flow-post-reply',
			array(
				'topic-workflow' => $data['topicWorkflow'],
				'title' => $data['boardWorkflow']->getOwnerTitle(),
				'user' => $data['agent'],
				'reply-to' => $data['topic'],
				'topic-title' => $data['topic'],
				'revision' => $data['post-2'],
			) );

		$this->assertNotifiedUser( $events, $user, $data['agent'] );
	}

	public function testWatchingBoard() {
		$data = $this->getTestData();
		if ( !$data ) {
			$this->markTestSkipped();
			return;
		}

		/** @var User $user */
		$user = $data['user'];
		$user->addWatch( $data['boardWorkflow']->getArticleTitle() );

		$events = $data['notificationController']->notifyNewTopic( array(
			'board-workflow' => $data['boardWorkflow'],
			'topic-workflow' => $data['topicWorkflow'],
			'topic-title' => $data['topic'],
			'first-post' => $data['post'],
			'user' => $data['agent'],
		) );

		$this->assertNotifiedUser( $events, $user, $data['agent'] );
	}

	protected function assertNotifiedUser( array $events, User $notifiedUser, User $notNotifiedUser ) {
		$users = array();
		foreach( $events as $event ) {
			$iterator = EchoNotificationController::getUsersToNotifyForEvent( $event );
			foreach( $iterator as $user ) {
				$users[] = $user;
			}
		}

		// convert user objects back into user ids to simplify assertion
		$users = array_map( function( $user ) { return $user->getId(); }, $users );

		$this->assertContains( $notifiedUser->getId(), $users );
		$this->assertNotContains( $notNotifiedUser->getId(), $users );
	}

	/**
	 * @return bool|array
	 * {
	 *     False on failure, or array with these keys:
	 *
	 *     @type Workflow $boardWorkflow
	 *     @type Workflow $topicWorkflow
	 *     @type PostRevision $post
	 *     @type PostRevision $topic
	 *     @type User $user
	 *     @type User $agent
	 *     @type NotificationController $notificationController
	 * }
	 */
	protected function getTestData() {
		$user = User::newFromName( 'Flow Test User' );
		$user->addToDatabase();
		$agent = User::newFromName( 'Flow Test Agent' );
		$agent->addToDatabase();

		$tuple = UserTuple::newFromUser( $agent );
		$topicTitle = $this->generateObject( array(
			'rev_user_wiki' => $tuple->wiki,
			'rev_user_id' => $tuple->id,
			'rev_user_ip' => $tuple->ip,

			'rev_flags' => 'wikitext',
			'rev_content' => 'some content',
		) );

		/*
		 * We don't really *have* to store everything for this test. We could
		 * just work off of the object we have here.
		 * However, our current CI setup forces us to not use Parsoid & write
		 * wikitext instead.
		 * Notifications need to convert the content to HTML & in order to do so
		 * have to know the title of the board the post is on (to resolve links
		 * & stuff).
		 * For those combined reasons, we'll store everything.
		 */
		$this->store( $topicTitle );

		$boardWorkflow = $topicTitle->getCollection()->getBoardWorkflow();
		$topicWorkflow = $topicTitle->getCollection()->getWorkflow();
		$firstPost = $topicTitle->reply( $topicWorkflow, $agent, 'ffuts dna ylper', 'wikitext' );
		$this->store( $firstPost );

		/*
		 * Generation of the 2nd post will be a bit hacky: there's some code to ensure
		 * that first replies are ignored when sending notifications, and that is done
		 * by checking timestamps. We want our tests to run fast so I won't sleep for
		 * a second. Instead, I'll just inject the new timestamp (which is 2 seconds
		 * in the future) in there.
		 */
		$secondPost = $topicTitle->reply( $topicWorkflow, $agent, 'lorem ipsum', 'wikitext' );
		$newId = UUID::getComparisonUUID( (int) $secondPost->getPostId()->getTimestamp( TS_UNIX ) + 2 );
		$reflection = new \ReflectionProperty( $secondPost, 'postId' );
		$reflection->setAccessible( true );
		$reflection->setValue( $secondPost, $newId );
		$this->store( $secondPost );

		return array(
			'boardWorkflow' => $boardWorkflow,
			'topicWorkflow' => $topicWorkflow,
			'post' => $firstPost,
			'post-2' => $secondPost,
			'topic' => $topicTitle,
			'user' => $user,
			'agent' => $agent,
			'notificationController' => Container::get( 'controller.notification' ),
		);
	}
}
