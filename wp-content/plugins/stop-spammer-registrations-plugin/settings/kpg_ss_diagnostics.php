<?php

if (!defined('ABSPATH')) exit; // just in case

$stats=kpg_ss_get_stats();
$options=kpg_ss_get_options();

if(!current_user_can('manage_options')) {
	die('Access Denied');
}

$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));

// for session speed checks
//if(!isset($_POST)||empty($_POST)) { // no post defined
//$_SESSION['kpg_stop_spammers_time']=time();
//	if (! isset($_COOKIE['kpg_stop_spammers_time'])) { // if previous set do not reset
//		setcookie( 'kpg_stop_spammers_time', strtotime("now"), strtotime('+1 min'));
//	}
//}

$ip=kpg_get_ip();
$hip="unknown";
if (array_key_exists('SERVER_ADDR',$_SERVER)) {
	$hip=$_SERVER["SERVER_ADDR"];
}
$email='';
$author='';
$subject='';
$body='';
if (array_key_exists('ip',$_POST)) $ip=$_POST['ip'];
if (array_key_exists('email',$_POST)) $email=$_POST['email'];
if (array_key_exists('author',$_POST)) $author=$_POST['author'];
if (array_key_exists('subject',$_POST)) $subject=$_POST['subject'];
if (array_key_exists('body',$_POST)) $body=$_POST['body'];

$nonce=wp_create_nonce('kpgstopspam_update');

?>
<div class="wrap">
<h2>Stop Spammers Option Testing</h2>
<p>This allows you to test the plugin against an IP address.</p>
<form method="post" action="">
<input type="hidden" name="action" value="update" />
<input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
<fieldset style="border:thin solid black;padding:6px;width:100%;">
<legend><span style="font-weight:bold;font-size:1.2em" >Option Testing</span></legend>
IP address: <input name="ip" type="text" value="<?php echo $ip; ?>"> (Your Server address is <?php echo $hip;?>)<br>
Email: <input name="email" type="text" value="<?php echo $email; ?>"><br>
Author/User: <input name="author" type="text" value="<?php echo $author; ?>"><br>
Subject: <input name="subject" type="text" value="<?php echo $subject; ?>"><br>
Comment: <textarea name="body"><?php echo $body; ?></textarea><br>

