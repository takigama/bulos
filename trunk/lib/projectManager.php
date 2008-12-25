<?php
/*
 * The purpose of this file is define all project-management related
 * functionality... no, it doesn't know prince2 ;)
 * 
 * all functions here start with $bls_pm_
 */
 
 
$bls_pb_pre_build_funcs["projectmanager_prebuild"] = "bls_pm_projectmanager_prebuild_function";


function bls_pm_projectmanager_prebuild_function()
{
	bls_ut_backTrace("bls_pm_projectmanager_prebuild_function");
	
	global $bls_base_work;

	$globdb = bls_db_getGlobalDB();
	
	if(isset($_REQUEST["function"])) {
		switch($_REQUEST["function"]) {
			case "projectbuildlogshow":
				$ppid = $_REQUEST["ppid"];
				$bid = $_REQUEST["bid"];
				
				echo "<pre>";
				$fn = "$bls_base_work/$ppid/Builds/$bid/output.log";
				error_log("file: $fn");
				$h = fopen("$fn", "r");
				$contents = fread($h, filesize($fn));
				echo $contents;
				fclose($h);
				echo "</pre>";
				
				exit(0);
				break;
			case "loadprojectcleanupsframe":
				bls_pm_loadProjectCleanupsFrame();
				exit(0);
				break;
			case "manageprojectbulids":
				bls_pm_manageBuilds();
				exit(0);
				break;
			case "manageproviderpackages":
				bls_pm_manageProviderPackages();
				exit(0);
				break;
			case "managecustompackages":
				bls_pm_manageCustomPackages();
				exit(0);
				break;
			case "deleteprojectbuild":
				$bid = $_REQUEST["bid"];
				$ppid = $_REQUEST["ppid"];
				
				// delete the fs components
				$build_loc = "$bls_base_work/$ppid/CurrentBuild/$bid/";
				$output_loc = "$bls_base_work/$ppid/Builds/$bid/";
				system("rm -rf $build_loc $output_loc > /dev/null 2>&1");
				
				// now delete db entries;
				$projDB = bls_db_getProjectDB($ppid);
				$projDB->query("delete from project_builds where build_id='$bid'");
				
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=loadbuildframe&ppid=$ppid");
				exit(0);
				break;
			case "createnewproject":
				$name = $_REQUEST["proj_name"];
				$id = bls_pm_projectSpaceSetup($name);
				bls_pb_reloadFrame("fr_tabs");
				bls_pb_reloadFrame("fr_menu", "index.php?function=loadproject&ppid=$id");
				exit(0);
				break;
			case "setcurrentprovider":
				$ppid = $_REQUEST["ppid"];
				$provider = $_REQUEST["provider"];
				
				// first, check if its already there
				$projDB = bls_db_getProjectDB($ppid);
				
				foreach($projDB->query("select count(*) from project_providers where provider_id='$provider'") as $row) {
					$val = $row[0];
				}
				
				if($val == "0") {
					$projDB->query("update project_providers set is_current='false'");
					$projDB->query("insert into project_providers values (NULL, '$provider', 'true')");
				} else {
					$projDB->query("update project_providers set is_current='false'");					
					$projDB->query("update project_providers set is_current='true' where provider_id='$provider'");					
				}
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=changeproviderinfo&ppid=$ppid");
				exit(0);
				break;
			case "exportproject":
				bls_pm_exportProjectFrame();
				exit(0);
				break;
			case "new_project":
				bls_pb_reloadFrame("fr_head", "index.php?frame=head&title=New Project");
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=ct_newproject"); 
				bls_pb_reloadFrame("fr_menu", "index.php?frame=menu&function=blankpage");
				exit(0);
				break;
			case "loadbuildframe":
				bls_pm_loadBuildFrame();
				exit(0);
				break;
			case "manageprojectbasepackages":
				bls_pm_manageBasePackages();
				exit(0);
				break;
			case "loadbootframe":
				bls_pm_loadBootFrame();
				exit(0);
				break;
			case "updatemainprojectinfo":
				$ppid = $_REQUEST["ppid"];
				$pdesc = $_REQUEST["pdesc"];
				$globdb->query("update project set project_description='$pdesc' where project_id='$ppid'");
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=loadprojectcontentframe&ppid=$ppid");
				exit(0);
				break;
			case "ct_newproject":
				bls_pm_newProjectForm();
				
				exit(0);
				break;
			case "changeproviderinfo":
				bls_pm_manageProjectProviders();
				exit(0);
				break;
			case "loadproject":
				// this will put this code into "menu"
				$ppid = $_REQUEST["ppid"];
				foreach($globdb->query("select project_name from project where project_id='$ppid'") as $row) {
					$title = $row["project_name"];
				}
				bls_pm_loadProjectMenu();
				bls_pb_reloadFrame("fr_head", "index.php?frame=head&title=$title");
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=loadprojectcontentframe&ppid=$ppid");
				exit(0);
				break;
			case "loadprojectcontentframe":
				bls_pm_projectMainContentPage();
				exit(0);
				break;
			case "loadprojectpackagesframe":
				bls_pm_projectPackageListManagementFrame();
				exit(0);
				break;
		}
	}
	
	// during the project managment pre-build, we also need to furnish the tabs bar with known projects.
	if(isset($_REQUEST["frame"])) if($_REQUEST["frame"] == "tabs") {
		global $bls_pb_tabs;
		$globdb = bls_db_getGlobalDB();
		foreach($globdb->query("select * from project") as $row) {
			$name = $row["project_name"];
			$url = "index.php?function=loadproject&ppid=".$row["project_id"];
			$bls_pb_tabs["<i><b>$name</b></i>"] = $url;
		}
	}
}


