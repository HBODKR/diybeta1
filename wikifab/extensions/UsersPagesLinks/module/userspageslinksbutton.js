(function() {

	
	function linkAdded( page, type) {
		pageuri = page.replace("'", "\\\'");
		unloadingMark(pageuri, type);
		$(".UsersPagesLinksButton[data-page='"+pageuri+"'][data-linkstype='"+type+"']").removeClass('addAction').addClass('rmAction');
		// increment counter :
		var counter ;
		counter = $(".UsersPagesLinksButtonCounter[data-page='"+pageuri+"'][data-linkstype='"+type+"'] button").html();
		counter = parseInt(counter) + 1;
		$(".UsersPagesLinksButtonCounter[data-page='"+pageuri+"'][data-linkstype='"+type+"'] button").html(counter);
		
		// change label
		var labelText = $(".UsersPagesLinksButton[data-page='"+pageuri+"'][data-linkstype='"+type+"'] .labelText");
		if (labelText.attr("data-undolabel") && labelText.attr("data-dolabel")) {
			labelText.html(labelText.attr("data-undolabel"));
		}
	};
	function linkRemoved(page, type) {
		pageuri = page.replace("'", "\\\'");
		unloadingMark(pageuri, type);
		$(".UsersPagesLinksButton[data-page='"+pageuri+"'][data-linkstype='"+type+"']").addClass('addAction').removeClass('rmAction');
		// decrement counter :
		var counter ;
		counter = $(".UsersPagesLinksButtonCounter[data-page='"+pageuri+"'][data-linkstype='"+type+"'] button").html();
		counter = parseInt(counter) - 1;
		$(".UsersPagesLinksButtonCounter[data-page='"+pageuri+"'][data-linkstype='"+type+"'] button").html(counter);

		// change label
		var labelText = $(".UsersPagesLinksButton[data-page='"+pageuri+"'][data-linkstype='"+type+"'] .labelText");
		if (labelText.attr("data-undolabel") && labelText.attr("data-dolabel")) {
			labelText.html(labelText.attr("data-dolabel"));
		}
	};

	function unloadingMark(pageuri, type) {
		$(".UsersPagesLinksButton[data-page='"+pageuri+"'][data-linkstype='"+type+"'] i.upl_icon").show();
		$(".UsersPagesLinksButton[data-page='"+pageuri+"'][data-linkstype='"+type+"'] i.upl_loading").hide();
	};
	function loadingMark(page, type) {
		pageuri = page.replace("'", "\\\'");
		$(".UsersPagesLinksButton[data-page='"+pageuri+"'][data-linkstype='"+type+"'] i.upl_icon").hide();
		$(".UsersPagesLinksButton[data-page='"+pageuri+"'][data-linkstype='"+type+"'] i.upl_loading").show();
	};
	
	
	function displayModal() {
		$( "#connectionRequiredModal" ).modal();
	}
	

	function addUsersPagesLinks(button) {
		
		if (! mw.config.get('wgUserId')) {
			displayModal();
			return;
		}
		
		var page = $(button).attr('data-page');
		var type = $(button).attr('data-linkstype');
		
		// fonction to do second request to execute follow action
		function ajaxPageslinkQuery(jsondata) {
			var token = jsondata.query.tokens.csrftoken;
			$.ajax({
				type: "POST",
				url: mw.util.wikiScript('api'),
				data: { action:'userspageslinks', format:'json', upl_action: 'add', token: token, page: page, type: type},
			    dataType: 'json',
			    success: function (jsondata) {
					if(jsondata.userspageslinks.success == 1) {
						linkAdded(page, type);
					}
			}});
		};
		
		// first request to get token
		loadingMark(page, type);
		$.ajax({
			type: "GET",
			url: mw.util.wikiScript('api'),
			data: { action:'query', format:'json',  meta: 'tokens', type:'csrf'},
		    dataType: 'json',
		    success: ajaxPageslinkQuery
		});
	};
	
	function rmUsersPagesLinks(button) {
		
		if (typeof wgUserId == 'undefined') {
			displayModal();
			return;
		}

		var page = $(button).attr('data-page');
		var type = $(button).attr('data-linkstype');
		
		// fonction to do second request to execute follow action
		function ajaxPagesLinksQuery(jsondata) {
			var token = jsondata.query.tokens.csrftoken;
			$.ajax({
				type: "POST",
				url: mw.util.wikiScript('api'),
				data: { action:'userspageslinks', format:'json', upl_action: 'remove', token: token, page: page, type: type},
			    dataType: 'json',
			    success: function (jsondata) {
					if(jsondata.userspageslinks.success == 1) {
						linkRemoved(page, type);
					}
			}});
		};
		
		// first request to get token
		loadingMark(page, type);
		$.ajax({
			type: "GET",
			url: mw.util.wikiScript('api'),
			data: { action:'query', format:'json',  meta: 'tokens', type:'csrf'},
		    dataType: 'json',
		    success: ajaxPagesLinksQuery
		});
	};

	$('.UsersPagesLinksButton').click(function() {
		if ($(this).hasClass('addAction')) {
			addUsersPagesLinks(this);
		} else {
			rmUsersPagesLinks(this);
		}
	});


})();
