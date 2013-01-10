<?
$result = $this->tag_model->popular_tags();
$pagedata = querydata($result);
?>
<div class="attached">
	<h3>Popular tags</h3>
	<ul id="latest-clips" class="reset">
	 <? foreach ($result as $item) : ?>
	 	<li><a href="/tags/<?= url_title($item['title'], 'dash', true) ?>"><?= $item['title'] ?></a></li>
	 <? endforeach ?>
	</ul>
</div>