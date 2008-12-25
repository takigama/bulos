<?php
/*
 * 
 * I was trying to avoid the use of "packageSources" as a name
 * simply because its the name i used prior to the re-write.
 * 
 * the package sources code will define how to retrieve packages
 * all functions here start with bls_ps_ 
 * 
 */
 
// for package sources we define a global array
// that just defines the package source types we can have
// it expects to find $mfw..["type"] = "Name" where "type" defines
// a functional prefix (i.e. bls_ps_"type"_getList and so on) and "Name"
// defines how its describes in web pages and so on.
// functions defined are:
// - initSource
// - getPackageList
// - getPackage
global $bls_ps_source;
$bls_ps_source["rpmfile"]["name"] = "RPM Files";
$bls_ps_source["rpmfile"]["description"] = "A package source that can read debian package format";
$bls_ps_source["rpmfile"]["param"]["fslocation"] = "Location";

$bls_ps_source["debfile"]["name"] = "DEB Files";
$bls_ps_source["debfile"]["description"] = "A package source that can read debian package format";
$bls_ps_source["debfile"]["param"]["fslocation"] = "Location";

$bls_ps_source["swfile"]["name"] = "Slackware Files (undefined)";
$bls_ps_source["swfile"]["param"]["fslocation"] = "Location";

$bls_ps_source["redhat"]["name"] = "RedHat/Fedora/Centos Style Repo";
$bls_ps_source["redhat"]["description"] = "A package source that reads redhat style repos, add urls for either mirror list of base url (not both)";
$bls_ps_source["redhat"]["param"]["mirrorlist"] = "Mirrorlist";
$bls_ps_source["redhat"]["param"]["repository"] = "Base URL";
$bls_ps_source["redhat"]["param"]["mirrorlistupdates"] = "Mirrorlist (updates)";
$bls_ps_source["redhat"]["param"]["repositoryupdates"] = "Base URL (updates)";

$bls_ps_source["ubuntu"]["name"] = "Ubuntu/Debian (undefined)";

$bls_ps_source["web"]["name"] = "A HTTP Location (undefined)";

// for now we'll just define our file type

// this function will be used to refresh a package source.
function bls_ps_refreshSourcePackageList($sid)
{
	//error_log("woudl refresh $sid");
	
	$globdb = bls_db_getGlobalDB();

	// now we figure out the function name for refreshing the list	
	foreach($globdb->query("select * from packagesources where source_id='$sid'") as $row) {
		$type = $row["source_type"];
		$func = "bls_ps_$type"."_refreshSourceList";
		error_log("calling $func");
	}
	
	$func($sid);
	
	return;
}

/*
 * Fedora/redhat repo definitions begin here
 */
 
/*function bls_ps_redhat_xmlParserHandler($xmlparser, $data)
{
	bls_ut_backTrace("bls_ps_redhat_xmlParserHandler");
	global $bls_ps_redhat_xmlpartser_lasthandler;
	
	error_log("data from parser: $data");
}*/
 
