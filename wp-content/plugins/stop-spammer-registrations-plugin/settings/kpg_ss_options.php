<?php

if (!defined('ABSPATH')) exit; // just in case

if(!current_user_can('manage_options')) {
	die('Access Denied');
}
$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
$options=kpg_ss_get_options();
extract($options);




//checks
$nonce='';
if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
if (!empty($nonce) && wp_verify_nonce($nonce,'kpgstopspam_update')) { 
	// update
	// check box items - keep track of these
	$optionlist= array(
	'chkamazon',
	'addtoallowlist',
	'chkadmin',
	'chkaccept',
	'chkbbcode',
	'chkreferer',
	'chkdisp',
	'chklong',
	'chkmulti',
	'chksession',
	'chk404',
	'chkexploits',
	'chkadminlog',
	'chkhosting',
	'chkakismet',
	'filterregistrations'
	);
	foreach ($optionlist as $check) {
		$v='N';
		if(array_key_exists($check,$_POST)) {
			$v=$_POST[$check];
			if ($v!='Y') $v='N';
		} 
		$options[$check]=$v;
	}
	// countries
	
$optionlist=array(
'chkAD','chkAE','chkAF','chkAL','chkAM','chkAR','chkAT','chkAU','chkAX','chkAZ','chkBA','chkBB','chkBD','chkBE','chkBG','chkBH','chkBN','chkBO','chkBR','chkBS','chkBY','chkBZ','chkCA','chkCD','chkCH','chkCL','chkCN','chkCO','chkCR','chkCU','chkCW','chkCY','chkCZ','chkDE','chkDK','chkDO','chkDZ','chkEC','chkEE','chkES','chkEU','chkFI','chkFJ','chkFR','chkGB','chkGE','chkGF','chkGI','chkGP','chkGR','chkGT','chkGU','chkGY','chkHK','chkHN','chkHR','chkHT','chkHU','chkID','chkIE','chkIL','chkIN','chkIQ','chkIR','chkIS','chkIT','chkJM','chkJO','chkJP','chkKE','chkKG','chkKH','chkKR','chkKW','chkKY','chkKZ','chkLA','chkLB','chkLK','chkLT','chkLU','chkLV','chkMD','chkME','chkMK','chkMM','chkMN','chkMO','chkMP','chkMQ','chkMT','chkMV','chkMX','chkMY','chkNC','chkNI','chkNL','chkNO','chkNP','chkNZ','chkOM','chkPA','chkPE','chkPG','chkPH','chkPK','chkPL','chkPR','chkPS','chkPT','chkPW','chkPY','chkQA','chkRO','chkRS','chkRU','chkSA','chkSC','chkSE','chkSG','chkSI','chkSK','chkSV','chkSX','chkSY','chkTH','chkTJ','chkTM','chkTR','chkTT','chkTW','chkUA','chkUK','chkUS','chkUY','chkUZ','chkVC','chkVE','chkVN','chkYE'
);
	foreach ($optionlist as $check) {
		$v='N';
		if(array_key_exists($check,$_POST)) {
			$v=$_POST[$check];
			if ($v!='Y') $v='N';
		} 
		$options[$check]=$v;
	}

	// text options
	if (array_key_exists('sesstime',$_POST)) {
		$sesstime=stripslashes($_POST['sesstime']);
		$options['sesstime']=$sesstime;
	}
	if (array_key_exists('multitime',$_POST)) {
		$multitime=stripslashes($_POST['multitime']);
		$options['multitime']=$multitime;
	}
	if (array_key_exists('multicnt',$_POST)) {
		$multicnt=stripslashes($_POST['multicnt']);
		$options['multicnt']=$multicnt;
	}



	kpg_ss_set_options($options);
	extract($options); // extract again to get the new options
	
	
}
$nonce=wp_create_nonce('kpgstopspam_update');

?>