// this function is used to build the frame content for exporting
// a project. When a project is exported, it goes up to the "root"
// where it can then be published, etc.
function bls_pm_exportProjectFrame()
{
	bls_ut_backTrace("bls_pm_exportProjectFrame");
	
	echo "<h2>Export Project</h2>";
}

// this function just shows the current details for the project in the main
// content window
function bls_pm_projectMainContentPage() 
{
	bls_ut_backTrace("bls_pm_projectMainContentPage");
	
	$ppid = $_REQUEST["ppid"];
	
	$globdb = bls_db_getGlobalDB();
	$projdb = bls_db_getProjectDB($ppid);
	
	foreach($globdb->query("select * from project where project_id='$ppid'") as $proj) {
		$pname = $proj["project_name"];
		$pdesc = $proj["project_description"];
		$pauth = $proj["project_author"];
	}
	
	echo "<h2>Project - $pname</h2>";
	echo "<b>Created By</b> $pauth<br>";
	echo "<form method=\"post\" action=\"index.php?function=updatemainprojectinfo&ppid=$ppid\"><textarea name=\"pdesc\" cols=70>$pdesc</textarea><br><input type=\"submit\" name=\"update\" value=\"update\"></form>";
}

// this functoin loads a project menu()
function bls_pm_loadProjectMenu()
{
	bls_ut_backTrace("bls_pm_loadProjectMenu");

	global $bls_pb_menus;

	$ppid = $_REQUEST["ppid"];
	
	$bls_pb_menus = array();
	
	$bls_pb_menus["Details"] = "index.php?frame=content&function=loadprojectcontentframe&ppid=$ppid";
	$bls_pb_menus["Export"] = "index.php?frame=content&function=exportproject&ppid=$ppid";
	$bls_pb_menus["Providers"] = "index.php?frame=content&function=changeproviderinfo&ppid=$ppid";
	$bls_pb_menus["Packages"] = "index.php?frame=content&function=loadprojectpackagesframe&ppid=$ppid";
	$bls_pb_menus["Cleanups"] = "index.php?frame=content&function=loadprojectcleanupsframe&ppid=$ppid";
	$bls_pb_menus["Build"] = "index.php?frame=content&function=loadbuildframe&ppid=$ppid";
	$bls_pb_menus["Boot"] = "index.php?frame=content&function=loadbootframe&ppid=$ppid";
	bls_pb_menuBuilder();
}

