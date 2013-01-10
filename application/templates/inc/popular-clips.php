<?
$result = $this->clip_model->popular_clips();
$pagedata = querydata($result);
?>
<div class="attached">
	<h3>Popular clips</h3>
	<ul id="latest-clips" class="reset">
	 <? foreach ($result as $item) : ?>
	 	<li><a href="/clips/view/<?= $item['id'] ?>"><?= $item['title'] ?></a></li>
	 <? endforeach ?>
	</ul>
</div>