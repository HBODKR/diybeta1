<?php

/**
 * A small wrapper around ObjectCache to manage
 * storing the last time a user has seen notifications
 */
class EchoSeenTime {

	/**
	 * Allowed notification types
	 * @var array
	 */
	private static $allowedTypes = array( 'alert', 'message' );

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var BagOStuff
	 */
	private $cache;

	/**
	 * @param User $user A logged in user
	 */
	private function __construct( User $user ) {
		$this->user = $user;
		$this->cache = ObjectCache::getInstance( 'db-replicated' );
	}

	/**
	 * @param User $user
	 * @return EchoSeenTime
	 */
	public static function newFromUser( User $user ) {
		return new self( $user );
	}

	/**
	 * @param string $type Type of seen time to get
	 * @param int $flags BagOStuff::READ_LATEST to use the master
	 * @param int $format Format to return time in, defaults to TS_MW
	 * @return string|bool Timestamp in specified format, or false if no stored time
	 */
	public function getTime( $type = 'all', $flags = 0, $format = TS_MW ) {
		$vals = array();
		if ( $type === 'all' ) {
			foreach ( self::$allowedTypes as $allowed ) {
				// Use TS_MW, then convert later, so max works properly for
				// all formats.
				$vals[] = $this->getTime( $allowed, $flags, TS_MW );
			}

			return wfTimestamp( $format, min( $vals ) );
		}

		if ( $this->validateType( $type ) ) {
			$key = wfMemcKey( 'echo', 'seen', $type, 'time', $this->user->getId() );
			$cas = 0; // Unused, but we have to pass something by reference
			$data = $this->cache->get( $key, $cas, $flags );
			if ( $data === false ) {
				// Check if the user still has it set in their preferences
				$data = $this->user->getOption( 'echo-seen-time', false );
			}
		}
		if ( $data !== false ) {
			$formattedData = wfTimestamp( $format, $data );
		} else {
			$formattedData = $data;
		}

		return $formattedData;
	}

	/**
	 * Sets the seen time
	 *
	 * @param string $time Time, in TS_MW format
	 * @param string $type Type of seen time to set
	 */
	public function setTime( $time, $type = 'all' ) {
		if ( $type === 'all' ) {
			foreach ( self::$allowedTypes as $allowed ) {
				$this->setTime( $time, $allowed );
			}
		} else {
			if ( $this->validateType( $type ) ) {
				$key = wfMemcKey( 'echo', 'seen', $type, 'time', $this->user->getId() );

				return $this->cache->set( $key, $time );
			}
		}
	}

	/**
	 * Validate the given type, make sure it is allowed.
	 *
	 * @param string $type Given type
	 * @return bool Type is allowed
	 */
	private function validateType( $type ) {
		return in_array( $type, self::$allowedTypes );
	}
}
