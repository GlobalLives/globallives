<?php
require_once 'functions.php';

if ( !current_user_can( 'manage_options' ) ) {
	return;
}
if (isset($_REQUEST['Clear'])){
	ga_dash_clear_cache();
	?><div class="updated"><p><strong><?php _e('Cleared Cache.', 'ga-dash' ); ?></strong></p></div>  
	<?php
}
if (isset($_REQUEST['Reset'])){

	ga_dash_reset_token();
	?><div class="updated"><p><strong><?php _e('Token Reseted.', 'ga-dash'); ?></strong></p></div>  
	<?php
}else if(ga_dash_safe_get('ga_dash_hidden') == 'Y') {  
        //Form data sent  
        $apikey = ga_dash_safe_get('ga_dash_apikey');  
        if ($apikey){
			update_option('ga_dash_apikey', sanitize_text_field($apikey));  
        }
		
        $clientid = ga_dash_safe_get('ga_dash_clientid');
        if ($clientid){		
			update_option('ga_dash_clientid', sanitize_text_field($clientid));  
        }
		
        $clientsecret = ga_dash_safe_get('ga_dash_clientsecret');  
        if ($clientsecret){			
			update_option('ga_dash_clientsecret', sanitize_text_field($clientsecret));  
		}
		
        $dashaccess = ga_dash_safe_get('ga_dash_access');  
        update_option('ga_dash_access', $dashaccess);
		
		$ga_dash_tableid_jail = ga_dash_safe_get('ga_dash_tableid_jail');  
        update_option('ga_dash_tableid_jail', $ga_dash_tableid_jail); 
		
		$ga_dash_pgd = ga_dash_safe_get('ga_dash_pgd');
		update_option('ga_dash_pgd', $ga_dash_pgd);

		$ga_dash_rd = ga_dash_safe_get('ga_dash_rd');
		update_option('ga_dash_rd', $ga_dash_rd);

		$ga_dash_sd = ga_dash_safe_get('ga_dash_sd');
		update_option('ga_dash_sd', $ga_dash_sd);		
		
		$ga_dash_map = ga_dash_safe_get('ga_dash_map');
		update_option('ga_dash_map', $ga_dash_map);
		
		$ga_dash_traffic = ga_dash_safe_get('ga_dash_traffic');
		update_option('ga_dash_traffic', $ga_dash_traffic);		

		$ga_dash_frontend = ga_dash_safe_get('ga_dash_frontend');
		update_option('ga_dash_frontend', $ga_dash_frontend);		
		
		$ga_dash_style = ga_dash_safe_get('ga_dash_style');
		update_option('ga_dash_style', $ga_dash_style);
		
		$ga_dash_jailadmins = ga_dash_safe_get('ga_dash_jailadmins');
		update_option('ga_dash_jailadmins', $ga_dash_jailadmins);
		
		$ga_dash_cachetime = ga_dash_safe_get('ga_dash_cachetime');
		update_option('ga_dash_cachetime', $ga_dash_cachetime);
		
		$ga_dash_tracking = ga_dash_safe_get('ga_dash_tracking');
		update_option('ga_dash_tracking', $ga_dash_tracking);		

		$ga_dash_tracking_type = ga_dash_safe_get('ga_dash_tracking_type');
		update_option('ga_dash_tracking_type', $ga_dash_tracking_type);			
		
		$ga_dash_default_ua = ga_dash_safe_get('ga_dash_default_ua');
		update_option('ga_dash_default_ua', $ga_dash_default_ua);

		$ga_dash_anonim = ga_dash_safe_get('ga_dash_anonim');
		update_option('ga_dash_anonim', $ga_dash_anonim);

		$ga_dash_userapi = ga_dash_safe_get('ga_dash_userapi');
		update_option('ga_dash_userapi', $ga_dash_userapi);			
		
		if (!isset($_REQUEST['Clear']) AND !isset($_REQUEST['Reset'])){
			?>  
			<div class="updated"><p><strong><?php _e('Options saved.', 'ga-dash'); ?></strong></p></div>  
			<?php
		}
    }else if(ga_dash_safe_get('ga_dash_hidden') == 'A') {
        $apikey = ga_dash_safe_get('ga_dash_apikey');  
        if ($apikey){
			update_option('ga_dash_apikey', sanitize_text_field($apikey));  
        }
		
        $clientid = ga_dash_safe_get('ga_dash_clientid');
        if ($clientid){		
			update_option('ga_dash_clientid', sanitize_text_field($clientid));  
        }
		
        $clientsecret = ga_dash_safe_get('ga_dash_clientsecret');  
        if ($clientsecret){			
			update_option('ga_dash_clientsecret', sanitize_text_field($clientsecret));  
		}

		$ga_dash_userapi = ga_dash_safe_get('ga_dash_userapi');
		update_option('ga_dash_userapi', $ga_dash_userapi);			
	}
	
