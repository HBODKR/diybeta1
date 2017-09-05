<?php
/**
 * class for display sample tutorial on home page, or on user page
 *
 * @file
 * @ingroup Extensions
 *
 * @author Pierre Boutet
 */

class WfSampleDisplay {


	protected static function getTags() {
		return array(
			'__lastTutorials__' => 'lastTuto',
			'__topTutorials__' => 'top'
		);
	}

	protected static function getSearchEngine() {
		return SearchEngine::create();
	}


	public static function addSampleParser( $input, $type = 'top', $number = 4 ) {
		
		//var_dump($input);
		//var_dump($type);
		//var_dump($input->getOutput());

		$term = 'chaise';
		$term = $type;

		$profile = new ProfileSection( __METHOD__ );
		$profile = new ProfileSection( "SpecialWfSearch::showResults" );

		$search = self::getSearchEngine();
		$search->setLimitOffset( $number, 0 );
		$search->setNamespaces( array(0) );
		$search->prefix = '';

		$term = $search->transformSearchTerm( $term );

		//wfRunHooks( 'SpecialSearchSetupEngine', array( $this, $this->profile, $search ) );

		$term = $search->transformSearchTerm( $term );


		//$titleMatches = $search->searchTitle( $term );
		$textMatches = $search->searchText( $term );


		$wikifabSearchResultFormatter = new WikifabSearchResultFormatter();
		$template = $GLOBALS['egChameleonLayoutFileSearchResult'];
		$wikifabSearchResultFormatter->setTemplate($template);

		$out = "";
		$matches = $textMatches;
		$result = $matches->next();
		while ( $result ) {
			$out .= $wikifabSearchResultFormatter->getPageDetails( $result );
			$result = $matches->next();
		}

		return array( $out, 'noparse' => true, 'isHTML' => true );
	}

}