<?php
/*
 * The purpose of this file is to define where things live on the file system
 * and other 
 */

$boot_port = "19211";
$max_boot = "3";

// this line is specifically for my environment at home
$bls_dev_loc = "/home/paulr/src/eclipse-workspace/minifedWeb2";

// do we log debug messages?
$bls_log_debug = false;

// if you wish to make httpd/apache (or whatever else outside this code base) do the authentication, uncomment the following line
// $bls_au_alt_upstream_auth = "yes";

$bls_base_work = "$bls_dev_loc/projectSpace/"; 						// base directory for project space
$bls_base_buildarea = "$bls_dev_loc/projectSpace/BuildArea/";					// where to do builds - this is kinda redundant actually ebcause its going to occure in the actual projects space
$bls_base_system = "$bls_dev_loc/system/";
$bls_base_sharedspace = "$bls_dev_loc/shared/";					// where to store shared info (like the mfbase package) and mfapi.
$bls_base_web = "$bls_dev_loc/www/";							// where the web pages are located
$bls_base_plugins = "$bls_dev_loc/plugins/";		// where to store plugins
$bls_base_lib = "$bls_dev_loc/lib/";		// where to store plugins
$bls_base_cache = "$bls_dev_loc/projectSpace/cache/";		// where to store cached files (probably used later by things like rpm repo downloaders)
$bls_base_globaldb = "$bls_dev_loc/projectSpace/global.db";		// where the global database is
$bls_base_logdb = "$bls_dev_loc/projectSpace/log.db";		// where the logging database is
$bls_base_export_location = "$bls_dev_loc/projectSpace/exportedProjects";		// where projects are exported to
$bls_base_publish_location = "$bls_dev_loc/projectSpace/publishedBuilds"; // where builds are published to

global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib;
global $bls_base_cache, $bls_base_globaldb, $bls_log_debug, $bls_base_system, $bls_base_export_location, $bls_base_publish_location;


// Now we include the main library
include_once("$bls_base_lib/lib.php");

?>
