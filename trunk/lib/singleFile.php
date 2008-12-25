<?php

/*
 * This file defines the functions for single file aditions (custom and base packages)
 * 
 * all functions here start with bls_sf_
 */

// single file defines things that are pulled from multiple locations
// as such we can define multiple ways of "pulling" files out
global $bls_sf_location;
$bls_sf_location["fs"] = "Filesystem";
$bls_sf_location["svn"] = "SVN";
$bls_sf_location["cvs"] = "CVS";
$bls_sf_location["httpftp"] = "HTTP/FTP";

// retrieval functions are defined with $bls_sf_location["key"] = "human name"
// such that the package retrieval function will become bls_sf_$key_getPackage
// $addr is sent as the unserialized value below.
// i need to think how this works with respect to the file extraction functions...
// dont want to end up back at another re-write - i need to draw this out on paper
// but i think how it will work is that the build script will get the custom addr
// then it will pull it down using "getPackage" to some tmp location and pass the
// result to the file extraction utils with $unpacklocation saying where to 
// undo it all

// also remember that $unpacklocation is the base of the prject build location
// the extractPackage thing will then do the rest (i.e. deal with "base locations")
function bls_sf_extractPackage($addr_real, $unpacklocation) {
	bls_ut_backTrace("bls_sf_extractPackage");

	// we go into eextract package with the serializedaddres, we have to unserialize it
	$addr = unserialize($addr_real);
	
	$type = $addr["type"];
	$base = $addr["base"];

	// create the upstream path
	if(!file_exists("$unpacklocation/$base/")) mkdir("$unpacklocation/$base/", 0755, true);

	$func = "bls_sf_" . $type . "_extractPackage";
	if (function_exists($func)) {
		$func ($addr, "$unpacklocation/$base");
	}
}

// svn
// for svn, we "checkout" the file into a temporary location
// then copy it into the right place
function bls_sf_svn_extractPackage($addr, $unpacklocation)
{

}

// file system
function bls_sf_fs_extractPackage($addr, $unpacklocation)
{
	bls_ut_backTrace("bls_sf_fs_extractPackage");
	
	
	// deal with directories
	if(is_dir($addr["addr"])) {
		$begin = $addr["addr"];
		system("cd $begin; tar cf - .|(cd $unpacklocation; tar xf -)");
	} else {
		// begin file extraction
		bls_fe_extractFile($addr["addr"], $unpacklocation);
	}	
}

function bls_sf_createSingleFileForm() {
	bls_ut_backTrace("bls_sf_createSingleFileForm");
?>
<table>
<tr><td>Package Name</td><td><input type="text" name="s_pkg_name"></td></tr>
<tr><td>Base Directory</td><td><input type="text" name="s_pkg_base" value="/"></td></tr>
<tr><td><?php bls_sf_selectForTypes() ?></td><td><input type="text" name="s_pkg_loc"></td></tr>
<tr><td>Username</td><td><input type="text" name="s_pkg_user"></td></tr>
<tr><td>Password</td><td><input type="text" name="s_pkg_pass"></td></tr>
<!-- <tr><td>Upload File</td><td><input type="file" name="file_addr"></td></tr> -->
<tr><td><input type="submit" name="add" value="Add"></td></tr>

</table>
<?php


}

// this simple function prints out a select for file types;
function bls_sf_selectForTypes() {
	bls_ut_backTrace("bls_sf_selectForTypes");
	global $bls_sf_location;

	echo "<select name=\"s_pkg_srctype\">";
	foreach ($bls_sf_location as $key => $val) {
		echo "<option value=\"$key\">$val</option>";
	}
	echo "</select>";
}

// the way pkg forms will work is that you'll get name from the aray,
// serialize param to a database and then when you need them you
// unserialize the address to the "extraction" function
function bls_sf_decodeSingleFileForm() {
	bls_ut_backTrace("bls_sf_decodeSingleFileForm");

	$pkg_name = $_REQUEST["s_pkg_name"];
	if (isset ($_REQUEST["s_pkg_base"])) {
		$pkg_base = $_REQUEST["s_pkg_base"];
	}
	if (isset ($_REQUEST["s_pkg_loc"])) {
		$ret["name"] = $pkg_name;
		$ret["param"]["base"] = $_REQUEST["s_pkg_base"];
		$ret["param"]["type"] = $_REQUEST["s_pkg_srctype"];
		$ret["param"]["addr"] = $_REQUEST["s_pkg_loc"];
		$ret["param"]["user"] = $_REQUEST["s_pkg_user"];
		$ret["param"]["pass"] = $_REQUEST["s_pkg_pass"];

		return $ret;
	}

	return false;
}

// this function takes the "param" part of a base file and
// prints a human-readable description
function bls_sf_describePackage($params) {
	bls_ut_backTrace("bls_sf_describePackage");
	global $bls_sf_location;

	$retval = "Package from " . $params["addr"] . ", of type, " . $bls_sf_location[$params["type"]] . ", base of " . $params["base"];

	return $retval;
}
?>
