<?php
/*
 * 
 * The purpose of this file is to include all the other components that are associated with the
 * builder
 * 
 */

// main components 
include_once("db.php");
include_once("setupFunctions.php");

// web components
include_once("pageBuilder.php");

// componets relating to repo's and sources
include_once("packageProviders.php");
include_once("packageSources.php");

// utility stuff
include_once("utilityFunctions.php");

// Authentication
include_once("authentication.php");

// project managment functionality
include_once("projectManager.php");

// single file page stuff
include_once("singleFile.php");

// cleanups page
include_once("cleanups.php");

// global base packages
include_once("basePackages.php");

// status log package
include_once("statusLog.php");

// file extractors
include_once("fileExtractors.php");

// qemu stuff
include_once("qemuFunctions.php");

// we define one function in "lib.php" which initializes the whole stack
// this includes trying to create databases, shared directories and so forth
// its also going to load the plugins.
// we dont care if it fails because it may be being called for reasons
// other then trying to use the site for building an minifedlet
bls_init();


// TODO, is this funciton we need to check for the existence of certain binaries.
function bls_init()
{
	bls_ut_backTrace("bls_init");
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_globaldb;

	// first, load all our plugins
	if(is_dir($bls_base_plugins)) {
		if($dh = opendir($bls_base_plugins)) {
			while($file = readdir($dh)) {
				if(preg_match("/.*plugin\.php$/", $file)>0) {
					include_once("$bls_base_plugins/$file");
				}
			}
			closedir($dh);
		}
	}
	
	// we dont care if this fails here, we keep going regardless.
	bls_sf_initSetup();
}
?>
