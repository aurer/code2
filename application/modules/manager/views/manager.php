<? $this->template->use_template('default') ?>
<? $this->template->set('pagetitle', 'DB Manager') ?>
<? $this->template->set('head') ?>
	<link rel="stylesheet" type="text/css" href="/theme/grey/css/dbmanager.css">
<? $this->template->end() ?>
<? $this->template->set('primary') ?>
	<div id="dbmanager">
		<? $this->dbmanager->generate_manager_interface() ?>
	</div>
<? $this->template->end() ?>