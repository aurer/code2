<? $this->template->use_template('default') ?>
<? $this->template->set('pagetitle', $clip['title']) ?>
<? $this->template->set('head') ?>
<script type="text/javascript" src="/theme/grey/js/clips.js"></script>
<link href="/theme/grey/google-prettify/prettify.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="/theme/grey/google-prettify/prettify.js"></script>
<script type="text/javascript">
$(function(){
	prettyPrint();

	var actionWidth = $('div.actions').width();	

	var confirmDelete = function(){
		console.log('Confirm');
		$('#clips, #taglist').fadeTo(300, .2);
		$('#main div.actions').addClass('text-center').animate({width:650, paddingTop:20, paddingBottom:20}, 400);
		$('#delete-clip').bind('click', doDelete);
		$('#add-clip').text('Don\'t delete').bind('click', cancelDelete);
		return false;
	}

	var doDelete = function(){
		var id=$('#clips').find('li.clip').attr('id').toString().replace(/clip-/,'');
		$.get('/clips/delete/'+id, {ajax:'true'}, function(data){
			if(data == 0){
				alert("Sorry, error deleting clip");
			} else {
				$('#clips').fadeOut(200, function(){
					window.location = '/clips/';
				});
			}
		});
		return false;
	}

	var cancelDelete = function(){
		$('#add-clip').text('Edit this clip').unbind('click');
		$('#main div.actions').removeClass('delete-confirmation rounded').animate({width:actionWidth, paddingTop:0, paddingBottom:0}, 400);
		$('#delete-clip').unbind('click', doDelete);
		$('#clips, #taglist').fadeTo(200, 1);
		return false;
	}
	
	$('#delete-clip').bind('click', confirmDelete);

});
</script>
<? $this->template->end() ?>

<? $this->template->set('primary') ?>
	
	<ul id="taglist" class="reset tall">
		<li>Tags: &nbsp;</li>
		<? foreach($tags as $tag): ?>
			<li><a class="rounded <?= $tag['category'] ?>" href="/tags/<?=url_title($tag['title'], 'dash', true)?>"><?=$tag['title']?></a></li>
		<? endforeach ?>
	</ul>
	
	<div class="actions right">
		<a class="btn" id="add-clip" href="/clips/edit/<?=$clip['id']?>">Edit this clip</a>
		<a class="btn" id="delete-clip" href="#">Delete this clip</a>
		<a class="btn" id="view-raw-clip" href="/clips/raw/<?=$clip['id']?>">View raw clip</a>
	</div>
	<p class="post-date">Posted <?= timespan(strtotime($clip['created']), now()); ?> ago.</p>
	<div id="clips">
		<ol class="cliplist reset">
			<li class="clip" id="clip-<?=$clip['id']?>">
				<pre class="clip-code visible"><code class="prettyprint"><?= htmlentities($clip['code'])?></code></pre>
			</li>
			
		</ol>
	</div>

<? $this->template->end() ?>

<? $this->template->set('secondary') ?>
	<div class="inner">
		
		<div id="clip-description">
			<h2>Description</h2>
			<? if($clip['description'] != ''): ?>
				<?= nl2br($clip['description']) ?>
			<? else: ?>
				<p>No description had been added for this clip.
				<br><a href="/clips/edit/<?=$clip['id']?>">Edit</a> the clip to add one</p>
			<? endif ?>
		</div>
		
	</div>
<? $this->template->end() ?>