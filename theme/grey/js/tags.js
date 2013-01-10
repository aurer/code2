handleTags = {
	
	testData : '{ "clips" : [{ "name":"Clip 1" , "id":"1" }, { "name":"Clip 2" , "id":"2" }, { "name":"Clip 3" , "id":"3" }]}',
	editMode : false,
	
	live : null, // Holds the tag being edited
	
	bindEvents : function(e){
		
		$('#managed-tags').find('div.tag').click(function(){
			handleTags.loadClips($(this));
		});
		
		$('#managed-tags').find('span.name, input.editname').click(function(e) {
		    handleTags.editTag($(this));
			e.stopPropagation(); // ^ Prevent clip loading when editing
		});
		
		$('#managed-tags').find('input.editname').live('blur', function(){
		    handleTags.closeEdit();
		});
	},
	
	editTag : function(e){
		var name = e.text();
	    var newname;
	    var input = '<input type="text" name="name" class="editname" value="' + name + '" />';
	    handleTags.live = e;
	    handleTags.editMode = true;
	    handleTags.closeEdit();
	    handleTags.editMode = true;
	
	    e.before(input).hide();
		
		$('#managed-tags').find('input.editname').focus();
	
	    $('#managed-tags').find('.tag .editname').keydown(function(k) {
	        if (k.keyCode == 13 || k.keyCode == 27) {
	            newname = $(this).val();
	            if (k.keyCode == 13) {
	                handleTags.updateTag(name, newname);
	            } else {
	            	handleTags.closeEdit();
	            }
	        }
	    });
	},
	
	closeEdit : function(){
		var editInput = $('#managed-tags .tag input.editname');
		if (editInput.length > 0) {
		    editInput.parent('.tag').find('span.name').show(0, function(){
				editInput.detach();
			});
		}
		handleTags.editMode = false;
	},
	
	updateTag : function(name, newname){
		$.post('/tags/update/', { 'tag_name':name, 'new_name':newname, 'ajax':'true' }).success(function(data){
			handleTags.live.text(newname);
			handleTags.closeEdit();
			console.log(data);
		}).error(function(){
			handleTags.closeEdit();
		});
	},
	
	loadClips : function(e){
		var tagname = e.find('.name').text();
		var tagID = e.attr('id').replace('tagid-','');
		var tagName = e.find('span.name').text();
		var tagURI = encodeURI(tagName.toLowerCase().replace(/\s/i, "+"));
		// Don't load if we're in edit mode
		if(handleTags.editMode !== true){
			var clipData=false;
			var clipHTML;
			$.get('/clips/by_tag/'+encodeURIComponent(tagname), function(data){
				// Parse JSON into object
				clipData = JSON.parse(data, function(key,val){
					return val;
				});
				// Turn JSON object into html array
				var clips = handleTags.buildClips(clipData);
				
				// Merge array
				clipHTML = clips.join('\n');
				// Display on page
				$('#clips ol').html(clipHTML);
				
				//var historyObject = {};
				// history.pushState(historyObject, "Tag "+tagName, "/tags/"+tagURI);
			})
		}
	},
	
	buildClips : function(data){
		var string=[];
		for(i=0; i<data.length; i++){			
			string[i] = '<li id="clip-'+data[i].id+'" class="clip">'+
							'<h3 class="clip-title rounded">'+data[i].title+'</h3>'+
							'<div class="clip-options">'+
								'<a class="view-clip" href="/clips/view/'+data[i].id+'/">Show details</a>'+
								'<a class="edit-clip" href="/clips/edit/'+data[i].id+'/">Edit</a>'+
							'</div>'+
						'</li>';
		}
		return string;
	}
}