function bls_ps_redhat_refreshSourceList($sid)
{
	bls_ut_backTrace("bls_ps_redhat_refreshSourceList");
	global $bls_base_cache;

	$globdb = bls_db_getGlobalDB();
	
	// get our cache directory
	$cache_dir = "$bls_base_cache/rpmrepo-$sid";
	
	if(!file_exists($cache_dir)) mkdir($cache_dir, 0755, true);
	if(!file_exists($cache_dir."/main/")) mkdir($cache_dir."/main/", 0755, true);
	if(!file_exists($cache_dir."/updates/")) mkdir($cache_dir."/updates/", 0755, true);

	// get the source address
	foreach($globdb->query("select * from packagesources where source_id='$sid'") as $row) {
		$addr = unserialize($row["source_address"]);
		$pid = $row["provider_id"];
	}
	
	// clear out the list
	$globdb->query("delete from packages where source_id='$sid'");
	
	$murl = "";
	$burl = "";
	if(isset($addr["mirrorlist"])) $murl = $addr["mirrorlist"];
	if(isset($addr["repository"])) $burl = $addr["repository"];
	$repotype = false;
	
	if($murl != "") {
		$url = $murl;
		$repotype = "m";
	} 
	if($burl != "") {
		$url = $burl;
		$repotype = "b";
	}
	if($repotype) error_log("would pursue $url");
	else error_log("no url, must bail");
	
	/*$mycurl = curl_init($url);
	curl_setopt($mycurl, CURLOPT_HEADER, 0);
	curl_setopt($mycurl, CURLOPT_RETURNTRANSFER, true);
	$page = curl_exec($mycurl);
	$err = curl_error($mycurl);
	if($page == false) error_log("got negurp, $err");
	curl_close($mycurl);*/

	// screw curl, maybe this will work?
	// only do this for mirror lists
	if($repotype == "m") {
		$fp = fopen($url, "rb");
		$c = 0;
		while(!feof($fp)) {
			$t = fgets($fp, 8192);
			if($t != false) {
				if(preg_match("/^http:\/\//", $t) > 0) {
					$mirrors[$c] = rtrim($t);
					$c++;	
				}
			}
			else break;	
		}
		fclose($fp);
	} else {
		// for base url encoding, we just send straight to mirrors the actual data
		error_log("have base url for encoding on type base for $url, $repotype");
		$mirrors[0] = "$url";
	}
	
	//exit(0);
	// we were called in here on a refresh, so no matter what, we get the primary sqlite db.
	$gotprimary = false;
	$c = 0;
	
	
	// before we get it... plunk off the old files
	// we need to get the repomd.xml file first.
	
	
	// OH crap, i've got this wrong, fedora's repos got a new key and so its all a bit painfull now.
	//if(file_exists("$cache_dir/primary.sqlite.bz2")) unlink("$cache_dir/primary.sqlite.bz2");
	//if(file_exists("$cache_dir/primary.sqlite")) unlink("$cache_dir/primary.sqlite");
	
	// flip thru the mirrors until we get repomd.xml.
	while(!$gotprimary)	{
		$fp = fopen($mirrors[$c]."/repodata/repomd.xml", "rb");
		if($fp != false) {
			$gotprimary = true;
			$fpout = fopen("$cache_dir/main/repomd.xml", "wb");
			while(!feof($fp)) {
				fwrite($fpout, fread($fp, 8192));
			}
			$workingmirror = $mirrors[$c];
			
			
			// that could go on forever.
			if(count($mirrors) < $c) return false;
		} else {
			if(count($mirrors) < $c) return false;
			$c++;	
		}
	}
	fclose($fp);
	
	$getprimarysql = true;
	
	if(file_exists("$cache_dir/main/primary.sqlite")) {
		// if we have a primary.sqlite file, we check its mod time	
		$xml = simplexml_load_file("$cache_dir/main/repomd.xml");
		foreach($xml->data as $key => $val) {
			if($val->location["href"] == "repodata/primary.sqlite.bz2") {
				$mylong = "".$val->timestamp; // <-- a great example of one of the many reasons OO php is rediculous
				if($mylong < filectime("$cache_dir/main/primary.sqlite")) {
					$getprimarysql = false;
				}
			}
		}
	}
	
	// TODO, we need ALOT of error catching in this script, php has some
	// serisouly odd networking bugs
	$fp = fopen("$workingmirror"."/repodata/primary.sqlite.bz2", "rb");
	$fpout = fopen("$cache_dir/main/primary.sqlite.bz2", "wb");
	while(!feof($fp)) {
		fwrite($fpout, fread($fp, 8192));
	}
	
	
	// now get the primary.sql
	
	// deal with the primary db
	system("rm -f $cache_dir/main/primary.sqlite; bunzip2 $cache_dir/main/primary.sqlite.bz2");
	
	
	/*
	 * UPDATES DB STARTS HERE
	 */
	// ok, now we get the updates db
	$upmurl = "";
	$upburl = "";
	if(isset($addr["mirrorlistupdates"])) $upmurl = $addr["mirrorlistupdates"];
	if(isset($addr["repositoryupdates"])) $upburl = $addr["repositoryupdates"];
	$repotype = false;
	
	if($upmurl != "") {
		$upurl = $upmurl;
		$uprepotype = "m";
	} 
	if($upburl != "") {
		$upurl = $upburl;
		$uprepotype = "b";
	}
	if($uprepotype) error_log("would pursue $upurl");
	else error_log("no url, must bail");
	
	/*$mycurl = curl_init($url);
	curl_setopt($mycurl, CURLOPT_HEADER, 0);
	curl_setopt($mycurl, CURLOPT_RETURNTRANSFER, true);
	$page = curl_exec($mycurl);
	$err = curl_error($mycurl);
	if($page == false) error_log("got negurp, $err");
	curl_close($mycurl);*/

	// screw curl, maybe this will work?
	// only do this for mirror lists
	if($uprepotype == "m") {
		$fp = fopen($upurl, "rb");
		$c = 0;
		while(!feof($fp)) {
			$t = fgets($fp, 8192);
			if($t != false) {
				if(preg_match("/^http:\/\//", $t) > 0) {
					$upmirrors[$c] = rtrim($t);
					$c++;	
				}
			}
			else break;	
		}
		fclose($fp);
	} else {
		// for base url encoding, we just send straight to mirrors the actual data
		error_log("have base url for encoding on type base for $upurl, $uprepotype");
		$upmirrors[0] = "$upurl";
	}
	
	//exit(0);
	// we were called in here on a refresh, so no matter what, we get the primary sqlite db.
	$upgotprimary = false;
	$c = 0;
	
	
	// before we get it... plunk off the old files
	// we need to get the repomd.xml file first.
	
	
	// OH crap, i've got this wrong, fedora's repos got a new key and so its all a bit painfull now.
	//if(file_exists("$cache_dir/primary.sqlite.bz2")) unlink("$cache_dir/primary.sqlite.bz2");
	//if(file_exists("$cache_dir/primary.sqlite")) unlink("$cache_dir/primary.sqlite");
	
	// flip thru the mirrors until we get repomd.xml.
	while(!$upgotprimary)	{
		$fp = fopen($upmirrors[$c]."/repodata/repomd.xml", "rb");
		if($fp != false) {
			$upgotprimary = true;
			$fpout = fopen("$cache_dir/updates/repomd.xml", "wb");
			while(!feof($fp)) {
				fwrite($fpout, fread($fp, 8192));
			}
			$upworkingmirror = $upmirrors[$c];
			
			
			// that could go on forever.
			if(count($upmirrors) < $c) return false;
		} else {
			if(count($upmirrors) < $c) return false;
			$c++;	
		}
	}
	fclose($fp);
	
	$upgetprimarysql = true;
	
	if(file_exists("$cache_dir/updates/primary.sqlite")) {
		// if we have a primary.sqlite file, we check its mod time	
		$xml = simplexml_load_file("$cache_dir/updates/repomd.xml");
		foreach($xml->data as $key => $val) {
			if($val->location["href"] == "repodata/primary.sqlite.bz2") {
				$mylong = "".$val->timestamp; // <-- a great example of one of the many reasons OO php is rediculous
				if($mylong < filectime("$cache_dir/updates/primary.sqlite")) {
					$getprimarysql = false;
				}
			}
		}
	}
	
	// TODO, we need ALOT of error catching in this script, php has some
	// serisouly odd networking bugs
	$fp = fopen("$workingmirror"."/repodata/primary.sqlite.bz2", "rb");
	$fpout = fopen("$cache_dir/updates/primary.sqlite.bz2", "wb");
	while(!feof($fp)) {
		fwrite($fpout, fread($fp, 8192));
	}
	
	
	// now get the primary.sql
	
	// deal with the primary db
	system("rm -f $cache_dir/updates/primary.sqlite; bunzip2 $cache_dir/updates/primary.sqlite.bz2");
	
	/*
	 * End updates download bit
	 */
		
	// and thats all we do. Oh wait, no, its not we need to then pump the crap in that db to the global db for this sid
	
	try {
		$rhdbobject = new PDO("sqlite:$cache_dir/main/primary.sqlite");
	} catch(PDOException $exep) {
		error_log("failure while trying to read sqlite primary db");
		return false;
	}

	try {
		$uprhdbobject = new PDO("sqlite:$cache_dir/main/primary.sqlite");
	} catch(PDOException $exep) {
		error_log("failure while trying to read sqlite primary db");
		return false;
	}
	
	// now the question is, do we set the mirror name directly into the full addr, or do we put that somewhere else?
	// error_log("back in the pants for the full tank crunker"); <-- if only eminem would use words like crunker?
	foreach($rhdbobject->query("select name, version, location_href, release from packages") as $row) {
		$name = $row["name"];
		$vers = $row["version"];
		$rels = $row["release"];
		$fulladdr = $row["location_href"];
		$globdb->query("insert into packages values (NULL, '$pid', '$sid', '$vers-$rels', '$name', '$fulladdr')");
	}

	// now we pump out the crapola
	foreach($uprhdbobject->query("select name, version, location_href, release from packages") as $row) {
		$name = $row["name"];
		$vers = $row["version"];
		$rels = $row["release"];
		$fulladdr = $row["location_href"];
		$globdb->query("delete from packages where package_name='$name'");
		$globdb->query("insert into packages values (NULL, '$pid', '$sid', '$vers-$rels', '$name', '$fulladdr')");
	}
	
	
	return true;
} 


