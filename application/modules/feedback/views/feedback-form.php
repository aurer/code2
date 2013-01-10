<? $this->template->use_template('default') ?>
<? $this->template->set('pagetitle', 'Feedback') ?>
<? $this->template->set('head') ?>
	<script src="/theme/grey/js/jquery.validate.min.js" type="text/javascript"></script>
	<script type="text/javascript">
	$(function() {
		$('#feedback-form').validate({
			errorElement : 'div',
			errorPlacement: function(error, element) {
				error.appendTo( element.parents("div.field") );
			}
		});
	});
	</script>
<? $this->template->end() ?>
<? $this->template->set('primary') ?>
	
	<?= form_open('', 'id="feedback-form" class="standard fullwidth"'); ?>
		<fieldset class="main">
			<div class="field required">
				<?= form_label('Name') ?>
				<span class="input"><?= form_input(array('name'=>'name', 'value'=>set_value('name'), 'class'=>"required" ) ) ?></span>
				<?= form_error('name') ?>
			</div>
			
			<div class="field required">
				<?= form_label('Email') ?>
				<span class="input"><?= form_input(array('name'=>'email', 'value'=>set_value('email'), 'class'=>"required email")) ?></span>
				<?= form_error('email') ?>
			</div>
			
			<div class="field">
				<?= form_label('Your feedback') ?>
				<span class="input"><?= form_textarea(array('name'=>'feedback', 'value'=>set_value('feedback'), 'rows'=>20, 'class'=>'required')) ?></span>
				<?= form_error('feedback') ?>
			</div>
		</fieldset>
		<fieldset class="submit">
			<div class="field submit">
				<span class="input">
					<?= form_submit('', 'Send', 'class="btn colorfade"') ?>
				</span>
			</div>
		</fieldset>
	<?= form_close() ?>

<? $this->template->end() ?>
<? $this->template->set('secondary') ?>
	<h3>Something to say?</h3>
	<p>If you've got a suggestion or idea for improvement or just want to get in touch, please use the form and I'll </p>
<? $this->template->end() ?>