<?php
/*
	Stop Spammers Plugin 
	Options Setup Page for MU switch
	
*/
if (!defined('ABSPATH')) exit; // just in case

if(!current_user_can('manage_options')) {
	die('Access Denied');
}


$trash=KPG_SS_PLUGIN_URL.'images/trash.png';
$tdown=KPG_SS_PLUGIN_URL.'images/tdown.png';
$tup=KPG_SS_PLUGIN_URL.'images/tup.png'; 
$whois=KPG_SS_PLUGIN_URL.'images/whois.png'; 
$stophand=KPG_SS_PLUGIN_URL.'images/stop.png';
$search=KPG_SS_PLUGIN_URL.'images/search.png'; 



$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));

?>

<div class="wrap">
<h2>Stop Spammers Reports</h2>
<?php
//$ip=kpg_get_ip();
$stats=kpg_ss_get_stats();
extract($stats);
$options=kpg_ss_get_options();
extract($options);
$ip=$_SERVER['REMOTE_ADDR'];
$nonce='';
$msg='';
if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
if (wp_verify_nonce($nonce,'kpgstopspam_update')) { 
	if (array_key_exists('kpg_stop_clear_hist',$_POST)) {
		// clean out the history
		$hist=array();
		$stats['hist']=$hist;
		$spcount=0;
		$stats['spcount']=$spcount;
		$spdate=$now;
		$stats['spdate']=$spdate;
		kpg_ss_set_stats($stats);
		
		extract($stats); // extract again to get the new options
		$msg="History Log Cleared";
	}
	if (array_key_exists('kpg_stop_update_log_size',$_POST)) {
		// update log size
		if (array_key_exists('kpg_sp_hist',$_POST)){
			$kpg_sp_hist=stripslashes($_POST['kpg_sp_hist']);
			$options['kpg_sp_hist']=$kpg_sp_hist;
			$msg="Log size updated";
			// update the options
			kpg_ss_set_options($options);
		}
	}
}
if (!empty($msg)) echo "<h3>$msg</h3>";


if ($spmcount>0) {
	?>
	<h3>Stop Spammers has stopped <?php echo $spmcount; ?> spammers since <?php echo $spmdate; ?>.</h3>
	<?php 
}
if ($spcount>0) {
	?>
	<h3>Stop Spammers has stopped <?php echo $spcount; ?> spammers since <?php echo $spdate; ?>.</h3>
	<?php 
}
$num_comm = wp_count_comments( );
$num = number_format_i18n($num_comm->spam);
if ($num_comm->spam>0 && KPG_SS_MU!='Y') {	
	?>
	<p>There are <a href='edit-comments.php?comment_status=spam'><?php echo $num; ?></a> spam comments waiting for you to report them</p>
	<?php 
}
$num_comm = wp_count_comments( );
$num = number_format_i18n($num_comm->moderated);
if ($num_comm->moderated>0 && KPG_SS_MU!='Y') {	
	?>
	<p>There are <a href='edit-comments.php?comment_status=moderated'><?php echo $num; ?></a> comments waiting to be moderated</p>
	<?php 
}
$nonce=wp_create_nonce('kpgstopspam_update');

?>
<hr/>
<h3>Recent Activity</h3>
<form method="post" action="">
<input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
<input type="hidden" name="kpg_stop_update_log_size" value="true" />

<fieldset style="border:thin solid black;padding:6px;margin:6px;">
<legend><span style="font-weight:bold;font-size:1.2em" >History Size:</span></legend>

Select the number of items to save in the History. Keep this small.<br>

<select name="kpg_sp_hist">
<option value="10" <?php if ($kpg_sp_hist=='10') echo "selected=\"true\""; ?>>10</option>
<option value="25" <?php if ($kpg_sp_hist=='25') echo "selected=\"true\""; ?>>25</option>
<option value="50" <?php if ($kpg_sp_hist=='50') echo "selected=\"true\""; ?>>50</option>
<option value="75" <?php if ($kpg_sp_hist=='75') echo "selected=\"true\""; ?>>75</option>
<option value="100" <?php if ($kpg_sp_hist=='100') echo "selected=\"true\""; ?>>100</option>
</select>
<p class="submit">
<input  class="button-primary" value="Update Log Size" type="submit" />
</p>
</form>

