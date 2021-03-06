<? $this->template->use_template('default') ?>
	
	<? $this->template->set('primary') ?>

		<?= form_open('/users/register', 'id="register" class="standard fullwidth"') ?>
			
			<fieldset class="main">
				<div class="field required field-title">
					<?= form_label('Username') ?>
					<span class="input"><?= form_input('username', set_value('username'), 'class="required" autofocus') ?></span>
					<?= form_error('username') ?>
				</div>
				
				<div class="field required field-tags">
					<?= form_label('Email') ?>
					<span class="input"><?= form_input('email', set_value('email'), 'class="required"') ?></span>
					<?= form_error('email') ?>
				</div>
				
				<div class="field required field-tags">
					<?= form_label('Password') ?>
					<span class="input"><?= form_password('password', set_value('password'), 'class="required"') ?></span>
					<?= form_error('password') ?>
				</div>
				
				<div class="field required field-tags">
					<?= form_label('Confirm password') ?>
					<span class="input"><?= form_password('password__confirm', set_value('password__confirm'), 'class="required"') ?></span>
					<?= form_error('password__confirm') ?>
				</div>
				
				
				<div class="field submit feild-submit">
					<span class="input">
						<a href="/clips/" class="cancel btn">Cancel</a>
						<?= form_submit('', 'Add it', 'class="btn"') ?>
					</span>
				</div>
			</fieldset>
			
		<?= form_close() ?>

	<? $this->template->end() ?>
<? $this->template->end_template() ?>