function bls_ps_redhat_getPackage($sid, $package_name)
{
	bls_ut_backTrace("bls_ps_redhat_refreshSourceList");
	global $bls_base_cache;

	$globdb = bls_db_getGlobalDB();
	
	// get our cache directory
	$cache_dir = "$bls_base_cache/rpmrepo-$sid";
	$pkg_cache_dir = "$bls_base_cache/rpmrepo-$sid/Packages/";
	
	$refresh = false;
	if(!file_exists($cache_dir)) {
		mkdir($cache_dir, 0755, true);
		$refresh=true;
	}
	if(!file_exists($pkg_cache_dir)) {
		mkdir($pkg_cache_dir, 0755, true);
		$refresh = true;
	}
	if(!file_exists("$cache_dir/primary.sqlite")) $refresh = true;
	if($refresh) bls_ps_redhat_refreshSourceList($sid);
	
	// first, lets make sure we have a package called "package_name"
	$n = 0;
	foreach($globdb->query("select * from packages where package_name='$package_name' and source_id='$sid'") as $row) {
		$pkgaddr = $row["package_full_addr"];
		
		$n++;
	}
	if($n==0) return false;
	
	// now lets check if it already exsts in the cache dir, if so return it..
	if(file_exists("$pkg_cache_dir/$package_name.rpm")) return "$pkg_cache_dir/$package_name.rpm";

	// get the source address
	foreach($globdb->query("select * from packagesources where source_id='$sid'") as $row) {
		$addr = unserialize($row["source_address"]);
		$pid = $row["provider_id"];
	}
	
	$url = $addr["mainurl"];
	$fp = fopen($url, "rb");
	$c = 0;
	while(!feof($fp)) {
		$t = fgets($fp, 8192);
		if($t != false) {
			if(preg_match("/^http:\/\//", $t) > 0) {
				$mirrors[$c] = rtrim($t);
				$c++;	
			}
		}
		else break;	
	}
	fclose($fp);
	
	echo "got mirrors:\n";
	foreach($mirrors as $cm) echo "    $cm\n";

	// we were called in here on a refresh, so no matter what, we get the primary sqlite db.
	$gotprimary = false;
	$c = 0;
	
	// flip thru the mirrors until we get primary.sqlite.
	while(!$gotprimary)	{
		$fp = fopen($mirrors[$c]."/".$pkgaddr, "rb");
		if($fp != false) {
			$gotprimary = true;
			$fpout = fopen("$pkg_cache_dir/$package_name.rpm", "wb");
			while(!feof($fp)) {
				fwrite($fpout, fread($fp, 8192));
			}
			$workingmirror = $mirrors[$c];
		}
		if($c > count($mirrors)) return false;
		$c++;
	}
	
	return "$pkg_cache_dir/$package_name.rpm";
}

