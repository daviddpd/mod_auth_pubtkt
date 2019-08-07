#!/usr/local/bin/php
<?php

global $options;
include "../userdb.inc";
function parseArgs() {

	$longopts  = array(
		"dbfile:",
		"user:",
		"token:",
		"deletetoken:",
		"timeout:",
		"graceperiod:",
		"passwd",
		"debug",
		"add",
		"modify",
		"change",
		"deleteuser",
	);

	$options = getopt("", $longopts);	
	return $options;

}


$opt = parseArgs();
print_r ( $opt );
if ( is_file ( $opt['dbfile']  ) ) {
	$db = opendb($opt['dbfile']);
	echo " file read .\n";
	print_r ($db);
} else {
	$db = json_decode ( '{ }', true );
}
if ( isset ( $opt{'add'} )  ) {
	adduser($db,$opt);
}
if ( isset ( $opt{'deleteuser'} )  ) {
	$user = $opt{'user'};
	unset ( $db{$user} );
}
if ( isset ( $opt{'change'} ) || isset ( $opt{'modify'} )  ) {
	updateUser($db,$opt);
}

if ( isset ( $opt{'dbfile'} )  ) {
	writedb($opt{'dbfile'}, $db);
}

?>
