<?php

namespace PageMediaGallery;

use GroupsPage\GroupsPageCore;

class Hooks {

	static public function onExtensionLoad() {

	}

	static function addToForm($text) {

		static $isStarted = false;
		if (!$isStarted) {
			self::start();
			$isStarted = true;
		}

	}
	static function start( ) {
		global $wgOut, $wgScriptPath, $wgJsMimeType, $wgFileExtensions, $wgUser;
		$wgOut->addModules ( 'ext.pageMediaGallery' );
		$wgOut->addJsConfigVars ( array (
				'wgFileExtensions' => array_values ( array_unique ( $wgFileExtensions ) )
		) );

		$pmgVars = array (
				'scriptPath' => $wgScriptPath
		);

		$pmgVars = json_encode ( $pmgVars );

		if ($wgUser->getId ()) {
			$galleryBody = self::getGalleryBody ( $wgOut->getTitle () );
			$wgOut->addHTML ( $galleryBody );
		}
		$wgOut->addScript ( "<script type=\"$wgJsMimeType\">window.pmgVars = $pmgVars;</script>\n" );

		return true;
	}



	public static function beforePageDisplay( $out ) {
		$out->addModules( 'ext.userswatchbutton.js' );
	}

	static function getGalleryBody($page) {

		// get all existing image linked to the page
		$files = [];
		if ($page) {
			$fileTitles = GroupsPageCore::getInstance()->getMemberPages($page);
			foreach ($fileTitles as $title) {
				$file = wfLocalFile( $title );
				//$file = \LocalFile::newFromTitle($fileTitles);
				if($file) {
					$files[] = $file;
				}
			}
		}


		$out = '';
		$out .= '<div id="PageGallery" class="pg_sidebar">

				<h6 class="PageGalleryTitle" title="'.wfMessage( 'pmg-gallery-subtitle' )->parse().'"
								type="button" data-toggle="tooltip" data-placement="right">'
					. wfMessage( 'pmg-gallery-title' )->parse().'
					<span class="smwtticon info"></span></h6>

				<div id="PageGalleryUploader">';

		foreach ($files as $file) {
			$fileUrl = $file->getUrl();
			$params = ['width' => 400];
			$mto = $file->transform( $params );
			if ( $mto && !$mto->isError() ) {
				// thumb Ok, change the URL to point to a thumbnail.
				$fileUrl = wfExpandUrl( $mto->getUrl(), PROTO_RELATIVE );
			}
			$out .= '<img class="mediaGalleryFile" src="' . $fileUrl . '" alt="' . $file->getName() . '"/> ';
		}

		$out .= '</div>
			<div class="pageGalleryControls">
			</div>
		</div>
				';
		return $out;
	}

	public static function onUploadComplete( &$image ) {

		// if file comment contain a link to a page, we expressaly link it to the page
		// Warning : page is not recorded yet, so it has no id !!!

		$text = $image->getLocalFile()->getDescription();


		$pattern = '/\[\[(.*)\]\]/';
		if (preg_match($pattern, $text, $matches)) {
			$pageUri = $matches[1];
			$page = \Title::newFromText($pageUri);
			$fileTitle = $image->getLocalFile()->getTitle();
			if ($page->exists()) {
				GroupsPageCore::getInstance()->addPagesToGroup($page, [$fileTitle]);
			}
		}
	}


}