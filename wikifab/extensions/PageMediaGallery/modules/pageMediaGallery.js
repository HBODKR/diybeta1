var pageMediaGallery, formGallery;

formGallery = {
		
	removeImg: function (closeButton){
		var container = $(closeButton).parents('.msuploadContainer');
		filename = $(closeButton).parent('li').attr('data-filename');
		$(closeButton).parent('li').remove();
		if (filename) {
			var inputs = $(container ).find('input.createboxInput');
			if (inputs.length == 0) {
				return;
			}
			inputs = inputs.filter(function() { 
				return this.value == filename; 
			});
			if (inputs.length > 0) {
				// remove filename from input
				inputs.first().val('');
			} 
		}
		formGallery.updateInputsFields( container );
	},
	
	updateInputsFields: function (container) {
		
	},

	addThumb: function(container, img, filename) {
		li = $('<li>').attr('data-filename', filename).append(img);
		
		var cancelbutton = $( '<span>' ).attr({ 'class': 'file-cancel', 'title': mw.msg( 'msu-cancel-upload' ) });
		cancelbutton.click( function () {
			formGallery.removeImg( this );
		});
		li.prepend( cancelbutton );
		
		//li.append('<span class="file-cancel" title="<msu-remove-image>"></span>');
		
		$(container).find('.formmediagallery ul').append(li);
	},
	addImageToFormsInputs: function (container, filename) {
		
		// this function automaticaly add image to forminputs included in container div
		// if all inputs are allready filled, it mark file as error, because not possible to add more
		// if there is no input in container div, , no check , just return OK (case of page gallery)
		
		// file.name : nom du fichier à ajouter :
		
		var inputs = $(container ).find('input.createboxInput');
		
		if (inputs.length == 0) {
			return true;
		}
		
		emptiesInputs = inputs.filter(function() { 
			return this.value == "" || this.value == 'No-image-yet.jpg'; 
		});
		
		
		
		if (emptiesInputs.length > 0) {
			// if we get an input with no value, we add filename to it
			emptiesInputs.first().val(filename);
		} else {
			$('<span>').html( mw.msg( 'msu-upload-nbfile-exceed' )).appendTo(container);
			return false;
		}
		return true;
	},
	addImage: function(container, img, filename) {
		
		result = formGallery.addImageToFormsInputs(container,filename);

		console.log("added image : " + result);
		
		if ( result) { 
			formGallery.addThumb(container, img, filename);
		} 
		// TODO : add image name to form input
		// TODO : manage error if too many files
	},
	
	initFormGalleriesArea: function () {
		$(".msuploadContainer").each(function (i) {
			container = this;
			if ($(this).find('.formmediagallery').length > 0) {
				return ;
			}
			$(this).children().hide();
			ol = $('<div>').addClass('formmediagallery');
			ol.append($('<ul>'));
			$(this).prepend(ol);
			files = new Array();
			$(this).find('input.createboxInput').each(function (i) {
				if ($(this).val()) {
					var image = $(this).parentsUntil('div').nextAll('.pfImagePreviewWrapper').find('img,video').first();
					formGallery.addThumb(container, image.clone(), $(this).val());
				}
			});
		});
	},
	
	init: function () {
		formGallery.initFormGalleriesArea();
		
		/*$('.multipleTemplateAdder').click(function () {
			// whe launch createMultipleUploader after a timeout, 
			//to be sure news divs are created before executing
			setTimeout(formGallery.initFormGalleriesArea, 150);
			return true;
		});*/
	}
}

/**
 * this manage the gallery of image, 
 * feature :
 *  we we can drop image from disk to upload it
 *  we ca drag an image to a formgallery, to add it in a form input
 */
