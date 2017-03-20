<?php
/*
	Stop Spammers Plugin 
	Options Setup Page for MU switch
	
*/
if (!defined('ABSPATH')) exit; // just in case

if(!current_user_can('manage_options')) {
	die('Access Denied');
}

$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
$options=kpg_ss_get_options();
extract($options);
//$ip=kpg_get_ip();
$nonce='';
$msg='';
if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
if (wp_verify_nonce($nonce,'kpgstopspam_update')) { 
	if (array_key_exists('action',$_POST)) {
		$optionlist=array('redir','notify','wlreq');
		foreach ($optionlist as $check) {
			$v='N';
			if(array_key_exists($check,$_POST)) {
				$v=$_POST[$check];
				if ($v!='Y') $v='N';
			} 
			$options[$check]=$v;
		}
		// other options
		if (array_key_exists('redirurl',$_POST)) {
			$redirurl=trim(stripslashes($_POST['redirurl']));
			$options['redirurl']=$redirurl;
		}
		if (array_key_exists('wlreqmail',$_POST)) {
			$wlreqmail=trim(stripslashes($_POST['wlreqmail']));
			$options['wlreqmail']=$wlreqmail;
		}
		if (array_key_exists('rejectmessage',$_POST)) {
			$rejectmessage=trim(stripslashes($_POST['rejectmessage']));
			$options['rejectmessage']=$rejectmessage;
		}
		if (array_key_exists('chkcaptcha',$_POST)) {
			$chkcaptcha=trim(stripslashes($_POST['chkcaptcha']));
			$options['chkcaptcha']=$chkcaptcha;
		}
		// added the API key stiff for Captchas
		if (array_key_exists('recaptchaapisecret',$_POST)) {
			$recaptchaapisecret=stripslashes($_POST['recaptchaapisecret']);
			$options['recaptchaapisecret']=$recaptchaapisecret;
		}
		if (array_key_exists('recaptchaapisite',$_POST)) {
			$recaptchaapisite=stripslashes($_POST['recaptchaapisite']);
			$options['recaptchaapisite']=$recaptchaapisite;
		}
		if (array_key_exists('solvmediaapivchallenge',$_POST)) {
			$solvmediaapivchallenge=stripslashes($_POST['solvmediaapivchallenge']);
			$options['solvmediaapivchallenge']=$solvmediaapivchallenge;
		}
		if (array_key_exists('solvmediaapiverify',$_POST)) {
			$solvmediaapiverify=stripslashes($_POST['solvmediaapiverify']);
			$options['solvmediaapiverify']=$solvmediaapiverify;
		}
		// validate the chkcaptcha variable
		if ($chkcaptcha=='G' && ($recaptchaapisecret=='' ||$recaptchaapisite=='')) {
			$chkcaptcha='Y';
			$options['chkcaptcha']=$chkcaptcha;
			$msg="You cannot use Google reCaptcha unless you have entered an API key";
		}
		if ($chkcaptcha=='S' && ($solvmediaapivchallenge=='' ||$solvmediaapiverify=='')) {
			$chkcaptcha='Y';
			$options['chkcaptcha']=$chkcaptcha;
			$msg="You cannot use Solce Media Captcha unless you have entered an API key";
		}
		kpg_ss_set_options($options);
		extract($options); // extract again to get the new options

	} 
}
$nonce=wp_create_nonce('kpgstopspam_update');

?>

<div class="wrap">
  <h2>Stop Spammers Challenge and Deny Options</h2>
  <?php
	if (!empty($msg)) echo "<span style=\"color:red;size=2em;\">$msg</span>";
?>
  <form method="post" action="">
    <input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
    <input type="hidden" name="action" value="update challenge" />
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Spammer Message</span></legend>
    This message is only visible to spammers. It only shows if spammers are rejected at the time login or comment form is displayed. You can use the shortcode <i>[reason]</i> to include the deny reason code with the message. You can also use <i>[ip]</i> in your message which would be the user's ip address. (You may not want to give spammers hints on how they were denied.)<br>
    <textarea id="rejectmessage" name="rejectmessage" cols="32" rows="5"><?php echo $rejectmessage; ?></textarea>
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Send spammer to another web page</span></legend>
    Enable redirect:
    <input type="checkbox" name ="redir" value="Y" <?php if ($redir=='Y') echo "checked=\"checked\""; ?> />
    <br>
    If you want you can send the spammer to a web page. This can be a custom page explaining terms of service, or a nasty message.<br>
    Redirect URL:
    <input size="77" name="redirurl" type="text" value="<?php echo $redirurl; ?>"/>
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Allow users to add to the Allow Request List</span></legend>
    <p>Users can see the form to add themselves to the request list, but lots of spammers fill it out randomly. This hides the request form.</p>
    Blocked users see the Allow request form:
    <input type="checkbox" name ="wlreq" value="Y" <?php if ($wlreq=='Y') echo "checked=\"checked\""; ?> />
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Notify Webmaster when user requests to be added to Allow List</span></legend>
    <p>Blocked users can add their email addresses to the the Allow List request. This will also send you an email notification.</p>
    Enable email request:
    <input type="checkbox" name ="notify" value="Y" <?php if ($notify=='Y') echo "checked=\"checked\""; ?> />
    <br>
    (Optional) email where requests are sent:<br>
    <input size="48" name="wlreqmail" type="text" value="<?php echo $wlreqmail; ?>"/>
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Second Chance Captcha Challenge</span></legend>
    <?php
	if (!empty($msg)) echo "<span style=\"color:red;size=1.2em;\">$msg</span>";
