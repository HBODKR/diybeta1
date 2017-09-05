<?php

/**
 * Database mapper for EchoNotification model
 */
class EchoNotificationMapper extends EchoAbstractMapper {

	/**
	 * @var EchoTargetPageMapper
	 */
	protected $targetPageMapper;

	public function __construct(
		MWEchoDbFactory $dbFactory = null,
		EchoTargetPageMapper $targetPageMapper = null
	) {
		parent::__construct( $dbFactory );
		if ( $targetPageMapper === null ) {
			$targetPageMapper = new EchoTargetPageMapper( $this->dbFactory );
		}
		$this->targetPageMapper = $targetPageMapper;
	}

	/**
	 * Insert a notification record
	 * @param EchoNotification
	 * @return null
	 */
	public function insert( EchoNotification $notification ) {
		$dbw = $this->dbFactory->getEchoDb( DB_MASTER );

		$row = $notification->toDbArray();
		$listeners = $this->getMethodListeners( __FUNCTION__ );

		DeferredUpdates::addUpdate( new AtomicSectionUpdate(
			$dbw,
			__METHOD__,
			function ( IDatabase $dbw, $fname ) use ( $row, $listeners ) {
				// Reset the bundle base if this notification has a display hash
				// the result of this operation is that all previous notifications
				// with the same display hash are set to non-base because new record
				// is becoming the bundle base
				if ( $row['notification_bundle_display_hash'] ) {
					$dbw->update(
						'echo_notification',
						array( 'notification_bundle_base' => 0 ),
						array(
							'notification_user' => $row['notification_user'],
							'notification_bundle_display_hash' =>
								$row['notification_bundle_display_hash'],
							'notification_bundle_base' => 1
						),
						$fname
					);
				}

				$row['notification_timestamp'] =
					$dbw->timestamp( $row['notification_timestamp'] );
				$res = $dbw->insert( 'echo_notification', $row, $fname );

				if ( $res ) {
					foreach ( $listeners as $listener ) {
						$dbw->onTransactionIdle( $listener );
					}
				}
			}
		) );
	}

	/**
	 * Extract the offset used for notification list
	 * @param $continue String Used for offset
	 * @throws MWException
	 * @return int[]
	 */
	protected function extractQueryOffset( $continue ) {
		$offset = array(
			'timestamp' => 0,
			'offset' => 0,
		);
		if ( $continue ) {
			$values = explode( '|', $continue, 3 );
			if ( count( $values ) !== 2 ) {
				throw new MWException( 'Invalid continue param: ' . $continue );
			}
			$offset['timestamp'] = (int)$values[0];
			$offset['offset'] = (int)$values[1];
		}

		return $offset;
	}

	/**
	 * Get unread notifications by user in the amount specified by limit order by
	 * notification timestamp in descending order.  We have an index to retrieve
	 * unread notifications but it's not optimized for ordering by timestamp.  The
	 * descending order is only allowed if we keep the notification in low volume,
	 * which is done via a deleteJob
	 * @param User $user
	 * @param int $limit
	 * @param string $continue Used for offset
	 * @param string[] $eventTypes
	 * @param Title[] $titles If set, only return notifications for these pages.
	 *  To find notifications not associated with any page, add null as an element to this array.
	 * @param int $dbSource Use master or slave database
	 * @return EchoNotification[]
	 */
	public function fetchUnreadByUser( User $user, $limit, $continue, array $eventTypes = array(), array $titles = null, $dbSource = DB_SLAVE ) {
		$conds['notification_read_timestamp'] = null;
		if ( $titles ) {
			$conds['event_page_id'] = $this->getIdsForTitles( $titles );
			if ( !$conds['event_page_id'] ) {
				return array();
			}
		}
		return $this->fetchByUserInternal( $user, $limit, $continue, $eventTypes, $conds, $dbSource );
	}

