<? if($this->_CI->session->userdata('logged_in')): ?>

<div class="attached">
	<h3>Welcome back <?= $this->_CI->session->userdata('user_username') ?></h3>
	<ul class="reset">
		<li><a href="/profile">Your Profle &rarr;</a></li>
		<li><a href="/myclips">Your clips &rarr;</a></li>
		<li><a href="/login/logout">Logout &rarr;</a></li>
	</ul>
	
</div>

<? endif ?>