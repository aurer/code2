<? if( !is_array($tags) ) $tags = $this->tag_model->get_distinct(); ?>
<div class="attached">
	<h3>Tags</h3>
	<ul id="taglist" class="reset">
		<? if(count($tags) < 1): ?>
			<li>No tags found, you need to get tagging!</li>			
		<? else: ?>
			<? foreach($tags as $tag): ?>
				<? $tagurl = url_title($tag['title'], 'dash', true) ?>
				<li <? if($search == $tagurl) echo 'class="active"' ?>><a class="rounded" href="/tags/<?= $tagurl?>"><?=ucfirst($tag['title'])?></a></li>
			<? endforeach ?>
		<? endif ?>
	</ul>
</div>