	/**
	 * Get read notifications by user in the amount specified by limit order by
	 * notification timestamp in descending order.  We have an index to retrieve
	 * unread notifications but it's not optimized for ordering by timestamp.  The
	 * descending order is only allowed if we keep the notification in low volume,
	 * which is done via a deleteJob
	 * @param User $user
	 * @param int $limit
	 * @param string $continue Used for offset
	 * @param string[] $eventTypes
	 * @param Title[] $titles If set, only return notifications for these pages.
	 *  To find notifications not associated with any page, add null as an element to this array.
	 * @param int $dbSource Use master or slave database
	 * @return EchoNotification[]
	 */
	public function fetchReadByUser( User $user, $limit, $continue, array $eventTypes = array(), array $titles = null, $dbSource = DB_SLAVE ) {
		$conds = array( 'notification_read_timestamp IS NOT NULL' );
		if ( $titles ) {
			$conds['event_page_id'] = $this->getIdsForTitles( $titles );
			if ( !$conds['event_page_id'] ) {
				return array();
			}
		}
		return $this->fetchByUserInternal( $user, $limit, $continue, $eventTypes, $conds, $dbSource );
	}

	/**
	 * Get Notification by user in batch along with limit, offset etc
	 *
	 * @param User $user the user to get notifications for
	 * @param int $limit The maximum number of notifications to return
	 * @param string $continue Used for offset
	 * @param array $eventTypes Event types to load
	 * @param array $excludeEventIds Event id's to exclude.
	 * @param Title[] $titles If set, only return notifications for these pages.
	 *  To find notifications not associated with any page, add null as an element to this array.
	 * @return EchoNotification[]
	 */
	public function fetchByUser( User $user, $limit, $continue, array $eventTypes = array(), array $excludeEventIds = array(), array $titles = null ) {
		$dbr = $this->dbFactory->getEchoDb( DB_SLAVE );

		$conds = array();
		if ( $excludeEventIds ) {
			$conds[] = 'event_id NOT IN ( ' . $dbr->makeList( $excludeEventIds ) . ' ) ';
		}
		if ( $titles ) {
			$conds['event_page_id'] = $this->getIdsForTitles( $titles );
			if ( !$conds['event_page_id'] ) {
				return array();
			}
		}

		return $this->fetchByUserInternal( $user, $limit, $continue, $eventTypes, $conds );
	}

	protected function getIdsForTitles( array $titles ) {
		$ids = array();
		foreach ( $titles as $title ) {
			if ( $title === null ) {
				$ids[] = null;
			} elseif ( $title->exists() ) {
				$ids[] = $title->getArticleId();
			}
		}
		return $ids;
	}

	/**
	 * @param User $user the user to get notifications for
	 * @param int $limit The maximum number of notifications to return
	 * @param string $continue Used for offset
	 * @param array $eventTypes Event types to load
	 * @param array $conds Additional query conditions.
	 * @param int $dbSource Use master or slave database
	 * @return EchoNotification[]
	 */
	protected function fetchByUserInternal( User $user, $limit, $continue, array $eventTypes = array(), array $conds = array(), $dbSource = DB_SLAVE ) {
		$dbr = $this->dbFactory->getEchoDb( $dbSource );

		if ( !$eventTypes ) {
			return array();
		}

		// There is a problem with querying by event type, if a user has only one or none
		// flow notification and huge amount other notifications, the lookup of only flow
		// notification will result in a slow query.  Luckily users won't have that many
		// notifications.  We should have some cron job to remove old notifications so
		// the notification volume is in a reasonable amount for such case.  The other option
		// is to denormalize notification table with event_type and lookup index.
		$conds = array(
			'notification_user' => $user->getID(),
			'event_type' => $eventTypes,
			'event_deleted' => 0,
		) + $conds;

		$offset = $this->extractQueryOffset( $continue );

		// Start points are specified
		if ( $offset['timestamp'] && $offset['offset'] ) {
			$ts = $dbr->addQuotes( $dbr->timestamp( $offset['timestamp'] ) );
			// The offset and timestamp are those of the first notification we want to return
			$conds[] = "notification_timestamp < $ts OR ( notification_timestamp = $ts AND notification_event <= " . $offset['offset'] . " )";
		}

		$res = $dbr->select(
			array( 'echo_notification', 'echo_event' ),
			'*',
			$conds,
			__METHOD__,
			array(
				'ORDER BY' => 'notification_timestamp DESC, notification_event DESC',
				'LIMIT' => $limit,
			),
			array(
				'echo_event' => array( 'LEFT JOIN', 'notification_event=event_id' ),
			)
		);

		// query failure of some sort
		if ( !$res ) {
			return array();
		}

		$allNotifications = array();
		foreach ( $res as $row ) {
			try {
				$notification = EchoNotification::newFromRow( $row );
				if ( $notification ) {
					$allNotifications[] = $notification;
				}
			} catch ( Exception $e ) {
				$id = isset( $row->event_id ) ? $row->event_id : 'unknown event';
				wfDebugLog( 'Echo', __METHOD__ . ": Failed initializing event: $id" );
				MWExceptionHandler::logException( $e );
			}
		}

		$data = array();
		/** @var EchoNotification $notification */
		foreach ( $allNotifications as $notification ) {
			$data[ $notification->getEvent()->getId() ] = $notification;
		}

		return $data;
	}