pageMediaGallery = {

	isOpen: false,
	
	toggle: function() {
		if(pageMediaGallery.isOpen) {
			pageMediaGallery.close();
		} else {
			pageMediaGallery.open();
		}
	},
	open: function() {
		pageMediaGallery.isOpen=true;
		
		$('#PageGallery').width(250);
		$("#PageGallery .pageGalleryControls").animate({ left: '250' }, 200);
		//$('#PageGallery .pageGalleryControls').css('left','250px');
		$('body').css('marginLeft','250px');
		
	},
	close: function() {
		pageMediaGallery.isOpen=false;
		
		$('#PageGallery').width(0);
		$('#PageGallery .pageGalleryControls').animate({ left: '250' }, 200);
		$('body').css('marginLeft','0px');
	},
	
	onRefresh: function () {
		// to activate draggable on news images : 
		pageMediaGallery.initDraggaBleBehaviour();
		$('#PageGallery').scrollTop($('#PageGallery')[0].scrollHeight);
	},
	
	
	addFileToPagesGroup: function(filePage, grouppage) {
		// fonction to do second request to execute follow action
		function ajaxGroupsPageQuery(jsondata) {
			var token = jsondata.query.tokens.csrftoken;
			$.ajax({
				type: "POST",
				url: mw.util.wikiScript('api'),
				data: { 
					action:'goupspage', 
					format:'json', 
					groupaction: 'add', token: token, 
					memberpage: filePage, 
					groupspage: grouppage
				},
			    dataType: 'json'
			});
		};
		
		// first request to get token
		$.ajax({
			type: "GET",
			url: mw.util.wikiScript('api'),
			data: { action:'query', format:'json',  meta: 'tokens', type:'csrf'},
		    dataType: 'json',
		    success: ajaxGroupsPageQuery
		});
	},
	
	initDraggaBleBehaviour: function() {
		
	    var $gallery = $( ".msupload-list" ),
	     $trash = $( ".msuploadContainer" );
	    
	    // create temp li in body :
	    $('<ul>').attr('id', 'draggableTempUl').appendTo($('body'));
		
	    // Let the gallery items be draggable
	    $( "li", $gallery ).draggable({
	      cancel: "a.ui-icon", // clicking an icon won't initiate dragging
	      revert: true, // when not dropped, the item will revert back to its initial position
	      //containment: "document",
	      //containment: [10, $('body').offset().top],
	      containment: 'window',
	      appendTo: '#draggableTempUl',
	      helper: "clone",
	      cursor: "move",
	      scroll: false,
	      start: function() {
	          $(".msuploadContainer").addClass('ui-droppable-active');
	      },
	      stop: function() {
	          $(".ui-droppable-active").removeClass('ui-droppable-active');
	      }
	    });
		
	    // Let the trash be droppable, accepting the gallery items
	    $trash.droppable({
	      accept: ".msupload-list > li",
	      classes: {
	        "ui-droppable-active": "ui-state-default",
	        "ui-droppable-hover": "ui-state-hover"
	      },
	      drop: function( event, ui ) {
	    	  var image = ui.draggable.find('img').clone();
	    	  if (image.length == 0 && ui.draggable.find('video').length > 0) {
	    		  image = ui.draggable.find('video').clone();
	    	  }
	    	  var filename = ui.draggable.attr('data-filename');
	    	  if ( ! filename) {
	    		  filename = ui.draggable.find('.file-name').first().text();
	    	  }
	    	  formGallery.addImage(this, image, filename );
	    	  
	    	  // trick to hide the 'revert' movement of the image back to the gallery
	    	  $('.ui-draggable-dragging').hide();
	      }
	    });
		
		
	},
	
	
	callMsUpload: function() {
		pageMediaGallery.uploader = MsUpload.createUploaderOnElement($('#PageGalleryUploader'), true);
		MsUpload.initWithImgElement(pageMediaGallery.uploader);
		MsUpload.onRefresh = pageMediaGallery.onRefresh;
		pageMediaGallery.addDropOnformmediagalleryEvent();
	},
	
	stepAdded: function() {
		setTimeout(pageMediaGallery.addDropOnformmediagalleryEvent, 100);
		setTimeout(pageMediaGallery.updateBindEvents, 100);
	},
	
	updateBindEvents: function() {
		$( ".addAboveButton" )
		    .off( "click", null, pageMediaGallery.stepAdded )
		    .on( "click", null, pageMediaGallery.stepAdded );
	},
	
	init: function () {
		//var openlink = $( '<a>Open</a>' ).click(pageMediaGallery.callMsUpload);
		
		//alert(openlink);
		//$('#PageGallery').prepend(openlink)
		$('#PageGallery .pageGalleryControls').appendTo('body');
		$('.pageGalleryControls').click(pageMediaGallery.toggle);
		pageMediaGallery.close();
		$('#PageGallery').show();
		setTimeout(pageMediaGallery.callMsUpload, 300);
		setTimeout(pageMediaGallery.initDraggaBleBehaviour, 600);
		setTimeout(pageMediaGallery.initBind, 610);
		
		$('.multipleTemplateAdder').click(function () {
			// whe launch createMultipleUploader after a timeout, 
			//to be sure news divs are created before executing
			setTimeout(pageMediaGallery.addDropOnformmediagalleryEvent, 100);
		});
		
		$( ".addAboveButton" )
			    .on( "click", null, pageMediaGallery.stepAdded );
	},
	
	initBind: function() {
		var uploader = pageMediaGallery.uploader;
		
		uploader.bind( 'FileUploaded',function(uploader, file, success) {
			if( success) {
				isUploaded = true;
				console.log(file);
				
				pageMediaGallery.addFileToPagesGroup('File:' + file.name,  mw.config.get('wgPageName'));
			}
		} );
	},
	
	
	
	
	addDropOnformmediagalleryEvent: function () {
		setTimeout(pageMediaGallery.initDraggaBleBehaviour, 600);
		/**
		 * Warning : this function can be called many times
		 *  to add events on div Dynamically added
		 *  so we remove previous event whith 'off' and check existence of div to add
		 */
		var uploader = pageMediaGallery.uploader;
		
		// this add drop zone around file fields in form, when a file is dropped, 
		// it upload it in the page gallery, and if success, it add it to the field
		
		var obj = $(".formmediagallery");
		
		// check if already bounded :
		console.log( obj.data('events'))
		//var bounded = -1 !== $.inArray(onButtonClicked, obj.data('events').drop);
		//console.log('isBouded' + bounded);
		
		
		
		obj.css('border', '2px dotted #0B85A1');
		if(obj.find('.dropHelp').length == 0) {
			obj.append('<span class="dropHelp">'+mw.msg( 'msu-dropzone' )+'</span>');
		}
		obj.off( "dragenter").on('dragenter', function (e) 
				{
				    e.stopPropagation();
				    e.preventDefault();
				    $(this).css('border', '2px solid #0B85A1');
				});
		obj.off( "dragleave").on('dragleave', function (e) 
				{
				    e.stopPropagation();
				    e.preventDefault();
				    $(this).css('border', '2px dotted #0B85A1');
				});
		obj.off( "dragover").on('dragover', function (e) 
		{
		     e.stopPropagation();
		     e.preventDefault();
		});
		obj.off( "drop").on('drop', function (e) 
		{
		     e.stopPropagation();
		     e.preventDefault();
			 var container = $(this).parent();
		     $(this).css('border', '2px dotted #0B85A1');
		     pageMediaGallery.open();
		     var files = e.originalEvent.dataTransfer.files;
		     for (index = 0; index < files.length; ++index) {
		    	var isUploaded = false;
		    	// add file to be uploaded in mediGallery
			    uploader.addFile(files[index]);
			    // add bin function to add it to form once it is uploaded
			    uploader.bind( 'FileUploaded',function(uploader, file, success) {
			    		if(! isUploaded && success) {
			    			isUploaded = true;
				    		var img = file.li.find('img.file-thumb');
			    			formGallery.addImage(container, img.clone(), file.name);
			    		}
			    	} );
		     }
		     //uploader.start();
		     pageMediaGallery.onRefresh();
		     
		});
		// avoid opening file when drop outside of areas :
		$(document).on('drop', function (e) 
				{
				    e.stopPropagation();
				    e.preventDefault();
				});
	}
};

mw.loader.using( 'ext.MsUpload' ).then( function () {
	$( pageMediaGallery.init );
	$( formGallery.init );
} );





