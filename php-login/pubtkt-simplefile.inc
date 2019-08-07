<?php

function local_login($username, $password) {
	$user_info = get_login_info($username);

	if (isset($user_info) && is_array($user_info)) {
		$out_info = $user_info['data'];
		$out_info['success'] = ($user_info['password'] === $password || $user_info['password'] === md5($password));
			
		return $out_info;
	}
	
	return array('success' => false);
}

function get_login_info($username) {
	global $localuserdb, $default_timeout, $default_graceperiod;
	
	$fd = @fopen($localuserdb, "r");
	if ($fd) {
		while (!feof($fd)) {
			$line = trim(fgets($fd));
			if (preg_match("/^\s*#/", $line))
				continue;
			if (!$line)
				continue;
			
			list($cusername,$cpassword,$tokens,$timeout,$graceperiod) = explode("\t", $line);
			
			if (!$timeout)
				$timeout = $default_timeout;
			
			if (!$graceperiod)
				$graceperiod = $default_graceperiod;
			
			if ($cusername === $username) {
				fclose($fd);
				return array('login' => $cusername, 'password' => $cpassword,
					'data' => array('tokens' => explode(",", $tokens), 'timeout' => $timeout, 'graceperiod' => $graceperiod));
			}
		}
		fclose($fd);
	}
	
	return NULL;
}

/* very simple file-based login auditing */
function log_login($ip, $username, $success) {
	global $logfile;
	$fd = @fopen($logfile, "a");
	if ($fd) {
		fputs($fd, time() . "\t$ip\t$username\t" . ($success ? "1" : "0") . "\n");
		fclose($fd);
	}
}


?>