function bls_pm_newProjectForm()
{
	bls_ut_backTrace("bls_pm_newProjectForm");
?>
<form action="index.php?function=createnewproject" method="post">
<table>
<tr><th>Project Creation</th></tr>
<tr><td>Name:</td><td><input type="text" name="proj_name"></td></tr>
<tr><td><input type="submit" name="Create" value="Create"></td></tr>
</table>
</form>
<?php
}


function bls_pm_manageProjectProviders()
{
	bls_ut_backTrace("bls_pm_newProjectForm");
	
	$ppid = $_REQUEST["ppid"];
	
	$projDB = bls_db_getProjectDB($ppid);
	$globdb = bls_db_getGlobalDB();
	
	echo "<h2>Providers</h2>";
	echo "<table><tr><th>Provider</th><th>Enabled?</th><th>Used?</th><th>Current?</th></tr>";

	$cpid = -1;
	foreach($projDB->query("select is_current,provider_id from project_providers") as $prov) {
		if($prov["is_current"] == "true") {
			$cpid = $prov["provider_id"];
		}
	}

	foreach($globdb->query("select provider_id,provider_name,provider_enabled from packageproviders") as $row) {
		$name = $row["provider_name"];
		$ena = $row["provider_enabled"];
		$prov_id = $row["provider_id"];
		
		if($ena == "true") $enabled = "<font color=\"green\">Yes</font>";
		else $enabled = "<font color=\"Red\">No</font>";
		
		foreach($projDB->query("select count(*) from packages_used where provider_id='$prov_id'") as $pk_us) {
			$pkgs = $pk_us[0];
		}
		
		foreach($projDB->query("select count(*) from project_providers where provider_id='$prov_id'") as $prov) {
			if($prov["0"] == "0") {
				$used = "<font color=\"red\">No</font>";
			} else {
				$used = "<font color=\"green\">Yes ($pkgs)</font>";				
			}
		}
		
		if("$prov_id" != "$cpid") {
			$sec_cur = "<a href=\"index.php?function=setcurrentprovider&ppid=$ppid&provider=$prov_id\">Set</a>";
		} else {
			$sec_cur = "<font color=\"green\">Current</font>";
		}
		
		echo "<tr><td>$name</td><td>$enabled</td><td>$used</td><td>$sec_cur</td></tr>";
	}
	echo "</table>";
}

function bls_pm_projectSpaceSetup($project_name)
{
	bls_ut_backTrace("bls_sf_projectSpaceSetup");
	global $bls_au_username, $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_globaldb;
	
	
	// first we create a database entry so we can figure out where to put the farker
	$globdb = bls_db_getGlobalDB();
	$globdb->query("insert into project values (NULL, '', '$project_name', '', '$bls_au_username', 'whatever')");
	$id = $globdb->lastInsertID();
	$globdb->query("update project set project_directory='$bls_base_work/$id/' where project_id='$id'");
	
	
	// first create the project space fot his project
	mkdir("$bls_base_work/$id");
	mkdir("$bls_base_work/$id/Builds");
	mkdir("$bls_base_work/$id/CurrentBuild");
	
	
	// create the project DB
	$projDB = bls_db_getProjectDB($id);
	
	// now create the sql info for the project
	$sql = "create table packages_used (" .
			"package_id INTEGER PRIMARY KEY AUTOINCREMENT," .
			"provider_id TEXT," .
			"package_name TEXT)";
	$projDB->query($sql);
	
	// we dont need this
	// now the provider ID's used here
	$sql = "create table project_providers (" .
			"pp_id INTEGER PRIMARY KEY AUTOINCREMENT," .
			"provider_id integer," .
			"is_current text)";
	$projDB->query($sql);
	
	// now cleanups
	$sql = "create table project_cleanups (" .
			"pc_id INTEGER PRIMARY KEY AUTOINCREMENT," .
			"cleanup_scope TEXT," .
			"cleanup_type TEXT," .
			"cleanup_location TEXT)";
	$projDB->query($sql);
	
	// now cleanups
	$sql = "create table project_custom_packages (" .
			"custom_id INTEGER PRIMARY KEY AUTOINCREMENT," .
			"custom_name TEXT," .
			"custom_addr TEXT)";
	$projDB->query($sql);
	
	$sql = "create table project_builds (" .
			"build_id INTEGER PRIMARY KEY AUTOINCREMENT," .
			"build_name TEXT," .
			"build_desc TEXT," .
			"build_cpus TEXT," .
			"build_mem TEXT)";
	$projDB->query($sql);
	
	$sql = "create table project_basepkgs (" .
			"project_base_id INTEGER PRIMARY KEY AUTOINCREMENT," .
			"base_id INTEGER)";
	$projDB->query($sql);

	return $id;
}


