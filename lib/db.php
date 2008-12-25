<?php
/*
 * this file defines all db functionality.. i.e. getting db handles and so forth.
 * 
 * all functions here should start with bls_db_
 */
 
// TODO we need to handle things if the get fails.
function bls_db_getGlobalDB()
{
	bls_ut_backTrace("bls_db_getGlobalDB");
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_globaldb;
	
	try {
		$dbobject = new PDO("sqlite:$bls_base_globaldb");
	} catch(PDOException $exep) {
		echo "<font color='red'>Cannot open sqlitedb, ".$exep->getMessage()."</font><br>";
		return;
	}
	
	return $dbobject;
}

// this function gets a project specific database
function bls_db_getProjectDB($id)
{
	bls_ut_backTrace("bls_db_getProjectDB");
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_logdb;
	
	// this function needs to go thru the global database and find the project we're looking for and where its defined location is
	try {
		$dbobject = new PDO("sqlite:$bls_base_work/$id/project.db");
	} catch(PDOException $exep) {
		echo "<font color='red'>Cannot open sqlitedb, ".$exep->getMessage()."</font><br>";
		return;
	}
	
	return $dbobject;
}

?>
