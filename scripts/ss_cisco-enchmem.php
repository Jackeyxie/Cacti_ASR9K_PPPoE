<?php

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

$no_http_headers = true;

/* display No errors */
error_reporting(0);

if (isset($config)) {
	include_once(dirname(__FILE__) . "/../lib/snmp.php");
}

if (!isset($called_by_script_server)) {
	include_once(dirname(__FILE__) . "/../include/global.php");
	include_once(dirname(__FILE__) . "/../lib/snmp.php");

	array_shift($_SERVER["argv"]);

	print call_user_func_array("ss_ciscoenchmem", $_SERVER["argv"]);
}

function ss_ciscoenchmem($hostname, $host_id, $snmp_auth, $cmd, $arg1 = "", $arg2 = "") {
	$snmp = explode(":", $snmp_auth);
	$snmp_version 	= $snmp[0];
	$snmp_port    	= $snmp[1];
	$snmp_timeout 	= $snmp[2];
	$ping_retries 	= $snmp[3];
	$max_oids	= $snmp[4];

	$snmp_auth_username   	= "";
	$snmp_auth_password   	= "";
	$snmp_auth_protocol  	= "";
	$snmp_priv_passphrase 	= "";
	$snmp_priv_protocol   	= "";
	$snmp_context         	= "";
	$snmp_community 		= "";

	if ($snmp_version == 3) {
		$snmp_auth_username   = $snmp[6];
		$snmp_auth_password   = $snmp[7];
		$snmp_auth_protocol   = $snmp[8];
		$snmp_priv_passphrase = $snmp[9];
		$snmp_priv_protocol   = $snmp[10];
		$snmp_context         = $snmp[11];
	}else{
		$snmp_community = $snmp[5];
	}

	$oids = array(
		"index" 		=> ".1.3.6.1.4.1.9.9.221.1.1.1.1.2",
		"type"	 		=> ".1.3.6.1.4.1.9.9.221.1.1.1.1.2",
		"physdesc" 		=> ".1.3.6.1.2.1.47.1.1.1.1.2",
		"physname" 		=> ".1.3.6.1.2.1.47.1.1.1.1.7",
		"poolname" 		=> ".1.3.6.1.4.1.9.9.221.1.1.1.1.3",
		"poolvalid" 		=> ".1.3.6.1.4.1.9.9.221.1.1.1.1.6",
		"poolused" 		=> ".1.3.6.1.4.1.9.9.221.1.1.1.1.7",
		"poolfree" 		=> ".1.3.6.1.4.1.9.9.221.1.1.1.1.8",
		);

	if ($cmd == "index") {
		$return_arr = ss_ciscoenchmem_reindex(cacti_snmp_walk($hostname, $snmp_community, $oids["index"], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));

		for ($i=0;($i<sizeof($return_arr));$i++) {
			print $return_arr[$i] . "\n";
		}

	}elseif ($cmd == "query") {
		$arg = $arg1;
		
		if (($arg == "physdesc" ) || ($arg == "physname")) {
			$arr_index = ss_ciscoenchmem_reindex(cacti_snmp_walk($hostname, $snmp_community, $oids["index"], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));

			for ($i=0;($i<sizeof($arr_index));$i++) {
				$arr[$i] = cacti_snmp_get($hostname, $snmp_community, $oids[$arg] . "." . substr($arr_index[$i], 0, strpos($arr_index[$i], '.')), $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol,$snmp_priv_passphrase,$snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, SNMP_POLLER);
				print $arr_index[$i] . "!" . $arr[$i] . "\n";
			}
		
		}elseif ($arg == "index") {
			$arr_index = ss_ciscoenchmem_reindex(cacti_snmp_walk($hostname, $snmp_community, $oids["index"], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));

			for ($i=0;($i<sizeof($arr_index));$i++) {
				print $arr_index[$i] . "!" . $arr_index[$i] . "\n";
			}
		
		}else{

			$arr_index = ss_ciscoenchmem_reindex(cacti_snmp_walk($hostname, $snmp_community, $oids["index"], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
			$arr = ss_ciscoenchmem_values(cacti_snmp_walk($hostname, $snmp_community, $oids[$arg], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));

			for ($i=0;($i<sizeof($arr_index));$i++) {
				print $arr_index[$i] . "!" . $arr[$i] . "\n";
			}
		}
	}elseif ($cmd == "get") {
		$arg = $arg1;
		$index = $arg2;

		if ($arg == "limit") {
		
		}else{
			return cacti_snmp_get($hostname, $snmp_community, $oids[$arg] . ".$index", $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol,$snmp_priv_passphrase,$snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, SNMP_POLLER);
		}
	}
}

function ss_ciscoenchmem_reindex($arr) {
	$return_arr = array();

	for ($i=0;($i<sizeof($arr));$i++) {
		$return_arr[$i] = preg_replace("/(1.3.6.1.4.1.9.9.221.1.1.1.1.2.)/", "", $arr[$i]["oid"]);
	}

	return $return_arr;
}

function ss_ciscoenchmem_values($arr) {
	$return_arr = array();

	for ($i=0;($i<sizeof($arr));$i++) {
		$return_arr[$i] = $arr[$i]["value"];
	}

	return $return_arr;
}

?>