function bls_pm_projectPackageListManagementFrame()
{
	bls_ut_backTrace("bls_pm_projectPackageListManagementFrame");
	
	$ppid = $_REQUEST["ppid"];
	
	$projDB = bls_db_getProjectDB($ppid);
	$globdb = bls_db_getGlobalDB();
	

	foreach($projDB->query("select provider_id from project_providers where is_current='true'") as $row) {
		$cpid = $row["provider_id"];
	}
	foreach($globdb->query("select provider_name from packageproviders where provider_id='$cpid'") as $row) $cpname = $row["provider_name"];

?>
<h2>Packages</h2>
<b>Current Provider</b> - <?php echo $cpname ?><br>

<table border=1><tr valign="top"><td>
<center><h3>Provider Packages</h3></center>
<form method="post" action="index.php?function=manageproviderpackages">
<input type="hidden" name="ppid" value="<?php echo $ppid ?>">
<input type="hidden" name="providerid" value="<?php echo $cpid ?>">
<table>
<tr><th>Available</th><th>Used</th></tr>
<tr><td>
<select name="packages_add[]" size="40" multiple="multiple">
<?php

	// this needs to be replaced with "get package list from provider()" code
	$pkglist = bls_pp_getProviderPackageList($cpid);
	foreach($pkglist as $row) {
		$pkname = $row;
		echo "<option value=\"$pkname\">$pkname</option>";
	}

?>
</select>
</td><td>
<select name="packages_remove[]" size="40" multiple="multiple">
<?php
	$c = 0;
	foreach($projDB->query("select * from packages_used where provider_id='$cpid' order by package_name") as $row) {
		$puid = $row["package_id"];
		$puname = $row["package_name"];
		echo "<option value=\"$puid\">$puname</option>";
		$c++;
	}
	if($c == 0) echo "<option>None Added</option>";
?>
</select>
</td></tr>
<tr><td><input type="submit" name="add" value="Add"></td><td><input type="submit" name="remove" value="Remove"></td></tr>
</table>
</form>
</td>

<td>
<center><h3>Base Packages</h3></center>
<form method="post" action="index.php?function=manageprojectbasepackages">
<input type="hidden" name="ppid" value="<?php echo $ppid ?>">
<input type="hidden" name="providerid" value="<?php echo $cpid ?>">
<table>
<tr><th>Available</th><th>Selected</th></tr>
<tr><td>
<select name="basepackages_add[]" size="8" multiple="multiple">
<?php
	$c = 0;
	foreach($globdb->query("select * from basepackages") as $row) {
		$id = $row["base_id"];
		$name = $row["base_name"];
		echo "<option value=\"$id\">$name</option>";
		$c++;
	}
	if($c==0) echo "<option>Nothing Available</option>";
?>
</select>
</td>
<td>
<select name="basepackages_added[]" size="8" multiple="multiple">
<?php
	$c = 0;
	foreach($projDB->query("select project_base_id,base_id from project_basepkgs") as $row) {
		$id = $row["base_id"];
		$lid = $row["project_base_id"];
		foreach($globdb->query("select base_name from basepackages where base_id='$id'") as $names) {
			$name = $names["base_name"];
		}
		echo "<option value=\"$lid\">$name</option>";
		$c++;
	}
	if($c==0) echo "<option>Nothing Added</option>";	
?>
</select>
</td></tr>
<tr><td><input type="submit" name="add" value="Add"></td><td><input type="submit" name="remove" value="Remove"></td></tr>
</table>
</form>

</td>
<td>



<center><h3>Custom Packages</h3></center>
<form method="post" action="index.php?function=managecustompackages">
<input type="hidden" name="ppid" value="<?php echo $ppid ?>">
<input type="hidden" name="providerid" value="<?php echo $cpid ?>">
<table><tr>
<td>
<input type="hidden" name="ppid" value="<?php echo $ppid ?>">
<input type="hidden" name="providerid" value="<?php echo $cpid ?>">
<?php bls_sf_createSingleFileForm(); ?>
</td><td>
<select name="custpackages_remove[]" size="8" multiple="multiple">
<?php
	$c = 0;
	foreach($projDB->query("select * from project_custom_packages") as $row) {
		$pkgid = $row["custom_id"];
		$pkgname = $row["custom_name"];
		echo "<option value=\"$pkgid\">$pkgname</option>";
		$c++;
	}
	if($c == 0) echo "<option>None Added</option>";
?>
</select><br>
<input type="submit" name="remove" value="Remove">
</td></tr></table>
</form>
</td></tr></table>

<?	
	
	
}


