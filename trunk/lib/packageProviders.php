<?php
/*
 * 
 * This file defines the package providers code. 
 * all this defines is the types of providers
 * we can define
 * 
 * Always remember, package providers tell us what we get, not how
 * we get it. package sources define how we get packages.
 * 
 * all functions here start with bls_pp_
 */

// first, our global array of package provider types, this is hard coded for now, and will
// only include most current releases as of when i started this re-write
global $bls_pp_provider_types;

// Fedora
$bls_pp_provider_types["fedora9-32"] = "Fedora 9 i386";
$bls_pp_provider_types["fedora9-64"] = "Fedora 9 x86_64";

// Centos
$bls_pp_provider_types["centos52-32"] = "Centos 5.2 i386";
$bls_pp_provider_types["centos51-32"] = "Centos 5.1 i386";
$bls_pp_provider_types["centos50-32"] = "Centos 5.0 i386";
$bls_pp_provider_types["centos52-64"] = "Centos 5.2 x86_64";
$bls_pp_provider_types["centos51-64"] = "Centos 5.1 x86_64";
$bls_pp_provider_types["centos50-64"] = "Centos 5.0 x86_64";

// Redhat
$bls_pp_provider_types["redhat52-32"] = "RedHat 5.2 i386";
$bls_pp_provider_types["redhat51-32"] = "RedHat 5.1 i386";
$bls_pp_provider_types["redhat50-32"] = "RedHat 5.0 i386";
$bls_pp_provider_types["redhat52-64"] = "RedHat 5.2 x86_64";
$bls_pp_provider_types["redhat51-64"] = "RedHat 5.1 x86_64";
$bls_pp_provider_types["redhat50-64"] = "RedHat 5.0 x86_64";

// Slackware
$bls_pp_provider_types["slackware121-386"] = "Slackware 12.1 i386";

// Ubuntu
$bls_pp_provider_types["ubuntu804-32"] = "Ubuntu 8.04 LTS (hardy) i386";
$bls_pp_provider_types["ubuntu810-32"] = "Ubuntu 8.10 (intrepid) i386";
$bls_pp_provider_types["ubuntu804-64"] = "Ubuntu 8.04 LTS (hardy) x86_64";
$bls_pp_provider_types["ubuntu810-64"] = "Ubuntu 8.10 (intrepid) x86_64";


// the above defines the possible package providers, should i have a custom one? not yet.
// the below defines how they're dealt with in code on the web
// a prebuild for pageBuilder functions
$bls_pb_pre_build_funcs["packageprovider_prebuild"] = "bls_pp_packageprovider_prebuild_function";


// most pages are called into via prebuild functions, here is ours. 
function bls_pp_packageprovider_prebuild_function()
{
	bls_ut_backTrace("bls_pp_packageprovider_prebuild_function");
	
	global $bls_base_system;

	$globdb = bls_db_getGlobalDB();
	
	if(isset($_REQUEST["function"])) {
		$ll = $_REQUEST["function"];
		switch($_REQUEST["function"]) {
			case "bls_pp_provideoptionsloader":
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=bls_pp_providerpage");
				//bls_pb_reloadFrame("fr_menu", "index.php?frame=menu&function=loadgloboptmenu");
				exit(0);
				break;
			case "bls_pp_providerpage":
				bls_pp_providerPage();
				exit(0);
				break;
			case "show_provider_packages":
				bls_pp_showPackagesPageForProvider();
				exit(0);
				break;
			case "show_source_packages":
				bls_pp_showPackagesPageForSource();
				exit(0);
				break;
			case "create_new_provider":
				// TODO - needs to be done right
				$name = $_REQUEST["prov_name"];
				$desc = $_REQUEST["prov_desc"];
				$type = $_REQUEST["prov_type"];
				$sql = "insert into packageproviders values (NULL, '$name', '$desc', '$type', 'true')";
				$globdb->query($sql);
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=bls_pp_providerpage");
				exit(0);
				break;
			case "delete_provider":
				$id = $_REQUEST["pp"];
				$globdb->query("delete from packageproviders where provider_id='$id'");
				$globdb->query("delete from packagesources where provider_id='$id'");
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=bls_pp_providerpage");
				exit(0);
				break;
			case "create_source":
				bls_ps_createSourceDialog();
				//bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=bls_pp_providerpage");
				exit(0);
				break;
			case "create_source_addr":
				bls_ps_createSourceDialogAddress();
				//bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=bls_pp_providerpage");
				exit(0);
				break;
			case "create_source_final":
				bls_ps_createSourceFinal();
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=bls_pp_providerpage");
				exit(0);
				break;
			case "delete_source":
				$id = $_REQUEST["sid"];
				$globdb->query("delete from packagesources where source_id='$id'");
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=bls_pp_providerpage");
				exit(0);
			case "enable_source":
				$id = $_REQUEST["sid"];
				$globdb->query("update packagesources set source_enabled='true' where source_id='$id'");
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=bls_pp_providerpage");
				exit(0);									
			case "disable_source":
				$id = $_REQUEST["sid"];
				$globdb->query("update packagesources set source_enabled='false' where source_id='$id'");
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=bls_pp_providerpage");
				exit(0);									
			case "enable_provider":
				$id = $_REQUEST["pid"];
				$globdb->query("update packageproviders set provider_enabled='true' where provider_id='$id'");
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=bls_pp_providerpage");
				exit(0);									
			case "disable_provider":
				$id = $_REQUEST["pid"];
				$globdb->query("update packageproviders set provider_enabled='false' where provider_id='$id'");
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=bls_pp_providerpage");
				exit(0);					
			case "refresh_source":
				$sid = $_REQUEST["sid"];
				system("/usr/bin/php $bls_base_system/refreshsource.php s $sid > /tmp/.rs.log 2>&1 &");
				//bls_ps_refreshSourcePackageList($sid);
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=bls_pp_providerpage");
				exit(0);
			case "refresh_provider":
				$pid = $_REQUEST["pp"];
				error_log("pid is $pid");
				system("/usr/bin/php $bls_base_system/refreshsource.php p $pid > /tmp/.rs.log 2>&1 &");
				//foreach ($globdb->query("select source_id from packagesources where provider_id='$pid'") as $priv) {
					//bls_ps_refreshSourcePackageList($priv["source_id"]);
				//}
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=bls_pp_providerpage");
				exit(0);
		}
	}	
}

