<?php
/*
 * This file will deal with how people login to the site.
 * 
 * All functions here start with bls_au_
 */
 

// if you write a plugin that provides a different auth method then you need to set
// $bls_au_alt_auth_method to something in order to stop this setup function being called 
// Also, in the config.php you can set "$bls_au_alt_upstream_auth" for authentication that
// occurs in the web server
if(!isset($bls_au_alt_auth_method) && !isset($bls_au_alt_upstream_auth)) $bls_sf_setup_functions["authentication"] = "bls_au_setup_authentication";


// this function sets up the auth database
function bls_au_setup_authentication()
{
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_globaldb, $bls_current_user;
	
	try {
		$dbobject = new PDO("sqlite:$bls_base_work/auth.db");
	} catch(PDOException $exep) {
		return;
	}

	$dbobject->query("create table authentication (auth_id INTEGER PRIMARY KEY AUTOINCREMENT, auth_login TEXT, auth_pass TEXT, auth_name TEXT)");
	
	$pass = md5("password");
	$dbobject->query("insert into authentication values (0, 'admin', '$pass', 'default admin user')");
}
 
// for now this will do
function bls_au_isAuthed()
{
	global $bls_au_username, $bls_au_alt_upstream_auth, $bls_pb_tabs;
	bls_ut_backTrace("bls_au_isAuthed");
	
	session_start();
	
	$logged = false;
	if(isset($bls_au_alt_upstream_auth)) {
		$bls_au_username = $_SERVER["REMOTE_USER"];
		$bls_pb_tabs["Logout ($bls_au_username)"] = "index.php?function=auth_logout";		
		$logged =  true;
	}
	if(isset($_SESSION["username"])) {
		$bls_au_username = $_SESSION["username"];
		$bls_pb_tabs["Logout ($bls_au_username)"] = "index.php?function=auth_logout";		
		$logged =  true;
	}
	
	
	return $logged;
}

// a function to destroy and logout a user
// php has a crappy way of dealing with sessions really
function bls_au_logoutUser()
{
	session_start();
	
	error_log("trying to unset session");
	
	if(isset($_SESSION["username"])) unset($_SESSION["username"]);
	$_SESSION = array();
	
	session_destroy();
	
	echo "<script>top.location='index.php'</script>";
}

// at the moment, the only way to do this is to use the internal db... but auth is segregated so 
// we can re-code at whim
function bls_au_checkLogin($user, $pass)
{
	global $bls_base_plugins, $bls_base_work, $bls_base_buildarea, $bls_base_sharedspace, $bls_base_web, $boot_port, $max_boot, $bls_base_lib, $bls_base_cache, $bls_base_globaldb, $bls_current_user;
	
	try {
		$dbobject = new PDO("sqlite:$bls_base_work/auth.db");
	} catch(PDOException $exep) {
		return;
	}

	foreach($dbobject->query("select auth_pass from authentication where auth_login='$user'") as $row) {
		$dbpass = $row["auth_pass"];
		$mdpass = md5($pass);
		error_log("checking $user for $pass and $mdpass for $dbpass");
		if($mdpass == $dbpass) return true;
	}	
	
	return false;
}

// print the auth dialog
function bls_au_authDialog()
{
	global $bls_au_username, $bls_au_alt_upstream_auth;

	if(isset($_REQUEST["login"])) {
		$user = $_REQUEST["user"];
		$pass = $_REQUEST["pass"];
		if(bls_au_checkLogin($user, $pass)) {
			$_SESSION["username"] = $user;
			header("Location: index.php");
			return;
		}
	}
	?>
<form method="post" action="index.php?login"
<table>
<tr><td>Username</td><td><input type="text" name="user"></td></tr>
<tr><td>Password</td><td><input type="password" name="pass"></td></tr>
<tr><td><input type="submit" name="Login" value="Login"></td></tr>
</table>
	<?php
}
?>