function bls_pm_manageProviderPackages()
{
	bls_ut_backTrace("bls_pm_manageProviderPackages");
	
	$ppid = $_REQUEST["ppid"];
	$providerid = $_REQUEST["providerid"];
	
	$projDB = bls_db_getProjectDB($ppid);
	$globdb = bls_db_getGlobalDB();
	
	
	// fora dding provider packages
	if(isset($_REQUEST["add"])) {
		foreach($_REQUEST["packages_add"] as $pkgs) {
			$projDB->query("delete from packages_used where package_name='$pkgs'");
			$projDB->query("insert into packages_used values (NULL, '$providerid', '$pkgs')");
		}
	}
	
	if(isset($_REQUEST["remove"])) {
		foreach($_REQUEST["packages_remove"] as $pkgs) {
			$projDB->query("delete from packages_used where package_id='$pkgs'");
		}
	}
	
	bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=loadprojectpackagesframe&ppid=$ppid");
}


function bls_pm_manageCustomPackages()
{
	bls_ut_backTrace("bls_pm_manageCustomPackages");
	
	
	$ppid = $_REQUEST["ppid"];
	$providerid = $_REQUEST["providerid"];
	
	$projDB = bls_db_getProjectDB($ppid);
	$globdb = bls_db_getGlobalDB();
	
	
	// fora dding provider packages
	if(isset($_REQUEST["add"])) {
		$cust_add = bls_sf_decodeSingleFileForm();
		$name = $cust_add["name"];
		$addr = serialize($cust_add["param"]);
		$projDB->query("insert into project_custom_packages values (NULL, '$name', '$addr')");
	}
	
	if(isset($_REQUEST["remove"])) {
		foreach($_REQUEST["custpackages_remove"] as $pkgs) {
			$projDB->query("delete from project_custom_packages where custom_id='$pkgs'");
		}
	}
	
	bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=loadprojectpackagesframe&ppid=$ppid");
}

