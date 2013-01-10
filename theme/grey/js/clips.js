cf = {
	
	showCodeGlobal : false,
	showAction : 'show',
	hideAction : 'hide',
	toggleAction : 'toggle',
	animSpeed : 200,
	showAllSpeed : 100,
	keyCode : 0,
	keyPressed : false,
	showCount : 0,
	viewMode : 1,
	miniMode : false,
	
	handleClipViewing : function(){
		$('#clips').find('.clip-title').live('click', function(e){
			var code = $(this).parent('li').find('.clip-code'),
				options = $(this).parent('li').find('.clip-options'),
				$this = $(this),
				a = cf.showCount,
				clipLink = $this.find('a').attr('href');
			
			e.preventDefault();
			
			// If we're in viewMode 1 and alt is not pressed, hide all other open code
			if(cf.viewMode === 1 && cf.showCount < 2){
				if(cf.keyCode !== 18 ){
					if(cf.miniMode === true){
						window.location = clipLink;
						return;
					}
					code.parent('li').siblings().find('.clip-code')[cf.hideAction](cf.showAllSpeed);
				}
			}
			
			code[cf.toggleAction](cf.animSpeed);
			options['slideToggle'](cf.animSpeed);
			
			// Count open items only after animations above have finished
			setTimeout(	function(){
				cf.showCount = $('#clips').find('.clip-code:visible').length;
				
				// Disable singMode if more than one item is visible
				if(cf.showCount > 1){
					cf.viewMode = 2;
				} else {
					cf.viewMode = 1;
				}
				
			}, cf.showAllSpeed+50);
			
		});
	},
	
	mapKeys : function(){
		$(document).bind('keydown', function(e){
			cf.keyPressed = true;
			cf.keyCode = e.keyCode;
		});
		$(document).bind('keyup', function(e){
			cf.keyPressed = false;
			cf.keyCode = 0;
		});
	},
	
	handleDelete : function(){
		$('#clips').find('a.delete-clip').click(function(){
			cf.confirmDelete( $(this).parents('li') );
		});
	},
	
	confirmDelete : function(clip){
		var clipId = clip.attr('id').toString().replace(/clip-/, '')
		clip.find('h3, div.clip-options, pre, div.clip-tags').hide();
		clip.append('<div class="delete-confirmation rounded"><h3 class="confirmation">Delete this clip?</h3><a class="btn cancel-delete">Cancel</a><a class="btn do-delete">Delete</a></div>');
		$('#clips').find('div.delete-confirmation a.cancel-delete').live('click', function(){
			cf.cancelDelete(clipId);
		});
		$('#clips').find('div.delete-confirmation a.do-delete').live('click', function(){
			cf.doDelete(clipId);
		});
	},
	
	cancelDelete : function(clipId){
		$('#clip-'+clipId).find('div.delete-confirmation').remove();
		$('#clip-'+clipId).find('h3, div.clip-options, div.clip-tags').fadeIn(400);
	},
	
	doDelete : function(clipId){
		$.get('/clips/delete/'+clipId, {ajax:'true'}, function(data){
			if(data == 0){
				alert("Sorry, error deleting clip "+0);
				return false;
			} else {
				$('#clip-'+clipId).fadeOut(200, function(){
					$(this).remove();
					return true;
				});
			}
		});
	},
	
	handleViewOptions : function(){
		$('#viewgrid, #viewlist').click(function() {
			cf.selectView( $(this).attr('id').replace(/view/, '') );
		});
	},
	
	selectView : function (view) {	
		
		var otherView = (view==='grid') ? 'list' : 'grid';
		
		if (view != 'grid' && view != 'list') {
			return false;
		}
		
		$('#view'+otherView).removeClass('active');
		$('#view'+view).addClass('active');
		
		if (view==='grid') {
			$('#clips').find('.cliplist').addClass('mini');
			cf.miniMode = true;
		} else {
			$('#clips').find('.cliplist').removeClass('mini');
			cf.miniMode = false;
		}
		
		localStorage.setItem('view', view);
		
	},
	
	loadViewPreference : function(){
		if(localStorage.getItem('view') == 'grid'){
			$('#viewgrid').click();
		}
	},

	/* Handle loading in add/edit forms */
	handleModalForms : function(){
		$('#add-clip').click(function(e){
    		e.preventDefault();
    		cf.loadForm('/clips/add #add-form');
	    });
	    
	    $('#page').delegate('#modal a.cancel, #modal-cover', 'click', function(e){
	    	e.preventDefault();
	    	cf.clearLoadedForm();
	    	return false;
	    });
	    
	    $('#clips').find('a.edit-clip').click(function(e){
	    	e.preventDefault();
	    	var link = $(this).attr('href');
	    	cf.loadForm(link+' #add-form');
	    });	
	    
	    // Setup loader animation
	    $('<span class="loader">').appendTo($("#modal-cover"));
	    Apng.target = $('.loader');
	    Apng.png = '/theme/grey/gfx/loader.png';
	    Apng.frames = 6;
	},
	 /* Load in a form (url) */
	loadForm : function(url){
		
		Apng.run();
	
		$('#modal-cover').fadeIn(100);
    	
    	// Load in the form
    	$('#modal').load(url, function() {
    		var topPos = $(window).scrollTop()+50,
    			modal = $(this);
    		
    		// Stop the loader animation
    		Apng.stop();
    		
    		// Set the nextpage parameter
    		modal.find('form').append( $('<input />',{ type: "hidden", name : "nextpage", value : "/clips/"}) );
	    	
	    	// Focus on the first field
	    	$('input[name=title]').focus();
    		
    		// Fade in the form
    		modal.fadeIn(300).contents().hide().fadeIn(300);
    		
    		// Position the modal window so it's within view
    		modal.css({top:topPos});
    		
    		// Validate required fields
    		$('#add-form').validate({
    			errorElement : 'div',
    			errorPlacement: function(error, element) {
    				error.appendTo( element.parents("div.field") );
    			}
    		});
    		cf.hideDescription();
    		// Autosuggest tags
    		$('input[name=tags]').autocomplete({
    			serviceUrl:'/tags/autocomplete/',
    			delimiter: /(,|;)\s*/
    		})
    	});
	},

	/* Clear the modal window */
	clearLoadedForm : function(){
		$('#modal').fadeOut(200, function(){
			$(this).contents().remove();
			$('#modal-cover').fadeOut(200);
			Apng.stop();
		});
	},
	
	/* Hide description by default */
	hideDescription : function() {
		var form = $('#add-form');
		var addButton = $('<a>', {
			class : "add-description btn left",
			text : "Add a description",
			style : 'margin-top: 5px',
			href : "#add-description"
		});
		form.find('.field-description').first().hide().after(addButton);
		$('#modal').delegate('a.add-description', 'click', function(){
			$(this).prev('div.field').slideDown(200).find('textarea').focus()
			$(this).fadeOut(200);
			return false;
		});
	},
};