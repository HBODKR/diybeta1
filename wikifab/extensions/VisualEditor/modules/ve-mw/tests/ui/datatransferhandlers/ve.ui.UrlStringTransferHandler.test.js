/*!
 * VisualEditor UserInterface UrlStringTransferHandler tests.
 *
 * @copyright 2011-2016 VisualEditor Team and others; see http://ve.mit-license.org
 */

/* global runUrlStringHandlerTest */

QUnit.module( 've.ui.UrlStringTransferHandler (MW)' );

/* Tests */

QUnit.test( 'paste', function ( assert ) {
	var i,
		cases = [
			{
				msg: 'External link converts to internal link',
				pasteString: location.origin + mw.Title.newFromText( 'Main Page' ).getUrl(),
				pasteType: 'text/plain',
				expectedData: function () {
					// Explicitly create an internal link so we can assert this behaviour is working
					var a = ve.dm.MWInternalLinkAnnotation.static.newFromTitle( new mw.Title.newFromText( 'Main Page' ) ).element;
					return [
						[ 'M', [ a ] ],
						[ 'a', [ a ] ],
						[ 'i', [ a ] ],
						[ 'n', [ a ] ],
						[ ' ', [ a ] ],
						[ 'P', [ a ] ],
						[ 'a', [ a ] ],
						[ 'g', [ a ] ],
						[ 'e', [ a ] ]
					];
				}
			}
		];

	QUnit.expect( cases.length );
	for ( i = 0; i < cases.length; i++ ) {
		runUrlStringHandlerTest( assert, cases[ i ].pasteString, cases[ i ].pasteHtml, cases[ i ].pasteType, cases[ i ].expectedData, cases[ i ].msg );
	}
} );
