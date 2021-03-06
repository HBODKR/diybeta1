<?php

namespace Flow\Actions;

use IContextSource;
use OutputPage;
use Page;
use Title;

class ViewAction extends FlowAction {
	function __construct( Page $page, IContextSource $context ) {
		parent::__construct( $page, $context, 'view' );
	}

	public function doesWrites() {
		return false;
	}

	public function showForAction( $action, OutputPage $output = null ) {
		parent::showForAction( $action, $output );

		$title = $this->context->getTitle();
		$this->context->getUser()->clearNotification( $title );

		if ( $output === null ) {
			$output = $this->context->getOutput();
		}
		$output->addCategoryLinks( $this->getCategories( $title ) );

	}

	protected function getCategories( Title $title ) {
		$id = $title->getArticleId();
		if ( !$id ) {
			return array();
		}

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			/* from */ 'categorylinks',
			/* select */ array( 'cl_to', 'cl_sortkey' ),
			/* conditions */ array( 'cl_from' => $id ),
			__METHOD__
		);

		$categories = array();
		foreach ( $res as $row ) {
			$categories[$row->cl_to] = $row->cl_sortkey;
		}

		return $categories;
	}
}
