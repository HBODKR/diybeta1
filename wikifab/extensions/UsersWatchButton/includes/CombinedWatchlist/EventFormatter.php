<?php
namespace CombinedWatchlist;

class EventFormatter extends \EchoBasicFormatter {
    /**
     * @param EchoEvent $event
     * @param string $param
     * @param Message $message
     * @param User $user
     */
    protected function processParam( $event, $param, $message, $user ) {


    	parent::processParam( $event, $param, $message, $user );

    	return;
    }
 }
