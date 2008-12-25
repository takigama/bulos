<?php
/*
 * Created on Dec 7, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 *
 */
 
include_once("../www/config.php");

if(isset($argv[1]) && isset($argv[2])) {
	$globdb = bls_db_getGlobalDB();
	$type = $argv[1];
	$id = $argv[2];
	
	if($type == "p") {
		// type of update is provider
		foreach($globdb->query("select provider_name from packageproviders where provider_id='$id'") as $row) {
			$name = $row["provider_name"];
		}
		bls_sf_log(4, "refreshsource", "Provider update beginning for $name");
		foreach ($globdb->query("select source_id,source_description from packagesources where provider_id='$id'") as $priv) {
			$sname = $priv["source_description"];
			bls_sf_log(4, "refreshsource", "Source update beginning for $sname in $name");
			bls_ps_refreshSourcePackageList($priv["source_id"]);
		}
		
	}
	
	if($type == "s") {

		foreach ($globdb->query("select a.provider_name,b.source_description from packagesources b,packageprovider a where b.source_id='$pid' and a.provider_id=b.provider_id") as $priv) {
			$sname = $priv["source_description"];
			$pname = $priv["privoder_name"];
		}

		bls_sf_log(4, "refreshsource", "Source update beginning for $sname in $pname");
		// type of update is source
		bls_ps_refreshSourcePackageList($id);
	}
	
	bls_sf_log(4, "refreshsource", "Update complete");
}

?>
