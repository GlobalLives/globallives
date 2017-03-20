<?php
if (!defined('ABSPATH')) exit; // just in case

if(!current_user_can('manage_options')) {
	die('Access Denied');
}

$stats=kpg_ss_get_stats();
extract($stats);
$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
// counter list - this should be copied from the get option utility
// counters should have the same name as the YN switch for the check.
// I see lots of missing counters here.
$counters=array(
'cntchkcloudflare'=>'pass CloudFlare',
'cntchkgcache'=>'pass Good Cache',
'cntchkakismet'=>'Reported by Akismet',
'cntchkgenallowlist'=>'pass Generated Allow List',
'cntchkgoogle'=>'pass Google',
'cntchkmiscallowlist'=>'pass Allow List',
'cntchkpaypal'=>'pass paypal',
'cntchkscripts'=>'pass scripts',
'cntchkvalidip'=>'pass uncheckable ip',
'cntchkwlem'=>'Allow List email',
'cntchkuserid'=>'Allow userid/author',
'cntchkwlist'=>'pass Allow List ip',
'cntchkyahoomerchant'=>'pass Yahoo merchant',
'cntchk404'=>'404 exploit attempt',
'cntchkaccept'=>'bad or missing accept header',
'cntchkadmin'=>'admin login attempt',
'cntchkadminlog'=>'passed login ok',
'cntchkagent'=>'bad or missing user agent',
'cntchkamazon'=>'Amazon AWS',
'cntchkaws'=>'Amazon AWS allow',
'cntchkbcache'=>'bad cache',
'cntchkblem'=>'Deny List email',
'cntchkuserid'=>'Deny userid/author',
'cntchkblip'=>'Deny List ip',
'cntchkbotscout'=>'botscout',
'cntchkdisp'=>'disposable email',
'cntchkdnsbl'=>'dnsbl hit',
'cntchkexploits'=>'exploit attempt',
'cntchkgooglesafe'=>'google safe browsing',
'cntchkhoney'=>'project honeypot',
'cntchkhosting'=>'known spam host',
'cntchkinvalidip'=>'block invalid ip',
'cntchklong'=>'long email',
'cntchkbbcode'=>'bbcode in request',
'cntchkreferer'=>'bad HTTP_REFERER',
'cntchksession'=>'session speed',
'cntchksfs'=>'Stop Forum Spam',
'cntchkspamwords'=>'spam words',
'cntchktld'=>'email TLD',
'cntchkubiquity'=>'ubiquity servers',
'cntchkmulti'=>'Repeated hits',


'cntchkAD'=>'Andorra',
'cntchkAE'=>'United Arab Emirates',
'cntchkAF'=>'Afghanistan',
'cntchkAL'=>'Albania',
'cntchkAM'=>'Armenia',
'cntchkAR'=>'Argentina',
'cntchkAT'=>'Austria',
'cntchkAU'=>'Australia',
'cntchkAX'=>'Aland Islands',
'cntchkAZ'=>'Azerbaijan',
'cntchkBA'=>'Bosnia And Herzegovina',
'cntchkBB'=>'Barbados',
'cntchkBD'=>'Bangladesh',
'cntchkBE'=>'Belgium',
'cntchkBG'=>'Bulgaria',
'cntchkBH'=>'Bahrain',
'cntchkBN'=>'Brunei Darussalam',
'cntchkBO'=>'Bolivia',
'cntchkBR'=>'Brazil',
'cntchkBS'=>'Bahamas',
'cntchkBY'=>'Belarus',
'cntchkBZ'=>'Belize',
'cntchkCA'=>'Canada',
'cntchkCD'=>'Congo, Democratic Republic',
'cntchkCH'=>'Switzerland',
'cntchkCL'=>'Chile',
'cntchkCN'=>'China',
'cntchkCO'=>'Colombia',
'cntchkCR'=>'Costa Rica',
'cntchkCU'=>'Cuba',
'cntchkCW'=>'CuraÃ§ao',
'cntchkCY'=>'Cyprus',
'cntchkCZ'=>'Czech Republic',
'cntchkDE'=>'Germany',
'cntchkDK'=>'Denmark',
'cntchkDO'=>'Dominican Republic',
'cntchkDZ'=>'Algeria',
'cntchkEC'=>'Ecuador',
'cntchkEE'=>'Estonia',
'cntchkES'=>'Spain',
'cntchkEU'=>'European Union',
'cntchkFI'=>'Finland',
'cntchkFJ'=>'Fiji',
'cntchkFR'=>'France',
'cntchkGB'=>'Great Britain',
'cntchkGE'=>'Georgia',
'cntchkGF'=>'French Guiana',
'cntchkGI'=>'Gibraltar',
'cntchkGP'=>'Guadeloupe',
'cntchkGR'=>'Greece',
'cntchkGT'=>'Guatemala',
'cntchkGU'=>'Guam',
'cntchkGY'=>'Guyana',
'cntchkHK'=>'Hong Kong',
'cntchkHN'=>'Honduras',
'cntchkHR'=>'Croatia',
'cntchkHT'=>'Haiti',
'cntchkHU'=>'Hungary',
'cntchkID'=>'Indonesia',
'cntchkIE'=>'Ireland',
'cntchkIL'=>'Israel',
'cntchkIN'=>'India',
'cntchkIQ'=>'Iraq',
'cntchkIR'=>'Iran, Islamic Republic Of',
'cntchkIS'=>'Iceland',
'cntchkIT'=>'Italy',
'cntchkJM'=>'Jamaica',
'cntchkJO'=>'Jordan',
'cntchkJP'=>'Japan',
'cntchkKE'=>'Kenya',
'cntchkKG'=>'Kyrgyzstan',
'cntchkKH'=>'Cambodia',
'cntchkKR'=>'Korea',
'cntchkKW'=>'Kuwait',
'cntchkKY'=>'Cayman Islands',
'cntchkKZ'=>'Kazakhstan',
'cntchkLA'=>"Lao People's Democratic Republic",
'cntchkLB'=>'Lebanon',
'cntchkLK'=>'Sri Lanka',
'cntchkLT'=>'Lithuania',
'cntchkLU'=>'Luxembourg',
'cntchkLV'=>'Latvia',
'cntchkMD'=>'Moldova',
'cntchkME'=>'Montenegro',
'cntchkMK'=>'Macedonia',
'cntchkMM'=>'Myanmar',
'cntchkMN'=>'Mongolia',
'cntchkMO'=>'Macao',
'cntchkMP'=>'Northern Mariana Islands',
'cntchkMQ'=>'Martinique',
'cntchkMT'=>'Malta',
'cntchkMV'=>'Maldives',
'cntchkMX'=>'Mexico',
'cntchkMY'=>'Malaysia',
'cntchkNC'=>'New Caledonia',
'cntchkNI'=>'Nicaragua',
'cntchkNL'=>'Netherlands',
'cntchkNO'=>'Norway',
'cntchkNP'=>'Nepal',
'cntchkNZ'=>'New Zealand',
'cntchkOM'=>'Oman',
'cntchkPA'=>'Panama',
'cntchkPE'=>'Peru',
'cntchkPG'=>'Papua New Guinea',
'cntchkPH'=>'Philippines',
'cntchkPK'=>'Pakistan',
'cntchkPL'=>'Poland',
'cntchkPR'=>'Puerto Rico',
'cntchkPS'=>'Palestinian Territory, Occupied',
'cntchkPT'=>'Portugal',
'cntchkPW'=>'Palau',
'cntchkPY'=>'Paraguay',
'cntchkQA'=>'Qatar',
'cntchkRO'=>'Romania',
'cntchkRS'=>'Serbia',
'cntchkRU'=>'Russian Federation',
'cntchkSA'=>'Saudi Arabia',
'cntchkSC'=>'Seychelles',
'cntchkSE'=>'Sweden',
'cntchkSG'=>'Singapore',
'cntchkSI'=>'Slovenia',
'cntchkSK'=>'Slovakia',
'cntchkSV'=>'El Salvador',
'cntchkSX'=>'Sint Maarten',
'cntchkSY'=>'Syrian Arab Republic',
'cntchkTH'=>'Thailand',
'cntchkTJ'=>'Tajikistan',
'cntchkTM'=>'Turkmenistan',
'cntchkTR'=>'Turkey',
'cntchkTT'=>'Trinidad And Tobago',
'cntchkTW'=>'Taiwan',
'cntchkUA'=>'Ukraine',
'cntchkUK'=>'United Kingdom',
'cntchkUS'=>'United States',
'cntchkUY'=>'Uruguay',
'cntchkUZ'=>'Uzbekistan',
'cntchkVC'=>'Saint Vincent And Grenadines',
'cntchkVE'=>'Venezuela',
'cntchkVN'=>'Viet Nam',
'cntchkYE'=>'Yemen',

'cntcap'=>'Passed Captcha', // captha success
'cntncap'=>'failed Captcha', // captha not success	
'cntpass'=>'Total Pass', // passed
);