/*
 * Fedora/rehat repo definitions end here
 */

/*
 * Debian file definitions start here
 */
function bls_ps_debfile_refreshSourceList($sid)
{
	bls_ut_backTrace("bls_ps_rpmfile_refreshSourceList");

	$globdb = bls_db_getGlobalDB();

	// first we get the package address and provider id	
	foreach($globdb->query("select * from packagesources where source_id='$sid'") as $row) {
		$addr = unserialize($row["source_address"]);
		$pid = $row["provider_id"];
	}
	
	// clear out the list
	$globdb->query("delete from packages where source_id='$sid'");
	
	$real_addr = $addr["fslocation"];
	$cmd = "find $real_addr -name '*.deb' -type f";
	$pkgpipe = popen($cmd, 'r');
	
	while(!feof($pkgpipe)) {
		$line = rtrim(fgets($pkgpipe));
		//$getpackage = "ar pf $line control.tar.gz|tar xfz - '*control' -O |grep '^[PV][ea][rc][sk][ia][og][ne]'";
		
		
		// just to make sure we dont get the end of something
		if($line != -1 && $line != "" && !feof($pkgpipe)) {
			$getpackage = "ar pf $line control.tar.gz|tar xfz - '*control' -O |egrep '^Package|^Version'";
			$pkginfopipe = popen($getpackage, 'r');
			
			// this is probably not the best way of pull the info out, but it'll do for now
			while(!feof($pkginfopipe)) {
				$pinfoline = rtrim(fgets($pkginfopipe));
				if(preg_match("/^Version:/", $pinfoline) > 0) {
					$vers = preg_replace("/^Version: /", "", $pinfoline);
				} 
				if(preg_match("/^Package:/", $pinfoline) > 0) {
					$name = preg_replace("/^Package: /", "", $pinfoline);
				}
				
			}
			
			$full_addr = $line;
			
			//error_log("package: $line in file for $name");
	
			$sql = "insert into packages values(NULL, '$pid', '$sid', '$vers', '$name', '$full_addr')";
			$globdb->query($sql);
		}
	}
	
	return true;
}

