<?php
/*
 * This file deals with cleanups in minifed, both for projects and for global templates
 * 
 * all functions here begin with bls_cu_
 */
 
// our pagebuilder variables and arrays.
global $bls_pb_pre_build_funcs, $bls_pb_titleName;


// a prebuild for pageBuilder functions
$bls_pb_pre_build_funcs["cleanups_prebuild"] = "bls_cu_cleanups_prebuild_function";

function bls_cu_cleanups_prebuild_function()
{
	$globdb = bls_db_getGlobalDB();

	if(isset($_REQUEST["function"])) {
		switch($_REQUEST["function"]) {
			case "globalcleanuppage":
				bls_cu_printCUFormGlobal();
				exit(0);
				break;
			case "projectcleanupsmanage":
				$ppid = $_REQUEST["ppid"];
				$projDB = bls_db_getProjectDB($ppid);
				
				$scope = $_REQUEST["cleanup_scope"];
				$type = $_REQUEST["cleanup_type"];
				$loc = $_REQUEST["cleanup_loc"];
				
				$projDB->query("insert into project_cleanups values (NULL, '$scope', '$type', '$loc')");
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=loadprojectcleanupsframe&ppid=$ppid");

				exit(0);
				break;
			case "addcleanupgroup":
				$name = $_REQUEST["newgroup"];
				$sql = "insert into cleanup_groups values (NULL, '$name')";
				$globdb->query($sql);
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=globalcleanuppage");
				exit(0);
				break;
			case "removecleanupgroup":
				$cid = $_REQUEST["id"];
				$sql = "delete from cleanups where group_id='$cid'";
				$globdb->query($sql);
				$sql = "delete from cleanup_groups where group_id='$cid'";
				$globdb->query($sql);
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=globalcleanuppage");
				exit(0);
				break;	
			case "removesinglecleanupentry":
				$cid = $_REQUEST["id"];
				$sql = "delete from cleanups where cleanup_id='$cid'";
				$globdb->query($sql);
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=globalcleanuppage");
				exit(0);
				break;
			case "copyglobaltoprorjectcleanups":
				$ppid = $_REQUEST["ppid"];
				$gid = $_REQUEST["globalname"];

				$projDB = bls_db_getProjectDB($ppid);
				$globdb = bls_db_getGlobalDB();
			
				foreach($globdb->query("select * from cleanups") as $row) {
					$cscope = $row["cleanup_scope"];
					$ctype = $row["cleanup_type"];
					$cloc = $row["cleanup_location"];
					
					$projDB->query("insert into project_cleanups values (NULL, '$cscope', '$ctype', '$cloc')");
				}
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=loadprojectcleanupsframe&ppid=$ppid");
				exit(0);
				break;
			case "removeprojectcleanup":
				$ppid = $_REQUEST["ppid"];
				$cid = $_REQUEST["cid"];

				$projDB = bls_db_getProjectDB($ppid);
				$projDB->query("delete from project_cleanups where pc_id='$cid'");
				
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=loadprojectcleanupsframe&ppid=$ppid");
				exit(0);
				break;				
			case "managecleanups":
				$gcid = $_REQUEST["gcid"];
				$type = $_REQUEST["cleanup_type"];
				$scope = $_REQUEST["cleanup_scope"];
				$location = $_REQUEST["cleanup_loc"];
				$sql = "insert into cleanups values (NULL, '$gcid', '$type', '$scope', '$location')";
				$globdb->query($sql);
				bls_pb_reloadFrame("fr_content", "index.php?frame=content&function=globalcleanuppage");
				exit(0);
				break;
		}
	}
	
}

