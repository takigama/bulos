<?php
/*
 * This file controls the status window - Grand!
 * 
 * All fucntiosn in this file begin with bls_sl_
 * 
 */
 
global $bls_sf_setup_functions; 
$bls_sf_setup_functions["statuslogsetup"] = "bls_sf_setupStatusLogInitial";

global $bls_pb_framestatus_content;
$bls_pb_framestatus_content = "bls_sf_printStatus";


function bls_sf_setupStatusLogInitial()
{
	bls_ut_backTrace("bls_sf_setupStatusLogInitial");
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_globaldb, $bls_base_logdb;
	
	try {
		$dbobject = new PDO("sqlite:$bls_base_logdb");
	} catch(PDOException $exep) {
		echo "<font color='red'>Cannot open sqlitedb, ".$exep->getMessage()."</font><br>";
		return;
	}
	
	$dbobject->query('create table statuslog ( status_id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, status_acked TEXT, status_time INTEGER, status_sev INTEGER, status_text TEXT)');
}

// this function gets the database we use for logging
function bls_sf_getLogDB()
{
	bls_ut_backTrace("bls_sf_getLogDB");
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_logdb;
	
	try {
		$dbobject = new PDO("sqlite:$bls_base_logdb");
	} catch(PDOException $exep) {
		echo "<font color='red'>Cannot open sqlitedb, ".$exep->getMessage()."</font><br>";
		return;
	}
	
	return $dbobject;
}


// controls the status window
function bls_sf_printStatus()
{
	bls_ut_backTrace("bls_sf_printStatus");
	
	$logdb = bls_sf_getLogDB();
	
	echo "<meta http-equiv=\"refresh\" content=\"5\"/>";

	echo "<table><tr><th>Time</th><th>Severity</th><th>Log</th></tr>";
	foreach($logdb->query("select * from statuslog order by status_time desc") as $row) {
		$tm = bls_ut_time($row["status_time"]);
		$sev = $row["status_sev"];
		$lgoe = $row["status_text"];
		echo "<tr><td>$tm</td><td>$sev</td><td>$lgoe</td></tr>";
	}
	echo "</table>";
}

// how to make a log entry - severity is 1-5 (debug, info, warn, error, fatal)
function bls_sf_log($sev, $component, $entry) 
{
	bls_ut_backTrace("bls_sf_log");
	global $bls_log_debug;
	
	if($sev == 1 && !$bls_log_debug) {
		return;
	}
	
	$tm = time();
	$db = bls_sf_getLogDB();
	$db->query("insert into statuslog values (NULL, 'false', '$tm', '$sev', '$entry')");
	
	return;
}



?>
