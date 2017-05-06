<?php
if (!defined('ABSPATH')) exit;

class kpg_ss_challenge extends be_module{ 
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		
		// it looks like I am not getting my stats and options correctly
		
		//sfs_debug_msg('Made it into challenge');
		$ip=kpg_get_ip();
		$stats=kpg_ss_get_stats();
		$options=kpg_ss_get_options();
		//$post=get_post_variables();
		
		/*
		page is HEADER, Allow List Request, Captchas and then a button
		Processing is 1) check for response from from
		2) else display form.


		*/
		// display deny message and captcha if set.
		// first, check to see if they should be redirected
		if ($options['redir']=='Y'&&!empty($options['redirurl'])) {
			//sfs_debug_msg('Redir?');
			header('HTTP/1.1 307 Moved');
			header('Status: 307 Moved');
			header("location: ".$options['redirurl']); 
			exit();
		}
		extract($options);
		$ke='';
		$km='';
		$kr='';
		$ka='';
		$kp=''; // serialized post
		
		// step 1 look for form response
		// nonce is in a field named kn - this is not to confuse with other forms that may be coming in
		$nonce='';
		$msg=''; // this is the body message for failed captchas, notifies and requests
		if (!empty($_POST)&&array_key_exists('kn',$_POST)) {
			//sfs_debug_msg('second time');
			$nonce=$_POST['kn'];
			// get the post items
			if (array_key_exists('ke',$_POST)) $ke=sanitize_text_field($_POST['ke']);
			if (array_key_exists('km',$_POST)) $km=sanitize_text_field($_POST['km']);
			if (strlen($km)>80) $km=substr($km,0,77).'...';
			if (array_key_exists('kr',$_POST)) $kr=sanitize_text_field($_POST['kr']);
			if (array_key_exists('ka',$_POST)) $ka=sanitize_text_field($_POST['ka']);
			if (array_key_exists('kp',$_POST)) $kp=$_POST['kp']; // serialized post
			if (!empty($nonce)&&wp_verify_nonce($nonce,'kpg_stopspam_deny')) {
				//sfs_debug_msg('nonce is good');
				// have a form return.
				//1) to see if the allow by request has been triggered
				$emailsent=$this->kpg_ss_send_email($options);
				//2) see if we should add to the allow list
				$allowset=false;
				if ($wlreq=='Y') { // allow things to added to allow list
					$allowset=$this->kpg_ss_add_allow($ip,$options,$stats,$post,$post);
				}
				// now the captcha settings
				$msg="Thank you,<br>";
				if ($emailsent) $msg.="The blog master has been notified by email.<br>";
				if ($allowset) $msg.="You request has been recorded.<br>";
				if (empty($chkcaptcha)||$chkcaptcha=='N') {
					// send out the thank you message
					wp_die($msg,"Stop Spammers",array('response' => 200));
					exit();
				}
				// they submitted a captcha
				switch ($chkcaptcha) {
				case 'G':
					if (array_key_exists('recaptcha',$_POST) &&!empty($_POST['recaptcha'])&&array_key_exists('g-recaptcha-response',$_POST)) {
						// check recaptcha
						$recaptchaapisecret=$options['recaptchaapisecret'];
						$recaptchaapisite=$options['recaptchaapisite'];
						if (empty($recaptchaapisecret)||empty($recaptchaapisite)) {
							$msg="Recaptcha Keys are not set.";
						} else {
							$g=$_REQUEST['g-recaptcha-response'];
							//$url="https://www.google.com/recaptcha/api/siteverify";
							$url="https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaapisecret&response=$g&remoteip=$ip";
							$resp=kpg_read_file($url);
							////sfs_debug_msg("recaptcha '$g', '$ip' '$resp' - \r\n".print_r($_POST,true));
							if (strpos($resp,'"success": true')!==false) { // found success
								//$kp=base64_encode(serialize($_POST));
								$_POST=unserialize(base64_decode($kp));
								////sfs_debug_msg("trying to return the post to the comments program".print_r($_POST,true));
								// success add to cache
								kpg_ss_log_good($ip,'passed recaptcha','pass');
								do_action('kpg_stop_spam_OK',$ip,$post); // So plugins can undo spam report
								return false;
							} else {
								$msg="Google reCaptcha entry does not match, try again";
							}
						}
					}
					break;					
				case 'S':
					if (array_key_exists('adcopy_challenge',$_POST) &&!empty($_POST['adcopy_challenge'])) {
						// solve media
						$solvmediaapivchallenge=$options['solvmediaapivchallenge'];
						$solvmediaapiverify=$options['solvmediaapiverify'];
						$adcopy_challenge=$_REQUEST['adcopy_challenge'];
						$adcopy_response=$_REQUEST['adcopy_response'];
						//$ip='127.0.0.1';
						
						$postdata = http_build_query(
						array(
							'privatekey' => $solvmediaapiverify,
							'challenge' => $adcopy_challenge,
							'response' => $adcopy_response,
							'remoteip' => $ip
							)
						);

						$opts = array('http' =>
							array(
							'method'  => 'POST',
							'header'  => 'Content-type: application/x-www-form-urlencoded',
							'content' => $postdata
							)
						);

						//$context  = stream_context_create($opts);
						// need to rewrite this post with the wp class
						
						
						/**********************************************
							try to use the sp function
						**********************************************/
						$body=array(
							'privatekey' => $solvmediaapiverify,
							'challenge' => $adcopy_challenge,
							'response' => $adcopy_response,
							'remoteip' => $ip
						);
						$args = array(
							'user-agent'  => 'WordPress/' . '4.2' . '; ' . get_bloginfo( 'url' ),
							'blocking'    => true,
							'headers'     => array('Content-type: application/x-www-form-urlencoded'),
							'method' => 'POST',
							'timeout' => 45,
							'redirection' => 5,
							'httpversion' => '1.0',
							'body' => $body,
							'cookies' => array()
						);
						$url='//verify.solvemedia.com/papi/verify/';
						$resultarray= wp_remote_post( $url, $args );
						$result=$resultarray['body'];
						
						
						//$result = 
						//file_get_contents('//verify.solvemedia.com/papi/verify/', 
						//false, $context);  

						if (strpos($result,'true')!==false) {
							$_POST=unserialize(base64_decode($kp));
							////sfs_debug_msg("trying to return the post to the comments program".print_r($_POST,true));
							// success add to cache
							kpg_ss_log_good($ip,'passed solve media captcha','pass');
							do_action('kpg_stop_spam_OK',$ip,$post); // So plugins can undo spam report
							return false;
						} else {
							$msg="Captcha entry does not match, try again";
						}
					}
					break;					
				case 'A':
				case 'Y':

					if (array_key_exists('nums',$_POST) &&!empty($_POST['nums'])) {
						// simple arithmetic - at lease it is different for each website and changes occasionally
						$seed=5;
						$spdate=$stats['spdate'];
						if (!empty($spdate)) $seed=strtotime($spdate);
						$nums=really_clean(sanitize_text_field($_POST['nums']));
						$nums+=$seed;
						$sum=really_clean(sanitize_text_field($_POST['sum']));
						if ($sum==$nums) {
							$_POST=unserialize(base64_decode($kp));
							////sfs_debug_msg("trying to return the post to the comments program".print_r($_POST,true));
							// success add to cache
							kpg_ss_log_good($ip,'passed Simple Aritmetic captcha','pass');
							do_action('kpg_stop_spam_OK',$ip,$post); // So plugins can undo spam report
							return false;
						} else {
							$msg="Your arithmetic sucks, try again";
						}
					}
					break;					
				case 'F':
					// future - more free captchas
					break;					
				}
			} // nonce check - not a valid nonce on form submit yet the value is there - what do we do?
			//sfs_debug_msg('leaving second time');
		} else {
			// first time through
			//print_r($post);
			//print_r($_POST);
			$ke=$post['email'];
			$km='';
			$kr="";
			//if (array_key_exists('reason',$post)) $kr=$post['reason'];
			$ka=$post['author'];
			$kp=base64_encode(serialize($_POST));
			//sfs_debug_msg('first time getting post stuff');
		}
		//sfs_debug_msg('creating form data');
		// made it here - we display the screens
		$knonce=wp_create_nonce('kpg_stopspam_deny');
		
