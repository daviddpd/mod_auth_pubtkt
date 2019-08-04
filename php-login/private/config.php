<?php

$domain = "web1.dpdtech.com";
$secure_cookie = true;	/* set to true if all your web servers use HTTPS */

$logfile = "log/login.log";
$privkeyfile = "/usr/local/etc/ssl/dpdtech/pubtkt/key.pem";
$pubkeyfile = "/usr/local/etc/ssl/dpdtech/pubtkt/cert.pem";
$keytype = "RSA";
$digest = "default";
$localuserdb = "private/users.txt";
$default_timeout = 86400;
$default_graceperiod = 3600;


?>