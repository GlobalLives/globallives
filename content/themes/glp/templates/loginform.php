<form name="loginform" id="loginform" action="/wp/wp-login.php" method="post" class="form-inline pull-right">
	<input type="text" name="log" id="user_login" placeholder="Username" />
	<input type="password" name="pwd" id="user_pass" placeholder="Password" />
	<button type="submit" class="btn">Go</button>
	<br>
	<input name="rememberme" type="checkbox" id="rememberme" value="forever" /> Remember Me
	<input type="hidden" name="redirect_to" value="http://globallives.dev/" />
</form>