<div class="wrap">
  <h2>Stop Spammers Options</h2>
  <form method="post" action="" name="kpg">
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
    <fieldset style="border:thin solid black;padding:6px;width:100%;">
    <legend><span style="font-weight:bold;font-size:1.5em" >Prevent Lockouts</span></legend>
    <p>This plugin aggressively checks for spammers and is unforgiving to the point where even you may get locked out of your own blog when you log off and try to log back in. There are two options which help prevent this, but these options can make it easier for a spammer to hack your site.<br/>
      When you are confident that the plugin is working you can uncheck these boxes.</p>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Automatically add admins to Allow List</span></legend>
    <input name="addtoallowlist" type="checkbox" value="Y" <?php if ($addtoallowlist=='Y') echo  "checked=\"checked\"";?>/>
    Whenever an administrative user logs in, the ip address is added to the Allow List. This means that you can't be locked out unless your IP address changes or you log in from a different location. As soon as a login is successful then the IP is white-listed to prevent future problems. Disable this if you think that you will never be locked out.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Check credentials on all login attempts</span></legend>
    <input name="chkadminlog" type="checkbox" value="Y" <?php if ($chkadminlog=='Y') echo  "checked=\"checked\"";?>/>
    Normally the plugin checks for spammers before WordPress can try to log in a user. 
	If you check this box, every attempt to login will be tested for a valid user. 
	This may allow a hacker to guess your user id and password by making thousands of attempts to login. 
	This is turned on initially to prevent you from being locked out of your own blog, 
	but should be unchecked after you verify that the plugin does not think you are a spammer.
    </fieldset>
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;width:100%;">
    <legend><span style="font-weight:bold;font-size:1.5em" >Validate Requests</span></legend>
    Spam robots do not always follow rules. They don't provide the proper request headers or are too quick. These items can be quickly checked. These rules are the most economical way of detecting spammers.
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Block Spam missing the HTTP_ACCEPT header</span></legend>
    <input name="chkaccept" type="checkbox" value="Y" <?php if ($chkaccept=='Y') echo  "checked=\"checked\"";?>/>
    Blocks users who have a missing or incomplete HTTP_ACCEPT header. All browsers provide this header. If a hit on your site is missing the HTTP_ACCEPT header it is because a poorly written robot is trying access your site.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Block invalid HTTP_REFERER</span></legend>
    <input name="chkreferer" type="checkbox" value="Y" <?php if ($chkreferer=='Y') echo  "checked=\"checked\"";?>/>
    When you submit a form, the all browser provide the web page that submitted the form. If this referring page is missing or does not match your website then the submit is probably from a program accessing your site. Some cell phone apps try to log in without the correct header. You may want to disable this function if you log into your website from your mobile device. Test it first - the better written apps provide the referring page.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Deny disposable email addresses</span></legend>
    <input name="chkdisp" type="checkbox" value="Y" <?php if ($chkdisp=='Y') echo  "checked=\"checked\"";?>/>
    Spammers who want to hide their true identities use disposable email address. You can get these from a number of sites. The spammer doesn't have to register. He just picks up any mail anonymously. Legitimate users use their real email address. It is very likely that anyone using a disposable email address is a spammer.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Check for long emails or author name</span></legend>
    <input name="chklong" type="checkbox" value="Y" <?php if ($chklong=='Y') echo  "checked=\"checked\"";?>/>
    Spammers can't resist using very long names and emails. This rejects these if the are over 64 characters in length.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Check for BBCODES</span></legend>
    <input name="chkbbcode" type="checkbox" value="Y" <?php if ($chkbbcode=='Y') echo  "checked=\"checked\"";?>/>
    BBCODES are codes like [url] that spammers like to place in comments. Wordpress does not support bbcodes without a plugin.
    If you have a bbcode plugin then disable this. This will make any comment that has bbcodes as spam.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Check for quick responses </span></legend>
    <input name="chksession" type="checkbox" value="Y" <?php if ($chksession=='Y') echo  "checked=\"checked\"";?>/>
    (disabled if caching is active) The plugin will drop a cookie with the current time in it. When the user enters a comment or tries to log into the system, the time is checked. If the user responds too fast, he is a spammer. If cookies are not supported this is disabled. Use the timeout value below to control the speed. (Stops the most spammers of all the methods listed here.)<br>
    Response Timeout value:
    <input name="sesstime" type="text" value="<?php echo $sesstime;?>" size="2"/>
    his is the time used to determine if a spammer has filled out a form too quickly. Humans take more than 10 seconds, at least, to fill out forms. The default is 4 seconds. If a user takes 4 seconds or less to fill out a form they are not human and are denied. Users who use automatic passwords may show up as false positives so keep this low.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Deny 404 exploit probing</span></legend>
    <input name="chk404" type="checkbox" value="Y" <?php if ($chk404=='Y') echo  "checked=\"checked\"";?>/>
    Robots often search your site for exploitable files. If there is a match to a known exploit URL, this will automatically add the IP address to the Deny List.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Deny IPs detected by Akismet</span></legend>
    <input name="chkakismet" type="checkbox" value="Y" <?php if ($chkakismet=='Y') echo  "checked=\"checked\"";?>/>
    Akismet does a good job detecting spam. If Akismet catches a spammer then the ip address should be added to the bad IP cache. Akismet will continue to block comment spam, but if there is a login or registration attempt from the same IP it will blocked.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Check for Exploits</span></legend>
    <input name="chkexploits" type="checkbox" value="Y" <?php if ($chkexploits=='Y') echo  "checked=\"checked\"";?>/>
    This checks for the PHP eval function and typical SQL injection strings in comments and login attempts. It also checks for JavaScript that may potentially be used for cross domain exploits.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Deny login attempts using 'admin' userid</span></legend>
    <input name="chkadmin" type="checkbox" value="Y" <?php if ($chkadmin=='Y') echo  "checked=\"checked\"";?>/>
    When a spammer starts hitting the login page with 'admin' anywhere in the login id and there is no matching user,
	then it is a spammer trying to figure your password. 
	Deny List immediately. This only works if you have do not have the user using 'admin' in their login id. 
	It is dangerous to have a user name 'admin'! 
	My sites get thousands of hits from robots trying to guess my admin password.
	This has the side effect of preventing users from registering with a user id with the word admin in their user name.
	Users cannot register with 'admin2' or 'superadmin' or 'Administrator'.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Check against list of Ubiquity-Nobis and other Spam Server IPs</span></legend>
    <input name="chkubiquity" type="checkbox" value="Y" <?php if ($chkubiquity=='Y') echo  "checked=\"checked\"";?>/>
    I have a list of hosting companies who tolerate spammers. They are the source of much Comment Spam and login attempts. This blocks many of them.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Check for major Hosting companies and cloud services</span></legend>
    <input name="chkhosting" type="checkbox" value="Y" <?php if ($chkhosting=='Y') echo  "checked=\"checked\"";?>/>
    Your users should come from ISPs only. If a request comes from a web host such as Softlayer, Rackspace, or Amazon AWS, it is likely that the the user is a spammer who is running some spam software to attack your site.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Check for many hits in a short time</span></legend>
    <input name="chkmulti" type="checkbox" value="Y" <?php if ($chkmulti=='Y') echo  "checked=\"checked\"";?>/><br>
	Deny access when there are 
	<select name="multicnt">
		<option val="4" <?php if ($multicnt<=4) echo "selected='selected'";?>>4</option>
		<option val="5" <?php if ($multicnt==5) echo "selected='selected'";?>>5</option>
		<option val="6" <?php if ($multicnt==6) echo "selected='selected'";?>>6</option>
		<option val="7" <?php if ($multicnt==7) echo "selected='selected'";?>>7</option>
		<option val="8" <?php if ($multicnt==8) echo "selected='selected'";?>>8</option>
		<option val="9" <?php if ($multicnt==9) echo "selected='selected'";?>>9</option>
		<option val="10" <?php if ($multicnt>=10) echo "selected='selected'";?>>10</option>
	</select>
	comments or logins in less than 
	<select name="multitime">
		<option val="1" <?php if ($multitime<=1) echo "selected='selected'";?>>1</option>
		<option val="2" <?php if ($multitime==2) echo "selected='selected'";?>>2</option>
		<option val="3" <?php if ($multitime==3) echo "selected='selected'";?>>3</option>
		<option val="4" <?php if ($multitime==4) echo "selected='selected'";?>>4</option>
		<option val="5" <?php if ($multitime==5) echo "selected='selected'";?>>5</option>
		<option val="6" <?php if ($multitime==6) echo "selected='selected'";?>>6</option>
		<option val="7" <?php if ($multitime==7) echo "selected='selected'";?>>7</option>
		<option val="8" <?php if ($multitime==8) echo "selected='selected'";?>>8</option>
		<option val="9" <?php if ($multitime==9) echo "selected='selected'";?>>9</option>
		<option val="10" <?php if ($multitime>=10) echo "selected='selected'";?>>10</option>
	</select>
	minutes.<br>
    Spammers hit your site over and over again. If you get more than 5 hits in 3 minutes, the spammer will be stopped,
	added to the bad cache and shown the challenge page.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Check for Amazon Cloud</span></legend>
    <input name="chkamazon" type="checkbox" value="Y" <?php if ($chkamazon=='Y') echo  "checked=\"checked\"";?>/>
    You can block Comments and Logins from Amazon Cloud Servers using this setting. It may be that good services use
    Amazon Cloud servers so you may not want to use this. Be careful about blocking Amazon. Sometimes you get spam from
    one of their servers, but they shut it down right away.
    </fieldset>
   <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Filter Login Requests</span></legend>
    <input name="filterregistrations" type="checkbox" value="Y" <?php if ($filterregistrations=='Y') echo  "checked=\"checked\"";?>/>
    Some plugins and themes bypass the standard registration forms. If you check this, Stop Spammers will try to intercept the login int the WordPress user login module. This will cause some overhead, but gives Stop Spammers another shot at detecting Spam. This is turned off by default because it could potentially be called at every page load.
    </fieldset>
    <br>
    <fieldset style="border:thin solid black;padding:6px;margin:6px;">
    <legend><span style="font-weight:bold;font-size:1.2em" >Block Countries</span></legend>
	<h4>This does not block the whole country. It only blocks spam sources in a country</h3>
    <p>Blocking countries only blocks the know spam blocks from those countries. I make an attempt to not block Residential IPS in countries
      where spammers are quickly shut down. <em><b>Blocking US will not block Cox, Verizon, At&amp;t, etc. It will block 
      Hosting companies that send out spam that are located in the US.</b></em></p>
    <p>Blocking RU will, however, block most of Russia, because Russian ISPs do not shut down zombie computers in
      residential blocks.</p>
    <p>If you block countries make sure that you have set the Challenge to use a Captcha screen so that legitimate users 
      can get into your site even if blocked.</p>
    <p>The biggest countries can put a strain on memory. US, Russia, India, Ukraine, Brazil, China, and Indonesia (in that order)
	are the source or most spam, but they also take up to a half a meg of memory to load. This may slow things a little
	and in some cases might shut down your blog. I run all of them on SiteGround.com without any issues, but if 
	you are using a free or low budget site to run your blog, there could be a problem.</p>
    <p>Check all:
      <input type="checkbox" name="ss_set" value="1" onclick='var t=kpg.ss_set.checked;var els=document.getElementsByTagName("INPUT");for (index = 0; index < els.length; ++index){if (els[index].type=="checkbox"){if (els[index].name.length==5){els[index].checked=t;}}}'/>
    </p>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkAD" type="checkbox" value="Y" <?php if ($chkAD=="Y") echo "checked=\"checked\"";?>/>Andorra</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkAE" type="checkbox" value="Y" <?php if ($chkAE=="Y") echo "checked=\"checked\"";?>/>United Arab Emirates</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkAF" type="checkbox" value="Y" <?php if ($chkAF=="Y") echo "checked=\"checked\"";?>/>Afghanistan</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkAL" type="checkbox" value="Y" <?php if ($chkAL=="Y") echo "checked=\"checked\"";?>/>Albania</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkAM" type="checkbox" value="Y" <?php if ($chkAM=="Y") echo "checked=\"checked\"";?>/>Armenia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkAR" type="checkbox" value="Y" <?php if ($chkAR=="Y") echo "checked=\"checked\"";?>/>Argentina</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkAT" type="checkbox" value="Y" <?php if ($chkAT=="Y") echo "checked=\"checked\"";?>/>Austria</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkAU" type="checkbox" value="Y" <?php if ($chkAU=="Y") echo "checked=\"checked\"";?>/>Australia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkAX" type="checkbox" value="Y" <?php if ($chkAX=="Y") echo "checked=\"checked\"";?>/>Aland Islands</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkAZ" type="checkbox" value="Y" <?php if ($chkAZ=="Y") echo "checked=\"checked\"";?>/>Azerbaijan</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkBA" type="checkbox" value="Y" <?php if ($chkBA=="Y") echo "checked=\"checked\"";?>/>Bosnia And Herzegovina</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkBB" type="checkbox" value="Y" <?php if ($chkBB=="Y") echo "checked=\"checked\"";?>/>Barbados</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkBD" type="checkbox" value="Y" <?php if ($chkBD=="Y") echo "checked=\"checked\"";?>/>Bangladesh</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkBE" type="checkbox" value="Y" <?php if ($chkBE=="Y") echo "checked=\"checked\"";?>/>Belgium</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkBG" type="checkbox" value="Y" <?php if ($chkBG=="Y") echo "checked=\"checked\"";?>/>Bulgaria</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkBH" type="checkbox" value="Y" <?php if ($chkBH=="Y") echo "checked=\"checked\"";?>/>Bahrain</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkBN" type="checkbox" value="Y" <?php if ($chkBN=="Y") echo "checked=\"checked\"";?>/>Brunei Darussalam</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkBO" type="checkbox" value="Y" <?php if ($chkBO=="Y") echo "checked=\"checked\"";?>/>Bolivia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkBR" type="checkbox" value="Y" <?php if ($chkBR=="Y") echo "checked=\"checked\"";?>/>Brazil</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkBS" type="checkbox" value="Y" <?php if ($chkBS=="Y") echo "checked=\"checked\"";?>/>Bahamas</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkBY" type="checkbox" value="Y" <?php if ($chkBY=="Y") echo "checked=\"checked\"";?>/>Belarus</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkBZ" type="checkbox" value="Y" <?php if ($chkBZ=="Y") echo "checked=\"checked\"";?>/>Belize</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkCA" type="checkbox" value="Y" <?php if ($chkCA=="Y") echo "checked=\"checked\"";?>/>Canada</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkCD" type="checkbox" value="Y" <?php if ($chkCD=="Y") echo "checked=\"checked\"";?>/>Congo, Democratic Republic</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkCH" type="checkbox" value="Y" <?php if ($chkCH=="Y") echo "checked=\"checked\"";?>/>Switzerland</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkCL" type="checkbox" value="Y" <?php if ($chkCL=="Y") echo "checked=\"checked\"";?>/>Chile</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkCN" type="checkbox" value="Y" <?php if ($chkCN=="Y") echo "checked=\"checked\"";?>/>China</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkCO" type="checkbox" value="Y" <?php if ($chkCO=="Y") echo "checked=\"checked\"";?>/>Colombia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkCR" type="checkbox" value="Y" <?php if ($chkCR=="Y") echo "checked=\"checked\"";?>/>Costa Rica</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkCU" type="checkbox" value="Y" <?php if ($chkCU=="Y") echo "checked=\"checked\"";?>/>Cuba</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkCW" type="checkbox" value="Y" <?php if ($chkCW=="Y") echo "checked=\"checked\"";?>/>CuraÃ§ao</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkCY" type="checkbox" value="Y" <?php if ($chkCY=="Y") echo "checked=\"checked\"";?>/>Cyprus</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkCZ" type="checkbox" value="Y" <?php if ($chkCZ=="Y") echo "checked=\"checked\"";?>/>Czech Republic</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkDE" type="checkbox" value="Y" <?php if ($chkDE=="Y") echo "checked=\"checked\"";?>/>Germany</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkDK" type="checkbox" value="Y" <?php if ($chkDK=="Y") echo "checked=\"checked\"";?>/>Denmark</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkDO" type="checkbox" value="Y" <?php if ($chkDO=="Y") echo "checked=\"checked\"";?>/>Dominican Republic</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkDZ" type="checkbox" value="Y" <?php if ($chkDZ=="Y") echo "checked=\"checked\"";?>/>Algeria</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkEC" type="checkbox" value="Y" <?php if ($chkEC=="Y") echo "checked=\"checked\"";?>/>Ecuador</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkEE" type="checkbox" value="Y" <?php if ($chkEE=="Y") echo "checked=\"checked\"";?>/>Estonia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkES" type="checkbox" value="Y" <?php if ($chkES=="Y") echo "checked=\"checked\"";?>/>Spain</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkEU" type="checkbox" value="Y" <?php if ($chkEU=="Y") echo "checked=\"checked\"";?>/>European Union</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkFI" type="checkbox" value="Y" <?php if ($chkFI=="Y") echo "checked=\"checked\"";?>/>Finland</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkFJ" type="checkbox" value="Y" <?php if ($chkFJ=="Y") echo "checked=\"checked\"";?>/>Fiji</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkFR" type="checkbox" value="Y" <?php if ($chkFR=="Y") echo "checked=\"checked\"";?>/>France</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkGB" type="checkbox" value="Y" <?php if ($chkGB=="Y") echo "checked=\"checked\"";?>/>Great Britain</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkGE" type="checkbox" value="Y" <?php if ($chkGE=="Y") echo "checked=\"checked\"";?>/>Georgia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkGF" type="checkbox" value="Y" <?php if ($chkGF=="Y") echo "checked=\"checked\"";?>/>French Guiana</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkGI" type="checkbox" value="Y" <?php if ($chkGI=="Y") echo "checked=\"checked\"";?>/>Gibraltar</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkGP" type="checkbox" value="Y" <?php if ($chkGP=="Y") echo "checked=\"checked\"";?>/>Guadeloupe</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkGR" type="checkbox" value="Y" <?php if ($chkGR=="Y") echo "checked=\"checked\"";?>/>Greece</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkGT" type="checkbox" value="Y" <?php if ($chkGT=="Y") echo "checked=\"checked\"";?>/>Guatemala</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkGU" type="checkbox" value="Y" <?php if ($chkGU=="Y") echo "checked=\"checked\"";?>/>Guam</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkGY" type="checkbox" value="Y" <?php if ($chkGY=="Y") echo "checked=\"checked\"";?>/>Guyana</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkHK" type="checkbox" value="Y" <?php if ($chkHK=="Y") echo "checked=\"checked\"";?>/>Hong Kong</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkHN" type="checkbox" value="Y" <?php if ($chkHN=="Y") echo "checked=\"checked\"";?>/>Honduras</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkHR" type="checkbox" value="Y" <?php if ($chkHR=="Y") echo "checked=\"checked\"";?>/>Croatia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkHT" type="checkbox" value="Y" <?php if ($chkHT=="Y") echo "checked=\"checked\"";?>/>Haiti</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkHU" type="checkbox" value="Y" <?php if ($chkHU=="Y") echo "checked=\"checked\"";?>/>Hungary</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkID" type="checkbox" value="Y" <?php if ($chkID=="Y") echo "checked=\"checked\"";?>/>Indonesia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkIE" type="checkbox" value="Y" <?php if ($chkIE=="Y") echo "checked=\"checked\"";?>/>Ireland</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkIL" type="checkbox" value="Y" <?php if ($chkIL=="Y") echo "checked=\"checked\"";?>/>Israel</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkIN" type="checkbox" value="Y" <?php if ($chkIN=="Y") echo "checked=\"checked\"";?>/>India</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkIQ" type="checkbox" value="Y" <?php if ($chkIQ=="Y") echo "checked=\"checked\"";?>/>Iraq</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkIR" type="checkbox" value="Y" <?php if ($chkIR=="Y") echo "checked=\"checked\"";?>/>Iran, Islamic Republic Of</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkIS" type="checkbox" value="Y" <?php if ($chkIS=="Y") echo "checked=\"checked\"";?>/>Iceland</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkIT" type="checkbox" value="Y" <?php if ($chkIT=="Y") echo "checked=\"checked\"";?>/>Italy</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkJM" type="checkbox" value="Y" <?php if ($chkJM=="Y") echo "checked=\"checked\"";?>/>Jamaica</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkJO" type="checkbox" value="Y" <?php if ($chkJO=="Y") echo "checked=\"checked\"";?>/>Jordan</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkJP" type="checkbox" value="Y" <?php if ($chkJP=="Y") echo "checked=\"checked\"";?>/>Japan</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkKE" type="checkbox" value="Y" <?php if ($chkKE=="Y") echo "checked=\"checked\"";?>/>Kenya</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkKG" type="checkbox" value="Y" <?php if ($chkKG=="Y") echo "checked=\"checked\"";?>/>Kyrgyzstan</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkKH" type="checkbox" value="Y" <?php if ($chkKH=="Y") echo "checked=\"checked\"";?>/>Cambodia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkKR" type="checkbox" value="Y" <?php if ($chkKR=="Y") echo "checked=\"checked\"";?>/>Korea</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkKW" type="checkbox" value="Y" <?php if ($chkKW=="Y") echo "checked=\"checked\"";?>/>Kuwait</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkKY" type="checkbox" value="Y" <?php if ($chkKY=="Y") echo "checked=\"checked\"";?>/>Cayman Islands</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkKZ" type="checkbox" value="Y" <?php if ($chkKZ=="Y") echo "checked=\"checked\"";?>/>Kazakhstan</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkLA" type="checkbox" value="Y" <?php if ($chkLA=="Y") echo "checked=\"checked\"";?>/>Lao People's Democratic Republic</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkLB" type="checkbox" value="Y" <?php if ($chkLB=="Y") echo "checked=\"checked\"";?>/>Lebanon</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkLK" type="checkbox" value="Y" <?php if ($chkLK=="Y") echo "checked=\"checked\"";?>/>Sri Lanka</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkLT" type="checkbox" value="Y" <?php if ($chkLT=="Y") echo "checked=\"checked\"";?>/>Lithuania</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkLU" type="checkbox" value="Y" <?php if ($chkLU=="Y") echo "checked=\"checked\"";?>/>Luxembourg</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkLV" type="checkbox" value="Y" <?php if ($chkLV=="Y") echo "checked=\"checked\"";?>/>Latvia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkMD" type="checkbox" value="Y" <?php if ($chkMD=="Y") echo "checked=\"checked\"";?>/>Moldova</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkME" type="checkbox" value="Y" <?php if ($chkME=="Y") echo "checked=\"checked\"";?>/>Montenegro</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkMK" type="checkbox" value="Y" <?php if ($chkMK=="Y") echo "checked=\"checked\"";?>/>Macedonia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkMM" type="checkbox" value="Y" <?php if ($chkMM=="Y") echo "checked=\"checked\"";?>/>Myanmar</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkMN" type="checkbox" value="Y" <?php if ($chkMN=="Y") echo "checked=\"checked\"";?>/>Mongolia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkMO" type="checkbox" value="Y" <?php if ($chkMO=="Y") echo "checked=\"checked\"";?>/>Macao</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkMP" type="checkbox" value="Y" <?php if ($chkMP=="Y") echo "checked=\"checked\"";?>/>Northern Mariana Islands</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkMQ" type="checkbox" value="Y" <?php if ($chkMQ=="Y") echo "checked=\"checked\"";?>/>Martinique</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkMT" type="checkbox" value="Y" <?php if ($chkMT=="Y") echo "checked=\"checked\"";?>/>Malta</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkMV" type="checkbox" value="Y" <?php if ($chkMV=="Y") echo "checked=\"checked\"";?>/>Maldives</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkMX" type="checkbox" value="Y" <?php if ($chkMX=="Y") echo "checked=\"checked\"";?>/>Mexico</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkMY" type="checkbox" value="Y" <?php if ($chkMY=="Y") echo "checked=\"checked\"";?>/>Malaysia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkNC" type="checkbox" value="Y" <?php if ($chkNC=="Y") echo "checked=\"checked\"";?>/>New Caledonia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkNI" type="checkbox" value="Y" <?php if ($chkNI=="Y") echo "checked=\"checked\"";?>/>Nicaragua</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkNL" type="checkbox" value="Y" <?php if ($chkNL=="Y") echo "checked=\"checked\"";?>/>Netherlands</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkNO" type="checkbox" value="Y" <?php if ($chkNO=="Y") echo "checked=\"checked\"";?>/>Norway</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkNP" type="checkbox" value="Y" <?php if ($chkNP=="Y") echo "checked=\"checked\"";?>/>Nepal</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkNZ" type="checkbox" value="Y" <?php if ($chkNZ=="Y") echo "checked=\"checked\"";?>/>New Zealand</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkOM" type="checkbox" value="Y" <?php if ($chkOM=="Y") echo "checked=\"checked\"";?>/>Oman</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkPA" type="checkbox" value="Y" <?php if ($chkPA=="Y") echo "checked=\"checked\"";?>/>Panama</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkPE" type="checkbox" value="Y" <?php if ($chkPE=="Y") echo "checked=\"checked\"";?>/>Peru</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkPG" type="checkbox" value="Y" <?php if ($chkPG=="Y") echo "checked=\"checked\"";?>/>Papua New Guinea</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkPH" type="checkbox" value="Y" <?php if ($chkPH=="Y") echo "checked=\"checked\"";?>/>Philippines</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkPK" type="checkbox" value="Y" <?php if ($chkPK=="Y") echo "checked=\"checked\"";?>/>Pakistan</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkPL" type="checkbox" value="Y" <?php if ($chkPL=="Y") echo "checked=\"checked\"";?>/>Poland</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkPR" type="checkbox" value="Y" <?php if ($chkPR=="Y") echo "checked=\"checked\"";?>/>Puerto Rico</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkPS" type="checkbox" value="Y" <?php if ($chkPS=="Y") echo "checked=\"checked\"";?>/>Palestinian Territory, Occupied</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkPT" type="checkbox" value="Y" <?php if ($chkPT=="Y") echo "checked=\"checked\"";?>/>Portugal</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkPW" type="checkbox" value="Y" <?php if ($chkPW=="Y") echo "checked=\"checked\"";?>/>Palau</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkPY" type="checkbox" value="Y" <?php if ($chkPY=="Y") echo "checked=\"checked\"";?>/>Paraguay</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkQA" type="checkbox" value="Y" <?php if ($chkQA=="Y") echo "checked=\"checked\"";?>/>Qatar</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkRO" type="checkbox" value="Y" <?php if ($chkRO=="Y") echo "checked=\"checked\"";?>/>Romania</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkRS" type="checkbox" value="Y" <?php if ($chkRS=="Y") echo "checked=\"checked\"";?>/>Serbia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkRU" type="checkbox" value="Y" <?php if ($chkRU=="Y") echo "checked=\"checked\"";?>/>Russian Federation</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkSA" type="checkbox" value="Y" <?php if ($chkSA=="Y") echo "checked=\"checked\"";?>/>Saudi Arabia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkSC" type="checkbox" value="Y" <?php if ($chkSC=="Y") echo "checked=\"checked\"";?>/>Seychelles</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkSE" type="checkbox" value="Y" <?php if ($chkSE=="Y") echo "checked=\"checked\"";?>/>Sweden</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkSG" type="checkbox" value="Y" <?php if ($chkSG=="Y") echo "checked=\"checked\"";?>/>Singapore</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkSI" type="checkbox" value="Y" <?php if ($chkSI=="Y") echo "checked=\"checked\"";?>/>Slovenia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkSK" type="checkbox" value="Y" <?php if ($chkSK=="Y") echo "checked=\"checked\"";?>/>Slovakia</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkSV" type="checkbox" value="Y" <?php if ($chkSV=="Y") echo "checked=\"checked\"";?>/>El Salvador</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkSX" type="checkbox" value="Y" <?php if ($chkSX=="Y") echo "checked=\"checked\"";?>/>Sint Maarten</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkSY" type="checkbox" value="Y" <?php if ($chkSY=="Y") echo "checked=\"checked\"";?>/>Syrian Arab Republic</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkTH" type="checkbox" value="Y" <?php if ($chkTH=="Y") echo "checked=\"checked\"";?>/>Thailand</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkTJ" type="checkbox" value="Y" <?php if ($chkTJ=="Y") echo "checked=\"checked\"";?>/>Tajikistan</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkTM" type="checkbox" value="Y" <?php if ($chkTM=="Y") echo "checked=\"checked\"";?>/>Turkmenistan</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkTR" type="checkbox" value="Y" <?php if ($chkTR=="Y") echo "checked=\"checked\"";?>/>Turkey</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkTT" type="checkbox" value="Y" <?php if ($chkTT=="Y") echo "checked=\"checked\"";?>/>Trinidad And Tobago</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkTW" type="checkbox" value="Y" <?php if ($chkTW=="Y") echo "checked=\"checked\"";?>/>Taiwan</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkUA" type="checkbox" value="Y" <?php if ($chkUA=="Y") echo "checked=\"checked\"";?>/>Ukraine</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkUK" type="checkbox" value="Y" <?php if ($chkUK=="Y") echo "checked=\"checked\"";?>/>United Kingdom</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkUS" type="checkbox" value="Y" <?php if ($chkUS=="Y") echo "checked=\"checked\"";?>/>United States</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkUY" type="checkbox" value="Y" <?php if ($chkUY=="Y") echo "checked=\"checked\"";?>/>Uruguay</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkUZ" type="checkbox" value="Y" <?php if ($chkUZ=="Y") echo "checked=\"checked\"";?>/>Uzbekistan</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkVC" type="checkbox" value="Y" <?php if ($chkVC=="Y") echo "checked=\"checked\"";?>/>Saint Vincent And Grenadines</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkVE" type="checkbox" value="Y" <?php if ($chkVE=="Y") echo "checked=\"checked\"";?>/>Venezuela</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkVN" type="checkbox" value="Y" <?php if ($chkVN=="Y") echo "checked=\"checked\"";?>/>Viet Nam</div>
<div style="float:left;border:black thin solid;margin:2px;padding:2px"> <input name="chkYE" type="checkbox" value="Y" <?php if ($chkYE=="Y") echo "checked=\"checked\"";?>/>Yemen</div>

	</fieldset>
    </fieldset>
    <br>
    <p class="submit">
      <input class="button-primary" value="Save Changes" type="submit" />
    </p>
  </form>
  <p>&nbsp;</p>
</div>
