<?php

namespace GroupsPage;

/**
 * add fonctions to add/remove page of a page group
 *
 * @author Pierre Boutet
 */
class ApiGroupsPage extends \ApiBase {
	public function __construct($query, $moduleName) {
		parent::__construct ( $query, $moduleName );
	}
	public function getAllowedParams() {
		return array (
				'memberpage' => array (
						\ApiBase::PARAM_TYPE => 'string',
						\ApiBase::PARAM_REQUIRED => true
				),
				'groupspage' => array (
						\ApiBase::PARAM_TYPE => 'string',
						\ApiBase::PARAM_REQUIRED => true
				),
				'groupaction' => array (
						\ApiBase::PARAM_TYPE => 'string',
						\ApiBase::PARAM_REQUIRED => false
				)
		);
	}
	public function getParamDescription() {
		return [ ];
	}
	public function getDescription() {
		return false;
	}
	public function execute() {
		$params = $this->extractRequestParams ();
		$page = $params ['memberpage'];
		$grouppageParam = $params ['groupspage'];
		$groupaction = $params['groupaction'];

		//$grouppageParam = 'Group:Toto';

		$page = \Title::newFromDBkey( $page );
		$grouppage = \Title::newFromDBkey ( $grouppageParam );

		$r = [ ];
		$fail = false;

		if (! $page->getArticleID ()) {
			$r ['result'] = 'fail';
			$r ['detail'] = 'page not found';
			$fail = true;
		}
		if (! $grouppage->getArticleID ()) {
			$r ['result'] = 'fail';
			$r ['detail'] = 'grouppage '. $grouppageParam . ' not found';
			$fail = true;
		}

		if ($groupaction != 'add' && $groupaction != 'remove') {
			$r ['result'] = 'fail';
			$r ['detail'] = 'groupaction should be add or remove';
			$fail = true;
		}
		if ($fail) {
			$this->getResult ()->addValue ( null, $this->getModuleName (), $r );
			return;
		}

		$core = new GroupsPageCore ();

		if ($groupaction == 'add') {
			$result = $core->addPagesToGroup ( $grouppage, [
					$page
			] );
		} else {
			$result = $core->removePagesFromGroup ( $grouppage, [
					$page
			] );
		}

		$r = [ ];
		if ($result) {
			$r ['success'] = 1;
			$r ['result'] = 'OK';
			$r ['detail'] = $result;
		} else {
			$r ['result'] = 'fail';
			$r ['detail'] = $result;
		}

		$this->getResult ()->addValue ( null, $this->getModuleName (), $r );
	}
	public function needsToken() {
		return 'csrf';
	}
}