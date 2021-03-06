<?php

use Flow\Container;
use Flow\Data\ObjectManager;

$installPath = getenv( 'MW_INSTALL_PATH' ) !== false ?
	getenv( 'MW_INSTALL_PATH' ) :
	__DIR__ . '/../../..';

require_once( $installPath . '/maintenance/Maintenance.php' );
// extending these - autoloader not yet wired up at the point these are interpreted
require_once( $installPath .'/includes/utils/BatchRowWriter.php' );
require_once( $installPath . '/includes/utils/RowUpdateGenerator.php' );

/**
 * Populates ref_id in flow_wiki_ref & flow_ext_ref.
 *
 * @ingroup Maintenance
 */
class FlowPopulateRefId extends LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();

		$this->mDescription = 'Populates ref_id in flow_wiki_ref & flow_ext_ref';

		$this->setBatchSize( 300 );
	}

	protected function getUpdateKey() {
		return __CLASS__;
	}

	protected function doDBUpdates() {
		$types = array(
			'flow_wiki_ref' => Container::get( 'storage.wiki_reference' ),
			'flow_ext_ref' => Container::get( 'storage.url_reference' ),
		);

		foreach ( $types as $table => $storage ) {
			$this->update( $storage );
		}

		$this->output( "Completed\n" );

		return true;
	}

	/**
	 * @param ObjectManager $storage
	 * @throws \Flow\Exception\InvalidInputException
	 */
	protected function update( ObjectManager $storage ) {
		global $wgFlowCluster;

		$total = 0;
		while ( true ) {
			$references = (array) $storage->find( array( 'ref_id' => null, 'ref_src_wiki' => wfWikiID() ), array( 'limit' => $this->mBatchSize ) );
			if ( !$references ) {
				break;
			}

			$storage->multiPut( $references, array() );
			$total += count( $references );
			$this->output( "Ensured ref_id for " . $total . " " . get_class( $references[0] ) . " references...\n" );
			wfWaitForSlaves( false, false, $wgFlowCluster );
		}

	}
}

$maintClass = 'FlowPopulateRefId';
require_once( RUN_MAINTENANCE_IF_MAIN );