		// this may be the second time through
		$formtop='';
		if (!empty($msg)) $msg="\r\n<br><span style=\"color:red;\"> $msg </span><hr/>\r\n";
		$formtop.="
<form action=\"\" method=\"post\" >
<input type=\"hidden\" name=\"kn\" value=\"$knonce\">
<input type=\"hidden\" name=\"kpg_deny\" value=\"$chkcaptcha\">
<input type=\"hidden\" name=\"kp\" value=\"$kp\">
<input type=\"hidden\" name=\"kr\" value=\"$kr\">
<input type=\"hidden\" name=\"ka\" value=\"$ka\">
";
		$formbot="
<input type=\"submit\" value=\"Press to continue\">

</form>

";
		$not='';
		if ($wlreq=='Y') {
			// halfhearted attempt to hide which field is the email field.
			$not="
<fieldset style=\"border:thin solid black;padding:6px;width:100%;\">
<legend><span style=\"font-weight:bold;font-size:1.2em\" >Allow Request</span></legend>
<p>You have been blocked from entering information on this blog. In order to prevent this from happening in the future you
may ask the owner to add your network address to a list that allows you full access.</p>
<p>Please enter your <b>e</b><b>ma</b><b>il</b> <b>add</b><b>re</b><b>ss</b> and a short note requesting access here</p>
<span style=\"color:FFFEFF;\">e</span>-<span style=\"color:FFFDFF;\">ma</span>il for contact(required)<!-- not the message -->: <input type=\"text\" value=\"\" name=\"ke\"><br>
message <!-- not email -->:<br><textarea name=\"km\"></textarea>
</fieldset>
";	
		}
		$captop="
<fieldset style=\"border:thin solid black;padding:6px;width:100%;\">
<legend><span style=\"font-weight:bold;font-size:1.2em\" >Please prove you are not a Robot</span></legend>
	
	
";
		$capbot="
</fieldset>
";