function bls_ps_debfile_getPackage($sid, $package_name)
{
	bls_ut_backTrace("bls_ps_debfile_getPackage");
	
	// here we actually get the package we want.

	$globdb = bls_db_getGlobalDB();

	// for rpm files, package_addr is all we need
	foreach($globdb->query("select package_full_addr from packages where package_name='$package_name' and source_id='$sid'") as $row) {
		$paddr = $row[0];
	}
	
	// nice and simple. For FS packages, if this function has access, so does the builder
	// as they run on the same user, but that may not always be the case. Then you'd have
	// to copy the file somewhere globally accessible
	return $paddr;
}

/*
 * DEB file definitions end here
 */


/*
 * RPM file definitions start here.
 */

// this function refreshes package source lists
function bls_ps_rpmfile_refreshSourceList($sid)
{
	bls_ut_backTrace("bls_ps_rpmfile_refreshSourceList");

	$globdb = bls_db_getGlobalDB();

	// first we get the package address and provider id	
	foreach($globdb->query("select * from packagesources where source_id='$sid'") as $row) {
		$addr = unserialize($row["source_address"]);
		$pid = $row["provider_id"];
	}
	
	$real_addr = $addr["fslocation"];
	
	// now we open 
	if(!$dirhand = opendir($real_addr)) return "cannot open file system where rpm's are stored'";
	
	
	// loop over the FS and use rpm to query the file system for the info from the rpm's
	while(($fname = readdir($dirhand)) != false) {
		if(preg_match("/.*\.rpm$/", $fname)) {
			//error_log("executing on $real_addr/$fname");
			$system = "/bin/rpm -q -p \"$real_addr/$fname\" --qf '%{NAME},%{VERSION}'";
				
			$output = shell_exec($system);
			
			$names = explode(",",$output);
			$name = $names[0];
			$vers = $names[1];
			$full_addr = "$real_addr/$fname";
			//error_log("for package: $fname got $output");
			$sql = "insert into packages values(NULL, '$pid', '$sid', '$vers', '$name', '$full_addr')";
			//error_log("sql: $sql");
			$globdb->query($sql);
		}
	}
	
	return true;
}


