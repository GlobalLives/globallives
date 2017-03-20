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

$trash=KPG_SS_PLUGIN_URL.'images/trash.png';
$tdown=KPG_SS_PLUGIN_URL.'images/tdown.png';
$tup=KPG_SS_PLUGIN_URL.'images/tup.png'; // fix this
$whois=KPG_SS_PLUGIN_URL.'images/whois.png'; // fix this

$nonce="";
	$ajaxurl=admin_url('admin-ajax.php');

// update options
if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
if (!empty($nonce) && wp_verify_nonce($nonce,'kpgstopspam_update')) { 
	if (array_key_exists('update_options',$_POST)) {

		if (array_key_exists('kpg_sp_cache',$_POST)) {
			$kpg_sp_cache=stripslashes($_POST['kpg_sp_cache']);
			$options['kpg_sp_cache']=$kpg_sp_cache;
		}
		if (array_key_exists('kpg_sp_good',$_POST)) {
			$kpg_sp_good=stripslashes($_POST['kpg_sp_good']);
			$options['kpg_sp_good']=$kpg_sp_good;
		}
		kpg_ss_set_options($options);
	}

}

// clear the cache
if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
if (wp_verify_nonce($nonce,'kpgstopspam_update')) { 
	if (array_key_exists('kpg_stop_clear_cache',$_POST)) {
		// clear the cache
		$badips=array();
		$goodips=array();
		$stats['badips']=$badips;
		$stats['goodips']=$goodips;
		kpg_ss_set_stats($stats);
		echo "<h2>Cache Cleared</h2>";
	}	
}
$nonce=wp_create_nonce('kpgstopspam_update');

?>
<div class="wrap">
Whenever a user tries to leave a comment, register or login, they are recorded in the Good cache if they pass or the Bad cache if they fail. If a user is blocked from access, they are added to the Bad cache. You can see the caches here. The caches clear themselves over time, but if you are getting lots of spam it is a good idea to clear these out manually by pressing the "clear cache" button.
<h3>Stop Spammers Cache Options</h3>
<form method="post" action="">
<input type="hidden" name="update_options" value="update" />
<input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
<fieldset style="border:thin solid black;padding:6px;margin:6px;">
<legend><span style="font-weight:bold;font-size:1.2em" >Bad Cache Size</span></legend>
You can change the number of entries to keep in your history and cache. The size of these items is an issue and will cause problems with some WordPress installations. It is best to keep these small.<br>
Bad IP Cache Size: <select name="kpg_sp_cache">
<option value="0" <?php if ($kpg_sp_cache=='0') echo "selected=\"true\""; ?>>0</option>
<option value="10" <?php if ($kpg_sp_cache=='10') echo "selected=\"true\""; ?>>10</option>
<option value="25" <?php if ($kpg_sp_cache=='25') echo "selected=\"true\""; ?>>25</option>
<option value="50" <?php if ($kpg_sp_cache=='50') echo "selected=\"true\""; ?>>50</option>
<option value="75" <?php if ($kpg_sp_cache=='75') echo "selected=\"true\""; ?>>75</option>
<option value="100" <?php if ($kpg_sp_cache=='100') echo "selected=\"true\""; ?>>100</option>
<option value="200" <?php if ($kpg_sp_cache=='200') echo "selected=\"true\""; ?>>200</option>
</select>
<br>Select the number of items to save in the bad IP cache. Avoid making too big as it can cause the plugin to run out of memory.
</fieldset>
<br>
<fieldset style="border:thin solid black;padding:6px;margin:6px;">
<legend><span style="font-weight:bold;font-size:1.2em" >Good Cache Size</span></legend>
The good cache should be set to just a few entries. The first time a spammer hits your site he may not be well known and once he gets in the good cache he can hit your site without being checked again. increasing the size of the cache means a spammer has more opportunities to hit your site without a new check.<br>
Good Cache Size: 
<select name="kpg_sp_good">
<option value="1" <?php if ($kpg_sp_good=='1') echo "selected=\"true\""; ?>>1</option>
<option value="2" <?php if ($kpg_sp_good=='2') echo "selected=\"true\""; ?>>2</option>
<option value="3" <?php if ($kpg_sp_good=='3') echo "selected=\"true\""; ?>>3</option>
<option value="4" <?php if ($kpg_sp_good=='4') echo "selected=\"true\""; ?>>4</option>
<option value="10" <?php if ($kpg_sp_good=='10') echo "selected=\"true\""; ?>>10</option>
<option value="25" <?php if ($kpg_sp_good=='25') echo "selected=\"true\""; ?>>25</option>
<option value="50" <?php if ($kpg_sp_good=='50') echo "selected=\"true\""; ?>>50</option>
<option value="75" <?php if ($kpg_sp_good=='75') echo "selected=\"true\""; ?>>75</option>
<option value="100" <?php if ($kpg_sp_good=='100') echo "selected=\"true\""; ?>>100</option>
<option value="200" <?php if ($kpg_sp_good=='200') echo "selected=\"true\""; ?>>200</option>
</select>
</fieldset>
<br>
<p class="submit">
<input class="button-primary" value="Save Changes" type="submit" />
</p>


