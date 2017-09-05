<?php

class ApiEchoUnreadNotificationPages extends ApiCrossWikiBase {
	/**
	 * @var bool
	 */
	protected $crossWikiSummary = false;

	/**
	 * @param ApiQuery $query
	 * @param string $moduleName
	 */
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'unp' );
	}

	/**
	 * @throws UsageException
	 */
	public function execute() {
		// To avoid API warning, register the parameter used to bust browser cache
		$this->getMain()->getVal( '_' );

		if ( $this->getUser()->isAnon() ) {
			$this->dieUsage( 'Login is required', 'login-required' );
		}

		$params = $this->extractRequestParams();

		$result = array();
		if ( in_array( wfWikiId(), $this->getRequestedWikis() ) ) {
			$result[wfWikiID()] = $this->getFromLocal( $params['limit'], $params['grouppages'] );
		}

		if ( $this->getRequestedForeignWikis() ) {
			$result += $this->getUnreadNotificationPagesFromForeign();
		}

		$apis = $this->foreignNotifications->getApiEndpoints( $this->getRequestedWikis() );
		foreach ( $result as $wiki => $data ) {
			$result[$wiki]['source'] = $apis[$wiki];
			$result[$wiki]['pages'] = $data['pages'] ?: array();
		}

		$this->getResult()->addValue( 'query', $this->getModuleName(), $result );
	}

	/**
	 * @param int $limit
	 * @param bool $groupPages
	 * @return array
	 */
	protected function getFromLocal( $limit, $groupPages ) {
		$dbr = MWEchoDbFactory::newFromDefault()->getEchoDb( DB_SLAVE );
		// If $groupPages is true, we need to fetch all pages and apply the ORDER BY and LIMIT ourselves
		// after grouping.
		$extraOptions = $groupPages ? array() : array( 'ORDER BY' => 'count DESC', 'LIMIT' => $limit );
		$rows = $dbr->select(
			array( 'echo_event', 'echo_notification' ),
			array( 'event_page_id', 'count' => 'COUNT(*)' ),
			array(
				'notification_user' => $this->getUser()->getId(),
				'notification_read_timestamp' => null,
				'event_deleted' => 0,
			),
			__METHOD__,
			array(
				'GROUP BY' => 'event_page_id',
			) + $extraOptions,
			array( 'echo_notification' => array( 'INNER JOIN', 'notification_event = event_id' ) )
		);

		if ( $rows === false ) {
			return array();
		}

		$nullCount = 0;
		$pageCounts = array();
		foreach ( $rows as $row ) {
			if ( $row->event_page_id !== null ) {
				$pageCounts[$row->event_page_id] = intval( $row->count );
			} else {
				$nullCount = intval( $row->count );
			}
		}

		$titles = Title::newFromIDs( array_keys( $pageCounts ) );

		$groupCounts = array();
		foreach ( $titles as $title ) {
			if ( $groupPages ) {
				// If $title is a talk page, add its count to its subject page's count
				$pageName = $title->getSubjectPage()->getPrefixedText();
			} else {
				$pageName = $title->getPrefixedText();
			}

			$count = $pageCounts[$title->getArticleId()];
			if ( isset( $groupCounts[$pageName] ) ) {
				$groupCounts[$pageName] += $count;
			} else {
				$groupCounts[$pageName] = $count;
			}
		}

		$userPageName = $this->getUser()->getUserPage()->getPrefixedText();
		if ( $nullCount > 0 && $groupPages ) {
			// Add the count for NULL (not associated with any page) to the count for the user page
			if ( isset( $groupCounts[$userPageName] ) ) {
				$groupCounts[$userPageName] += $nullCount;
			} else {
				$groupCounts[$userPageName] = $nullCount;
			}
		}

		arsort( $groupCounts );
		if ( $groupPages ) {
			$groupCounts = array_slice( $groupCounts, 0, $limit );
		}

		$result = array();
		foreach ( $groupCounts as $pageName => $count ) {
			if ( $groupPages ) {
				$title = Title::newFromText( $pageName );
				$pages = array( $title->getSubjectPage()->getPrefixedText(), $title->getTalkPage()->getPrefixedText() );
				if ( $pageName === $userPageName ) {
					$pages[] = null;
				}
				$pageDescription = array(
					'ns' => $title->getNamespace(),
					'title' => $title->getPrefixedText(),
					'unprefixed' => $title->getText(),
					'pages' => $pages,
				);
			} else {
				$pageDescription = array( 'title' => $pageName );
			}
			$result[] = $pageDescription + array(
				'count' => $count,
			);
		}
		if ( !$groupPages && $nullCount > 0 ) {
			$result[] = array(
				'title' => null,
				'count' => $nullCount,
			);
		}

		return array(
			'pages' => $result,
			'totalCount' => MWEchoNotifUser::newFromUser( $this->getUser() )->getLocalNotificationCount(),
		);
	}

	/**
	 * @return array
	 */
	protected function getUnreadNotificationPagesFromForeign() {
		$result = array();
		foreach ( $this->getFromForeign() as $wiki => $data ) {
			$result[$wiki] = $data['query'][$this->getModuleName()][$wiki];
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getAllowedParams() {
		global $wgEchoMaxUpdateCount;

		return parent::getAllowedParams() + array(
			'grouppages' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
			),
			'limit' => array(
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_DFLT => 10,
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => $wgEchoMaxUpdateCount,
				ApiBase::PARAM_MAX2 => $wgEchoMaxUpdateCount,
			),
			// there is no `offset` or `continue` value: the set of possible
			// notifications is small enough to allow fetching all of them at
			// once, and any sort of fetching would be unreliable because
			// they're sorted based on count of notifications, which could
			// change in between requests
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=query&meta=unreadnotificationpages' => 'apihelp-query+unreadnotificationpages-example-1',
		);
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Echo_(Notifications)/API';
	}
}
