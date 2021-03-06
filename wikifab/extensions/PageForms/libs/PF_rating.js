( function( $, mw, pf ) {
        'use strict';

	jQuery.fn.applyRatingInput = function() {
		var starWidth = $(this).attr('data-starwidth');
		var curValue = $(this).attr('data-curvalue');
		var numStars = $(this).attr('data-numstars');
		var allowsHalf = $(this).attr('data-allows-half');
		var ratingsSettings = {
			normalFill: '#ddd',
			starWidth: starWidth,
			numStars: numStars,
			maxValue: numStars,
			rating: curValue
		};
		if ( allowsHalf === null ) {
			ratingsSettings.fullStar = true;
		} else {
			ratingsSettings.halfStar = true;
		}

		$(this).rateYo(ratingsSettings)
		.on("rateyo.set", function (e, data) {
 
			$(this).parent().children(":hidden").attr("value", data.rating);
		});
	};

}( jQuery, mediaWiki, pf ) );