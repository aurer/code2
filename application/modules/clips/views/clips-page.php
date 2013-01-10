<? $this->template->use_template('default') ?>
<? $this->template->set('pagetitle', 'Clips') ?>
<? $this->template->set('head') ?>
<link href="/theme/grey/google-prettify/prettify.css" type="text/css" rel="stylesheet" />
<link rel="stylesheet" href="/theme/grey/css/autocomplete.css" />
<script type="text/javascript" src="/theme/grey/js/clips.js"></script>
<script type="text/javascript" src="/theme/grey/js/activefilter.js"></script>
<script type="text/javascript" src="/theme/grey/google-prettify/prettify.js"></script>
<script src="/theme/grey/js/jquery.validate.min.js" type="text/javascript"></script>
<script src='/theme/grey/js/jquery.autocomplete-min.js'></script>
<script src='/theme/grey/js/apng.js'></script>
<script type="text/javascript">
$(function(){

	prettyPrint();
	
    cf.handleClipViewing();
	cf.mapKeys();
	cf.handleDelete();
	cf.handleViewOptions();
	cf.loadViewPreference();
    cf.handleModalForms();

	$.activeFilter({
		needle: '#filter-keyword',
		haystack: '.clip h3 a',
		hideEle: '.clip',
		fxHide: 'slideUp',
		fxShow: 'slideDown',
		hideSpeed: 200,
		showSpeed: 200,
		regex: true
	});
    
});
</script>
<? $this->template->end() ?>

<? $this->template->set('primary') ?>

	<?= ini_get('output_handler'); ?>
	<div id="modal" class="rounded"></div>
	<div id="modal-cover"></div>	
	
	<div class="actions">
		<a class="btn add" id="add-clip" href="/clips/add/"><span>Add a Clip</span></a>
		<a class="btn active" id="viewlist" href="#toggleview"><img src="/theme/grey/gfx/list.png" alt="List view"/></a>
		<a class="btn" id="viewgrid" href="#toggleview"><img src="/theme/grey/gfx/grid.png" alt="Grid view"/></a>
		<form id="filter" action="/clips/search/" method="get">
			<input class="rounded" name="q" type="text" id="filter-keyword" placeholder="Filter"/>
		</form>
	</div>
	
	<? if($paged) echo $this->pagination->create_links() ?>
	
	<div id="clips">
		<ol class="cliplist reset">
			
			<? if(count($clips) < 1): ?>
			
				<p>No matching clips</p>
			
			<? else: ?>
				
				<? foreach($clips as $clip): ?>
				
				<? $tagtitles = array(); ?>
				
				<? $cliptags = $this->tag_model->tags_for_clip($clip['id']); ?>
				
				<li class="clip" id="clip-<?=$clip['id']?>">
					<h3 class="clip-title"><a class="rounded" href="/clips/view/<?=$clip['id']?>/"><?=$clip['title']?></a></h3>
					<div class="clip-options">
						<a class="view-clip" href="/clips/view/<?=$clip['id']?>/">View details</a>
						<a class="edit-clip" href="/clips/edit/<?=$clip['id']?>/">Edit</a>
						<a class="delete-clip" href="#delete">Delete</a>
						<a class="raw-clip" href="/clips/raw/<?=$clip['id']?>/">Raw</a>
					</div>
					<pre class="clip-code hidden"><code class="prettyprint"><?= htmlspecialchars($clip['code']) ?></code></pre>
				</li>
				<? endforeach ?>
			<? endif ?>
		</ol>
	</div>
	
	<? if($paged) echo $this->pagination->create_links() ?>
	

<? $this->template->end() ?>

<? $this->template->set('secondary') ?>
	<? include(APPPATH.'templates/inc/tags.php') ?>
	<? include(APPPATH.'templates/inc/latest-clips.php') ?>
	<? include(APPPATH.'templates/inc/popular-clips.php') ?>
	<? include(APPPATH.'templates/inc/popular-tags.php') ?>
<? $this->template->end() ?>