// the main provider page.
function bls_pp_providerPage()
{
	bls_ut_backTrace("bls_pp_providerPage");
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_ps_source, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_globaldb, $bls_pp_provider_types;
	
	$globdb = bls_db_getGlobalDB();

	echo "<h2>Package Providers</h2>";
	// first we need a form for adding a new pp.
	?>
<hr>
<h3>New Provider</h3>
<form action="index.php?function=create_new_provider" method="post">
<table>
<tr><td>Provider Name</td><td><input type="text" name="prov_name"></td></tr>
<tr><td>Description</td><td><textarea name="prov_desc" cols="90"></textarea></td></tr>
<tr><td>Provider Type</td><td><select name="prov_type">
<?php
	foreach($bls_pp_provider_types as $key => $val) {
		echo "<option value=\"$key\"> $val </option>\n";
	}
?>
</select></td></tr>
<tr><td><input type="submit" name="Create" value="Create"></td></tr>
</table></form><hr>
	<?php
	
	$c = 0;
	foreach($globdb->query("select * from packageproviders") as $row) {
		if($c == 0) {
			$c++;
			echo "<h3>Existing Providers</h3>";
		}
		$p_id = $row["provider_id"];
		$p_name = $row["provider_name"];
		$p_desc = $row["provider_description"];
		$p_type_sh = $row["provider_type"];
		$p_end = $row["provider_enabled"];
		if($p_end == "true") {
			$p_enabled = "<font color=\"green\">Enabled</font>";
			$e_url = "<a href=\"index.php?function=disable_provider&pid=$p_id\">Disable Provider</a>";
		} else {
			$p_enabled = "<font color=\"red\">Disabled</font>";
			$e_url = "<a href=\"index.php?function=enable_provider&pid=$p_id\">Enable Provider</a>";
		}
		
		if(isset($bls_pp_provider_types["$p_type_sh"])) {
			$p_type = $bls_pp_provider_types["$p_type_sh"];
		} else {
			$p_type = "(unknown) $p_type_sh";
		}
		
		echo "<hr><h4>$p_name - $p_enabled - Provides $p_type</h4>";
		echo "<pre>$p_desc</pre><table>";

		echo "<tr><td><a href=\"index.php?function=create_source&pp=$p_id\">New Source</a></td><td><a href=\"index.php?function=delete_provider&pp=$p_id\">Delete Provider</a></td>" .
				"<td>$e_url</td><td><a href=\"index.php?function=refresh_provider&pp=$p_id\">Refresh</a></td><td><a href=\"index.php?function=show_provider_packages&pid=$p_id\">Show Packages</a></td></tr>";
		echo "</table>";
		echo "<h4>Package Sources</h4>";
		echo "<table border=\"1\">";
		echo "<tr><td></td><th>Source Description</th><th>Source Type</th></tr>";
		foreach($globdb->query("select * from packagesources where provider_id='$p_id'") as $row) {
			$sid = $row["source_id"];
			$desc = $row["source_description"];
			$type_ind = $row["source_type"];
			$type = $bls_ps_source["$type_ind"]["name"];
			if($row["source_enabled"] != "true") {
				$dis = "<font color=\"red\">(off)</font>";
				$surl = 	"<a href=\"index.php?function=enable_source&sid=$sid\">Enable</a>";
			} else {
				$dis = "<font color=\"green\">(on)</font>";
				$surl = 	"<a href=\"index.php?function=disable_source&sid=$sid\">Disable</a>";
			}
			echo "<tr><td><b>Source $dis</b></td><td>$desc</td><td>$type</td><td>$surl</td><td><a href=\"index.php?function=delete_source&sid=$sid\">Delete</a></td>" .
					"<td><a href=\"index.php?function=refresh_source&sid=$sid\">Refresh</a></td><td><a href=\"index.php?function=show_source_packages&sid=$sid\">Show Packages</a></td></tr>";
		}
		echo "</table>";
	}
	
}