<div style="width:50%;float:left;">
<p class="submit"><input name="testopt" class="button-primary" value="Test Options" type="submit" /></p>
</div>
<div style="width:50%;float:right;">
<p class="submit"><input name="testcountry" class="button-primary" value="Test Countries" type="submit" /></p>
</div>
<br style="clear:both;">
<?php 
$nonce='';
if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
if (!empty($nonce) && wp_verify_nonce($nonce,'kpgstopspam_update')) { 
	$post=get_post_variables();
	if (array_key_exists('testopt',$_POST)) {
		// do the test
		$optionlist= array(
		'chkaws',
		'chkcloudflare',
		'chkgcache',
		'chkgenallowlist',
		'chkgoogle',
		'chkmiscallowlist',
		'chkpaypal',
		'chkscripts',
		'chkvalidip',
		'chkwlem',
		'chkwluserid',
		'chkwlist',
		'chkyahoomerchant'
		);
		$m1=memory_get_usage(true);
		$m2=memory_get_peak_usage(true);
		echo "<br>Memory used, peak: $m1, $m2<br>";
		echo "<ul>Allow Checks<br>";
		foreach ($optionlist as $chk) {
			$ansa=be_load($chk,$ip,$stats,$options,$post);
			if (empty($ansa)) $ansa='OK';
			echo "$chk : $ansa<br>";
		}
		echo "</ul>";
		$optionlist= array(
		'chk404',
		'chkaccept',
		'chkadmin',
		'chkadminlog',
		'chkagent',
		'chkamazon',
		'chkbbcode',
		'chkbcache',
		'chkblem',
		'chkbluserid',
		'chkblip',
		'chkbotscout',
		'chkdisp',
		'chkdnsbl',
		'chkexploits',
		'chkgooglesafe',
		'chkhoney',
		'chkhosting',
		'chkinvalidip',
		'chklong',
		'chkreferer',
		'chksession',
		'chksfs',
		'chkspamwords',
		'chktld',
		'chkubiquity',
		'chkmulti'
		);
		$m1=memory_get_usage(true);
		$m2=memory_get_peak_usage(true);
		echo "<br>Memory used, peak: $m1, $m2<br>";
		echo "<ul>Deny Checks<br>";
		foreach ($optionlist as $chk) {
			$ansa=be_load($chk,$ip,$stats,$options,$post);
			if (empty($ansa)) $ansa='OK';
			echo "$chk : $ansa<br>";
		}
		echo "</ul>";
		$optionlist=array();
		$a1=apply_filters('kpg_ss_addons_allow',$optionlist);
		$a3=apply_filters('kpg_ss_addons_deny',$optionlist);
		$a5=apply_filters('kpg_ss_addons_get',$optionlist);
		$optionlist=array_merge($a1,$a3,$a5);
		if (!empty($optionlist)) {
			echo "<ul>Addon Checks<br>";
			foreach ($optionlist as $chk) {
				$ansa=be_load($chk,$ip,$stats,$options,$post);
				if (empty($ansa)) $ansa='OK';
				$nm=$chk[1];
				echo "$nm : $ansa<br>";
			}
			echo "</ul>";
		}
		$m1=memory_get_usage(true);
		$m2=memory_get_peak_usage(true);
		echo "<br>Memory used, peak: $m1, $m2<br>";
		
		
		
	}
	if (array_key_exists('testcountry',$_POST)) {
		$optionlist=array(
'chkAD','chkAE','chkAF','chkAL','chkAM','chkAR','chkAT','chkAU','chkAX','chkAZ','chkBA','chkBB','chkBD','chkBE','chkBG','chkBH','chkBN','chkBO','chkBR','chkBS','chkBY','chkBZ','chkCA','chkCD','chkCH','chkCL','chkCN','chkCO','chkCR','chkCU','chkCW','chkCY','chkCZ','chkDE','chkDK','chkDO','chkDZ','chkEC','chkEE','chkES','chkEU','chkFI','chkFJ','chkFR','chkGB','chkGE','chkGF','chkGI','chkGP','chkGR','chkGT','chkGU','chkGY','chkHK','chkHN','chkHR','chkHT','chkHU','chkID','chkIE','chkIL','chkIN','chkIQ','chkIR','chkIS','chkIT','chkJM','chkJO','chkJP','chkKE','chkKG','chkKH','chkKR','chkKW','chkKY','chkKZ','chkLA','chkLB','chkLK','chkLT','chkLU','chkLV','chkMD','chkME','chkMK','chkMM','chkMN','chkMO','chkMP','chkMQ','chkMT','chkMV','chkMX','chkMY','chkNC','chkNI','chkNL','chkNO','chkNP','chkNZ','chkOM','chkPA','chkPE','chkPG','chkPH','chkPK','chkPL','chkPR','chkPS','chkPT','chkPW','chkPY','chkQA','chkRO','chkRS','chkRU','chkSA','chkSC','chkSE','chkSG','chkSI','chkSK','chkSV','chkSX','chkSY','chkTH','chkTJ','chkTM','chkTR','chkTT','chkTW','chkUA','chkUK','chkUS','chkUY','chkUZ','chkVC','chkVE','chkVN','chkYE'
		
		
		);
		
		//KE - Kenya
		//chkMA missing
		//SC - Seychelles
		
		$m1=memory_get_usage(true);
		$m2=memory_get_peak_usage(true);
		echo "<br>Memory used, peak: $m1, $m2<br>";
		foreach ($optionlist as $chk) {
			$ansa=be_load($chk,$ip,$stats,$options,$post);
			if (empty($ansa)) $ansa='OK';
			echo "$chk : $ansa<br>";
		}
		$m1=memory_get_usage(true);
		$m2=memory_get_peak_usage(true);
		echo "<br>Memory used, peak: $m1, $m2<br>";
	}
}


?>
</fieldset>

<div style="width:50%;float:left;">
<h3>Display All Options</h3>
<p>You can dump all options here (useful for debugging): </p>
<p class="submit"><input name="dumpoptions" class="button-primary" value="Dump Options" type="submit" /></p>
</div>
<div style="width:50%;float:right;">
<h3>Display All Stats</h3>
<p>You can dump all Stats here: </p>
<p class="submit"><input name="dumpstats" class="button-primary" value="Dump Stats" type="submit" /></p>
</div>
<br style="clear:both;">
<?php
if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
if (!empty($nonce) && wp_verify_nonce($nonce,'kpgstopspam_update')) { 
	if (array_key_exists('dumpoptions',$_POST)) {
		?>
		<pre>

		<?php
		echo "\r\n";
		$options=kpg_ss_get_options();
		foreach($options as $key=>$val) {
			if (is_array($val)) $val=print_r($val,true);
			echo "<b>&bull; $key</b> = $val\r\n";
		}
		echo "\r\n";
		?>

		</pre>
		<?php
	}
}
?>
<?php
if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
if (!empty($nonce) && wp_verify_nonce($nonce,'kpgstopspam_update')) { 
	if (array_key_exists('dumpstats',$_POST)) {
		?>
		<pre>

		<?php
		$stats=kpg_ss_get_stats();
		echo "\r\n";
		foreach($stats as $key=>$val) {
			if (is_array($val)) $val=print_r($val,true);
			echo "<b>&bull; $key</b> = $val\r\n";
		}
		echo "\r\n";
		?>

		</pre>
		<?php
	}
}
?>
<p>&nbsp;</p>



