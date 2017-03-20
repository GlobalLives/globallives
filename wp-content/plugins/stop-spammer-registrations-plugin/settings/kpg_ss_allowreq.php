<?php
/*
	Stop Spammers Plugin 
	Cache Options
*/
if (!defined('ABSPATH')) exit; // just in case

if(!current_user_can('manage_options')) {
	die('Access Denied');
}
$stats=kpg_ss_get_stats();
extract($stats);
$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
$options=kpg_ss_get_options();
extract($options);
$stats=kpg_ss_get_stats();
extract($stats);

$trash=KPG_SS_PLUGIN_URL.'images/trash.png';
$tdown=KPG_SS_PLUGIN_URL.'images/tdown.png';
$tup=KPG_SS_PLUGIN_URL.'images/tup.png'; // fix this
$whois=KPG_SS_PLUGIN_URL.'images/whois.png'; // fix this

$nonce="";
$ajaxurl=admin_url('admin-ajax.php');

// update options
if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
if (!empty($nonce) && wp_verify_nonce($nonce,'kpgstopspam_update')) { 
	if (array_key_exists('kpg_stop_clear_wlreq',$_POST)) {
		$wlrequests=array();
		$stats['wlrequests']=$wlrequests;
		kpg_ss_set_stats($stats);
	}

}

$nonce=wp_create_nonce('kpgstopspam_update');

?>
<div class="wrap">
<p>When users are blocked they can fill out a form asking to be added to the allow list. Any users that have filled out the form will appear below. Some spam robots fill in any form that they find so their may be some garbage here.</p>
<?php

if (count($wlrequests)==0) {
	echo "No requests";
	
}
else {
	?>
	<h3>Allow List Requests</h3>
	<form method="post" action="">
	<input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
	<input type="hidden" name="kpg_stop_clear_wlreq" value="true" />
	<p class="submit">
	<input  class="button-primary" value="Clear the Requests" type="submit" />
	</p>
	</form>  
	<?php
	?>
	<table style="background-color:#eeeeee;" cellspacing="2">
	<thead>
		<tr style="background-color:ivory;text-align:center;"><th>Time</th><th>IP</th><th>Email</th><th>Reason</th><th>url</th></tr>
	</thead>
	<tbody id="wlreq">	
	<?php
	$show='';
	$cont='wlreqs';
	// wlrequs has an array of arrays
	//	
	// time,ip,email,author,reasion,info,sname
	//time,ip,email,author,reasion,info,sname
			// use the be_load to get badips
			$options=kpg_ss_get_options();
			$stats=kpg_ss_get_stats();
			$show=be_load('kpg_ss_get_alreq','x',$stats,$options);
	echo $show;
	
	?>
	</tbody>
	</table>
	<?PHP
} 
?>