if (isset($_REQUEST['Authorize'])){
	$adminurl = admin_url("#ga-dash-widget");
	echo '<script> window.location="'.$adminurl.'"; </script> ';
}
	
if(!get_option('ga_dash_access')){
	update_option('ga_dash_access', "manage_options");	
}

if(!get_option('ga_dash_style')){
	update_option('ga_dash_style', "blue");	
}

$apikey = get_option('ga_dash_apikey');  
$clientid = get_option('ga_dash_clientid');  
$clientsecret = get_option('ga_dash_clientsecret');  
$dashaccess = get_option('ga_dash_access'); 
$ga_dash_tableid_jail = get_option('ga_dash_tableid_jail');
$ga_dash_pgd = get_option('ga_dash_pgd');
$ga_dash_rd = get_option('ga_dash_rd');
$ga_dash_sd = get_option('ga_dash_sd');
$ga_dash_map = get_option('ga_dash_map');
$ga_dash_traffic = get_option('ga_dash_traffic');
$ga_dash_frontend = get_option('ga_dash_frontend');
$ga_dash_style = get_option('ga_dash_style');
$ga_dash_cachetime = get_option('ga_dash_cachetime');
$ga_dash_jailadmins = get_option('ga_dash_jailadmins');
$ga_dash_tracking = get_option('ga_dash_tracking');
$ga_dash_tracking_type = get_option('ga_dash_tracking_type');
$ga_dash_default_ua = get_option('ga_dash_default_ua');
$ga_dash_anonim = get_option('ga_dash_anonim');
$ga_dash_userapi = get_option('ga_dash_userapi');

if ( is_rtl() ) {
	$float_main="right";
	$float_note="left";
}else{
	$float_main="left";
	$float_note="right";	
}

