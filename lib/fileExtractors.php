<?php
/*
 * This file contains all the functions to extract files.
 * 
 * all functions here start with bls_fe_
 */

// this whole file needs to change.. it should ONLY be about extracting files, not getting them also
// this file should also deal with directories


global $file_extractors;

$file_extractors["targz"]["extractor"] = "bls_fe_targzextrator";
$file_extractors["targz"]["typefind"] = "bls_fe_targztypefind";
$file_extractors["rpm"]["extractor"] = "bls_fe_rpmextrator";
$file_extractors["rpm"]["typefind"] = "bls_fe_rpmtypefind";
$file_extractors["tar"]["extractor"] = "bls_fe_tarextrator";
$file_extractors["tar"]["typefind"] = "bls_fe_tartypefind";
$file_extractors["tarbz"]["extractor"] = "bls_fe_tarbzextrator";
$file_extractors["tarbz"]["typefind"] = "bls_fe_tarbztypefind";
$file_extractors["zip"]["extractor"] = "bls_fe_zipextrator";
$file_extractors["zip"]["typefind"] = "bls_fe_ziptypefind";
$file_extractors["deb"]["typefind"] = "bls_fe_debtypefind";
$file_extractors["deb"]["extractor"] = "bls_fe_debextractor";


function bls_fe_extractFile($file, $location)
{
	global $file_extractors;
	
	$location = bls_ut_sanitizePathName($location);
	$file = bls_ut_sanitizePathName($file);
	
	//mw_statLog("green", "called into fe_extract with $file, $location, $type");
	
	/*
	switch($type) {
		case "file":
		case "fs":
			// we do nothing
			break;
		case "svn":
			// we need to download the stuff 
			// svn downloads a file structure.
			if($user != "") $user_f = "--username $user";
			else $user_f = "";
			if($pass != "") $pass_f = "--password $pass";
			else $pass_f = "";
			
			$cmd = "cd $location;svn co $user_f $pass_f $file . < /dev/null > /tmp/svnlog 2>&1";
			system($cmd);
			// yeah, thats gunna work :|
			return 0;
			break;
		case "url":
		case "http":
		case "ftp":
			// we download the files, den we extract da files
			$bname = basename($file);
			$tmplocfn = "/tmp/.mfw.".rand()."-extra-$bname";
			//$cmd = "mkdir $tmpdir; wget -nd -$location";
			
			// this is gunna work SOOO well
			$fp = fopen($tmplocfn, "w");
			
			$ch = "";
			
			$mycurl = curl_init($file);
			curl_setopt($mycurl, CURLOPT_FILE, $fp);
			curl_setopt($mycurl, CURLOPT_HEADER, 0);
			if($user != "" && $pass != "") curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
			curl_exec($mycurl);
			curl_close($mycurl);
			fclose($fp);
			fe_extractFile($tmplocfn, $location);
			unlink($tmplocfn);

			return 0;
			break;
	}
	*/
	foreach($file_extractors as $key => $val) {
		echo "testing $key, ".$val["extractor"].", ".$val["typefind"]." on $file\n";
		if($val["typefind"]($file)) {
			echo "Calling extracor for ".$key."\n";
			return $val["extractor"]($file, $location);
		}
	}

	return false;
}

function bls_fe_debextractor($file, $location)
{
	// the very kewl thing about deb packages is they are 3 files inside an ar archive.
	// deb files can either have data.tar.gz or data.tar.bz2 - and there are others too
	// lzma??? god, thats what we need in the age of infinite disk, another compression
	// algorithm. Fair's fair i guess, there are places where high compression are still
	// very important these days, though i would have though the debian packages are
	// the last place they'd be required.
	$cmd = "ar tf $file |grep data.tar";
	$output = rtrim(`$cmd`);
	
	switch($output) {
		case "data.tar.gz":
			$cmd = "cd $location; ar pf $file data.tar.gz |tar xfz -";
			break;
		case "data.tar.bz2":
			$cmd = "cd $location; ar pf $file data.tar.bz2 |tar xfj -";
			break;
		case "data.tar.lzma":
			$cmd = "cd $location; ar pf $file data.tar.lzma	 |lzcat |tar xf -";
			break;
	}
	
	echo "extractor: $location extraction for $file with $cmd\n";
	
	$retval = 0;

	system($cmd, $retval);

	return $retval;	
}

function bls_fe_targzextrator($file, $location)
{
	$cmd = "cd $location; tar xfz $file";

	echo "extractor: $location extraction for $file with $cmd\n";
	
	$retval = 0;

	system($cmd, $retval);

	return $retval;
}

function bls_fe_rpmextrator($file, $location)
{
	$cmd = "cd $location; rpm2cpio $file |cpio -idu";

	echo "extractor: $location extraction for $file with $cmd\n";
	
	$retval = 0;

	system($cmd, $retval);

	return $retval;
}

function bls_fe_tarextrator($file, $location)
{
	$cmd = "cd $location; tar xf $file";

	echo "extractor: $location extraction for $file with $cmd\n";
	
	$retval = 0;

	system($cmd, $retval);

	return $retval;
}

function bls_fe_tarbzextrator($file, $location)
{
	$cmd = "cd $location; tar xfj $file";

	echo "extractor: $location extraction for $file with $cmd\n";
	
	$retval = 0;

	system($cmd, $retval);

	return $retval;
}

function bls_fe_zipextrator($file, $location)
{
	$cmd = "cd $location; unzip $file";

	echo "extractor: $location extraction for $file with $cmd\n";
	
	$retval = 0;

	system($cmd, $retval);

	return $retval;
}

function bls_fe_debtypefind($file)
{
	if(file_exists($file)) {
		if(preg_match('/.*.deb$/i', $file)>0) return true;
	} else return false;
	return false;
}

function bls_fe_targztypefind($file)
{
	if(file_exists($file)) {
		if(preg_match('/.*.tar.gz$/i', $file)>0) return true;
		if(preg_match('/.*.tgz$/i', $file)>0) return true;
	} else return false;
	return false;
}

function bls_fe_rpmtypefind($file)
{
	if(file_exists($file)) {
		if(preg_match('/.*.rpm$/i', $file)>0) return true;
	} else return false;
	return false;
}

function bls_fe_tartypefind($file)
{
	if(file_exists($file)) {
		if(preg_match('/.*.tar$/i', $file)>0) return true;
	} else return false;
	return false;


}

function bls_fe_tarbztypefind($file)
{
	if(file_exists($file)) {
		if(preg_match('/.*.tar.bz2$/i', $file)>0) return true;
		if(preg_match('/.*.tbz$/i', $file)>0) return true;
	} else return false;
	return false;
}

function bls_fe_ziptypefind($file)
{
	if(file_exists($file)) {
		if(preg_match('/.*.zip$/i', $file)>0) return true;
	} else return false;
	return false;
}

?>