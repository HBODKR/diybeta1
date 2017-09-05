<?php
namespace UsersPagesLinks;

/**
 * add fonctions to add/remove users pages links
 *
 * @ingroup SF
 *
 * @author Pierre Boutet
 */
class ApiUsersPagesLinks extends \ApiBase {

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName );
	}

	public function getAllowedParams() {
		return array(
				'user' => array (
						\ApiBase::PARAM_TYPE => 'string',
						\ApiBase::PARAM_REQUIRED => false
				),
				'page' => array (
						\ApiBase::PARAM_TYPE => 'string',
						\ApiBase::PARAM_REQUIRED => true
				),
				'type' => array (
						\ApiBase::PARAM_TYPE => 'string',
						\ApiBase::PARAM_REQUIRED => true
				),
				'upl_action' => array (
						\ApiBase::PARAM_TYPE => 'string',
						\ApiBase::PARAM_REQUIRED => true
				),
		);
	}

	public function getParamDescription() {
		return [];
	}

	public function getDescription() {
		return false;
	}

	private function failResult($message) {
		$r = [
				'result' => 'fail',
				'detail' => $message
		];
		$this->getResult()->addValue(null, $this->getModuleName(), $r);

		return false;
	}

	public function execute() {
		global $wgUsersPagesLinksTypes;

		$params = $this->extractRequestParams();

		$userParam = $params['user'];
		$pageParam = $params['page'];
		$type = $params['type'];
		$action = $params['upl_action'];

		// check params
		if (!in_array($type, $wgUsersPagesLinksTypes)) {
			return $this->failResult('Type not allowed for UsersPagesLinks : ' . $type . ' autorized = ' . implode ($wgUsersPagesLinksTypes));
		}

		$allowAction = ['add', 'remove'];
		if (! in_array($action, $allowAction)){
			return $this->failResult('Action not allowed for UsersPagesLinks : ' . $action);
		}

		if ($userParam){
			// TODO manage specifying user in some case (remove  bad guy from group,...)
			return $this->failResult('Specifying user is not allowed for this action : ' . $action);
		}

		$page = \Title::newFromDBkey($pageParam);
		if (! $page || ! $page->getText()) {
			return $this->failResult('Page not found : ' . $pageParam);
		}

		//all params are checked

		$user = $this->getUser();

		$core = new UsersPagesLinksCore();

		if ($action == 'remove'){
			$result = $core->removeLink( $user, $page, $type );
		} else {
			$result = $core->addLink( $user, $page, $type );
		}

		$r=[];
		if($result) {
			$r['success'] = 1;
			$r['result'] = 'OK';
			$r['detail'] = $result;
		} else {
			$r['result'] = 'fail';
			$r['detail'] = $result;
		}

		$this->getResult()->addValue(null, $this->getModuleName(), $r);
	}

	public function needsToken() {
		return 'csrf';
	}
}