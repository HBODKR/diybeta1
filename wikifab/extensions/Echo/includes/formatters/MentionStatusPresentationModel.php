<?php

/**
 * Presenter for 'mention-failure' and 'mention-success' notifications
 *
 * @author Christoph Fischer <christoph.fischer@wikimedia.de>
 *
 * @license GNU GPL v2+
 */
class EchoMentionStatusPresentationModel extends EchoEventPresentationModel {
	use EchoPresentationModelSectionTrait;

	public function getIconType() {
		if ( $this->isMixedBundle() ) {
			return 'mention-status-bundle';
		}
		if ( $this->isMentionSuccess() ) {
			return 'mention-success';
		}
		return 'mention-failure';
	}

	public function getHeaderMessage() {
		if ( $this->isTooManyMentionsFailure() ) {
			$msg = $this->getMessageWithAgent( 'notification-header-mention-failure-too-many' );
			$msg->numParams( $this->getMaxMentions() );
			return $msg;
		}

		if ( $this->isBundled() ) {
			if ( $this->isMixedBundle() ) {
				$successCount = $this->getBundleSuccessCount();

				$msg = $this->getMessageWithAgent( 'notification-header-mention-status-bundle' );
				$msg->numParams( $this->getBundleCount() );
				$msg->params( $this->getTruncatedTitleText( $this->event->getTitle() ) );
				$msg->numParams( $this->getBundleCount() - $successCount );
				$msg->numParams( $successCount );
				return $msg;
			}
			if ( $this->isMentionSuccess() ) {
				$msgKey = 'notification-header-mention-success-bundle';
			} else {
				$msgKey = 'notification-header-mention-failure-bundle';
			}
			$msg = $this->getMessageWithAgent( $msgKey );
			$msg->numParams( $this->getBundleCount() );
			$msg->params( $this->getTruncatedTitleText( $this->event->getTitle() ) );
			return $msg;
		}

		if ( $this->isMentionSuccess() ) {
			$msgKey = 'notification-header-mention-success';
		} else {
			// Messages that can be used here:
			// * notification-header-mention-failure-user-unknown
			// * notification-header-mention-failure-user-anonymous
			$msgKey = 'notification-header-mention-failure-' . $this->getFailureType();
		}
		$msg = $this->getMessageWithAgent( $msgKey );
		$msg->params( $this->getSubjectName() );
		return $msg;
	}

	public function getCompactHeaderMessage() {
		if ( $this->isMentionSuccess() ) {
			$msg = $this->getMessageWithAgent( 'notification-compact-header-mention-success' );
		} else {
			// Messages that can be used here:
			// * notification-compact-header-mention-failure-user-unknown
			// * notification-compact-header-mention-failure-user-anonymous
			$msg = $this->msg( 'notification-compact-header-mention-failure-' . $this->getFailureType() );
		}
		$msg->params( $this->getSubjectName() );
		return $msg;
	}

	public function getPrimaryLink() {
		return array(
			// Need FullURL so the section is included
			'url' => $this->getTitleWithSection()->getFullURL(),
			'label' => $this->msg( 'notification-link-text-view-mention-failure' )
				->numParams( $this->getBundleCount() )
				->text()
		);
	}

	public function getSecondaryLinks() {
		if ( $this->isBundled() ) {
			return false;
		}

		$talkPageLink = $this->getPageLink(
			$this->getTitleWithSection(),
			'',
			true
		);

		return array( $talkPageLink );
	}

	public function isMentionSuccessEvent( EchoEvent $event ) {
		return $event->getType() === 'mention-success';
	}

	private function isMentionSuccess() {
		return $this->isMentionSuccessEvent( $this->event );
	}

	private function getSubjectName() {
		return $this->event->getExtraParam( 'subject-name', '' );
	}

	private function getFailureType() {
		return $this->event->getExtraParam( 'failure-type', 'user-unknown' );
	}

	private function isTooManyMentionsFailure() {
		return $this->getType() === 'mention-failure-too-many';
	}

	private function getMaxMentions() {
		global $wgEchoMaxMentionsCount;
		return $this->event->getExtraParam( 'max-mentions', $wgEchoMaxMentionsCount );
	}

	private function getBundleSuccessCount() {
		return $this->getBundleCount( false, array( $this, 'isMentionSuccessEvent' ) );
	}

	private function isMixedBundle() {
		$successCount = $this->getBundleSuccessCount();
		$failCount = $this->getBundleCount() - $successCount;
		return $successCount > 0 && $failCount > 0;
	}
}