</form>
<?php

if (count($badips)==0&&count($goodips)==0) echo "Nothing in the cache";
else {
	?>
	<h3>Cached Values</h3>
	<form method="post" action="">
	<input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
	<input type="hidden" name="kpg_stop_clear_cache" value="true" />
	<p class="submit">
	<input  class="button-primary" value="Clear the Cache" type="submit" />
	</p>
	</form>  
	<table>
	<tr>
	<?php
	if (count($badips)>0) {
		arsort($badips);
		?>
		<td width="30%" align="center">Rejected IPs</td>
		<?php
	}
	?>
	<?php
	if (count($goodips)>0) {
		?>
		<td width="30%" align="center">Good IPs</td>
		<?php
	}
	?>
	</tr>
	<tr>
	<?php
	if (count($badips)>0) {
		?>
		<td  style="border:1px solid black;font-size:.88em;padding:3px;" valign="top" id="badips"><?php
			// use the be_load to get badips
			$options=kpg_ss_get_options();
			$stats=kpg_ss_get_stats();
			$show=be_load('kpg_ss_get_bcache','x',$stats,$options);
			/*
			$show='';
			$cont='badips';
			foreach ($badips as $key => $value) {
				$show.="<a href=\"http://www.stopforumspam.com/search?q=$key\" target=\"_stopspam\">$key: $value</a> ";
				// try ajax on the delete from bad cache
				$onclick="onclick=\"sfs_ajax_process('$key','$cont','delete_bcache','$ajaxurl');return false;\"";
				$show.=" <a href=\"\" $onclick title=\"Delete $key from Cache\" alt=\"Delete $key from Cache\" ><img src=\"$trash\" width=\"16px\" /></a> ";			
				$onclick="onclick=\"sfs_ajax_process('$key','$cont','add_black','$ajaxurl');return false;\"";
				$show.=" <a href=\"\" $onclick title=\"Add to $key Deny List\" alt=\"Add to Deny List\" ><img src=\"$tdown\" width=\"16px\" /></a> ";
				$onclick="onclick=\"sfs_ajax_process('$key','$cont','add_white','$ajaxurl');return false;\"";
				$show.=" <a href=\"\" $onclick title=\"Add to $key Allow List\" alt=\"Add to Allow List\" ><img src=\"$tup\" width=\"16px\" /></a> ";
				$who="<a title=\"whois\" target=\"_stopspam\" href=\"http://lacnic.net/cgi-bin/lacnic/whois?lg=EN&query=$key\"><img src=\"$whois\" width=\"16px\"/></a> ";
				$show.=$who;
				$show.="<br/>";
			}
			*/
			echo $show;
		?></td>
		<?php
	}
	?>
	<?php
	if (count($goodips)>0) {
		arsort($goodips);
		?>
		<td  style="border:1px solid black;font-size:.88em;padding:3px;" valign="top" id="goodips"><?php
		
			// use the be_load to get badips
			$options=kpg_ss_get_options();
			$stats=kpg_ss_get_stats();
			$show=be_load('kpg_ss_get_gcache','x',$stats,$options);
			/*$show='';
			$cont='goodips';
			foreach ($goodips as $key => $value) {
				$show.="<a href=\"http://www.stopforumspam.com/search?q=$key\" target=\"_stopspam\">$key: $value</a> ";
				// try ajax on the delete from bad cache
				$onclick="onclick=\"sfs_ajax_process('$key','$cont','delete_gcache','$ajaxurl');return false;\"";
				$show.=" <a href=\"\" $onclick title=\"Delete $key from Cache\" alt=\"Delete $key from Cache\" ><img src=\"$trash\" width=\"16px\" /></a> ";			
				$onclick="onclick=\"sfs_ajax_process('$key','$cont','add_black','$ajaxurl');return false;\"";
				$show.=" <a href=\"\" $onclick title=\"Add to $key Deny List\" alt=\"Add to Deny List\" ><img src=\"$tdown\" width=\"16px\" /></a> ";
				$onclick="onclick=\"sfs_ajax_process('$key','$cont','add_white','$ajaxurl');return false;\"";
				$show.=" <a href=\"\" $onclick title=\"Add to $key Allow List\" alt=\"Add to Allow List\" ><img src=\"$tup\" width=\"16px\" /></a> ";
				$show.="<br/>";
			}
			*/
			echo $show;

		?></td>
		<?php
	}
	?>
	</tr>
	</table>

	<?PHP
} 
?>