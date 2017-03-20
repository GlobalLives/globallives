<?php
// dumped the utility functions into it's own separate file
// I am trying to keep the plugin foorprint down as low as possible

// rename each function with an _l and then call after a load

function kpg_append_file($filename,&$content) {
	// this writes content to a file in the uploads director in the 'stop-spammer-registrations' directory
	// changed to write to the current directory - content_dir is a bad place
	$file=KPG_SS_PLUGIN_DATA.$filename;
	$f=@fopen($file,'a');
	if (!$f) return false;
	fwrite($f,$content);
	fclose($f);
	@chmod($file,0640); // read/write for owner and owners groups.
	return true;
}
function kpg_read_file($f,$method='GET') {
		// try this using Wp_Http
		if( !class_exists( 'WP_Http' ) )
		include_once( ABSPATH . WPINC. '/class-http.php' );
		$request = new WP_Http;
		$parms=array();
		$parms['timeout']=10; // bump timeout a little we are timing out in google
		$parms['method']=$method;
		$result = $request->request( $f ,$parms);
		// see if there is anything there
		if (empty($result)) return '';
		
		if (is_array($result)) {
			$ansa=$result['body']; 
			return $ansa;
		}
		if (is_object($result) ) {
			$ansa='ERR: '.$result->get_error_message();
			return $ansa; // return $ansa when debugging
			//return '';
		}
		return '';
}
function kpg_read_filex($filename) {
	// read file
	$file=KPG_SS_PLUGIN_DATA.$filename;
	if (file_exists($file)) {
		return file_get_contents($file);
	}
	return "File not found";
}

function kpg_file_exists($filename) {
	$file=KPG_SS_PLUGIN_DATA.$filename;
	if (!file_exists($file)) return false;
	return filesize($file);
}
function kpg_file_delete($filename) {
	$file=KPG_SS_PLUGIN_DATA.$filename;
	return @unlink($file);
}
// debug functions
// change the debug=false to debug=true to start debugging.
// the plugin will drop a file sfs_debug_output.txt in the current directory (root, wp-admin, or network) 
// directory must be writeable or plugin will crash.

function sfs_errorsonoff($old=null) {
	$debug=true;  // change to true to debug, false to stop all debugging.
	if (!$debug) return;
	if (empty($old)) return set_error_handler("sfs_ErrorHandler");
	restore_error_handler();
}
function sfs_debug_msg($msg) {
	// used to aid debugging. Adds to debug file
	$debug=true;
    $ip=kpg_get_ip();
	if (!$debug) return;
	$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
	// get the program that is running
	$sname=$_SERVER["REQUEST_URI"];	
	if (empty($sname)) {
		$sname=$_SERVER["SCRIPT_NAME"];	
	}
	
	$f='';
	$f=@fopen(KPG_SS_PLUGIN_DATA.".sfs_debug_output.txt",'a');
	if(empty($f)) return false;
	@fwrite($f,$now.": ".$sname.", ".$msg.", ".$ip."\r\n");
	@fclose($f);

}
function sfs_ErrorHandler($errno, $errmsg, $filename, $linenum, $vars) {
	// write the answers to the file
	// we are only concerned with the errors and warnings, not the notices
	//if ($errno==E_NOTICE || $errno==E_WARNING) return false;
	//if ($errno==2048) return; // wordpress throws deprecated all over the place.
	$serrno="";
	if (
			(strpos($filename,'kpg')===false)
			&&(strpos($filename,'admin-options')===false)
			&&(strpos($filename,'mu-options')===false)
			&&(strpos($filename,'stop-spam')===false)
			&&(strpos($filename,'sfr_mu')===false)
			&&(strpos($filename,'settings.php')===false)
			&&(strpos($filename,'options-general.php')===false)
			) return false;
	switch ($errno) {
	case E_ERROR: 
		$serrno="Fatal run-time errors. These indicate errors that can not be recovered from, such as a memory allocation problem. Execution of the script is halted. ";
		break;
	case E_WARNING: 
		$serrno="Run-time warnings (non-fatal errors). Execution of the script is not halted. ";
		break;
	case E_NOTICE: 
		$serrno="Run-time notices. Indicate that the script encountered something that could indicate an error, but could also happen in the normal course of running a script. ";
		break;
		default;
		$serrno="Unknown Error type $errno";
	}
	if (strpos($errmsg,'modify header information')) return false;
	$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
	$m1=memory_get_usage(true);
	$m2=memory_get_peak_usage(true);
	$ip=kpg_get_ip();
	$msg="
	Time: $now
	Error number: $errno
	Error type: $serrno
	Error Msg: $errmsg
	IP address: $ip
	File name: $filename
	Line Number: $linenum
	Memory used, peak: $m1, $m2
	---------------------
	";
	// write out the error
	$f='';
	$f=@fopen(KPG_SS_PLUGIN_DATA.".sfs_debug_output.txt",'a');
	if(empty($f)) return false;
	@fwrite($f,$msg);
	@fclose($f);
	return false;
}

?>