// this function gets the provider package list as one huge array
function bls_pp_getProviderPackageList($provider_id)
{
	bls_ut_backTrace("bls_pp_getProviderPackageList");
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_ps_source, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_globaldb, $bls_pp_provider_types;
	
	$globdb = bls_db_getGlobalDB();
	

	$pkg_list = array();
	$c = 0;

	// first determine if the provider is enabeld
	$sql = "select provider_enabled from packageproviders where provider_id='$provider_id'";
	foreach($globdb->query($sql) as $row) $ena = $row[0];
	
	if($ena != 'true') {
		// if now, we return so
		$pkg_list[0] = "Provider Disabled";
		return $pkg_list;
	}
	
	$sql = "select " .
				"distinct(package_name) " .
			"from " .
				"packages a, packagesources b " .
			"where " .
				"a.source_id=b.source_id and " .
				"a.provider_id='$provider_id' and " .
				"b.source_enabled='true'";
	
	foreach($globdb->query($sql) as $row) {
		$pkg_list[$c] = $row[0];
		$c++;
	}
	
	if($c == 0) {
		$pkg_list[0] = "No Available Packages";
	}
	
	return $pkg_list;
}


// this function tells a package provider to refresh its lists
function bls_pp_refreshProviderPackageList($provider_id)
{
	bls_ut_backTrace("bls_pp_getProviderPackageList");
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_ps_source, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_globaldb, $bls_pp_provider_types;
	
	$globdb = bls_db_getGlobalDB();
	
	foreach($globdb->query("select source_id from packagesources where provider_id='$provider_id'") as $row) {
		bls_ps_refreshSourcePackageList($row["source_id"]);
	}
	
	return;
}

function bls_pp_getPackage($package_name, $provider_id)
{
	bls_ut_backTrace("bls_pp_getPackage");
	// we need a funky select here to get things from package providers/sources
	// this will return a location on disk where the file is
	$globdb = bls_db_getGlobalDB();

	$sql = "select a.package_full_addr, a.version_detail, a.source_id, b.source_type from packages a,packagesources b where " .
			"b.source_enabled='true' and a.package_name='$package_name' and b.provider_id='$provider_id' and a.source_id = b.source_id order by a.version_detail asc";
	
	// TODO: get a package of the highest version
	$av = '0';
	foreach($globdb->query($sql) as $row) {
		// what i need is a version comparison algorithm thats going to get it right 99% of the time.
		// for now, we'll just get the last file
		$paddr = $row[0];
		$psid = $row[2];
		$ptype = $row[3];
	}
	
	echo "have $package_name, $provider_id, $ptype, $paddr, $psid\n";
	
	// now we do our actual get package for that package type.
	$func = "bls_ps_$ptype"."_getPackage";
	return $func($psid, $package_name);
}


function bls_pp_showPackagesPageForSource()
{
	bls_ut_backTrace("bls_pp_showPackagesPageForSource");
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_ps_source, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_globaldb, $bls_pp_provider_types;
	
	$sid = $_REQUEST["sid"];
	
	$globdb = bls_db_getGlobalDB();
	
	foreach($globdb->query("select * from packagesources where source_id='$sid'") as $row) {
		$source = $row["source_description"];
		if($source == "") $source = "package source";
	}
	
	echo "<h2>Packages for $source</h2><hr>";
	echo "<table border=\"1\"><tr><th>Name</th><th>Version</th></tr>";
	$c = 0;
	foreach($globdb->query("select * from packages where source_id='$sid' order by package_name") as $row) {
		$c++;
		$pname = $row["package_name"];
		$pvers = $row["version_detail"];
		echo "<tr><td>$pname</td><td>$pvers</td></tr>";
	}
	echo "</table>";
}

function bls_pp_showPackagesPageForProvider()
{
	bls_ut_backTrace("bls_pp_showPackagesPageForSource");
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_ps_source, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_globaldb, $bls_pp_provider_types;
	
	$pid = $_REQUEST["pid"];
	
	$globdb = bls_db_getGlobalDB();
	
	foreach($globdb->query("select * from packageproviders where provider_id='$pid' order by package_name") as $row) {
		$provider = $row["provider_name"];
	}
	
	echo "<h2>Packages for $provider</h2><hr>";
	echo "<table border=\"1\"><tr><th>Name</th><th>Version</th></tr>";
	$c = 0;
	foreach($globdb->query("select * from packages where provider_id='$pid' order by package_name") as $row) {
		$c++;
		$pname = $row["package_name"];
		$pvers = $row["version_detail"];
		echo "<tr><td>$pname</td><td>$pvers</td></tr>";
	}
	echo "</table>";
	
	
}

?>