?>  
<div class="wrap">
<div style="width:70%;float:<?php echo $float_main; ?>;">  
    <?php echo "<h2>" . __( 'Google Analytics Dashboard Settings', 'ga-dash' ) . "</h2>"; ?>  
        <form name="ga_dash_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
		<?php echo "<h3>". __( 'Google Analytics API', 'ga-dash' )."</h3>"; ?>  
        <?php echo "<i>".__("You should watch this", 'ga-dash')." <a href='http://www.deconf.com/en/projects/google-analytics-dashboard-for-wordpress/' target='_blank'>". __("Step by step video tutorial")."</a> ".__("before proceeding to authorization", 'ga-dash').". ".__("To authorize this application using our API Project, press the", 'ga_dash')." <b>".__("Authorize Application", 'ga-dash')."</b> ".__(" button. If you want to authorize it using your own API Project, check the option bellow and enter your project credentials before pressing the", 'ga-dash')." <b>".__("Authorize Application", 'ga-dash')."</b> ".__("button.", 'ga-dash')."</i>";?>
		<p><input name="ga_dash_userapi" type="checkbox" id="ga_dash_userapi" onchange="this.form.submit()" value="1"<?php if (get_option('ga_dash_userapi')) echo " checked='checked'"; ?>  /><?php echo "<b>".__(" use your own API Project credentials", 'ga-dash' )."</b>"; ?></p>
		<?php
		if (get_option('ga_dash_userapi')){?>
			<p><?php echo "<b>".__("API Key:", 'ga-dash')." </b>"; ?><input type="text" name="ga_dash_apikey" value="<?php echo $apikey; ?>" size="61"></p>  
			<p><?php echo "<b>".__("Client ID:", 'ga-dash')." </b>"; ?><input type="text" name="ga_dash_clientid" value="<?php echo $clientid; ?>" size="60"></p>  
			<p><?php echo "<b>".__("Client Secret:", 'ga-dash')." </b>"; ?><input type="text" name="ga_dash_clientsecret" value="<?php echo $clientsecret; ?>" size="55"></p>  
			<?php echo "<i>".__("Old users should also follow this", 'ga-dash')." <a href='http://www.deconf.com/en/projects/google-analytics-dashboard-for-wordpress/' target='_blank'>". __("step by step video tutorial")."</a> ".__(", there are some major changes in this version, if you want to use your own API Project, you should delete your old API Project and create a new one!", 'ga-dash')."</i>";?>
		<?php }?>
		<p><?php 
			if (get_option('ga_dash_token')){
				echo "<input type=\"submit\" name=\"Reset\" class=\"button button-primary\" value=\"".__("Clear Authorization", 'ga-dash')."\" />";
				?> <input type="submit" name="Clear" class="button button-primary" value="<?php _e('Clear Cache', 'ga-dash' ) ?>" /><?php		
				echo '<input type="hidden" name="ga_dash_hidden" value="Y">';  
			} else{
				echo "<input type=\"submit\" name=\"Authorize\" class=\"button button-primary\" value=\"".__("Authorize Application", 'ga-dash')."\" />";
				?> <input type="submit" name="Clear" class="button button-primary" value="<?php _e('Clear Cache', 'ga-dash' ) ?>" /><?php
				echo '<input type="hidden" name="ga_dash_hidden" value="A">';
				echo "</form>";
				_e("(the rest of the settings will show up after completing the authorization process)", 'ga-dash' );
				echo "</div>";
				?>
				<div class="note" style="float:<?php echo $float_note; ?>;text-align:<?php echo $float_main; ?>;"> 
						<center>
							<h3><?php _e("Setup Tutorial",'ga-dash') ?></h3>
							<a href="http://www.deconf.com/en/projects/google-analytics-dashboard-for-wordpress/" target="_blank"><img src="../wp-content/plugins/google-analytics-dashboard-for-wp/img/video-tutorial.png" width="95%" /></a>
						</center>
						<center>
							<br /><h3><?php _e("Support Links",'ga-dash') ?></h3>
						</center>			
						<ul>
							<li><a href="http://www.deconf.com/en/projects/google-analytics-dashboard-for-wordpress/" target="_blank"><?php _e("Google Analytics Dashboard Official Page",'ga-dash') ?></a></li>
							<li><a href="http://wordpress.org/support/plugin/google-analytics-dashboard-for-wp" target="_blank"><?php _e("Google Analytics Dashboard Wordpress Support",'ga-dash') ?></a></li>
							<li><a href="http://forum.deconf.com/en/wordpress-plugins-f182/" target="_blank"><?php _e("Google Analytics Dashboard on Deconf Forum",'ga-dash') ?></a></li>			
						</ul>
						<center>
							<br /><h3><?php _e("Useful Plugins",'ga-dash') ?></h3>
						</center>			
						<ul>
							<li><a href="http://www.deconf.com/en/projects/youtube-analytics-dashboard-for-wordpress/" target="_blank"><?php _e("YouTube Analytics Dashboard",'ga-dash') ?></a></li>
							<li><a href="http://www.deconf.com/en/projects/google-adsense-dashboard-for-wordpress/" target="_blank"><?php _e("Google Adsense Dashboard",'ga-dash') ?></a></li>
							<li><a href="http://www.deconf.com/en/projects/clicky-analytics-plugin-for-wordpress/" target="_blank"><?php _e("Clicky Analytics",'ga-dash') ?></a></li>						
							<li><a href="http://wordpress.org/extend/plugins/follow-us-box/" target="_blank"><?php _e("Follow Us Box",'ga-dash') ?></a></li>			
						</ul>			
				</div></div><?php				
				return;
			} ?>
		</p>  
		<?php echo "<h3>" . __( 'Access Level', 'ga-dash' ). "</h3>";?>
		<p><?php _e("View Access Level: ", 'ga-dash' ); ?>
		<select id="ga_dash_access" name="ga_dash_access">
			<option value="manage_options" <?php if (($dashaccess=="manage_options") OR (!$dashaccess)) echo "selected='yes'"; echo ">".__("Administrators", 'ga-dash');?></option>
			<option value="edit_pages" <?php if ($dashaccess=="edit_pages") echo "selected='yes'"; echo ">".__("Editors", 'ga-dash');?></option>
			<option value="publish_posts" <?php if ($dashaccess=="publish_posts") echo "selected='yes'"; echo ">".__("Authors", 'ga-dash');?></option>
			<option value="edit_posts" <?php if ($dashaccess=="edit_posts") echo "selected='yes'"; echo ">".__("Contributors", 'ga-dash');?></option>
		</select></p>

		<p><?php

			_e("Lock selected access level to this profile: ", 'ga-dash' );
			$profiles=get_option('ga_dash_profile_list');
			$not_ready=false;
			
			if (!is_array($profiles)){
				$not_ready=true;
			}			
			
			echo '<select id="ga_dash_tableid_jail" name="ga_dash_tableid_jail">';
			if (!$not_ready) {			
				foreach ($profiles as $items) {
					if ($items[3]){
						if (!get_option('ga_dash_tableid_jail')) {
							update_option('ga_dash_tableid_jail',$items[1]);
						}
						echo '<option value="'.$items[1].'"'; 
						if ((get_option('ga_dash_tableid_jail')==$items[1])) echo "selected='yes'";
						echo '>'.ga_dash_get_profile_domain($items[3]).'</option>';
					} else {
						$not_ready=true;
						ga_dash_clear_cache();
					}
				}
			}	
			echo '</select>';
			if ($not_ready){
				echo '<font color="red"> &#9668;-- '.__("your profile list needs an update:",'ga-dash').'</font>';
				$adminurl = admin_url("#ga-dash-widget");
				echo ' <a href="'.$adminurl.'">'.__("Click here",'ga-dash').'</a>';
			}			
		?></p>
		
		<p><input name="ga_dash_jailadmins" type="checkbox" id="ga_dash_jailadmins" value="1"<?php if (get_option('ga_dash_jailadmins')) echo " checked='checked'"; ?>  /><?php _e(" disable dashboard's Switch Profile functionality", 'ga-dash' ); ?></p>
		<?php echo "<h3>" . __( 'Frontend Settings', 'ga-dash' ). "</h3>";?>
		<p><input name="ga_dash_frontend" type="checkbox" id="ga_dash_frontend" value="1"<?php if (get_option('ga_dash_frontend')) echo " checked='checked'"; ?>  /><?php _e(" show page visits and top searches in frontend (after each article)", 'ga-dash' ); ?></p>
		<?php echo "<h3>" . __( 'Backend Settings', 'ga-dash' ). "</h3>";?>
		<p><input name="ga_dash_map" type="checkbox" id="ga_dash_map" value="1"<?php if (get_option('ga_dash_map')) echo " checked='checked'"; ?>  /><?php _e(" show geo map for visits", 'ga-dash' ); ?></p>
		<p><input name="ga_dash_traffic" type="checkbox" id="ga_dash_traffic" value="1"<?php if (get_option('ga_dash_traffic')) echo " checked='checked'"; ?>  /><?php _e(" show traffic overview", 'ga-dash' ); ?></p>
		<p><input name="ga_dash_pgd" type="checkbox" id="ga_dash_pgd" value="1"<?php if (get_option('ga_dash_pgd')) echo " checked='checked'"; ?>  /><?php _e(" show top pages", 'ga-dash' ); ?></p>
		<p><input name="ga_dash_rd" type="checkbox" id="ga_dash_rd" value="1"<?php if (get_option('ga_dash_rd')) echo " checked='checked'"; ?>  /><?php _e(" show top referrers", 'ga-dash' ); ?></p>		
		<p><input name="ga_dash_sd" type="checkbox" id="ga_dash_sd" value="1"<?php if (get_option('ga_dash_sd')) echo " checked='checked'"; ?>  /><?php _e(" show top searches", 'ga-dash' ); ?></p>		
		<p><?php _e("CSS Settings: ", 'ga-dash' ); ?>
		<select id="ga_dash_style" name="ga_dash_style">
			<option value="blue" <?php if (($ga_dash_style=="blue") OR (!$ga_dash_style)) echo "selected='yes'"; echo ">".__("Blue Theme", 'ga-dash');?></option>
			<option value="light" <?php if ($ga_dash_style=="light") echo "selected='yes'"; echo ">".__("Light Theme", 'ga-dash');?></option>
		</select></p>
		<?php echo "<h3>" . __( 'Cache Settings', 'ga-dash' ). "</h3>";?>
		<p><?php _e("Cache Time: ", 'ga-dash' ); ?>
		<select id="ga_dash_cachetime" name="ga_dash_cachetime">
			<option value="900" <?php if ($ga_dash_cachetime=="900") echo "selected='yes'"; echo ">".__("15 minutes", 'ga-dash');?></option>
			<option value="1800" <?php if ($ga_dash_cachetime=="1800") echo "selected='yes'"; echo ">".__("30 minutes", 'ga-dash');?></option>
			<option value="3600" <?php if (($ga_dash_cachetime=="3600") OR (!$ga_dash_cachetime)) echo "selected='yes'"; echo ">".__("1 hour", 'ga-dash');?></option>
			<option value="7200" <?php if ($ga_dash_cachetime=="7200") echo "selected='yes'"; echo ">".__("2 hours", 'ga-dash');?></option>
		</select></p>

		<?php echo "<h3>" . __( 'Google Analytics Tracking', 'ga-dash' ). "</h3>";?>

		<p><?php _e("Enable Tracking: ", 'ga-dash' ); ?>
		<select id="ga_dash_tracking" name="ga_dash_tracking">
			<option value="0" <?php if (($ga_dash_tracking=="0") OR (!$ga_dash_tracking)) echo "selected='yes'"; echo ">".__("Disabled", 'ga-dash');?></option>
			<option value="1" <?php if ($ga_dash_tracking=="1") echo "selected='yes'"; echo ">".__("Single Domain", 'ga-dash');?></option>
			<option value="2" <?php if ($ga_dash_tracking=="2") echo "selected='yes'"; echo ">".__("Domain and Subdomains", 'ga-dash');?></option>
			<option value="3" <?php if ($ga_dash_tracking=="3") echo "selected='yes'"; echo ">".__("Multiple TLD Domains", 'ga-dash');?></option>			
		</select>
		<?php	if (!$ga_dash_tracking){
				echo ' <font color="red"> &#9668;-- '.__("the tracking feature is currently disabled!",'ga-dash').'</font>';
			}			
		?>
		</p>

		<p><?php _e("Tracking Type: ", 'ga-dash' ); ?>
		<select id="ga_dash_tracking_type" name="ga_dash_tracking_type">
			<option value="classic" <?php if (($ga_dash_tracking_type=="classic") OR (!$ga_dash_tracking_type)) echo "selected='yes'"; echo ">".__("Classic Analytics", 'ga-dash');?></option>
			<option value="universal" <?php if ($ga_dash_tracking_type=="universal") echo "selected='yes'"; echo ">".__("Universal Analytics", 'ga-dash');?></option>
		</select></p>
		<p><?php
			_e("Default Tracking Domain: ", 'ga-dash' );
			$profiles=get_option('ga_dash_profile_list');
			$not_ready=false;
			
			if (!is_array($profiles)){
				$not_ready=true;
			}	
			
			echo '<select id="ga_dash_default_ua" name="ga_dash_default_ua">';
			if (!$not_ready) {
				foreach ($profiles as $items) {
					if (isset($items[2])){
						if (!get_option('ga_dash_default_ua')) {
							update_option('ga_dash_default_ua',$items[2]);
							ga_dash_clear_cache();
						}
						echo '<option value="'.$items[2].'"'; 
						if ((get_option('ga_dash_default_ua')==$items[2])) echo "selected='yes'";
						echo '>'.ga_dash_get_profile_domain($items[3]).'</option>';
					} else {
					
						$not_ready=true;
					
					}	
				}
			}	
			echo '</select>';
			if ($not_ready){
				echo '<font color="red"> &#9668;-- '.__("your profile list needs an update:",'ga-dash').'</font>';
				$adminurl = admin_url("#ga-dash-widget");
				echo ' <a href="'.$adminurl.'">'.__("Click here",'ga-dash').'</a>';
			}	
		?></p>		
		<p><input name="ga_dash_anonim" type="checkbox" id="ga_dash_anonim" value="1"<?php if (get_option('ga_dash_anonim')) echo " checked='checked'"; ?>  /><?php _e(" anonymize IPs while tracking", 'ga-dash' ); ?></p>				
		<p class="submit">  
        <input type="submit" name="Submit" class="button button-primary" value="<?php _e('Update Options', 'ga-dash' ) ?>" />
        </p>  
    </form>  
