<?php

if (!defined('ABSPATH')) exit; // just in case

if(!current_user_can('manage_options')) {
	die('Access Denied');
}
$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
$options=kpg_ss_get_options();
extract($options);
$wordpress_api_key=get_option('wordpress_api_key');
if (empty($wordpress_api_key)) $wordpress_api_key='';
$nonce='';
if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
if (!empty($nonce) && wp_verify_nonce($nonce,'kpgstopspam_update')) { 
	if (array_key_exists('action',$_POST)) {
		// other api keys
		if (array_key_exists('apikey',$_POST)) {
			$apikey=stripslashes($_POST['apikey']);
			$options['apikey']=$apikey;
		}
		if (array_key_exists('googleapi',$_POST)) {
			$googleapi=stripslashes($_POST['googleapi']);
			$options['googleapi']=$googleapi;
		}
		if (array_key_exists('honeyapi',$_POST)) {
			$honeyapi=stripslashes($_POST['honeyapi']);
			$options['honeyapi']=$honeyapi;
		}
		if (array_key_exists('botscoutapi',$_POST)) {
			$botscoutapi=stripslashes($_POST['botscoutapi']);
			$options['botscoutapi']=$botscoutapi;
		}
		if (array_key_exists('sfsfreq',$_POST)) {
			$sfsfreq=stripslashes($_POST['sfsfreq']);
			$options['sfsfreq']=$sfsfreq;
		}
		if (array_key_exists('sfsage',$_POST)) {
			$sfsage=stripslashes($_POST['sfsage']);
			$options['sfsage']=$sfsage;
		}
		if (array_key_exists('hnyage',$_POST)) {
			$hnyage=stripslashes($_POST['hnyage']);
			$options['hnyage']=$hnyage;
		}
		if (array_key_exists('hnylevel',$_POST)) {
			$hnylevel=stripslashes($_POST['hnylevel']);
			$options['hnylevel']=$hnylevel;
		}
		if (array_key_exists('botfreq',$_POST)) {
			$botfreq=stripslashes($_POST['botfreq']);
			$options['botfreq']=$botfreq;
		}
   $optionlist=array('chksfs','chkdnsbl');
 	foreach ($optionlist as $check) {
		$v='N';
		if(array_key_exists($check,$_POST)) {
			$v=$_POST[$check];
			if ($v!='Y') $v='N';
		} 
		$options[$check]=$v;
	}

		kpg_ss_set_options($options);

		extract($options); // extract again to get the new options
	}
}

$nonce=wp_create_nonce('kpgstopspam_update');

?>

