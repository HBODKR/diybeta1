<?php

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once ( "$IP/maintenance/Maintenance.php" );

class BackfillReadBundles extends Maintenance {
	public function __construct() {
		parent::__construct();

		$this->mDescription = "Backfill echo_notification.notification_read_timestamp for bundles";

		$this->setBatchSize( 300 );
	}

	public function execute() {
		$dbFactory = MWEchoDbFactory::newFromDefault();
		$dbw = $dbFactory->getEchoDb( DB_MASTER );
		$dbr = $dbFactory->getEchoDb( DB_SLAVE );
		$iterator = new BatchRowIterator(
			$dbr,
			'echo_notification',
			array( 'notification_user', 'notification_event' ),
			$this->mBatchSize
		);
		$iterator->setFetchColumns( array( 'notification_bundle_display_hash', 'notification_read_timestamp' ) );

		$unreadNonBase = $dbr->selectSQLText(
			'echo_notification',
			'notification_bundle_display_hash',
			array(
				'notification_bundle_base' => 0,
				'notification_read_timestamp IS NULL',
				"notification_bundle_display_hash <> ''",
			)
		);

		$iterator->addConditions( array(
			'notification_bundle_base' => 1,
			'notification_read_timestamp IS NOT NULL',
			"notification_bundle_display_hash IN ( $unreadNonBase )",
		) );

		$processed = 0;
		foreach ( $iterator as $batch ) {
			foreach ( $batch as $row ) {
				$userId = $row->notification_user;
				$displayHash = $row->notification_bundle_display_hash;
				$readTimestamp = $row->notification_read_timestamp;

				$result = $dbw->update(
					'echo_notification',
					array( 'notification_read_timestamp' => $readTimestamp ),
					array(
						'notification_user' => $userId,
						'notification_bundle_display_hash' => $displayHash,
						'notification_bundle_base' => 0,
						'notification_read_timestamp IS NULL',
					)
				);

				if ( !$result ) {
					$this->output( "Failed to set read_timestamp on notifications with bundle_display_hash: $displayHash\n" );
				}

				$processed += $dbw->affectedRows();
			}

			$this->output( "Updated $processed notifications.\n" );
			$dbFactory->waitForSlaves();
		}
	}
}

$maintClass = "BackfillReadBundles";
require_once ( DO_MAINTENANCE );