</form>
<?php
// if there is a log file we can display it here
$dfile=KPG_SS_PLUGIN_DATA.'.sfs_debug_output.txt';
if (file_exists($dfile)) {
	if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
	if (!empty($nonce) && wp_verify_nonce($nonce,'kpgstopspam_update')) { 
		if (array_key_exists('killdebug',$_POST)) {
			$f=unlink($dfile);
			echo "<p>file deleted<p>";
		}
	}
	
}
if (file_exists($dfile)) {
	// we have a file. We can view it or delete it.
	$nonce="";
	$to=get_option('admin_email');
	$f=file_get_contents($dfile);
	$ff=wordwrap($f,70,"\r\n");

	?>
	<p>The plugin displays a log of errors that it has found. 
	It appears that you have generated some errors. 
	You can use this debug problems or notify the plugin author that the plugin has problems on your system.</p>
	<fieldset style="border:thin solid black;padding:6px;width:80%;margin-left:auto;margin-right:auto;">
	<legend><span style="font-weight:bold;font-size:1.2em" >Send Debug Report</span></legend>
	<p>This is an email form and should bring up your email client when clicked. If it doesn't you can copy report
information and paste it into an email message and send to support@blogseye.com. This form will not work
with all browsers and configurations.</p>
<form method="get" target="_blank" action="mailto:support@blogseye.com">
<input type="hidden" name="subject" value="Debug Report">
<table>
<tr><td>From:</td><td><input type="text" name="from" value="<?php echo $to; ?>"></td></tr>
<tr><td>Log File:</td><td><textarea rows="12" cols="71" name="body"><?php echo $ff; ?></textarea></td></tr>
</table>
<input type="submit" value="Send"><br>
(If pressing enter does nothing, either your email client cannot handle the form submit, or the log file is too big. If
this is the case, you will have to copy the log file and paste it into an email to support@blogseye.com.)
</form> 
</fieldset>
<?php
if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
if (!empty($nonce) && wp_verify_nonce($nonce,'kpgstopspam_update')) { 
	if (array_key_exists('showdebug',$_POST)) {
		echo "<h4>debug output</h4><pre>$f</pre><h4>end of file</h4>";
	}
}
$nonce=wp_create_nonce('kpgstopspam_update');
?>
<div style="width:50%;float:left;">
<form method="post" action="">
<input type="hidden" name="update_options" value="update" />
<input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
<p class="submit">
<input class="button-primary" name="showdebug" value="Show Debug File" type="submit" />
</p>
</form>
</div>
<div style="width:50%;float:right;">
<form method="post" action="">
<input type="hidden" name="update_options" value="update" />
<input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
<p class="submit">
<input class="button-primary" name="killdebug" value="Delete Debug File" type="submit" />
</p>
</form>
</div>
<br style="clear:both;">
<?php	    
	
	}
	$ini='';
	$pinf=true;
	$ini=@ini_get('disable_functions');
	if (!empty($ini)) {
		$disabled = explode(',',$ini);
		if (is_array($disabled) && in_array('phpinfo', $disabled)) {
			$pinf=false;
		}
	} 
	if ($pinf) {
?>
<a href="" onclick="document.getElementById('shpinf').style.display='block';return false;">show phpinfo</a>
<?php

ob_start();
phpinfo();

preg_match ('%<style type="text/css">(.*?)</style>.*?(<body>.*</body>)%s', ob_get_clean(), $matches);

# $matches [1]; # Style information
# $matches [2]; # Body information

echo "<div class='phpinfodisplay' id=\"shpinf\" style=\"display:none;\"><style type='text/css'>\n",
	join( "\n",
		array_map(
			create_function(
				'$i',
				'return ".phpinfodisplay " . preg_replace( "/,/", ",.phpinfodisplay ", $i );'
				),
			preg_split( '/\n/', $matches[1] )
			)
		),
	"</style>\n",
	$matches[2],
	"\n</div>\n";


		
	}
?>

</div>