<div class="wrap">
<h2>Stop Spammers Web Services Options</h2>
<p>There are many services that can be used to check for spam or protect your website against spammers. Most require a key so that only registered users can use their services. All of the services here can be used by Stop Spammers and all are free. </p>
<form method="post" action="">
<input type="hidden" name="action" value="update" />
<input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
	<fieldset style="border:thin solid black;padding:6px;width:100%;">
	<legend><span style="font-weight:bold;font-size:1.2em" >StopForumSpam.com API Key</span></legend>
	<input size="32" name="apikey" type="text" value="<?php echo $apikey; ?>"/><br>
		Enable StopForumSpam Lookups: <input name="chksfs" type="checkbox" value="Y" <?php if ($chksfs=='Y') echo  "checked=\"checked\"";?>/> Check to enable SFS lookups<br>

	You do not need an api key to check the Stop Forum Spam database, but if you want to report any
	spam that you find, you need to provide it here. You can register and get an API key at <a href="http://www.stopforumspam.com/keys" target="_blank">www.stopforumspam.com</a>.<br>
	You can set the minimum settings to allow possible spammers to use your site. <br/>
	You may wish to forgive spammers with few incidents or no recent activity. I would recommend that to be on the safe side you should block users who appear on the spam database unless they specifically ask to be Allow Listed. Allowed values are 0 to 9999. Only numbers are accepted.
	<table align="center" cellspacing="1" style="background-color:#CCCCCC;font-size:.9em;">
	<tr bgcolor="white">
		<td valign="top">Deny spammers found on Stop Forum Spam with more than
		<input size="3" name="sfsfreq" type="text" value="<?php echo $sfsfreq; ?>"/>
		incidents, and occurring less than
		<input size="4" name="sfsage" type="text" value="<?php echo $sfsage; ?>"/>
		days ago. </td>
    </tr>
	</table>
	</fieldset>

	<br>
	<fieldset style="border:thin solid black;padding:6px;width:100%;">
	<legend><span style="font-weight:bold;font-size:1.2em" >Project Honeypot API Key</span></legend>
		<input size="32" name="honeyapi" type="text" value="<?php echo $honeyapi; ?>"/><br>
		This api key is used for querying the Project Honeypot Deny List. It is required if you want to 
		check IP addresses against the Project Honeypot database. You can register and get an API key at <a href="https://www.projecthoneypot.org/account_login.php" target="_blank">www.projecthoneypot.com</a>.<br>
        Allowed values are 0 to 9999. Only numbers are accepted.
		<table align="center" cellspacing="1" style="background-color:#CCCCCC;font-size:.9em;">
			<tr bgcolor="white">
			<td valign="top">Deny spammers found on Project HoneyPot with incidents less than
			<input size="3" name="hnyage" type="text" value="<?php echo $hnyage; ?>"/>
			days ago, and with more than
			<input size="4" name="hnylevel" type="text" value="<?php echo $hnylevel; ?>"/>
			threat level. (25 threat level is average, threat level 5 is fairly low.)</td>
			</tr>
		</table>
	</fieldset>
	<br>
	<fieldset style="border:thin solid black;padding:6px;width:100%;">
	<legend><span style="font-weight:bold;font-size:1.2em" >BotScout API Key</span></legend>
		<input size="32" name="botscoutapi" type="text" value="<?php echo $botscoutapi; ?>"/><br>
		This api key is used for querying the Botscout database. It is required if you want to 
		check IP addresses against the Botscout.com database. You can register and get an API key at <a href="http://botscout.com/getkey.htm" target="_blank">www.botscout.com</a>.<br>
		Allowed values are 0 to 9999. Only numbers are accepted.<br>
		Please note that botscout is disabled in this release because of policy changes at Botscout.com.
		<table align="center" cellspacing="1" style="background-color:#CCCCCC;font-size:.9em;">
			<tr bgcolor="white">
				<td valign="top">Deny spammers found on BotScout with more than
				<input size="3" name="botfreq" type="text" value="<?php echo $botfreq; ?>"/>
				incidents.</td>
			</tr>
		</table>
	</fieldset>
	<br>

	<fieldset style="border:thin solid black;padding:6px;width:100%;">
	<legend><span style="font-weight:bold;font-size:1.2em" >Check against DNSBL lists such as Spamhaus.org</span></legend>
<input name="chkdnsbl" type="checkbox" value="Y" <?php if ($chkdnsbl=='Y') echo  "checked=\"checked\"";?>/>	Checks the IP on Spamhaus.org. This is primarily used for email spam, but the same bots sending out email spam are probably running comment spam and other exploits.

    </fieldset>
	<br>
	
	
	
	<fieldset style="border:thin solid black;padding:6px;width:100%;">
	<legend><span style="font-weight:bold;font-size:1.2em" >Google Safe Browsing API Key</span></legend>
		<input size="32" name="googleapi" type="text" value="<?php echo $googleapi; ?>"/><br>
		<a href="https://developers.google.com/safe-browsing/key_signup" target="_blank">Sign up for a Google Safe Browsing API Key</a> If this api key is present, URLs found in comments will be checked for Phishing or Malware sites and if found will be rejected. 
	</fieldset>
	<br>



<br/>
<p class="submit">
<input class="button-primary" value="Save Changes" type="submit" />
</p>
</form>
<p>&nbsp;</p>
</div>