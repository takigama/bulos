<?php
/*
 * This file deals with base packages
 * 
 * all functions in this file begin with bls_bp_
 * 
 */
 
 
// our pagebuilder variables and arrays.
global $bls_pb_pre_build_funcs, $bls_pb_titleName;


// a prebuild for pageBuilder functions
$bls_pb_pre_build_funcs["basepackages_prebuild"] = "bls_bp_basepackage_prebuild_function";

function bls_bp_basepackage_prebuild_function()
{
	bls_ut_backTrace("bls_bp_basepackage_prebuild_function");
	
	$globdb = bls_db_getGlobalDB();
	
	if(isset($_REQUEST["function"])) {
		switch($_REQUEST["function"]) {
			case "globalbasepackages":
				bls_bp_mainPage();
				exit(0);
				break;
			case "addglobalbasepackage":
				bls_bp_addGlobalPackages();
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=globalbasepackages");
				exit(0);
				break;
			case "removeglobalbasepackage":
				$id = $_REQUEST["id"];
				$globdb->query("delete from basepackages where base_id='$id'");
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=globalbasepackages");
				exit(0);
				break;		
		}
	}
	
}


function bls_bp_mainPage()
{
	bls_ut_backTrace("bls_bp_mainPage");
	
	$globdb = bls_db_getGlobalDB();
?>
<h2>Base Packages</h2>
<h3>Current</h3>
<table>
<tr><th>Package Name</th><th>Description</th></tr>
<?php
	// print out current pacakges
	foreach($globdb->query("select * from basepackages") as $row) {
		$id = $row["base_id"];
		$pkgname = $row["base_name"];
		$pkgaddr = unserialize($row["base_addr"]);
		$description = bls_sf_describePackage($pkgaddr);
		$url = "index.php?function=removeglobalbasepackage&id=$id";
		echo "<tr><td>$pkgname</td><td>$description</td><td><a href=\"$url\">Remove</a></td></tr>";
	}
?>
</table>
<hr>
<h3>Add Package</h3>
<form method="post" action="index.php?function=addglobalbasepackage">
<?php
	bls_sf_createSingleFileForm();
	
	echo "</form>";
}


function bls_bp_addGlobalPackages()
{
	bls_ut_backTrace("bls_bp_addGlobalPackages");
	
	$globdb = bls_db_getGlobalDB();
	
	if(isset($_REQUEST["add"])) {
		$cust_add = bls_sf_decodeSingleFileForm();
		$name = $cust_add["name"];
		$addr = serialize($cust_add["param"]);
		$globdb->query("insert into basepackages values (NULL, '$name', '$addr')");
	}	
}



?>
