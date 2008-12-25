<?php
/*
 * This is the utility library.
 * Simple things that perform little functions (like a standard way of printing time for eg)
 *
 * All functions here will begin with bls_ut_
 */

// the backtrace function - this should be called by ALL functions as their first step
global $bls_back_trace;
function bls_ut_backTrace($funcname)
{
	global $bls_back_trace;
		
	if(isset($bls_back_trace["n"])) {
		$bls_back_trace["n"]++;
		$num = $bls_back_trace["n"];
		$bls_back_trace["$num"] = "$funcname";
	} else {
		$bls_back_trace["n"] = 0;
		$bls_back_trace[0] = "$funcname";
	}
}







// this needs to be done properly TODO
function bls_ut_sanitizePathName($file)
{
	bls_ut_backTrace("bls_ut_sanitizePathName");
	
	$file = str_replace(" ", "", $file);
	$file = str_replace("\t", "", $file);
	$file = str_replace("\n", "", $file);
	$file = str_replace("\r", "", $file);
	$file = str_replace(";", "", $file);
	
	return $file;
}





function bls_ut_time($time=0)
{
	bls_ut_backTrace("bls_ut_time");
	
	if($time == 0) return strftime("%a %d-%b-%Y %H:%M:%S"); 
	else return strftime("%a %d-%b-%Y %H:%M:%S", $time);
	
}





function bls_ut_sizeToString($input)
{
	bls_ut_backTrace("bls_ut_sizeToString");

        if($input > 50000000000) {
                $ret = (int)($input /(1024*1024*1024));
                return "$ret Gb";
        }
        if($input > 50000000) {
                $ret = (int)($input /(1024*1024));
                return "$ret Mb";
        }
        if($input > 50000) {
                $ret = (int)($input /1024);
                return "$ret kb";
        }
        if($input > 50) {
                $ret = (int)$input;
                return "$ret b";
        }
        return $input;
}


?>
