<?php
/*
	Example login page for mod_auth_pubtkt
	(https://neon1.net/mod_auth_pubtkt)
	
	written by Manuel Kasper <mk@neon1.net>
*/

require_once("pubtkt.inc");
require_once("pubtkt-simplefile.inc");
require_once("userdb.inc");
require_once("ldap.php");

/*	Set the parameters relevant to your domain below.
	WARNING: do not use the example keys provided with the distribution
	in production - otherwise, anyone could fake your tickets! Generate
	your own key!
*/

include ("./private/config.php");

/* authenticates the user with the given password against the local
   user database; returns an array with the following information:
   
   success => true/false,
   tokens => array(tokens that should be given to user),
   timeout => how long the ticket should be valid (in seconds)
   graceperiod => how long the ticket should be refreshed before expiring (in seconds)
*/

/* use the last username, if known (saves the user from having to type that all the time) */
if ( isset ($_COOKIE['sso_lastuser']) ) {
	$username = $_COOKIE['sso_lastuser'];
} else {
	$username = "";
}
$password = "";
$err = "";
$loginsuccess = false;

if (isset ($_GET['back'])) {
	/* Extract the host name of the 'back' URL so we can tell the user when
	   there will be no point in trying to log in, as the cookie won't be
	   available to the target server (e.g. if users try to access a server
	   by its IP address instead of by its proper host name), thus
	   avoiding confusion. */
	$urlp = parse_url($_GET['back']);
	$reshost = $urlp['host'];
	$server_allowed = preg_match("/$domain\$/", $reshost);
} else {
	$server_allowed = true;
}

if (isset ($_COOKIE['auth_pubtkt']) && $_COOKIE['auth_pubtkt']) {
	/* Extract data from existing cookie so we can nicely offer the user
	   a logout function. No attempt at verifying the ticket is made,
	   as that's not necessary at this point. */
	$ticket = pubtkt_parse($_COOKIE['auth_pubtkt']);
	$tkt_validuntil = $ticket['validuntil'];
	$tkt_graceperiod = $ticket['graceperiod'];
	$tkt_uid = $ticket['uid'];

	/* Checking validity of the ticket and if we are between begin of grace 
	   period and end of ticket validity. If so we can refresh ticket */
	if (pubtkt_verify($pubkeyfile, $keytype, $digest, $_COOKIE['auth_pubtkt']) && isset($tkt_graceperiod)
		&& is_numeric($tkt_graceperiod) && ($tkt_graceperiod <= time()) 
		&& (time() <= $tkt_validuntil)) {

		/* getting user information */
		$user_info = get_login_info($tkt_uid);

		if (isset($user_info) && is_array($user_info)) {
	
			$tkt_validuntil = time() + $user_info['data']['timeout'];
	
			/* generate the ticket now and set a domain cookie */
			$tkt = pubtkt_generate($privkeyfile, $keytype, $digest, $tkt_uid,
				$ticket['cip'], $tkt_validuntil, $user_info['data']['graceperiod'], join(",", $user_info['data']['tokens']), "");
			setcookie("auth_pubtkt", $tkt, 0, "/", $domain, $secure_cookie);
	
			setcookie("sso_lastuser", $tkt_uid, time()+30*24*60*60);
	
			if ($_GET['back']) {
				header("Location: " . $_GET['back']);
				exit;
			}
		} else {
			/* User is not present in user database (anymore) - delete the cookie */
			setcookie("auth_pubtkt", false, time() - 86400, "/", $domain, $secure_cookie);
		}
	}
}

if ( isset ($_POST['username']) && isset ($_POST['password']) ) {
	$username = strtolower($_POST['username']);	/* always lower-case usernames for easier matching */
	$password = $_POST['password'];
	
	if ( isset ( $username )  && isset ( $password ) ) 
	{
			/* try to authenticate */
		if ( $auth_method == 'file' ) 
		{
			$res = local_login($username, $password);
		} else 
			if ( $auth_method == 'simpledb' ) 
		{
			$db = opendb($simpledb_file);
			$opt{'user'} = $username;
			$opt{'pw'} = $password;
			$res = verifyUserPw($db, $opt);
		} else 
			if ( $auth_method == 'ldap' ) 
		{
			$res = ldap_auth($username, $password);
		}
	} else {
		$res = array ();
		$res['success'] = 0;
	}
	
	if ($res['success']) {
		log_login($_SERVER['REMOTE_ADDR'], $username, true);
		
		$tkt_validuntil = time() + $res['timeout'];
		
		/* generate the ticket now and set a domain cookie */
		$tkt = pubtkt_generate($privkeyfile, $keytype, $digest, $username,
			$_SERVER['REMOTE_ADDR'], $tkt_validuntil, $res['graceperiod'], join(",", $res['tokens']), "");
		setcookie("auth_pubtkt", $tkt, 0, "/", $domain, $secure_cookie);
		
		setcookie("sso_lastuser", $username, time()+30*24*60*60);
		
		if ( isset ($_GET['back']) ) {
			header("Location: " . $_GET['back']);
			exit;
		}
	} else {
		log_login($_SERVER['REMOTE_ADDR'], $username, false);
		$loginerr = "Authentication failed. Please try again.";
	}
}
?>
<html>
<head>
<title><?= $brand_title ?></title>
<link rel="stylesheet" type="text/css" href="style.css">
<script type="text/javascript">
function dofocus() {
	if (document.loginform.username.value)
		document.loginform.password.focus();
	else
		document.loginform.username.focus();
}

