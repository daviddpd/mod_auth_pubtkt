<?php

$auth_method = "ldap"; // file, simpledb, ldap

$domain = "";
$secure_cookie = true;	/* set to true if all your web servers use HTTPS */

$logfile = "log/login.log";
$privkeyfile = "/etc/pki/tls/certs/pubtkt/key.pem";
$pubkeyfile = "/etc/pki/tls/certs/pubtkt/cert.pem";
$keytype = "RSA";
$digest = "default";
$localuserdb = "private/users.txt";
$default_timeout = 86400;
$default_graceperiod = 3600;
$default_token = "care2";

// your ldap server 
$ldap_host = "";
// (base location of ldap search)
$ldap_dn = "ou=people,dc=,dc=";
// $ldap_binddn = "uid=$user,$ldap_dn";
$ldap_starttls = TRUE;

$simpledb_file = "private/userdb.json";

$brand_title = "mod_auth_pubtkt Single Sign-On";
$brand_logo = "logo.gif";
$brand_img_attr = "";  // attrs to the IMG HTML tag for the above image.


?>
