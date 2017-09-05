( function ( mw, $ ) {
	/**
	 * Notification API handler
	 *
	 * @class
	 * @extends mw.echo.api.APIHandler
	 *
	 * @constructor
	 * @param {Object} [config] Configuration object
	 */
	mw.echo.api.LocalAPIHandler = function MwEchoApiLocalAPIHandler( config ) {
		config = config || {};

		// Parent constructor
		mw.echo.api.LocalAPIHandler.parent.call( this,
			new mw.Api( { ajax: { cache: false } } ),
			config
		);
	};

	/* Setup */

	OO.inheritClass( mw.echo.api.LocalAPIHandler, mw.echo.api.APIHandler );

	/**
	 * @inheritdoc
	 */
	mw.echo.api.LocalAPIHandler.prototype.fetchNotifications = function ( type, source, isForced, overrideParams ) {
		if ( overrideParams ) {
			return this.createNewFetchNotificationPromise( type, source, overrideParams );
		} else if ( isForced || this.isFetchingErrorState( type, source ) ) {
			// Force new promise
			return this.createNewFetchNotificationPromise( type, source, overrideParams );
		}

		return this.getFetchNotificationPromise( type, source, overrideParams );
	};

	/**
	 * @inheritdoc
	 */
	mw.echo.api.LocalAPIHandler.prototype.updateSeenTime = function ( type ) {
		type = Array.isArray( type ) ? type : [ type ];

		return this.api.postWithToken( 'csrf', {
			action: 'echomarkseen',
			type: type.length === 1 ? type[ 0 ] : 'all',
			timestampFormat: 'ISO_8601'
		} )
			.then( function ( data ) {
				return data.query.echomarkseen.timestamp;
			} );
	};

	/**
	 * @inheritdoc
	 */
	mw.echo.api.LocalAPIHandler.prototype.markAllRead = function ( type ) {
		type = Array.isArray( type ) ? type : [ type ];

		return this.api.postWithToken( 'csrf', {
			action: 'echomarkread',
			sections: type.join( '|' )
		} )
			.then( function ( result ) {
				return OO.getProp( result.query, 'echomarkread', type, 'rawcount' ) || 0;
			} );
	};

	/**
	 * @inheritdoc
	 */
	mw.echo.api.LocalAPIHandler.prototype.markItemsRead = function ( itemIdArray, isRead ) {
		var data = {
				action: 'echomarkread'
			};

		if ( isRead ) {
			data.list = itemIdArray.join( '|' );
		} else {
			data.unreadlist = itemIdArray.join( '|' );
		}

		return this.api.postWithToken( 'csrf', data );
	};

	/**
	 * Fetch the number of unread notifications.
	 *
	 * @param {string} type Notification type, 'alert', 'message' or 'all'
	 * @param {boolean} [ignoreCrossWiki] Ignore cross-wiki notifications when fetching the count.
	 *  If set to false (by default) it counts notifications across all wikis.
	 */
	mw.echo.api.LocalAPIHandler.prototype.fetchUnreadCount = function ( type, ignoreCrossWiki ) {
		var normalizedType = this.normalizedType[ type ],
			apiData = {
				action: 'query',
				meta: 'notifications',
				notsections: normalizedType,
				notgroupbysection: 1,
				notmessageunreadfirst: 1,
				notlimit: this.limit,
				notprop: 'count',
				uselang: this.userLang
			};

		if ( !ignoreCrossWiki ) {
			apiData.notcrosswikisummary = 1;
		}

		return this.api.get( apiData )
			.then( function ( result ) {
				if ( type === 'message' || type === 'alert' ) {
					return OO.getProp( result.query, 'notifications', normalizedType, 'rawcount' ) || 0;
				} else {
					return OO.getProp( result.query, 'notifications', 'rawcount' ) || 0;
				}
			} );
	};

	/**
	 * @inheritdoc
	 */
	mw.echo.api.LocalAPIHandler.prototype.getTypeParams = function ( type ) {
		return $.extend( {}, this.typeParams[ type ], {
			notcrosswikisummary: 1
		} );
	};
} )( mediaWiki, jQuery );
