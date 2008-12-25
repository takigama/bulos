<?php
/*
 * This file does the actual builds...
 * 
 * 
 */
 
// the only configurable here, where to get the web's config.php
include_once("../www/config.php");

if(isset($argv[1]) && isset($argv[2])) {
	$ppid = $argv[1];
	$bid = $argv[2];
	
	$projDB = bls_db_getProjectDB($ppid);
	$globdb = bls_db_getGlobalDB();
	
	// first, lets get some info
	foreach($projDB->query("select * from project_providers where is_current='true'") as $row) {
		$provid = $row["provider_id"];
	}
	
	// build name
	foreach($projDB->query("select build_name from project_builds where build_id='$bid'") as $row) {
		$build_name  = $row["build_name"];
	}
	
	// project name
	foreach($globdb->query("select project_name from project where project_id='$ppid'") as $row) {
		$project_name = $row["project_name"];
	}
	
	// create some directories we'll need
	$build_loc = "$bls_base_work/$ppid/CurrentBuild/$bid/";
	$output_loc = "$bls_base_work/$ppid/Builds/$bid/";
	
	if(!file_exists($build_loc)) mkdir("$build_loc");
	if(!file_exists($output_loc)) mkdir("$output_loc");
	
	bls_sf_log(5, "build", "beginning build \"$build_name\" for \"$project_name\"");
	
	// first we do packages
	foreach($projDB->query("select * from packages_used where provider_id='$provid'") as $row) {
		$pkgname = $row["package_name"];
		echo "getting package, $pkgname from $provid\n";
		$floc = bls_pp_getPackage($pkgname, $provid);
		bls_fe_extractFile($floc, $build_loc);
	}
	
	// now base packages
	foreach($projDB->query("select * from project_basepkgs") as $row) {
		$bid = $row["base_id"];
		foreach($globdb->query("select * from basepackages") as $brow) {
			$bpaddr = $brow["base_addr"];
			$bpname = $brow["base_name"];
		}
		
		echo "extracting base package $bpname\n";
		bls_sf_extractPackage($bpaddr, $build_loc);
	}
	
	// now custom packages
	foreach($projDB->query("select * from project_custom_packages") as $row) {
		$custom_name = $row["custom_name"];
		$custom_addr = $row["custom_addr"];
		
		echo "extracting custom package $custom_name\n";
		bls_sf_extractPackage($custom_addr, $build_loc);
	}
	
	
	// now cleanups
	foreach($projDB->query("select * from project_cleanups") as $row) {
		$cleantype = $row["cleanup_type"];
		$cleanuploc = $row["cleanup_location"];
		
		echo "entering cleanup for location $cleanuploc\n";
		
		if($cleantype == "rm") {
			$script = "cd $build_loc; rm -rf ./$cleanuploc";
			echo "doing a $cleantype on $cleanuploc - executing $script\n";
			system($script);
		}
		if($cleantype == "find") {
			$script = "cd $build_loc; find . -name '$cleanuploc' -exec rm -rf {} \;";
			echo "doing a $cleantype on $cleanuploc - executing $script\n";
			system($script);
		}
	}
	
	
	
	// now get the kernel
	// again, simple... however, we need to make this a little
	// more variable because of different OS's having kernels in
	// different spots
	echo "Getting out the kernel\n";
	$script = "cd $build_loc; mv boot/vmlinu* $output_loc";
	system("$script");
	
	
	
	
	// now create the initrd
	// this is the easy bit, relatively speaking
	// is there any reason to gzip -9 for a file thats giong to be unpacked again
	// and splayed into a fs?
	echo "Creating initial rood disk\n";
	$script = "cd $build_loc; find .|cpio -o -c |gzip -c -f > $output_loc/initrd.img";
	system($script);
	
	
	
	
	
	
	
	
	bls_sf_log(5, "build", "build complete for $build_name in project $project_name");
	
}

?>
