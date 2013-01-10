<? $this->template->use_template('default') ?>
	
	<? $this->template->set('pagetitle') ?>Thanks for registering<? $this->template->end() ?>
	<? $this->template->set('primary') ?>
		
		<h2>Hi <?= $user['username'] ?>,</h2>
		<p>Welcome to the site and thanks for registering, you can now sign in to the site and start adding your content.</p>
		
	<? $this->template->end() ?>
<? $this->template->end_template() ?>