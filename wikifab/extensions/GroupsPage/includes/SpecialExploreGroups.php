<?php

namespace GroupsPage;

class SpecialExploreGroups extends \SpecialWfExplore {

	private $pageLimitResult;

	public function __construct() {
		parent::__construct( 'ExploreGroups', array(NS_GROUP) );

		$this->pageLimitResult = ExploreGroupsTag::PAGE_LIMIT;

		$this->WfExploreCore->setNamespace(array('Group'));
		$this->WfExploreCore->setSearchPageTitle( self::getTitleFor( 'ExploreGroups' ));
		$this->WfExploreCore->setPageResultsLimit($this->pageLimitResult);
		$this->WfExploreCore->setFilters(array());
		$this->WfExploreCore->setMessageKey('load-more', 'exploregroups-explore-load-more');


		$formatter = new \WikifabExploreResultFormatter();
		$formatter->setTemplate(__DIR__ . '/../layout/layout-group-search-result.html');

		$this->WfExploreCore->setFormatter($formatter);

	}

	public function simpleSearch(\WebRequest $request) {

		$page = $request->getVal('page', '1');


		$dbr = wfGetDB( DB_SLAVE );

		$limit = $this->pageLimitResult;
		$offset = ($page -1 ) * $limit;

		$res = $dbr->select(
				'page',
				array(
						'page_id'
				),
				array(
						'page_namespace' => NS_GROUP,
				),
				__METHOD__,
				array(
					'LIMIT' => $limit,
					'OFFSET' => $offset
				)
		);

		$pages = array();
		if ( $res->numRows() > 0 ) {
			foreach ( $res as $row ) {
				$pages[] =\WikiPage::newFromID($row->page_id );
			}
			$res->free();
		}
		return $pages;
	}


	/**
	 * Fail to specify namespace :
	 * override execute function to get it simplier  while not found other solutions
	 */

	/**
	 * Entry point
	 *
	 * @param string $par
	 */
	public function execute( $par ) {
		global $smwgQDefaultNamespaces;

		$smwgQDefaultNamespaces = 'Group';

		$this->setHeaders();
		$this->outputHeader();
		$out = $this->getOutput();
		$out->allowClickjacking();
		$out->addModuleStyles( array(
				'mediawiki.special', 'mediawiki.special.search', 'mediawiki.ui', 'mediawiki.ui.button',
				'mediawiki.ui.input',
		) );
		$out->addModuleScripts( 'ext.wikifab.wfExplore.js' );

		// Strip underscores from title parameter; most of the time we'll want
		// text form here. But don't strip underscores from actual text params!
		$titleParam = str_replace( '_', ' ', $par );

		$request = $this->getRequest();

		$this->load();

		//$this->results = $this->WfExploreCore->executeSearch( $request );
		$this->WfExploreCore->results = $this->simpleSearch($request);

		$this->showResults();
	}

	/**
	 * @param string $term
	 */
	public function showResults( ) {

		$this->setupPage();

		$out = $this->getOutput();

		$out->addHtml($this->WfExploreCore->getHtmlForm());

		$out->addHtml($this->WfExploreCore->getSearchResultsHtml());

	}
}