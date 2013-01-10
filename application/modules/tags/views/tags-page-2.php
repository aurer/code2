<? $this->template->use_template('default') ?>
<? $this->template->set('pagetitle', 'Current tags') ?>
<? $this->template->set('head') ?>
<link href="/theme/grey/google-prettify/prettify.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="/theme/grey/google-prettify/prettify.js"></script>
<script type="text/javascript" src="/theme/grey/js/apng.js"></script>
<script type="text/javascript">
$(function(){
	prettyPrint();
	
	// Replace template content with data
	var parseTemplate = function(template, data){
		var i=0,
			len=data.length,
			fragment='';
			
        function replace(obj) {
            var t, key, reg;
 
            for (key in obj) {
                reg = new RegExp('{{' + key + '}}', 'ig');
                t = (t || template).replace(reg, obj[key]);
            }
            return t;
        }
        for (; i < len; i++) {
            fragment += replace(data[i]);
        }
        return fragment;
	}	
	
	// Add an ol to contain loaded clips
	$('#tags').find('li').append('<ol class="clips hidden reset">');

	// Handle loading in clips for this tag
	$('#tags').find('li.unloaded > a').live('click', function(){
		var tagname = $(this).attr('href').replace(/\/tags\//, '').replace(/\//, ''),
			parent = $(this).parent('li'),
			cliplist = parent.find('ol.clips'),
			template = document.querySelector('#tag-template').innerHTML;
		
		parent.css('opacity', 0.3);
		cliplist.removeClass('hidden');
		$.getJSON('/tags/get_clips/'+tagname, function(data){
			var items=[];
			cliplist.html(parseTemplate(template, data)).parent('li').attr('class','loaded open');
			parent.css('opacity', 1);
		});
		return false;
	});
	
	// Handle tags with clips already loaded
	$('#tags').find('.loaded > a').live('click', function(){
		var listItem = $(this).next('ol');
		
		$(this).parent('li').toggleClass('open');
		
		if(listItem.hasClass('hidden')){
			listItem.removeClass('hidden');
		} else {
			listItem.addClass('hidden');
		}
		return false;
	});
	
});
</script>
<style type="text/css">
.hidden{
	display: none;
}
</style>
<? $this->template->end() ?>

<? $this->template->set('primary') ?>
	<div class="actions">
		<p><a class="btn" href="/tags/manage/">Manage tags</a></p>
	</div>
	
	<div id="tags" class="">
		<script id="tag-template" type="template">
			<li id="clip-{{id}}">
				<a href="/clips/view/{{id}}/" class="rounded"><span class="name">{{title}}</span></a>
			</li>
		</script>
		<ol id="tags" class="reset">	
			<? foreach($tags as $tag): ?>
				<li class="unloaded closed" id="tag-<?= $tag['id'] ?>">
					<a class="rounded" href="/tags/<?= url_title($tag['title'], 'dash', true) ?>/">
						<span class="name"><?=$tag['title']?></span>
						<span class="count rounded" title="<?=$tag['count']?> clips using this tag"><?=$tag['count']?></span>
					</a>
				</li>	
			<? endforeach ?>
		</ol>
	</div>
<? $this->template->end() ?>

<? $this->template->set('secondary') ?>
	<? include(APPPATH.'templates/inc/latest-clips.php') ?>
	<? include(APPPATH.'templates/inc/popular-clips.php') ?>
	<? include(APPPATH.'templates/inc/popular-tags.php') ?>
<? $this->template->end() ?>