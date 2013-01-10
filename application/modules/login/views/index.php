<? $this->template->use_template('login') ?>
<? $this->template->set('bodyclass', 'one-column') ?>
<? $this->template->set('pagetitle', "Login") ?>
<? $this->template->set('primary') ?>
<div class="inner">
	<div id="login">
		<h1>Login</h1>
		
		<form action="/login" method="post" id="loginform" name="loginform">
			<input type="text" class="username" name="username" placeholder="Username" />
			<input type="password" class="password" name="password" placeholder="Password" />
			<input type="submit" value="Go" />
		</form>
		<div id="info">
			
		</div>
		
	</div>
</div>
<? $this->template->end() ?>