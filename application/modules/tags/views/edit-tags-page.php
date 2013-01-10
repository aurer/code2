<? $this->template->use_template('default') ?>
<? $this->template->set('pagetitle', 'Manage tags') ?>
<? $this->template->set('head') ?>
<script type="text/javascript" src="/theme/grey/js/tags.js"></script>
<script type="text/javascript" src="/theme/grey/js/clips.js"></script>
<script type="text/javascript" src="/theme/grey/js/libs/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript">
$(function(){
	handleTags.bindEvents();
	//cf.handleClips();
	//cf.handleKeys();
	
	$( ".sortable" ).sortable({
		connectWith: '.sortable',
		receive: function(event, ui) {
			update(event.srcElement.childNodes[1].innerText /* Child span */ , event.srcElement.parentNode.parentNode.id);
		},
		remove: function(event, ui) { 
			checkIfEmpty(event, ui);
		},
		revert: 100
	});

	var update = function(tag, list){
		console.log(tag);
		
		$.post('/tags/categorise/', { ajax:'true', id:tag.replace(/tag-/, ''), category:list}, function(data){
			console.log(data);
		});
		
		//console.log("Moved "+tag+" into "+list);
	}

	var checkIfEmpty = function(event, ui){
		$( ".sortable" ).each(function(){
			var t = $(this);
			count = t.find('li').size();
			if(count < 1){
				t.addClass('empty');
			} else {
				t.removeClass('empty');
			}
		})
	}

	$( "ul.sortable" ).disableSelection();
});
</script>
<style>

</style>
<? $this->template->end() ?>

<? $this->template->set('primary') ?>
		
	<div id="managed-tags">
		<div id="none" class="clips">
			<h2>General</h2>
			<ul class="sortable reset">
				<? foreach($tags as $tag): if(!in_array($tag['category'], array('language', 'framework') ) ) :?>
					<li id="tag-<?=$tag['id']?>" class="tag">
						<span class="name"><?=$tag['title']?></span>
						<span class="count"><?=$tag['count']?></span>
					<li>
				<? endif; endforeach; ?>
			</ul>
		</div>
		<div id="language" class="clips">
			<h2>Language</h2>
			<ul class="sortable reset">
				<? foreach($tags as $tag): if($tag['category'] == 'language') :?>
					<li id="tag-<?=$tag['id']?>" class="tag">
						<span class="name"><?=$tag['title']?></span>
						<span class="count"><?=$tag['count']?></span>
					<li>
				<? endif; endforeach; ?>
			</ul>
		</div>
		<div id="framework" class="clips">
			<h2>Framework</h2>
			<ul class="sortable reset">
				<? foreach($tags as $tag): if($tag['category'] == 'framework') :?>
					<li id="tag-<?=$tag['id']?>" class="tag">
						<span class="name"><?=$tag['title']?></span>
						<span class="count"><?=$tag['count']?></span>
					<li>
				<? endif; endforeach; ?>
			</ul>
		</div>
	</div>
<? $this->template->end() ?>

<? $this->template->set('secondary') ?>
	<h2>Editing tags</h2>
	<p>This page shows all the tags currently in use along with a count of how many clips are using that tag.</p>
	<hr class="dash" />
	<p>Click on a tag to edit it, press return to save your changes or esc to stop editing without saving the change.</p>
<? $this->template->end() ?>