$message="";

$nonce='';
if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
if (wp_verify_nonce($nonce,'kpgstopspam_update')) { 
	if (array_key_exists('clear',$_POST)) {
		foreach($counters as $v1=>$v2) {
			$stats[$v1]=0;
		}	
		$addonstats=array();
		$stats['addonstats']=$addonstats;
		$msg="Summary Cleared";
		kpg_ss_set_stats($stats);
		extract($stats); // extract again to get the new options
	}
	if (array_key_exists('update_total',$_POST)) {
		$stats['spmcount']=$_POST['spmcount'];
		$stats['spmdate']=$_POST['spmdate'];
		kpg_ss_set_stats($stats);
		extract($stats); // extract again to get the new options
	}
}

$nonce=wp_create_nonce('kpgstopspam_update');

?>
<div class="wrap" style="position:relative;">
<h2>Stop Spammers Plugin Version <?php echo KPG_SS_VERSION;?></h2>

<?php
if (!empty($msg)) echo "<h3>$msg</h3>";


$current_user_name=wp_get_current_user()->user_login;

if ($current_user_name=='admin') {
	echo "<p style=\"color:red;font-style::italic;\">You are using the admin id \"admin\". This is 
	an invitation to hackers to try and guess your password. Please change this.<br>
	Here is discussion on WordPress.org:
	<a href=\"https://wordpress.org/support/topic/how-to-change-admin-username?replies=4\" target=\"_blank\">How to Change Admin Username</a>
	</p>";
}
$showcf=false; // hidethis for now.
if ($showcf && array_key_exists('HTTP_CF_CONNECTING_IP',$_SERVER)&& !function_exists( 'cloudflare_init' ) &&!defined('W3TC') ){
	echo "<p style=\"color:red;font-style::italic;\">
	CloudFlare Remote IP address detected. Please install the <a href=\"http://wordpress.org/plugins/cloudflare/\" target=\"_blank\">CloudFlare Plugin</a>.
	This plugin works best with the CloudFlare plugin when yout website is using CloudFlare.
	</p>";
}
if ($spmcount>0) {
	?>
	<script type="text/javascript">
	function showcheat() {
		var el=document.getElementById("cheater");
		el.style.display="block";
		return false;
	}
	</script>
	<h3>Stop Spammers in total has stopped <a style="text-decoration:none;color:black;" href="" onclick="showcheat();return false;"><?php echo $spmcount; ?></a> spammers since <?php echo $spmdate; ?>.</h3>
	<div id="cheater" style="display:none;">
		This is cheating! enter a new Total Spam Count:<br>
	<form method="post" action="">
	<input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
	<input type="hidden" name="update_total" value="update total" />
	Count:<input type="text" name="spmcount" value="<?php echo $spmcount;?>" /><br>
	Date: <input type="text" name="spmdate" value="<?php echo $spmdate;?>" /><br>
	<p class="submit" style="clear:both;">
	<input class="button-primary" value="update total spam" type="submit" />
	</p>

	</form>
		</p>
	
	</div>
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
$summry='';
foreach($counters as $v1=>$v2) {
	if (!empty($stats[$v1])) {
		$summry.= "<div style='float:left;border:thin black solid;margin:4px;padding:3px;'>$v2: ".$stats[$v1]."</div>";
	} else {
		// echo "  $v1 - $v2 , ";
	}

}
$addonstats=$stats['addonstats'];
foreach($addonstats as $key=>$data) {
	// count is in data[0] and use the plugin name
	$summry.= "<div style='float:left;border:thin black solid;margin:4px;padding:3px;'>$key: ".$data[0]."</div>";
}
if (!empty($summry)) {
	?>
	<hr/>
	<h3></h3>
	<fieldset style="border:thin solid black">
	<legend><span style="font-weight:bold;font-size:1.2em" >Summary of Spam</span></legend>

	<?php
	echo $summry;
	?>

	<form method="post" action="">
	<input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
	<input type="hidden" name="clear" value="clear summary" />
	<p class="submit" style="clear:both;">
	<input class="button-primary" value="Clear Summary" type="submit" />
	</p>

	</form>
	</fieldset>

	<?php
}

$ip =kpg_get_ip();
?>
<p>Your current IP address is: <span style="font-weight:bold;font-size:1.1em;"><?php echo $ip;?>.</span><p>
<?php
// check the ip to see if we are local

$ansa=be_load('chkvalidip',kpg_get_ip());
if ($ansa==false) {
	$ansa=be_load('chkcloudflare',kpg_get_ip());
}
if ($ansa!==false) {
	?>
	<p>This address is invalid for testing for the following reason: 
	<span style="font-weight:bold;font-size:1.2em;"><?php echo $ansa;?></span>.<br>
	If you working on a local installation of Wordpress, this might be OK. However, if the plugin reports that your IP is invalid it may be because you are using CloudFlare or a proxy server to access this page. This will make it impossible for the plugin to check IP addresses. You may want to go to the Stop Spammers Testing menu option in order to test all possible reasons that your IP is not appearing as the IP of the machine that your using to browse this site.<br>
	It is possible to use the plugin if this problem appears, but most checking functions will be turned off. The plugin will still perform spam checks which do not require an IP.<br>
	If the error says that this is a CloudFlare ip address, you can fix this by installing the cloudflare plugin. If you use CloudFlare to protect and speed up your site then you MUST install the CloudFlare plugin. This plugin will be crippled until you install it.
	
	<p>
	<?php

}

// need the current guy
$sname='';
if(isset($_SERVER['REQUEST_URI'])) $sname=$_SERVER["REQUEST_URI"];	
if (empty($sname)) {
	$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
	$sname=$_SERVER["SCRIPT_NAME"];	
}
if (strpos($sname,'?')!==false) $sname=substr($sname,0,strpos($sname,'?'));

?>

<div style="float:left;width:calc(100% - 245px);">
<h3>Plugin Functions</h3>
<ol>
<li> <a href="?page=stop_spammers">Summary</a>: This checks to see if there may be problems from your current incoming IP address and displays a summary of events.</li>
<li> <a href="?page=ss_allowrequests">Allow Requests</a>: Displays users who were denied and
filled out the form requesting access to your site.</li>
<li> <a href="?page=ss_reports">Log Report</a>: Shows details of the most recent events detected by Stop Spammers.
</li>
<li> <a href="?page=ss_cache">Cache</a>: Shows the cache of recently detected events.
</li>
<li> <a href="?page=ss_options">Protection Options</a>: This has all the options for checking for spam an logins. You can also block whole countries.
</li>
<li> <a href="?page=ss_allow_list">Allow Options</a>: Here you can set up your Allow List to allow ip addresses to login and leave comments on your site, without being checked for spam. It also sets up the options which you can use to allow certain kinds of users into your site, even though they may trigger spam detection.
</li>
<li> <a href="?page=ss_deny_list">Block Lists</a>: This is where you set up your Deny List for IPs and email. It also allows you to enter spam words and phrases that trigger spam.
</li>
<li> <a href="?page=kpg_ss_webservices_settings">Web Services</a>: This is where you enter the API keys for StopForumSpam.com and other web checking services. You don't need to have these set for the plugin to work, but if you do, you will have better protection and the ability to report spam. 
</li>
<li> <a href="?page=ss_challenge">Challenge and Deny</a>: This sets up Captcha and Notification options. You can give users who trigger the plugin a second chance to use a captcha. Supports Google ReCaptcha and Solve Media Captcha. 
</li>
<li> <a href="?page=ss_threat_scan">Threat Scan</a>: Searches options and local system files for exploits. This can sometimes detect if your site has been hacked.
</li>
<li> <a href="?page=ss_option_maint">WP Options Maintenance</a>: This lists all of the loaded options that it thinks may be from plugins. This lets you find and inspect the options set by other plugins and will let you delete orphan options from plugins that have been deleted, but did not clean up their mess.
</li>

<li> <a href="?page=ss_diagnostics">Diagnostics</a>: You can use this to test an IP, Email or comment against all of the options. This can tell you more about why an IP address might fail. It will also show you any options that might crash the plugin on your site due to system settings.
</li>
<li> <a href="?page=ss_addons">Addons</a>: Lists currently installed addons.
</li>
<li> <a href="?page=ss_contribute">Contribute</a>: The plugin is free, but I would appreciate your support. 
</li>
</ol>

<h4>More of Keith's Plugins at WordPress.org</h4>
<p>These are plugins are written for myself. Many I've used and then stopped using. Some are very useful.<br>
I do not upload to wordpress very often so check the Beta Plugins page to see if there is a newer version on my site.</p>
<ol>
<li><a href="https://wordpress.org/plugins/akismet-wedge-for-mu-plugin/">Akismet Wedge for MU Plugin</a>: Globalizes the akismet api key so it can be used across blogs.</li>
<li><a href="https://wordpress.org/plugins/amazon-product-link-widget/">Amazon Product Link Widget</a>: Displays a random Amazon link box from a list of asin product ids.</li>
<li><a href="https://wordpress.org/plugins/collapse-page-and-category-plugin/">Collapse Page and Category Plugin</a>: Uses CSS and Javascript to collapse children on Pages and Categories or any Unordered List that uses the css class &#039;children&#039;.</li>
<li><a href="https://wordpress.org/plugins/custom-post-type-list-widget/">Custom Post Type List Widget</a>: Displays a list of Custom Posts Type entries in a sidebar.</li>
<li><a href="https://wordpress.org/plugins/email-php-errors-plugin/">Email PHP Errors Plugin</a>: Catches php errors, and reports last 25. Can also email the results. Good for debugging themes and plugins.</li>
<li><a href="https://wordpress.org/plugins/exit-screen-plugin/">Exit Screen Plugin</a>: Prompts the user when they leave your blog, showing a &quot;last chance&quot; exit page behind the prompt.</li>
<li><a href="https://wordpress.org/plugins/feed-ads-plugin/">Feed Ads Plugin</a>: Feed Ads will randomly insert text at the end of rss entries. Useful for ads, taglines, news, or links.</li>
<li><a href="https://wordpress.org/plugins/keywords-widget/">Keywords Widget</a>: Watches for hits arriving from search engines. Puts a list of recent keywords as a widget.</li>
<li><a href="https://wordpress.org/plugins/looser-search-plugin/">Looser Search Plugin</a>: A more more relaxed search. It is much more like to find something right or almost right than the built-in wordpress search.</li>
<li><a href="https://wordpress.org/plugins/mu-global-options-plugin/">MU Global Options Plugin</a>: Plugin that uses options from main blog in other blogs.</li>
<li><a href="https://wordpress.org/plugins/mu-manage-comments-plugin/">MU Manage Comments Plugin</a>: Lists on one page unmoderated and spam comments for all MU blogs for moderation.</li>
<li><a href="https://wordpress.org/plugins/no-right-click-images-plugin/">No Right Click Images Plugin</a>: Disables right click context menu on images to help deter leeches from glomming images.</li>
<li><a href="https://wordpress.org/plugins/open-in-new-window-plugin/">Open in New Window Plugin</a>: Opens external links in a new window, keeping your blog page in the browser so you don&#039;t lose surfers to another site.</li>
<li><a href="https://wordpress.org/plugins/outbound-click-tracker/">Outbound Click Tracker Plugin</a>: Tracks clicks on outbound links without Google Analytics. Find out where your readers are going.</li>
<li><a href="https://wordpress.org/plugins/permalink-finder/">Permalink Finder Plugin</a>: Never get a 404 page not found again. If you have restructured or moved your blog, this plugin will find the right post or page every time.</li>
<li><a href="https://wordpress.org/plugins/recent-google-searches-widget/">Recent Google Searches Widget</a>: Watches for hits arriving from search engines. Puts a list of recent queries as a widget.</li>
<li><a href="https://wordpress.org/plugins/remove-color-widget/">Remove Color Widget</a>: Removes text styling from blogs displaying black text on white background.</li>
<li><a href="https://wordpress.org/plugins/search-engine-keywords-related-posts-widget/">Search Engine Keywords Related Posts Widget</a>: Watches for hits arriving from search engines. Displays a list of posts related to the search engine keywords.</li>
<li><a href="https://wordpress.org/plugins/shortcode-grab-bag/">ShortCode Grab Bag Plugin</a>: List active shortcodes on post edit page with drag and drop to post. Includes a few simple shortcodes.</li>
<li><a href="https://wordpress.org/plugins/simple-rich-text-widget/">Simple Rich Text Widget</a>: A simple rich text format widget for adding formatted HTML to sidebars.</li>
<li><a href="https://wordpress.org/plugins/static-pages/">Static Pages</a>: Automatically wrap a WordPress Theme around static HTML pages, pdf files, audio or video files. Add the look and feel of WordPress to legacy static files</li>
<li><a href="https://wordpress.org/plugins/stop-spammer-registrations-plugin/">Stop Spammers Spam Prevention</a>: Aggressive anti-spam plugin that eliminates comment spam, trackback spam, contact form spam and registration spam. Protects against malicious attacks.</li>
<li><a href="https://wordpress.org/plugins/youtube-poster-plugin/">YouTube Poster Plugin</a>: Runs searches on YouTube and automatically creates a blog post for each video found.</li>

</ol>
<p>There are many more plugins that are not available on the Wordpress site. These can be updated through the Update plugin I've written. It will be available and ready through the addons page here.</p>
</div>
<div style="width:240px;float:right;">
Powered By <a href="http://www.siteground.com/recommended?referrer_id=6975888" target="_blank">SiteGround.com</a>


<h4>Plugin Support Forum</h4>
<ol>
<?php display_feed('https://wordpress.org/support/rss/plugin/stop-spammer-registrations-plugin'); ?>
</ol>
<h4>Posts at BlogsEye.com</h4>
<ol>
<?php display_feed('http://www.blogseye.com/feed.xml'); ?>
</ol>

</div>
</div>
<?php

function display_feed($url) {
	// quick and dirty get_rss replacement
include_once( ABSPATH . WPINC . '/feed.php' );

// Get a SimplePie feed object from the specified feed source.
$rss = fetch_feed( $url );
$maxitems=0;
if ( ! is_wp_error( $rss ) ) { // Checks that the object is created correctly

    // Figure out how many total items there are, but limit it to 5. 
    $maxitems = $rss->get_item_quantity( 10 ); 

    // Build an array of all the items, starting with element 0 (first element).
    $rss_items = $rss->get_items( 0, $maxitems );

}
?>

<ul>
    <?php if ( $maxitems == 0 ) { ?>
        <li><?php _e( 'No items', 'my-text-domain' ); ?></li>
    <?php } else { ?>
        <?php // Loop through each feed item and display each item as a hyperlink. ?>
        <?php foreach ( $rss_items as $item ) { ?>
            <li>
                <a href="<?php echo esc_url( $item->get_permalink() ); ?>"
                    title="<?php printf( __( 'Posted %s', 'my-text-domain' ), $item->get_date('j F Y | g:i a') ); ?>">
                    <?php echo esc_html( $item->get_title() ); ?>
                </a>
            </li>
        <?php } ?>
    <?php } ?>
</ul>
	
<?php	
	
}
// end of module

?>