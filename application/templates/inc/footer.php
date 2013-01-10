<?= $page_footer ?>
<p class="copyright">
Copyright &copy; 2011 Phil Maurer</p>
<p class="meta">Powered by <a href="http://codeigniter.com" target="_blank">Codeigniter</a></p>
<p class="links">
	<a href="http://codeigniter.com" target="_blank">Codeigniter</a> | 
	<a href="http://aurer.co.uk">Aurer.co.uk</a> | 
	<a href="http://twitter.com/philmau">Twitter</a> |
	<a href="/feedback/">Feedback</a> | 
	<? if($this->_CI->session->userdata('logged_in')) : ?>
		<a href="/login/logout">Logout</a>
	<? else : ?>
		<a href="/login">Login</a>
	<? endif ?>
	| <a href="/manager/">Manager</a>
</p>