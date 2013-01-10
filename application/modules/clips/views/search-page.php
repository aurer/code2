<? $this->template->use_template('default') ?>
<? $this->template->set('pagetitle', "Search clips") ?>

<? $this->template->set('head') ?>
<script type="text/javascript" src="/theme/grey/js/clips.js"></script>
<script type="text/javascript" src="/theme/grey/js/activefilter.js"></script>
<script type="text/javascript">
$(function(){
	cf.handleClipViewing();
	cf.mapKeys();
	cf.handleDelete();
	cf.handleViewOptions();
	cf.loadViewPreference();
});
</script>
<? $this->template->end() ?>

<? $this->template->set('primary') ?>

	<?= ini_get('output_handler'); ?>
	<div id="modal"></div>
	<div id="modal-cover"></div>
	
	<div class="actions">
		<a class="btn" id="add-clip" href="/clips/">Show all clips</a>
		<a class="btn active" id="viewlist" href="#toggleview"><img src="/theme/grey/gfx/list.png" alt="List view"/></a>
		<a class="btn" id="viewgrid" href="#toggleview"><img src="/theme/grey/gfx/grid.png" alt="Grid view"/></a>
		<form id="filter" action="/clips/search/" method="get">
			<input class="rounded" name="q" type="text" id="filter-keyword" placeholder="Filter" value="<?=$this->input->get('q')?>" />
		</form>
	</div>
	<p class="result"><b><?=count($clips)?></b> clips found containing: <strong><?=$this->input->get('q')?></strong></p>
	<div id="clips">
		<ol class="cliplist reset">
			<? foreach($clips as $clip): ?>
			
			<? $tagtitles = array(); ?>
			
			<? $cliptags = $this->tag_model->tags_for_clip($clip['id']); ?>
			
			<li class="clip" id="clip-<?=$clip['id']?>">
					<h3 class="clip-title"><a class="rounded" href="/clips/view/<?=$clip['id']?>/"><?=$clip['title']?></a></h3>
					<div class="clip-options">
						<a class="view-clip" href="/clips/view/<?=$clip['id']?>/">View details</a>
						<a class="edit-clip" href="/clips/edit/<?=$clip['id']?>/">Edit</a>
						<a class="delete-clip" href="#delete">Delete</a>
					</div>
					
					<pre class="clip-code hidden"><code class="prettyprint"><?= htmlentities($clip['code'])?></code></pre>
					<div class="clip-tags">
						<? foreach ($cliptags as $tag) echo "<span>$tag[title]</span> " ?>
					</div>
				</li>
			<? endforeach ?>
		</ol>
	</div>

<? $this->template->end() ?>

<? $this->template->set('secondary') ?>
	<? include(APPPATH.'templates/inc/tags.php') ?>
	<? include(APPPATH.'templates/inc/latest-clips.php') ?>
	<? include(APPPATH.'templates/inc/popular-clips.php') ?>
	<? include(APPPATH.'templates/inc/popular-tags.php') ?>
<? $this->template->end() ?>