/* The JS code below comes in useful when users open several SSO-protected
   web pages at once without being logged in (e.g. by opening a group of
   bookmarks in tabs. Without this, after logging in on any of the many
   SSO login forms they'd see, they'd manually have to refresh all other
   tabs/windows. The code below checks for changes in the login cookie and
   attempts to direct the browser to the 'back' URL if a change is detected.
*/

var initial_cookie;

function checkCookie_initial() {
	initial_cookie = readCookie('auth_pubtkt');
	setTimeout('checkCookie()', 1000);
}

function checkCookie() {
	/* look for valid login cookie, and if found, redirect to 'back' URL */
	var cookie = readCookie('auth_pubtkt');
	if (cookie != initial_cookie) {
		/* cookie has changed - redirect if back URL provided */
		var backurl = getArg('back');
		if (backurl)
			document.location.href = backurl;
	} else {
		setTimeout('checkCookie()', 1000);
	}
}

function getArg(argname) {
	var qs = document.location.search.substring(1, document.location.search.length);
	if (qs.length == 0)
		return;
	
	qs = qs.replace(/\+/g, ' ');
	var args = qs.split('&');
	
	for (var i = 0; i < args.length; i++) {
		var pair = args[i].split('=');
		
		if (pair[0] == argname)
			return unescape(pair[1]);
	}
	
	return null;
}

function readCookie(cookiename) {
	var nameEQ = cookiename + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}
</script>
</head>

<body onload="dofocus(); checkCookie_initial()">

<table width="100%" height="100%">
<tr><td align="center" valign="middle">

<img src="<?= $brand_logo ?>" <?= $brand_img_attr ?> >
<h2>Single Sign-On</h2>

<?php if (!$server_allowed): ?>

<p>The server <?php echo htmlspecialchars($reshost); ?> is unknown.</p>

<?php elseif ($loginsuccess): ?>

<p>You have successfully signed on.</p>
<p class="small">Your login ticket will expire on <?php echo date("d.m.Y H:i:s", $tkt_validuntil); ?>.</p>

<?php else: ?>

<?php if (isset ($_GET['timeout']) && $_GET['timeout']): ?>
<p>Your session has ended due to a timeout; please log in again.</p>
<?php elseif (isset ($_GET['unauth']) && $_GET['unauth']): ?>
<p>You don&apos;t have permission to access the desired resource on
<?php echo htmlspecialchars($reshost); ?>;<br>you may try logging in again
with different credentials.</p>
<?php elseif (isset ($tkt_uid) && $tkt_validuntil >= time() && $ticket['cip'] == $_SERVER['REMOTE_ADDR']): ?>
<p>You are currently logged on as &apos;<?php echo htmlspecialchars($tkt_uid); ?>&apos;.
<form action="logout.php" method="POST">
<input type="submit" name="logout" value="Logout">
</form>
</p>
<?php endif; ?>

<?php if (isset ($loginerr)): ?>
<p class="errmsg"><?php echo nl2br(htmlspecialchars($loginerr)); ?></p>
<?php endif; ?>

<form name="loginform" method="POST" action="">
<table class="logintbl">
	<tr>
		<th>Username:</th>
		<td><input type="text" name="username" size="20" value="<?php echo htmlspecialchars($username); ?>"></td>
	</tr>
	<tr>
		<th>Password:</th>
		<td><input type="password" name="password" size="20" autocomplete="off"></td>
	</tr>
	<tr class="blank">
		<td></td>
		<td><input type="submit" name="submit" value="Login"></td>
	</tr>
</table>
</form>

<?php endif; ?>

</tr></td>
</table>

</body>
</html>
