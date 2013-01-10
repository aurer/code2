<? $pages = array(
	"clips"=>"Clips",
	"tags"=>"Tags",
	"about"=>"About",
); ?>

<nav id="nav1">
	<ul>
	<? foreach($pages as $page_link=>$page_title) : ?>
		<? $active = ($page_link == $this->_CI->uri->segment(1))? "active" : "" ; ?>
		<li class="<?=$active?>"><a href="/<?=$page_link?>/"><?=$page_title?></a></li>
	<? endforeach; ?>
	</ul>
</nav>