?>
    <p> The plugin is extremely aggressive and will probably block some small number of legitimate users. You can give users a second chance by displaying a CAPTCHA image and asking them to type in the letters that they see. This prevents lockouts.<br>
      This option will override the email notification option above.<br>
      By default the plugin will support the arithmetic question which is OK. For better results I have included the ability to use Google's ReCaptcha, or you can try SolveMedia's captcha using ads which can provide a revenue stream.<br>
      <input type="radio" value="N" name="chkcaptcha" <?php if ($chkcaptcha=='N') echo "checked=\"checked\""; ?>/>
      No Captcha (default)<br>
      <input type="radio" value="G" name="chkcaptcha" <?php if ($chkcaptcha=='G') echo "checked=\"checked\""; ?>/>
      Google ReCaptcha<br>
      <input type="radio" value="S" name="chkcaptcha" <?php if ($chkcaptcha=='S') echo "checked=\"checked\""; ?>/>
      Solve Media Captcha<br>
      <input type="radio" value="A" name="chkcaptcha" <?php if ($chkcaptcha=='A') echo "checked=\"checked\""; ?>/>
      Really stupid arithmetic question<br>
    </p>
    <p>In odder to use Solve Media or Google reCaptcha you will need to get an API key. Open Captcha is no longer supported so the arithmetic question will be used for those that had it set.</p>
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;width:100%;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Google recaptcha API Key</span></legend>
    Site Key:
    <input size="64" name="recaptchaapisite" type="text" value="<?php echo $recaptchaapisite; ?>"/>
    <br>
    Secret Key:
    <input size="64" name="recaptchaapisecret" type="text" value="<?php echo $recaptchaapisecret; ?>"/>
    <br>
    These api keys are used for displaying a Google recaptcha on your site.  
    You can display the recaptcha in case a real user is blocked, so they can still leave a comment. 
    You can register and get an API key at <a href="https://www.google.com/recaptcha/admin#list" target="_blank">https://www.google.com/recaptcha/admin#list</a>.
    
    If the keys are correct you should see the recaptcha here:<br>
    <?php 
		if (!empty($recaptchaapisite)) {
		?>
    <script type="text/javascript" src="https://www.google.com/recaptcha/api.js" async defer></script>
    <div class="g-recaptcha" data-sitekey="<?php echo $recaptchaapisite; ?>"></div>
    If the recaptcha form looks good you need to enable the recaptcha on the  Challenge &amp; Deny Options menu page. (see left).
    <?php 
		 }
		 ?>
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;width:100%;">
    <legend><span style="font-weight:bold;font-size:1.2em" >SolveMedia captcha API Key</span></legend>
    Solve Media Challenge Key:
    <input size="64" name="solvmediaapivchallenge" type="text" value="<?php echo $solvmediaapivchallenge; ?>"/>
    <br>
    Solve Media Verification Key:
    <input size="64" name="solvmediaapiverify" type="text" value="<?php echo $solvmediaapiverify; ?>"/>
    <br>
    This api key is used for displaying a SolveMedia captcha on your site. 
    This captcha has the added advantage that you can make a little money by having your captcha be an ad.
    (It is unlikely that spammers will click through on an ad, but Hey! You never know.) 
    You can display the captcha in case a real user is blocked, so they can still leave a comment. 
    You can register and get an API key at <a href="https://portal.solvemedia.com/portal/public/signup" target="_blank">portal.solvemedia.com/portal/public/signup</a>.
    If the keys are correct you should see the captcha here:<br>
    <?php 
		if (!empty($solvmediaapivchallenge)) {
		?>
    <script type="text/javascript"
        src="http://api.solvemedia.com/papi/challenge.script?k=<?php echo $solvmediaapivchallenge;?>">
         </script>
    If the captcha form looks good you need to enable the captcha on the Challenge &amp; Deny Options menu page. (see left).
    (Solvemedia has a plugin that can be used for registrations and logins that you could use as a revenue stream.)
    <?php 
		 }
		 ?>
    </fieldset>
    <br>
    <br>
    <p class="submit">
      <input class="button-primary" value="Save Changes" type="submit" />
    </p>
  </form>
</div>
