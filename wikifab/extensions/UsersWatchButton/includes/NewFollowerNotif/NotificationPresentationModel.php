<?php

namespace NewFollowerNotif;

class NotificationPresentationModel extends \EchoEventPresentationModel {
	public function getIconType() {
		return 'user-add';
	}

	public function canRender() {
		return true;
	}

	protected function getHeaderMessageKey() {
		return "newfollower-notif-header-message";
	}

	public function getHeaderMessage() {
		$msg = $this->msg( $this->getHeaderMessageKey() );

		list( $formattedName, $genderName ) = $this->getAgentForOutput();
		$page = $this->event->getTitle()->getBaseText();
		$msg->params( $formattedName, $genderName, $page );

		return $msg;
	}

	public function getBodyMessage() {
		$key = 'combinedwatchlist-notif-body-message';
		$msg = $this->msg( $key );
		list( $formattedName, $genderName ) = $this->getAgentForOutput();
		$page = $this->event->getTitle()->getBaseText();
		$agentLink = $this->getAgentLink();
		$msg->params( $formattedName, $genderName, $page, $agentLink );
		return $msg;
	}




	public function getPrimaryLink(){
		return $this->getPageLink($this->event->getTitle(), '', true);
	}


	public function getSecondaryLinks() {
		$links = array( $this->getAgentLink() );
		return $links;

	}
}