	/**
	 * Get the last notification in a set of bundle-able notifications by a bundle hash
	 * @param User $user
	 * @param string $bundleHash The hash used to identify a set of bundle-able notifications
	 * @return EchoNotification|bool
	 */
	public function fetchNewestByUserBundleHash( User $user, $bundleHash ) {
		$dbr = $this->dbFactory->getEchoDb( DB_SLAVE );

		$row = $dbr->selectRow(
			array( 'echo_notification', 'echo_event' ),
			array( '*' ),
			array(
				'notification_user' => $user->getId(),
				'notification_bundle_hash' => $bundleHash
			),
			__METHOD__,
			array( 'ORDER BY' => 'notification_timestamp DESC', 'LIMIT' => 1 ),
			array(
				'echo_event' => array( 'LEFT JOIN', 'notification_event=event_id' ),
			)
		);
		if ( $row ) {
			return EchoNotification::newFromRow( $row );
		} else {
			return false;
		}
	}

	/**
	 * Fetch EchoNotifications by user and event IDs.
	 *
	 * @param User $user
	 * @param int[] $eventIds
	 * @return EchoNotification[]|bool
	 */
	public function fetchByUserEvents( User $user, $eventIds ) {
		$dbr = $this->dbFactory->getEchoDb( DB_SLAVE );

		$result = $dbr->select(
			array( 'echo_notification', 'echo_event' ),
			'*',
			array(
				'notification_user' => $user->getId(),
				'notification_event' => $eventIds
			),
			 __METHOD__,
			array(),
			array(
				'echo_event' => array( 'INNER JOIN', 'notification_event=event_id' ),
			)
		 );

		if ( $result ) {
			$notifications = array();
			foreach ( $result as $row ) {
				$notifications[] = EchoNotification::newFromRow( $row );
			}
			return $notifications;
		} else {
			return false;
		}
	}


	/**
	 * Fetch a notification by user in the specified offset.  The caller should
	 * know that passing a big number for offset is NOT going to work
	 * @param User $user
	 * @param int $offset
	 * @return EchoNotification|bool
	 */
	public function fetchByUserOffset( User $user, $offset ) {
		$dbr = $this->dbFactory->getEchoDb( DB_SLAVE );
		$row = $dbr->selectRow(
			array( 'echo_notification', 'echo_event' ),
			array( '*' ),
			array(
				'notification_user' => $user->getId(),
				'event_deleted' => 0,
			),
			__METHOD__,
			array(
				'ORDER BY' => 'notification_timestamp DESC, notification_event DESC',
				'OFFSET' => $offset,
				'LIMIT' => 1
			),
			array(
				'echo_event' => array( 'LEFT JOIN', 'notification_event=event_id' ),
			)
		);

		if ( $row ) {
			return EchoNotification::newFromRow( $row );
		} else {
			return false;
		}
	}

	/**
	 * Batch delete notifications by user and eventId offset
	 * @param User $user
	 * @param int $eventId
	 * @return boolean
	 */
	public function deleteByUserEventOffset( User $user, $eventId ) {
		$dbw = $this->dbFactory->getEchoDb( DB_MASTER );
		$res = $dbw->delete(
			'echo_notification',
			array(
				'notification_user' => $user->getId(),
				'notification_event < ' . (int)$eventId
			),
			__METHOD__
		);

		return $res;
	}

	/**
	 * Fetch ids of users that have notifications for certain events
	 *
	 * @param int[] $eventIds
	 * @return int[]|false
	 */
	public function fetchUsersWithNotificationsForEvents( $eventIds ) {
		$dbr = $this->dbFactory->getEchoDb( DB_SLAVE );

		$res = $dbr->select(
			array( 'echo_notification' ),
			array( 'userId' => 'DISTINCT notification_user' ),
			array(
				'notification_event' => $eventIds
			),
			__METHOD__
		);

		if ( $res ) {
			$userIds = array();
			foreach ( $res as $row ) {
				$userIds[] = $row->userId;
			}
			return $userIds;
		} else {
			return false;
		}
	}

}
