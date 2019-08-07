<?php

function writedb ($filename, &$db) {
	$fw = fopen($filename, "w");
	$data = json_encode ( $db ) ;
	fputs($fw,$data, strlen($data));
	fclose($fw);
}

function opendb ($filename) {
	$fw = fopen($filename, "r");
	$json = fread($fw,filesize($filename));
	fclose($fw);
	return json_decode ( $json, true );
}

function prompt_echo($x) {

    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
    	if ( $x ) { 
        	system('stty echo');
    	} else {
	        system('stty -echo');
	    }
}


function pwPrompt(&$pw) {
	echo 'Enter password: ';
	prompt_echo(0);
	$password1 = rtrim(fgets(STDIN), PHP_EOL);
	prompt_echo(1);
	echo PHP_EOL;

	echo 'Re-Enter password: ';
	prompt_echo(0);
	$password2 = rtrim(fgets(STDIN), PHP_EOL);
	prompt_echo(1);
	echo PHP_EOL;
	if ( ! hash_equals( $password1, $password2 ) ) {
		return 1;
	} else {
		$pw = password_hash ( $password1, PASSWORD_ARGON2ID );		
		return 0;
	}
}

function updateUser(&$db,&$opt) 
{
	$user = $opt{'user'};
	if ( isset ($db{$user}) ) 
	{ 
	
		if ( isset ( $opt{'passwd'} ) ) 
		{
			do { 
				$ret = pwPrompt($pw);
				if ( $ret ) { echo "Passwords didn't match.\n"; }
			} while ( $ret );
			$db{$user}{'pw'} = $pw;
		}
		
		if ( isset ( $opt{'token'} ) ) {
			$t = $opt{'token'};
			$db{$user}{'tokens'}{$t} = 1;
		}
		if ( isset ( $opt{'deletetoken'} ) ) {
			$t = $opt{'token'};
			unset ($db{$user}{'tokens'}{$t});
		}		
		if ( isset ( $opt{'timeout'} ) ) {
			$db{$user}{'timeout'} = $opt{'timeout'};
		}
		if ( isset ( $opt{'graceperiod'} ) ) {
			$db{$user}{'graceperiod'} = $opt{'graceperiod'};
		}		
	} else {
		print "No such user : $user \n";
	}
	return;
}

function addUser(&$db,&$opt) 
{
	$user = $opt{'user'};
	if ( isset ($db{'user'}{$user}) ) 
	{ 
		print "Users already exists.";
		return;
	}
	do { 
		$ret = pwPrompt($pw);
		if ( $ret ) { echo "Passwords didn't match.\n"; }
	} while ( $ret );
	
	$db{$user} = array();
	$db{$user}{'pw'} = $pw;
	$t = $opt{'token'};
	$db{$user}{'tokens'}{$t} = 1;
	$db{$user}{'timeout'} = $opt{'timeout'};
	$db{$user}{'graceperiod'} = $opt{'graceperiod'};
	
}

function verifyUserPw(&$db,&$opt) {

	$user = $opt{'user'};
	$pw = $opt{'pw'};
	$ret = password_verify($pw, $db{$user}{'pw'});	
	$out['success'] = $ret;
	$out['tokens']  = array_keys ( $db{$user}{'tokens'});
	$out['timeout'] = $db{$user}{'timeout'};
	$out['graceperiod'] = $db{$user}{'graceperiod'};
	
	error_log ( json_encode ( $out ) ) ;
	return $out;

}

?>