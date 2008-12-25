<?php
/*
 * this file is used to define anything that has to be executed
 * on the first access (of either a project or the site itself)
 * 
 * All functions here start with bls_sf_  
 */
 
// TODO: we need to think about "upgrades" in the setup functions.
// for example, we could store a version number in the db's
// and when its "old" we execute a set of sql to update it

// all functions defined by this array are executed the
// first time the web page is hit (i.e. to initialise the
// global database, file system etc) and when a new project
// is created.
// All functions are called as funciton_name("projectname")
// where projectname = "" if its called during the global setup 
global $bls_sf_setup_functions; 
$bls_sf_setup_functions["global"] = "bls_sf_globalSetup";






// this function is called globally by the main lib.php file
// its purpose is to figure out if the project space exists,
// whether the global db exists and initialise them if necessary
function bls_sf_initSetup()
{
	bls_ut_backTrace("bls_sf_initSetup");
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_globaldb;
	global $bls_sf_setup_functions; 
	
	
	if(file_exists($bls_base_work) && is_dir($bls_base_work) && file_exists($bls_base_globaldb)) {
		// we go no further, everything seems in place
		return true;
	}
	
	// ok, so things dont exist, lets create them.
	foreach($bls_sf_setup_functions as $setupcalls) {
		$setupcalls("");
	}
}






// TODO we need to handle things if the global setup fails for any reason
// this functions purpose is to create the global project space.
function bls_sf_globalSetup($project_name)
{
	bls_ut_backTrace("bls_sf_globalSetup");
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_globaldb;
	
	// we only care about global setup in this function
	if($project_name != "") return;

	// create the base directory for "projectSpace"
	mkdir($bls_base_work);
	
	// get the global database
	$globDB = bls_db_getGlobalDB();
	
	// now execute our SQL for the global db structure
	$sql = 'CREATE TABLE "project" (' .
			'"project_id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,' .
			'"project_directory" TEXT,' .
			'"project_name" TEXT,' .
			'"project_description" TEXT,' .
			'"project_author" TEXT,' .
			'"project_create_time" TEXT)';
	$globDB->query($sql);
	
	$sql = 'CREATE TABLE "packageproviders" (' .
			'"provider_id" INTEGER PRIMARY KEY AUTOINCREMENT,' .
			'"provider_name" TEXT,' .
			'"provider_description" TEXT,' .
			'"provider_type" TEXT,' .
			'"provider_enabled" TEXT)';
	$globDB->query($sql);
	
	$sql = 'CREATE TABLE "packagesources" (' .
			'"source_id" INTEGER PRIMARY KEY AUTOINCREMENT,' .
			'"provider_id" INTEGER,' .
			'"source_description" TEXT,' .
			'"source_type" TEXT,' .
			'"source_address" TEXT,' .
			'"source_enabled" TEXT)';
	$globDB->query($sql);

	$sql = 'CREATE TABLE "packages" (' .
			'"package_id" INTEGER PRIMARY KEY AUTOINCREMENT,' .
			'"provider_id" INTEGER,' .
			'"source_id" INTEGER,' .
			'"version_detail" TEXT,' .
			'"package_name" TEXT,' .
			'"package_full_addr" TEXT)';
	$globDB->query($sql);

	$sql = 'CREATE TABLE "cleanup_groups" (' .
			'"group_id" INTEGER PRIMARY KEY AUTOINCREMENT,' .
			'"group_name" TEXT)';
	$globDB->query($sql);

	$sql = 'CREATE TABLE "cleanups" (' .
			'"cleanup_id" INTEGER PRIMARY KEY AUTOINCREMENT,' .
			'"group_id" INTEGER,' .
			'"cleanup_type" TEXT,' .
			'"cleanup_scope" TEXT,' .
			'"cleanup_location" TEXT)';
	$globDB->query($sql);

	$sql = 'CREATE TABLE "basepackages" (' .
			'"base_id" INTEGER PRIMARY KEY AUTOINCREMENT,' .
			'"base_name" TEXT,' .
			'"base_addr" TEXT)';
	$globDB->query($sql);

	$sql = 'CREATE TABLE "qemu" (' .
			'"qemu_id" INTEGER PRIMARY KEY AUTOINCREMENT,' .
			'"project_id" TEXT,' .
			'"build_id" TEXT,' .
			'"build_name" TEXT,' .
			'"qemu_proc_id" TEXT,' .
			'"qemu_socket_id" TEXT,' .
			'"qemu_port" TEXT)';
	$globDB->query($sql);

}






// this functions job is to create the project space and its associated db

?>