// deh build frame ting
function bls_pm_loadBuildFrame()
{
	bls_ut_backTrace("bls_pm_loadBuildFrame");
	
	$ppid = $_REQUEST["ppid"];
	
	$projDB = bls_db_getProjectDB($ppid);
	
?>
<h2>Builds</h2>
<hr>
<h3>Available Builds</h3>
<table border="1"><tr><th>Build Name</th><th>Description</th><th>CPU's</th><th>Memory</th><th>Control</th></tr>
<?php
	foreach($projDB->query("select * from project_builds") as $row) {
		$name = $row["build_name"];
		$buildid = $row["build_id"];
		$desc = $row["build_desc"];
		$cpus = $row["build_cpus"];
		$memory = $row["build_mem"];
		$urls = "<a href=\"index.php?frame=content&function=deleteprojectbuild&ppid=$ppid&bid=$buildid\">Delete</a> " .
				"<a href=\"index.php?frame=content&function=bootprojectbuild&ppid=$ppid&bid=$buildid\">Boot</a> " .
				"<a href=\"index.php?frame=content&function=publishprojectbuild&ppid=$ppid&bid=$buildid\">Publish</a> " .
				"<a href=\"index.php?frame=content&function=projectbuildlogshow&ppid=$ppid&bid=$buildid\">Log</a>";
		echo "<tr><td>$name</td><td>$desc</td><td>$cpus</td><td>$memory</td><td>$urls</td></tr>";		
	}
?>
</table>
<hr>
<h3>New Build</h3>
<form method="post" action="index.php?frame=content&function=manageprojectbulids&ppid=<?php echo $ppid ?>">
<table>
<tr><td>Build Name</td><td><input type="text" name="build_name"></td></tr>
<tr valign="top"><td>Description</td><td><textarea name="build_desc" cols="80"></textarea></td></tr>
<tr><td>CPU's</td><td><input type="text" name="build_cpus" value="2"></td></tr>
<tr><td>Memory</td><td><input type="text" name="build_mem" value="384"></td></tr>
<tr><td><input type="submit" name="build" value="Build"></td></tr>
</table>
</form>
<hr>
<?php

}

function bls_pm_loadBootFrame()
{
	bls_ut_backTrace("bls_pm_loadBootFrame");
	
	echo "boot";	
}


function bls_pm_manageBasePackages()
{
	bls_ut_backTrace("bls_pm_manageBasePackages");
	
	$ppid = $_REQUEST["ppid"];
	
	$projDB = bls_db_getProjectDB($ppid);

	if(isset($_REQUEST["add"])) foreach($_REQUEST["basepackages_add"] as $valin) {

		$projDB->query("delete from project_basepkgs where base_id='$valin'");
		$projDB->query("insert into project_basepkgs values (NULL, '$valin')");
		
	}	
	
	if(isset($_REQUEST["remove"])) foreach($_REQUEST["basepackages_added"] as $bpaid) {
		$projDB->query("delete from project_basepkgs where project_base_id='$bpaid'");
	}
	
	bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=loadprojectpackagesframe&ppid=$ppid");
	
}

// this function is where builds are called from
function bls_pm_manageBuilds()
{
	bls_ut_backTrace("bls_pm_manageBasePackages");
	global $bls_base_system,$bls_base_work;
	
	$ppid = $_REQUEST["ppid"];
	
	$projDB = bls_db_getProjectDB($ppid);
	
	$bname = $_REQUEST["build_name"];
	$bdesc = $_REQUEST["build_desc"];
	$bcpus = $_REQUEST["build_cpus"];
	$bmem = $_REQUEST["build_mem"];
				"build_name TEXT," .
			"build_desc TEXT," .
			"build_cpus TEXT," .
			"build_mem TEXT)";
	
	$projDB->query("insert into project_builds values (NULL, '$bname', '$bdesc', '$bcpus', '$bmem')");
	$id = $projDB->lastInsertID();
	
	// create the logfile location
	mkdir("$bls_base_work/$ppid/Builds/$id/");	
	$logfile = "$bls_base_work/$ppid/Builds/$id/output.log";
	
	// setup the script and execute it
	$script = "/usr/bin/php $bls_base_system/build.php $ppid $id";
	system("$script > $logfile 2>&1 &");
	
	bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=loadbuildframe&ppid=$ppid");
}


function bls_pm_loadProjectCleanupsFrame()
{
	bls_ut_backTrace("bls_pm_loadProjectCleanupsFrame");
	
	$ppid = $_REQUEST["ppid"];
	
	bls_cu_printProjectCUForm($ppid);
	//bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=loadprojectcleanupsframe&ppid=$ppid"); <-- what was i thinking?
	
}

?>