		// now the captchas
		$cap='';
		switch ($chkcaptcha) {
		case 'G':
			// recaptcha
			$recaptchaapisite=$options['recaptchaapisite'];
			$cap="
			<script src=\"https://www.google.com/recaptcha/api.js\" async defer></script>\r\n
			<input type=\"hidden\" name=\"recaptcha\" value=\"recaptcha\">
<div class=\"g-recaptcha\" data-sitekey=\"$recaptchaapisite\"></div>


";

			break;
		case 'S':
			$solvmediaapivchallenge=$options['solvmediaapivchallenge'];
			$cap="
			<script type=\"text/javascript\"
	src=\"http://api.solvemedia.com/papi/challenge.script?k=$solvmediaapivchallenge\">
</script>

<noscript>
	<iframe src=\"http://api.solvemedia.com/papi/challenge.noscript?k=$solvmediaapivchallenge\"
	height=\"300\" width=\"500\" frameborder=\"0\"></iframe><br/>
	<textarea name=\"adcopy_challenge\" rows=\"3\" cols=\"40\">
	</textarea>
	<input type=\"hidden\" name=\"adcopy_response\" value=\"manual_challenge\"/>
</noscript><br>
";

			break;
		case 'A':
		case 'Y':
			// arithmetic
			$n1=rand ( 1 , 9 );
			$n2=rand ( 1 , 9 );
			
			// try a much more nteresting way that can't be generalized
			// use the "since" date from stats
			$seed=5;
			$spdate=$stats['spdate'];
			if (!empty($spdate)) $seed=strtotime($spdate);
			$stupid=$n1+$n2-$seed;
			
			$cap="
<P>Enter the SUM of these two numbers: <span style=\"size:4em;font-weight:bold;\">$n1 + $n2</span><br>
<input name=\"sum\" value=\"\" type=\"text\">
<input type=\"hidden\" name=\"nums\" value=\"$stupid\"><br>
<input type=\"submit\" value=\"Press to continue\">


";	
			break;
		case 'F':
			// future
		default:
			$captop='';
			$capbot='';
			$cap='';
			break;
		}
		
