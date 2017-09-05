<?php

namespace UsersPagesLinks;

class Buttons  {

	public static function onParserFirstCallInit($parser ) {
		$parser->setFunctionHook( 'usersPagesLinksButton', 'UsersPagesLinks\\Buttons::parserButton' );
		$parser->setFunctionHook( 'usersPagesLinksUsersList', 'UsersPagesLinks\\Buttons::parserUsersPagesLinksUsersList' );

		return true;
	}


	public static function onBeforePageDisplay( $out ) {
		$out->addModules( 'ext.userspageslinks.js' );
		$out->addHTML(self::getConnectionRequiredModal($out));
	}

	public static function onSkinTemplateNavigation( &$page, &$content_navigation ) {
		global $wgUser, $wgUsersPagesLinksFoNamespaces;


		// if no button defined for this namespace, return
		if ($wgUsersPagesLinksFoNamespaces) {
			$ns = $page->getTitle()->getNamespace();
			if( ! isset($wgUsersPagesLinksFoNamespaces[$ns])) {
				return true;
			}
		}

		$pagesLinksActives = UsersPagesLinksCore::getInstance()->getUserPageLinks($wgUser, $page->getTitle());
		$pagesLinksCounters= UsersPagesLinksCore::getInstance()->getPageCounters($page->getTitle());

		if ( ! isset($content_navigation['NetworksLinks']) ) {
			$content_navigation['NetworksLinks'] = [];
		}


		foreach ($pagesLinksCounters as $type => $count) {
			if ($wgUsersPagesLinksFoNamespaces && ! in_array($type, $wgUsersPagesLinksFoNamespaces[$ns])) {
				//if this button not include for this namespace, skip it
				continue;
			}
			$content_navigation['NetworksLinks'][$type] = [
					'buttonType' => 'counter',
					'type' => $type,
					'count' => $count,
					'redundant' => true,
					'active' => in_array($type, $pagesLinksActives),
					'activebis' => isset($pagesLinksActives[$type]),
					'pageUri' => $page->getTitle()->getPrefixedDBkey()
			];
		}

		return true;
	}

	private static function formatUsersList($users, $class) {
		$out = '<div class="usersPageLinksUsers row">';

		foreach ($users as $followedUser) {
			$out .= '<div class="col-md-4 col-sm-6 col-xs-12 UserListcard">';
			$data = [];

			$data['id'] = $followedUser->getId();
			$data['url'] = $followedUser->getUserPage()->getLinkURL();
			$avatar = new \wAvatar( $data['id'], 'ml' );
			$data['avatar'] = $avatar->getAvatarURL();
			$data['name'] = $followedUser->getRealName();
			if ( ! $data['name']) {
				$data['name'] = $followedUser->getName();
			}

			$out .= '<a href="'.$data['url'].'">';
			$out .= '<div class="avatar">' . $data['avatar'] . '</div>';
			$out .= '<span class="name">' . $data['name'] . '</span>';
			$out .= '</a>';

			$out .= '</div>';
		}
		$out .= '</div>';
		return $out;

	}

	static function getUsersListHtml(\Title $page, $type) {
		$users = UsersPagesLinksCore::getInstance()->getPagesLinksUsers($page, $type);
		return self::formatUsersList($users, $type);
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

	public static function parserUsersPagesLinksUsersList( \Parser $input, $type = 'undifined', $grouppage = null ) {


		if( ! $grouppage) {
			if (!$input->getTitle() ) {
				trigger_error("No title founded");
				return false;
			}
			$page = $input->getTitle();
		} else {
			$page = \Title::newFromDBkey($grouppage);
		}

		$html = self::getUsersListHtml($page, $type);


		return array( $html, 'noparse' => true, 'isHTML' => true );
	}


	public static function parserButton( \Parser $input, $type = 'star', $grouppage = null ) {
		global $wgUsersPagesLinksTypesUndoLabelsKey;

		$input->getOutput()->addModules( 'ext.userspageslinks.js' );

		if( ! $grouppage) {
			if (!$input->getTitle() ) {
				trigger_error("No title founded");
				return false;
			}
			$grouppage = $input->getTitle()->getPrefixedDBkey();
		}

		$pagesLinksActives = UsersPagesLinksCore::getInstance()->getUserPageLinks($input->getUser(), $input->getTitle());
		$pagesLinksCounters= UsersPagesLinksCore::getInstance()->getPageCounters($input->getTitle());

		if (UsersPagesLinksCore::getInstance()->hasLink($input->getUser(), $input->getTitle(), $type)){
			$addClass='rmAction';
		} else {
			$addClass='addAction';
		}

		$counter = isset($pagesLinksCounters[$type]) ? $pagesLinksCounters[$type] : 0;

		$doLabel = wfMessage('userspageslinks-' . $type);
		$undoLabel = '';

		switch($type) {
			case 'star':
				$faClass ='fa fa-heart';
				break;
			case 'member':
				$faClass ='fa fa-group';
				break;
			case 'ididit':
				$faClass ='fa fa-hand-peace-o';
				break;
			default:
				$faClass ='fa fa-eye';
				break;
		}
		$wgUsersPagesLinksTypesUndoLabelsKey = [
				'member' => 'userspageslinks-unmember'
		];

		$undoLabel = isset($wgUsersPagesLinksTypesUndoLabelsKey[$type]) ? wfMessage($wgUsersPagesLinksTypesUndoLabelsKey[$type]) : "";


		if (UsersPagesLinksCore::getInstance()->hasLink($input->getUser(), $input->getTitle(), $type)){
			$addClass='rmAction';
			if ($undoLabel !== '') {
				$label = $undoLabel;
			} else {
				$label = $doLabel;
			}
		} else {
			$addClass='addAction';
			$label = $doLabel;
		}

		$button = '<a class="UsersPagesLinksButton '.$addClass.'" data-linkstype="'.$type.'" data-page="'.$grouppage.'" >';
		$button .= '<button class=" doActionLabel">';
		$button .= '<span ><i class="'.$faClass.' upl_icon"></i> ';
		$button .= '<i class="fa fa-spinner fa-spin upl_loading" style="display:none"></i> ';
		$button .= '<span class="labelText" data-doLabel="' . $doLabel . '" data-undoLabel="' . $undoLabel . '">' . $label.'</span>';
		$button .= '</span>';
		$button .= '</button>';
		$button .= '</a>';

		$button .= '<a class="UsersPagesLinksButtonCounter '.$addClass.'" data-linkstype="'.$type.'" data-page="'.$grouppage.'" >';
		$button .= '<button>';
		$button .= $counter;
		$button .= '</button>';
		$button .= '</a>';


		return array( $button, 'noparse' => true, 'isHTML' => true );
	}



	/**
	 * Adds an "action" (i.e., a tab) to edit the current article with
	 * a form
	 */
	static function displayTab( $obj, &$links ) {


		$button = '<button class="addToGroupsPage" data-grouppage="Group:toto" data-page="Horloge_de_Fibonacci" > add to group</button>';

		$content_actions = &$links['views'];

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

		$content_actions['addtogroup'] = $form_create_tab;

		return true; // always return true, in order not to stop MW's hook processing!
	}
}
