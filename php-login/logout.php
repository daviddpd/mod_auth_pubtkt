<?php
/*
	Example logout page for mod_auth_pubtkt
	(https://neon1.net/mod_auth_pubtkt)
	
	written by Manuel Kasper <mk@neon1.net>
*/

include ("./private/config.php");

if ( isset ($_POST['logout']) ) {
	/* only do this if there really has been a POST; otherwise we could
	   be fooled by pre-caching browsers etc. */
	setcookie("auth_pubtkt", "", time() - 86400, "/", $domain, true);
} else {
	header("Location: login.php");
	exit;
}

?>
<html>
<head>
<title><?= $brand_title ?></title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>

<table width="100%" height="100%">
<tr><td align="center" valign="middle">

<img src="<?= $brand_logo ?>" <?= $brand_img_attr ?> >
<h2>Single Sign-On</h2>

<p>You are now logged out.</p>

</tr></td>
</table>

</body>
</html>