</div>
<div class="note" style="float:<?php echo $float_note; ?>;text-align:<?php echo $float_main; ?>;"> 
		<center>
			<h3><?php _e("Setup Tutorial",'ga-dash') ?></h3>
			<a href="http://www.deconf.com/en/projects/google-analytics-dashboard-for-wordpress/" target="_blank"><img src="../wp-content/plugins/google-analytics-dashboard-for-wp/img/video-tutorial.png" width="95%" /></a>
		</center>
		<center>
			<br /><h3><?php _e("Support Links",'ga-dash') ?></h3>
		</center>			
		<ul>
			<li><a href="http://www.deconf.com/en/projects/google-analytics-dashboard-for-wordpress/" target="_blank"><?php _e("Google Analytics Dashboard Official Page",'ga-dash') ?></a></li>
			<li><a href="http://wordpress.org/support/plugin/google-analytics-dashboard-for-wp" target="_blank"><?php _e("Google Analytics Dashboard Wordpress Support",'ga-dash') ?></a></li>
			<li><a href="http://forum.deconf.com/en/wordpress-plugins-f182/" target="_blank"><?php _e("Google Analytics Dashboard on Deconf Forum",'ga-dash') ?></a></li>			
		</ul>
		<center>
			<br /><h3><?php _e("Useful Plugins",'ga-dash') ?></h3>
		</center>			
		<ul>
			<li><a href="http://www.deconf.com/en/projects/youtube-analytics-dashboard-for-wordpress/" target="_blank"><?php _e("YouTube Analytics Dashboard",'ga-dash') ?></a></li>
			<li><a href="http://www.deconf.com/en/projects/google-adsense-dashboard-for-wordpress/" target="_blank"><?php _e("Google Adsense Dashboard",'ga-dash') ?></a></li>
			<li><a href="http://www.deconf.com/en/projects/clicky-analytics-plugin-for-wordpress/" target="_blank"><?php _e("Clicky Analytics",'ga-dash') ?></a></li>						
			<li><a href="http://wordpress.org/extend/plugins/follow-us-box/" target="_blank"><?php _e("Follow Us Box",'ga-dash') ?></a></li>			
		</ul>			
</div>
</div>