		// have a display
		// need to send it to the display
		if (empty($msg)) $msg=$rejectmessage;
		
		$ansa="
		$msg
		$formtop
		$not
		$captop
		$cap
		$capbot
		$formbot
		";
		wp_die($ansa,"Stop Spammers",array('response' => 200));
		exit();
		
		

	}

	public function kpg_ss_send_email($options=array()) {
		if (!array_key_exists('notify',$options)) return false;
		$notify=$options['notify'];
		$wlreqmail=$options['wlreqmail'];
		if ($notify=='N') return false;
		if ( array_key_exists('ke',$_POST) && !empty($_POST['ke'])) {
			// send wp_mail to sysop
			$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
			$ke=$_POST['ke'];
			if (!is_email($ke))  return false;
			if (empty($ke)) return false;
			$ke=sanitize_text_field($_POST['ke']);
			$km=sanitize_text_field($_POST['km']);
			if (strlen($km)>200) $km=substr($km,0,197).'...';
			$kr=really_clean(sanitize_text_field($_POST['kr']));
			$to=get_option('admin_email');
			if (!empty($wlreqmail)) $to=$wlreqmail;
			$subject='Allow List request from blog '.get_bloginfo('name');
			$ip=kpg_get_ip();

			$message="
Webmaster,
A request has been received from someone who has been marked as a spammer by the STOP SPAMMER plugin.
You have are being notified because you have checked off the box on the settings page indicating that you wanted this email.
The information from the request is:
Time: $now
User IP: ". $ip ."
User email: ". $ke ."
Spam Reason: ". $kr ."
Users Message: ". $km ."

Please be aware that the user has been recognized as a potential spammer. 
Some spam robots are already filling out the request form with a bogus explanation. 


Stop Spammers Plugin";
			$message=wordwrap($message, 70, "\r\n");
			$headers = 'From: '.get_option('admin_email'). "\r\n";
			wp_mail( $to, $subject, $message,$headers );
			$rejectmessage="<h2>Mail sent, thank you</h2>";
			return true;
		}
	}
	public function kpg_ss_add_allow($ip,$options=array(),$stats=array(),$post=array(),$post1=array()) {
		// add to the wlrequest option
		// time,ip,email,author,reasion,info,sname
		$sname=$this->getSname();
		$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
		$ke="";
		if (array_key_exists('ke',$_POST)) {
			$ke=sanitize_text_field($_POST['ke']); // email
		}
		//sfs_debug_msg("in add allow:'$ke'");
		if (empty($ke)) return false;
		if (!is_email($ke)) return false;
		$km=really_clean(sanitize_text_field($_POST['km'])); //user message
		if (strlen($km)>80) $km=substr($km,0,77).'...';
		$kr=really_clean(sanitize_text_field($_POST['kr'])); // reason
		$ka=really_clean(sanitize_text_field($_POST['ka'])); // author
		$req=array($ip,$ke,$ka,$kr,$km,$sname);
		// add to the request list
		$wlrequests=$stats['wlrequests'];
		if (empty($wlrequests)||!is_array($wlrequests)) $wlrequests=array();
		$wlrequests[$now]=$req;
		// save stats
		$stats['wlrequests']=$wlrequests;
		//sfs_debug_msg("added request:'$ke'");
		kpg_ss_set_stats($stats);
		return true;
	} 
	
}
?>