<? $this->template->use_template('default') ?>
<? $this->template->set('bodyclass', 'one-column') ?>
<? $this->template->set('pagetitle', "Edit: " . $clip['title']) ?>
<? $this->template->set('head') ?>
	<link rel="stylesheet" href="/theme/grey/css/autocomplete.css" />
	<script src="/theme/grey/js/jquery.validate.min.js" type="text/javascript"></script>
	<script src='/theme/grey/js/jquery.autocomplete-min.js'></script>
	<script type="text/javascript">
	$(function() {
		$('#add-form').validate({
			errorElement : 'div',
			errorPlacement: function(error, element) {
				error.appendTo( element.parents("div.field") );
			},
		});
		$('input[name=tags]').autocomplete({
			serviceUrl:'/tags/autocomplete/',
			delimiter: /(,|;)\s*/
		})
	});
	</script>
<? $this->template->end() ?>
<? $this->template->set('primary') ?>
	
	<?= form_open('', 'id="add-form" class="standard fullwidth"'); ?>
		<fieldset class="main">
			<div class="field required">
				<?= form_label('Title') ?>
				<span class="input"><?= form_input('title', $clip['title'], 'class="required" autofocus') ?></span>
				<?= form_error('title') ?>
			</div>
			
			<div class="field required">
				<?= form_label('Tags') ?>
				<span class="input"><?= form_input('tags', implode(", ", $tags), 'class="required"') ?></span>
				<?= form_error('tags') ?>
			</div>
			
			<div class="field required">
				<?= form_label('Code') ?>
				<span class="input"><?= form_textarea(array('name'=>'code', 'value'=>htmlspecialchars_decode($clip['code']), 'rows'=>20, 'class'=>'required	')) ?></span>
				<?= form_error('code') ?>
			</div>
			
			<div class="field field-private type-checkbox">
				<span class="input"><?= form_checkbox('private', 'true', ($clip['private']==1), 'id=private') ?></span>
				<?= form_label('Keep this clip private', 'private') ?>
				<?= form_error('e') ?>
			</div>
			
			<div class="field">
				<?= form_label('Description') ?>
				<span class="input"><?= form_textarea(array('name'=>'description', 'value'=>$clip['description'], 'rows'=>20)) ?></span>
				<?= form_error('description') ?>
			</div>
		</fieldset>
		<fieldset class="submit">
			<div class="field submit">
				<span class="input">
					<a href="/clips/" class="btn">Cancel</a>
					<?= form_submit('', 'Save', 'class="btn colorfade"') ?>
				</span>
			</div>
		</fieldset>
	<?= form_close() ?>
	
<? $this->template->end() ?>