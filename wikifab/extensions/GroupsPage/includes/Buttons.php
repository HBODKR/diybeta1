<?php

namespace GroupsPage;

class Buttons  {



	public static function onBeforePageDisplay( $out ) {
		global $wgGroupsPagesNamespacesEnabled;
		if( ! $out->getTitle()) {
			return true;
		}
		$ns = $out->getTitle()->getNamespace();
		if(isset($wgGroupsPagesNamespacesEnabled[$ns]) || $ns == NS_GROUP) {
			$out->addModules( 'ext.groupspage.js' );
		}
	}

	public static function onSkinTemplateNavigation( &$page, &$content_navigation) {
		global $wgUser, $wgGroupsPagesNamespacesEnabled;

		// if no button defined for this namespace, return
		if ($wgGroupsPagesNamespacesEnabled) {
			$ns = $page->getTitle()->getNamespace();
			if( ! in_array($ns, $wgGroupsPagesNamespacesEnabled, true)) {
				return true;;
			}
		}

		$groupsAdded = GroupsPageCore::getInstance()->getGroupsPages($page->getTitle());


		if( $wgUser->getId()) {
			$usersGroups = \UsersPagesLinks\UsersPagesLinksCore::getInstance()->getUsersPagesLinks($wgUser, 'member');
		} else {
			$usersGroups = [];
		}
		if ( ! isset($content_navigation['NetworksLinks']) ) {
			$content_navigation['NetworksLinks'] = [];
		}

		$content_navigation['NetworksLinks']['addtogroup'] = [
				'buttonType' => 'dropDown',
				'type' => 'addtogroup',
				'redundant' => true,
				'pageUri' => $page->getTitle()->getDBkey(),
				'label' =>  wfMessage('groupspage-addtogroup-label' ),
				'groups' => $usersGroups,
				'message' => wfMessage('groupspage-addtogroup-message' ),
				'groupsAdded' => $groupsAdded,
		];

		return true;
	}


	public static function getConnectionRequiredModal($out) {

		$loginTitle = \SpecialPage::getSafeTitleFor( 'Userlogin' );
		$page = $out->getTitle();
		$urlaction = 'returnto=' . $page->getPrefixedDBkey();
		$loginUrl = $loginTitle->getLocalURL( $urlaction );
		$createAccountUrl = $loginTitle->getLocalURL( $urlaction . '&type=signup' );

		$ret = '
				<div class="modal fade" id="connectionRequiredModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog" role="document">
				<div class="modal-content">
				<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">'.wfMessage('userlogin').'</h4>
				</div>
				<div class="modal-body">
				'.wfMessage('userspageslinks-connectionmodal-content').'
				</div>
				<div class="modal-footer">
				<a href="'.$loginUrl.'"><button type="button" class="btn btn-default">'.wfMessage('gotaccountlink').'</button></a>
				<a href="'.$createAccountUrl.'"><button type="button" class="btn btn-primary">'.wfMessage('nologinlink').'</button></a>
				</div>
				</div>
				</div>
				</div>';
		return $ret;
	}


	public static function parserButton( \Parser $input, $grouppage = null ) {

		return \UsersPagesLinks\Buttons::parserButton($input, 'member',$grouppage);
	}



	/**
	 * Adds an "action" (i.e., a tab) to edit the current article with
	 * a form
	 */
	static function displayTab( $obj, &$links ) {


		$button = '<button class="addToGroupsPage" data-grouppage="Group:toto" data-page="Horloge_de_Fibonacci" > add to group</button>';


		if ( method_exists ( $obj, 'getTitle' ) ) {
			$title = $obj->getTitle();
		} else {
			$title = $obj->mTitle;
		}
		$groupNameSpace = [ NS_GROUP, NS_GROUP_TALK];

		if ( !isset( $title ) ||
			( !in_array( $title->getNamespace(), $groupNameSpace ) ) ) {
					return true;
		}

		$form_create_tab = array(
			'class' => '',
			'text' => $button,
			'href' => $title->getLocalURL( 'action=formcreate' )
		);

		$content_actions = &$links['views'];
		$content_actions['addtogroup'] = $form_create_tab;

		return true; // always return true, in order not to stop MW's hook processing!
	}
}