</fieldset>
<fieldset style="border:thin solid black;padding:6px;margin:6px;">
<legend><span style="font-weight:bold;font-size:1.2em" >Clear Activity</span></legend>
<form method="post" action="">
<input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
<input type="hidden" name="kpg_stop_clear_hist" value="true" />

<p class="submit">
<input  class="button-primary" value="Clear Recent Activity" type="submit" />
</p>
</form>
</fieldset>

</p>
<?php
if (empty($hist)) {
	echo "<p>Nothing in Logs</p>";
} else {
	?>
	<table style="background-color:#eeeeee;" cellspacing="2">
	<tr style="background-color:ivory;text-align:center;">
	<td>date/time</td>
	<td>email</td>
	<td>IP</td>
	<td>author, user/pwd</td>
	<td>script</td>
	<td>reason
	<?php
	if (function_exists('is_multisite') && is_multisite()) {
		?>
		</td>
		<td>blog</td>
		<?php
	}
	?>
	</tr>
	<?php
	// sort hist by date descending
	krsort($hist);
	foreach($hist as $key=>$data) {
		//$hist[$now]=array($ip,$email,$author,$sname,'begin');
		$em=strip_tags(trim($data[1]));
		$dt=strip_tags($key);
		$ip=$data[0];
		$au=strip_tags($data[2]);
		$id=strip_tags($data[3]);
		if (empty($au)) $au=' -- ';
		if (empty($em)) $em=' -- ';
		$reason=$data[4];
		$blog=1;
		if (count($data)>5) $blog=$data[5];
		if (empty($blog)) $blog=1;
		if(empty($reason)) 
		$reason="passed";
		$stopper="<a title=\"Check Stop Forum Spam (SFS)\" target=\"_stopspam\" href=\"http://www.stopforumspam.com/search.php?q=$ip\"><img src=\"$stophand\" width=\"16px\"/></a>";
		$honeysearch="<a title=\"Check project HoneyPot\" target=\"_stopspam\" href=\"https://www.projecthoneypot.org/ip_$ip\"><img src=\"$search\" width=\"16px\"/></a>";
		$botsearch="<a title=\"Check BotScount\" target=\"_stopspam\" href=\"http://botscout.com/search.htm?stype=q&sterm=$ip\"><img src=\"$search\" width=\"16px\"/></a>";
		$who="<a title=\"whois nic lookup\" target=\"_stopspam\" href=\"http://lacnic.net/cgi-bin/lacnic/whois?lg=EN&query=$ip\"><img src=\"$whois\" width=\"16px\"/></a>";
		echo "<tr style=\"background-color:white;\">
				<td style=\"font-size:.9em;padding:2px;\">$dt</td>
				<td style=\"font-size:.9em;padding:2px;\">$em</td>
				<td style=\"font-size:.9em;padding:2px;\">$ip $who $stopper $honeysearch $botsearch";
		if (strpos($reason,'passed')!==false && ($id=='/'||strpos($id,'login')!==false) && !in_array($ip,$blist) && !in_array($ip,$wlist)) {
			
			echo "<a href=\"\" onclick=\"return addblack('$ip');\" title=\"Add to Deny List\" alt=\"Add to Deny List\" ><img src=\"$tdown\" width=\"16px\" /></a>";
		}
		echo "</td><td style=\"font-size:.9em;padding:2px;\">$au</td>
				<td style=\"font-size:.9em;padding:2px;\">$id</td>
				<td style=\"font-size:.9em;padding:2px;\">$reason</td>";
		if (function_exists('is_multisite') && is_multisite()) {
			// switch to blog and back
			$blogname=get_blog_option( $blog, 'blogname' );
			$blogadmin=esc_url( get_admin_url($blog) );
			$blogadmin=trim($blogadmin,'/');
			echo "<td style=\"font-size:.9em;padding:2px;\" align=\"center\">";
			echo "<a href=\"$blogadmin/edit-comments.php\">$blogname</a>";
			echo "</td>";
		}			
		echo "</tr>";
	}
	?>
	</table>
	<?php
}

?>
</div>
