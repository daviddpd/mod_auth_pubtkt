<?php

$auth_method = "simpledb"; // file, simpledb, ldap

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


// your ldap server 
$ldap_host = "ldap.{DOMAIN}.{TLD}";
// (base location of ldap search)
$ldap_dn = "ou=people,dc={DOMAIN},dc={TLD}";
// $ldap_binddn = "uid=$user,$ldap_dn";
$ldap_starttls = TRUE;

$simpledb_file = "private/userdb.json";

?>