function bls_cu_printCUFormGlobal()
{
	bls_ut_backTrace("bls_cu_printCUFormGlobal");
	
	$globdb = bls_db_getGlobalDB();

?>
<h2>Cleanup's</h2><hr>
<h3>Groups</h3>
<h4>Existing Groups</h4>
<table>
<?php
	foreach($globdb->query("select * from cleanup_groups") as $row) {
		$name = $row["group_name"];
		$url = "index.php?function=removecleanupgroup&id=".$row["group_id"];
		echo "<tr><td>$name</td><td><a href=\"$url\">Remove</a></td></tr>";
	}
?>
<table>
<h4>Create New Group</h4>
<form method="post" action="index.php?function=addcleanupgroup">
New Group Name <input type="text" name="newgroup"> <input type="submit" value="add" name="Add">
</form>
<hr>
<h3>Clean Up Entries</h3><hr>
<?php
	foreach($globdb->query("select * from cleanup_groups") as $row) {
		$gname = $row["group_name"];
		$gid = $row["group_id"];
		echo "<h4>$gname</h4>";
		echo "<form method=\"post\" action=\"index.php?function=managecleanups\">";
		echo "<input type=\"hidden\" name=\"gcid\" value=\"$gid\">";
		echo "<table><tr><th>Type</th><th>Scope</th><th>Location</th></tr>";
		foreach($globdb->query("select * from cleanups where group_id='$gid'") as $newrow) {
			$cu_loc = $newrow["cleanup_location"];
			$cu_type = $newrow["cleanup_type"];
			$cu_scope = $newrow["cleanup_scope"];
			$cu_id = $newrow["cleanup_id"];
			$url = "index.php?function=removesinglecleanupentry&id=$cu_id";
			echo "<tr><td>$cu_type</td><td>$cu_scope</td><td>$cu_loc</td><td><a href=\"$url\">Remove</a></td></tr>";
		}		
		echo "<tr><td><select name=\"cleanup_type\"><option value=\"rm\">RM</option><option value=\"find\">find</option></select></td>";
		echo "<td><select name=\"cleanup_scope\"><option value=\"global\">Global</option><option value=\"local\">Local</option></select></td>";
		echo "<td><input type=\"text\" name=\"cleanup_loc\"></td><td><input type=\"submit\" name=\"add\" value=\"Add\"></td></tr>";
		echo "</table>";
		echo "</form><hr>";	
	}
}


function bls_cu_printProjectCUForm($pid)
{
	bls_ut_backTrace("bls_cu_printProjectCUForm");
	
	$projDB = bls_db_getProjectDB($pid);
	$globdb = bls_db_getGlobalDB();
	
	?>
<h2>Cleanup's</h2><hr>
<h3>Copy from Globals</h3>
<form method="post" action="index.php?function=copyglobaltoprorjectcleanups&ppid=<?php echo $pid ?>">
<select name="globalname">
<?php
	$c = 0;
	foreach($globdb->query("select * from cleanup_groups") as $row) {
		$gname = $row["group_name"];
		$gid = $row["group_id"];
		echo "<option value=\"$gid\">$gname</option>";
		$c++;
	}
	if($c == 0) echo "<option>None Available</option>";

?>
</select>
<?php if($c != 0) echo "<input type=\"submit\" name=\"add\" value=\"add\">"; ?>
</form>
<hr>
<h3>Clean Up Entries</h3><hr>
<form method="post" action="index.php?function=projectcleanupsmanage&ppid=<?php echo $pid ?>">
<table>
<tr><th>Type</th><th>Scope</th><th>Location</th><th>Remove</th></tr>
<?php
	foreach($projDB->query("select * from project_cleanups") as $row) {
		$cid = $row["pc_id"];
		$cscope = $row["cleanup_scope"];
		$ctype = $row["cleanup_type"];
		$cloc = $row["cleanup_location"];
		$url = "index.php?ppid=$pid&function=removeprojectcleanup&cid=$cid";
		echo "<tr><td>$ctype</td><td>$cscope</td><td>$cloc</td><td><a href=\"$url\">Remove</a></td></tr>";
	}

	echo "<tr><td><select name=\"cleanup_type\"><option value=\"rm\">RM</option><option value=\"find\">find</option></select></td>";
	echo "<td><select name=\"cleanup_scope\"><option value=\"global\">Global</option><option value=\"local\">Local</option><option value=\"project\">Project</option></select></td>";
	echo "<td><input type=\"text\" name=\"cleanup_loc\"></td><td><input type=\"submit\" name=\"add\" value=\"Add\"></td></tr>";

?>
</table>
</form>

<?php
}

?>
