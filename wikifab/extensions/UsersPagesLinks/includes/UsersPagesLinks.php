<?php
namespace UsersPagesLinks;
/**
 *
 * @file
 * @ingroup Extensions
 *
 * @author Pierre Boutet
 */

class UsersPagesLinks {

	public static function onLoadExtensionSchemaUpdates( \DatabaseUpdater $updater ) {

		$updater->addExtensionTable( 'userspageslinks',
				__DIR__ . '/tables.sql' );
		$updater->addExtensionIndex( 'userspageslinks', 'upl_user_page_link_pk',
				__DIR__ . '/patch_index_unique.sql' );
		return true;
	}
}