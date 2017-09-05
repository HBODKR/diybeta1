<?php

namespace GroupsPage;

class Hook  {

	public static function onLoadExtensionSchemaUpdates( \DatabaseUpdater $updater ) {

		$updater->addExtensionTable( 'pagesbelonging',
				__DIR__ . '/tables.sql' );
		return true;
	}

	/**
	 * @param EchoEvent $event
	 * @param string $param
	 * @param Message $message
	 * @param User $user
	 */
	protected function processParam( $event, $param, $message, $user ) {


		parent::processParam( $event, $param, $message, $user );

		return;
	}



	# Parser function to insert a link changing a tab.
	public static function onParserFirstCallInit( $parser ) {
		$parser->setFunctionHook( 'joinGroupButton', array('GroupsPage\\Buttons', 'parserButton' ));
		$parser->setFunctionHook( 'displayMemberTutorials', array('GroupsPage\\Hook', 'parserDisplayMemberTutorials' ));
		$parser->setFunctionHook( 'displayExploreGroups', array('GroupsPage\\ExploreGroupsTag', 'addSampleParser' ));
		return true;
	}


	public static function parserDisplayMemberTutorials(\Parser $input, $groupsPage =  null) {

		$out = '<!-- display member tuttorials -->';

		if( $groupsPage == null || $groupsPage == 'this') {
			$grouppageTitle = $input->getTitle();
		} else {
			// TODO : how to get back page Title when in a namespace ? (have to change '_' in ':')
			$grouppageTitle = \Title::newFromDBkey($groupsPage);
		}

		if( !$grouppageTitle ) {
			return '';
		}
		$pages = GroupsPageCore::getInstance()->getMemberPages($grouppageTitle);


		$wikifabSearchResultFormatter = new \WikifabExploreResultFormatter();
		$wikifabSearchResultFormatter->setTemplate($GLOBALS['egChameleonLayoutFileSearchResult']);

		$out .= '<div class="row">';
		foreach ($pages as $page) {
			$result = \SearchResult::newFromTitle( $page );
			$out .= $wikifabSearchResultFormatter->getPageDetails( $result );
		}
		$out .= '</div>';
		$out .= '<!-- end display member tuttorials -->';



		return array( $out, 'noparse' => true, 'isHTML' => true );
	}
}
