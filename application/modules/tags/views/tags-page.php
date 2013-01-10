<? $this->template->use_template('default') ?>
<? $this->template->set('pagetitle', 'Current tags') ?>
<? $this->template->set('head') ?>
<link href="/theme/grey/google-prettify/prettify.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="/theme/grey/google-prettify/prettify.js"></script>
<script type="text/javascript">
$(function(){
	prettyPrint();	
});
</script>
<? $this->template->end() ?>

<? $this->template->set('primary') ?>
	<div class="actions">
		<p><a class="btn" href="/tags/manage/">Manage tags</a></p>
	</div>
	
	<div id="tags" class="">
		<? foreach (range('A','Z') as $i): ?>
			<div class="alpha">
			<h2><?=$i?></h2>
				<ol>
			<? foreach($tags as $tag): ?>
				<? if( substr( strtolower($tag['title'] ), 0, 1) == strtolower($i) ): ?>
					<li><a href="/tags/<?= url_title($tag['title'], 'dash', true) ?>" class="alphatag rounded" id="tagid">
						<span class="name"><?=$tag['title']?></span>
						<small class="count rounded" title="<?=$tag['count']?> clips using this tag"><?=$tag['count']?></small>
					</a></li>
				<? endif ?>
			<? endforeach ?>
			</ol>
			</div>
		<? endforeach ?>
		</div>
	</dl>
<? $this->template->end() ?>

<? $this->template->set('secondary') ?>
	<? include(APPPATH.'templates/inc/latest-clips.php') ?>
	<? include(APPPATH.'templates/inc/popular-clips.php') ?>
	<? include(APPPATH.'templates/inc/popular-tags.php') ?>
<? $this->template->end() ?>