function bls_ps_rpmfile_getPackage($sid, $package_name)
{
	bls_ut_backTrace("bls_ps_rpmfile_getPackage");
	
	// here we actually get the package we want.

	$globdb = bls_db_getGlobalDB();

	// for rpm files, package_addr is all we need
	foreach($globdb->query("select package_full_addr from packages where package_name='$package_name' and source_id='$sid'") as $row) {
		$paddr = $row[0];
	}
	
	// nice and simple. For FS packages, if this function has access, so does the builder
	// as they run on the same user, but that may not always be the case. Then you'd have
	// to copy the file somewhere globally accessible
	return $paddr;
}




/*
 * RPM File defs end here
 */



// this function servs as the "create package provider source dialog" form
function bls_ps_createSourceDialog()
{
	bls_ut_backTrace("bls_ps_createSourceDialog");
	global $bls_ps_source;
	
	$globdb = bls_db_getGlobalDB();
	$ppid = $_REQUEST["pp"];

?>
<form method="post" action="index.php?function=create_source_addr">
<h2>Create Source</h2>
<table>
<input type="hidden" name="ppid" value="<?php echo $ppid ?>">
<tr><th>Description</th><td><textarea name="cs_desc"></textarea></td></tr>
<tr><th>Type</th><td>
<select name="cs_type">
<?php
	foreach($bls_ps_source as $key => $var) {
		$name = $var["name"];
		echo "<option value=\"$key\">$name</option>";
	}
?>
</select>
</td></tr>
<tr><td><input type="submit" name="Next" value="Next"></td></tr>
</table>
</form>

<?php	
}

function bls_ps_createSourceDialogAddress()
{
	bls_ut_backTrace("bls_ps_createSourceDialogAddress");
	global $bls_ps_source;

	$globdb = bls_db_getGlobalDB();
	$ppid = $_REQUEST["ppid"];
	$cst = $_REQUEST["cs_type"];
	$desc = $_REQUEST["cs_desc"];

	// in this function we need to read the type specified and create a form for it... oh god, the pain.
?>
<form method="post" action="index.php?function=create_source_final">
<h2>Address Details for New Source</h2>
<input type="hidden" name="cs_type" value="<?php echo $cst ?>">
<input type="hidden" name="ppid" value="<?php echo $ppid ?>">
<input type="hidden" name="cs_desc" value="<?php echo $desc ?>">

<table>
<?php
	if(isset($bls_ps_source["$cst"]["description"])) echo "<tr><th>Description</th><td>".$bls_ps_source["$cst"]["description"]."</th></tr>";
	foreach($bls_ps_source["$cst"]["param"] as $key => $var) {
		echo "<tr><td>$var</td><td><input type=\"text\" name=\"source_$key\" size=\"70\"></td></tr>";
	}
?>
<tr><td><input type="submit" name="Create" value="Create"></td></tr>
</table>
</form>
<?php	
}


function bls_ps_createSourceFinal()
{
	bls_ut_backTrace("bls_ps_createSourceDialogAddress");
	global $bls_ps_source;

	$globdb = bls_db_getGlobalDB();
	$ppid = $_REQUEST["ppid"];
	$desc = $_REQUEST["cs_desc"];
	$cst = $_REQUEST["cs_type"];
	foreach($bls_ps_source["$cst"]["param"] as $key => $var) {
		$addr[$key] = $_REQUEST["source_$key"];
	}
	$addr_real = serialize($addr);
	
	$globdb->query("insert into packagesources values (NULL, '$ppid', '$desc', '$cst', '$addr_real', 'true')");
}




function bls_ps_extractFile($file, $location)
{
	// need to define this here
	
}



?>
