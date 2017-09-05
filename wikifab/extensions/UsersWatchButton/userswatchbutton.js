(function() {
	

	function followed($user) {
		$(".UsersWatchButton[data-user='"+$user+"']").hide();
		$(".UsersUnWatchButton[data-user='"+$user+"']").show();
	};
	function unfollowed($user) {
		$(".UsersWatchButton[data-user='"+$user+"']").show();
		$(".UsersUnWatchButton[data-user='"+$user+"']").hide();
	};


	function displayModal() {
		$( "#connectionRequiredModal" ).modal();
	}

	$('.btn-message').click(function() {
		if (typeof wgUserId == 'undefined') {
			displayModal();
			return false;
		}
		return true;
	});

	$('.UsersWatchButton').click(function() {
		
		if (typeof wgUserId == 'undefined') {
			displayModal();
			return;
		}
		
		var userToFollow = $(this).attr('data-user');
		var button = this;
		
		// fonction to do second request to execute follow action
		function ajaxFollow(jsondata) {
			var token = jsondata.query.tokens.csrftoken;
			$.ajax({
				type: "POST",
				url: mw.util.wikiScript('api'),
				data: { action:'userswatch', format:'json', watch: 'yes', token: token, user: userToFollow},
			    dataType: 'json',
			    success: function (jsondata) {
					if(jsondata.userswatch.success == 1) {
						followed(userToFollow);
					}
			}});
		};
		
		// first request to get token
		$.ajax({
			type: "GET",
			url: mw.util.wikiScript('api'),
			data: { action:'query', format:'json',  meta: 'tokens', type:'csrf'},
		    dataType: 'json',
		    success: ajaxFollow
		});
	});


	$('.UsersUnWatchButton').click(function() {
		
		var userToFollow = $(this).attr('data-user');
		var button = this;
		
		// fonction to do second request to execute follow action
		function ajaxFollow(jsondata) {
			var token = jsondata.query.tokens.csrftoken;
			$.ajax({
				type: "POST",
				url: mw.util.wikiScript('api'),
				data: { action:'userswatch', format:'json', watch: 'no', token: token, user: userToFollow},
			    dataType: 'json',
			    success: function (jsondata) {
					if(jsondata.userswatch.success == 1) {
						unfollowed(userToFollow);
					}
			}});
		};
		
		// first request to get token
		$.ajax({
			type: "GET",
			url: mw.util.wikiScript('api'),
			data: { action:'query', format:'json',  meta: 'tokens', type:'csrf'},
		    dataType: 'json',
		    success: ajaxFollow
		});
	});
})();