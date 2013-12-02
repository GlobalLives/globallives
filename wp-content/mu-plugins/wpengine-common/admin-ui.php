<?php
global $wpe_netdna_domains, $memcached_servers, $current_user;
//setup form url
$form_url = parse_url($_SERVER['REQUEST_URI']);
$active_tab = @$_GET['tab'] ?: 'general';
$form_url = add_query_arg(array('page'=>'wpengine-common','tab'=>$active_tab),$form_url['path']);

if ( ! current_user_can( 'manage_options' ) )
    return false;

if ( MULTISITE && ! is_super_admin() ) {
    //echo 'You do not have permission';
    ?>
    <div class="wrap">
        <h2>Error</h2>
        <p>You do not have permission to access this.</p>
    </div>
    <?php
    return false;
}

$plugin    = WpeCommon::instance();
$message   = '';
$error     = '';
$options   = $plugin->get_options();
$site_info = $plugin->get_site_info();

// Load current field values, which come from option settings unless they're given by parameters to pre-populate.
$fv_regex_html_post_process = isset($_REQUEST['regex_html_post_process']) ? stripslashes($_REQUEST['regex_html_post_process']) : $this->get_regex_html_post_process_text();

// Process form submissions
if ( isset( $_POST['options'] ) && isset( $_POST['submit'] ) ) {
    check_admin_referer( PWP_NAME . '-config' );

    foreach ( $options as $key => $value ) {
        if ( isset( $_POST['options'][$key] ) ) {
            $plugin->set_option( $key, $options[$key] = stripslashes( $_POST['options'][$key] ) );
        }
    }

    $error = $plugin->validate_options( $options );
    if ( empty( $error ) ) {
        $message = __( "Settings have been successfully updated", PWP_NAME );
    }
}
// Process form submissions
if ( isset( $_POST['displayoptions'] ) && isset( $_POST['displayoptions'] ) ) {
    check_admin_referer( PWP_NAME . '-config' );

	$error = "";
	$plugin->set_wpengine_admin_bar_enabled( $_POST['wpe-adminbar-enable'] );
    	if ( empty( $error ) ) {
        	$message = __( "Settings have been successfully updated", PWP_NAME );
    	}
	
	//update the user access role	
	if(!empty($_POST['wpe-adminbar-roles']))
		$plugin->set_option('wpe-adminbar-roles', $_POST['wpe-adminbar-roles']);
	else 
		delete_option('wpe-adminbar-roles');
}

// Process snapshot -> staging
$just_started_snapshot = false;
if ( wpe_param( 'snapshot' ) ) {
    check_admin_referer( PWP_NAME . '-config' );

    // Can't run one if one is already running
    $status = $plugin->get_staging_status();
    if ( $status['have_snapshot'] && ! $status['is_ready'] ) {
        $error = "<b>A staging snapshot is already in progress.</b><br>Please wait for the current staging process to complete, then you can either use the staging area or you can then request another snapshot.";
    } else {
        try {
            $plugin->snapshot_to_staging();
            $message               = "Your staging site is being built in the background.  <b>It can take a long time</b>, especially for large sites.<br>";
            $just_started_snapshot = true;
        } catch ( Exception $e ) {
            $error = $e;
        }
    }
}

// Process mirror-s3
if ( wpe_param( 'mirror-s3' ) ) {
    check_admin_referer( PWP_NAME . '-config' );

    $s3bucket    = $plugin->get_option( 'wpe-mirror-s3-bucket' );
    $notify_list = $plugin->get_option( 'wpe-mirror-s3-notify' );
    $message     = $plugin->mirror_to_s3( $s3bucket, $notify_list );
}

// Process saving CDN info
if ( WPE_CDN_DISABLE_ALLOWED && wpe_param( 'cdn-control' ) ) {
    check_admin_referer( PWP_NAME . '-config' );

	// Enabled/Disabled
    $current_state = $this->is_cdn_enabled();
    $new_state     = !!$_REQUEST['cdn-enable'];
    if ( $current_state != $new_state ) {
        $this->set_cdn_enabled( $new_state );
        if ( $new_state ) {  // if enabling, flush the old CDN contents since we might have altered things in the meantime
            WpeCommon::clear_maxcdn_cache();
        }
        WpeCommon::purge_varnish_cache();  // refresh our own cache (after CDN purge, in case that needed to clear before we access new content)
        $message = "CDN support is now <b>" . ($new_state ? 'enabled' : 'disabled') . "</b>.";
    } else {
        $message = "No change; CDN support was already " . ($new_state ? 'enabled' : 'disabled') . ".";
    }
}

// Process saving object cache status
if ( wpe_param( 'object-cache-control' ) ) {
    check_admin_referer( PWP_NAME . '-config' );

	// Enabled/Disabled
    $current_state = $this->is_object_cache_enabled();
    $new_state     = !!$_REQUEST['object-cache-enable'];
    if ( $current_state != $new_state ) {
        $this->set_object_cache_enabled( $new_state );
        WpeCommon::purge_varnish_cache();  // refresh our own cache (after CDN purge, in case that needed to clear before we access new content)
        $message = "Object/Transient cache support is now <b>" . ($new_state ? 'enabled' : 'disabled') . "</b>.";
    } else {
        $message = "No change; object/transient cache support was already " . ($new_state ? 'enabled' : 'disabled') . ".";
    }
}

// Process saving advanced info
if ( wpe_param( 'advanced' ) ) {
    check_admin_referer( PWP_NAME . '-config' );

	// RAND() Enabled/Disabled
    $current_state = $this->is_rand_enabled();
    $new_state     = !!$_REQUEST['rand_enabled'];
    if ( $current_state != $new_state ) {
        $this->set_rand_enabled( $new_state );
        $message = "ORDER BY RAND() support is now <b>" . ($new_state ? 'enabled' : 'disabled') . "</b>.";
    }

	// HTML post-processing
	$result = $this->set_regex_html_post_process_text( $fv_regex_html_post_process );
	if ( $result !== TRUE ) {
		$error = "<b>Error in HTML replacement regex:</b><br>$result<br>(Maybe you forgot the beginning and ending characters?)";
	}
}

// Fix file permissions
if ( wpe_param( 'file-perms' ) ) {
	check_admin_referer( PWP_NAME . '-config' );
	$url = "https://api.wpengine.com/1.2/?method=file-permissions&account_name=" . PWP_NAME . "&wpe_apikey=" . WPE_APIKEY;
	$http = new WP_Http;
	$msg  = $http->get( $url );
        if ( is_a( $msg, 'WP_Error' ) )
            return false;
	if ( ! isset( $msg['body'] ) )
            return false;
        $data = json_decode( $msg['body'], true );
	$message = @$data['message'];
}

// Process purging all caches
if ( wpe_param( 'purge-all' ) ) {
    check_admin_referer( PWP_NAME . '-config' );
    // check_admin_referer(PWP_NAME.'-config');		DO NOT CHECK because it's OK to just hit it from anywhere, and in fact we do.
    WpeCommon::purge_memcached();
    WpeCommon::clear_maxcdn_cache();
    WpeCommon::purge_varnish_cache();  // refresh our own cache (after CDN purge, in case that needed to clear before we access new content)
    $message = "All of these caches have been purged: HTML-page-caching, CDN (statics), and WordPress Object/Transient Caches.";
}

if ( is_wpe_snapshot() ) {
    $error         = "Cannot use the standard WPEngine controls from a staging server!<br/><br/>This is valid only from your live site.";
    $have_snapshot = FALSE;
} else {
    $snapshot_state = $plugin->get_staging_status();
    if ( $just_started_snapshot && $snapshot_state['have_snapshot'] ) {  // if this, fake it!
        $snapshot_state['status']   = "Starting the staging snapshot process...";
        $snapshot_state['is_ready'] = false;
    }
	$have_snapshot = (bool) $snapshot_state['have_snapshot'];
}?>

<div class="wrap">

<?php if ( ! empty( $error ) ) : ?>
        <div class="error"><p><?php echo $error; ?></p></div>
<?php endif; ?>

<?php if ( ! empty( $message ) ) : ?>
        <div class="updated fade"><p><?php echo $message; ?></p></div>
<?php endif; ?>

    <?php if ( ! is_wpe_snapshot() ) { ?>
	<h2 class="nav-tab-wrapper">
		<a class="nav-tab <?php if($active_tab=='general') { echo 'nav-tab-active'; } ?>" href="<?php echo add_query_arg(array('tab'=>'general')); ?>">General Settings</a> 
		<a class="nav-tab <?php if($active_tab=='staging') { echo 'nav-tab-active'; } ?>" href="<?php echo add_query_arg(array('tab'=>'staging')); ?>">Staging</a>
	</h2>
	
	<div class="wpe-content-wrapper">
	<?php if( $active_tab == 'general'): ?>
		<div class="span-30">
			<p><b>You should <a href="http://eepurl.com/i3HPf" target="_blank">subscribe to our customer announcement list</a></b> to get updates on new features, system developments, and account and billing information.  You can of course unsubscribe at any time, and we use it only for infrequent but important announcements.</p>
			<p>Your DNS should either be set to CNAME to <code><?= $site_info->name ?>.wpengine.com</code> or an A record to <code><?= $site_info->public_ip ?></code>.</p>
			<p>Your SFTP access (<i>not FTP!</i>) is at hostname <code><?= $site_info->sftp_host ?></code> or IP at <code><?= $site_info->sftp_ip ?></code> on port <code><?= $site_info->sftp_port ?></code>. Username and password starts out the same as you specified when you signed up for your blog (which was <code><?= $site_info->name ?></code>), but can be <a href="<?php echo get_option('wpe-install-userportal','https://my.wpengine.com'); ?>" target="_blank">changed here</a>.</p>
		</div><!--.span-30-->
      		<br class="clear"/>
        

        <h2>Dynamic Page &amp; Database Cache Control</h2>
        <p>
            We aggressively cache everything from pages to feeds to 301-redirects on sub-domains; this makes your site load
            lightning-fast for your non-logged-in readers.  99.9% of the time this is what you want, but every once in a while
            something happens where our cache should have been purged but wasn't.  For example, some URL plugins change
            behavior without alerting WordPress.
        </p>
<? if ( count($memcached_servers) ) { ?>
		<p>
			We also support the <a href="http://codex.wordpress.org/Class_Reference/WP_Object_Cache" target="_blank">WordPress Object Cache</a>
			(which also powers the <a href="http://codex.wordpress.org/Transients_API" target="_blank">WordPress Transient API</a>).
			Although this greatly accelerates both the front- and back-end, it also can cause trouble with certain plugins.
			In particular you might need to purge this cache manually to get consistent behavior after making configuration changes.
		</p>
<? } ?>
        <p>
            You use this button to purge all caches -- on our caching proxies, on the CDN, in memcached, everything.
        </p>
        <form method="post" name="options" action="<?php echo esc_url($form_url); ?>">
    		<?php wp_nonce_field( PWP_NAME . '-config' ); ?>
			<table class="form-table">
				<? if ( count($memcached_servers) ) { ?>
				<tr valign="top">
					<th scope="row"><label for="object-cache-enable">Object/Transient Cache</label></th>
					<td>
						<? if ( defined('WP_CACHE') && WP_CACHE ) { ?>
						<select name="object-cache-enable">
							<option value="1" <?= $this->is_object_cache_enabled() ? "selected" : "" ?> >Enabled</option>
							<option value="0" <?= $this->is_object_cache_enabled() ? "" : "selected" ?> >Disabled</option>
						</select>
						<div class="description">
         						Generally you want this enabled for maximum speed and scale, but if your site is under active development
							or if you have a theme or plugin which is incompatible it might
							be more convenient to have it temporarily disabled.
						</div>
						<? } else { ?>
							<b>Cannot enable caching:</b> WordPress object/transient caching requires that <code>WP_CACHE</code>
							be defined as <code>TRUE</code> inside <code>wp-config.php</code>.  Currently that define is either
							missing or set to <code>FALSE</code>.
						<? } ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="object-cache-control" value="Save" class="button-primary" /></td>
				</tr>
				<? } ?>
				<tr>
					<td></td>
					<td style="border-top: 1px solid #c0c0c0;">
						<input type="submit" name="purge-all" value="Purge All Caches" class="button-primary" onclick="return confirm('Please be patient, this sometimes takes a while.');"/>
						(Purges <i>everything</i>: The page-cache, the CDN cache, and the object cache)
					</td>
				</tr>
				<tr>
					<td></td>
					<td style="border-top: 1px solid #c0c0c0;">
						<input type="submit" name="file-perms" value="Reset File Permissions" class="button-primary" onclick="return confirm('Please be patient, this sometimes takes a while.');"/>
						(Properly sets your WP file permissions needed for normal operation.  Use this button after uploading files via SFTP.)
					</td>
				</tr>
			</table>
        </form>

<?php if ( PWP_NAME == 'balsamiqmain' ): ?>

            <hr/>
            <h2>Mirror site to S3</h2>
            <p>Use this command to trigger a mirror of your site to an S3 bucket for static serving of your content.
                See <a href="http://wpengine.com/faq/blog-s3/" target="_blank">this web page</a> for more information
                about the tradeoffs of using S3 to serve your site.
            </p>
            <form method="post" name="options" action="<?php echo esc_url( $form_url ); ?>">
                <p class="submit submit-top">
        <?php wp_nonce_field( PWP_NAME . '-config' ); ?>
                    <input type="submit" name="mirror-s3" value="Mirror to S3" class="button-primary"/>
                </p>
            </form>

<?php endif; ?>

        <hr/>

        <h2>CDN Control</h2>
        <p>
            <b>Configure your CDN</b> (described <a href="http://wpengine.com/faq/what-is-a-cdn/" target="_blank">here</a>).
        </p>
    <? if ( isset( $wpe_netdna_domains ) && count( $wpe_netdna_domains ) > 0 ) { ?>
            <form method="post" name="options" action="<?php echo esc_url( $form_url ); ?>">
            <?php wp_nonce_field( PWP_NAME . '-config' ); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="cdn-domains">CDN Domains</label></th>
					<td>
				            <p>
				                Right now, the following domains are configured for use with a CDN.  If you see something missing or extra,
				                <a href="http://wpengine.zendesk.com">contact tech support</a>.
				            </p>
				            <ul><?php
				        foreach ( $wpe_netdna_domains as $zinfo ) {
				            print("<li><code>" . htmlspecialchars( $zinfo['match'] ) . "</code></li>" );
				        }
				        ?></ul>
					</td>
				</tr>
	<? if ( WPE_CDN_DISABLE_ALLOWED ) { ?>
				<tr valign="top">
					<th scope="row"><label for="cdn-enable">CDN Activated</label></th>
					<td>
	                    <select name="cdn-enable">
	                        <option value="1" <?= $this->is_cdn_enabled() ? "selected" : "" ?> >Enabled</option>
	                        <option value="0" <?= $this->is_cdn_enabled() ? "" : "selected" ?> >Disabled</option>
	                    </select>
						<div class="description">
         					Generally you want this enabled for maximum speed and scale, but if your site is under active development it might
            				be more convenient to have it temporarily disabled.
						</div>
					</td>
				</tr>
				<tr><td></td><td>
	                <p class="submit submit-top">
	                    <input type="submit" name="cdn-control" value="Save" class="button-primary" />
	                </p></td>
				</tr>
	<? } ?>
			</table>
            </form>
    <? } else { ?>
            <p>
                Right now <b>no domains are configured</b> for use with a CDN.  If you're ready to enable CDN support,
                <a href="http://wpengine.zendesk.com">contact tech support</a> and we'll set you up.
            </p>
    <? } ?>

        <hr/>

        <h2>Display Options</h2>
           <form method="post" name="displayoptions" action="<?php echo esc_url( $form_url ); ?>">
           <?php wp_nonce_field( PWP_NAME . '-config' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="wpe-adminbar-enable">WP Engine Admin Bar</label></th>
				<td>
                    <select name="wpe-adminbar-enable">
                        <option value="1" <?= $this->is_wpengine_admin_bar_enabled() ? "selected" : "" ?> >Enabled</option>
                        <option value="0" <?= $this->is_wpengine_admin_bar_enabled() ? "" : "selected" ?> >Disabled</option>
                    </select>
					<div class="description comment">
        				Should we display the "WP Engine Quick Links" menu in the WordPress admin titlebar?
					</div>
				</td>
			</tr>
			<tr valign="top">
			<td>Restrict Access</td>
			<td>
			<div class="description comment">Select which roles should have access to the "WP Engine Quick Links" menu</div>
			<p>
				<?php $has_access = get_option('wpe-adminbar-roles',array()); ?>
				<?php $roles = new WP_Roles(); ?>
				<?php foreach( $role_names = $roles->get_names() as $role=>$role_name): ?>
					<?php $checked = in_array($role, $has_access) ? 'checked="checked"' : ''; ?>
					<input name="wpe-adminbar-roles[]" value="<?php echo $role; ?>" type="checkbox" <?php echo $checked; ?> /> <?php echo $role_name; ?><br/>
				<?php endforeach; ?>
			</p>
			</td>
			</tr>
			<tr><td></td><td>
                <p class="submit submit-top">
                    <input type="submit" name="displayoptions" value="Save" class="button-primary" />
                </p></td>
			</tr>
		</table>
          </form>

        <hr/>
        <h2>Web Server &amp; PHP Error Log</h2>
        <p>
            You can always retrieve the most recent entries from the error log with the following links:
        </p>
        <p>
            [<a href="<?= $this->get_access_log_url( 'current' ) ?>">
                Access Log &mdash; Production Site &mdash; Current
            </a>]<br>
            [<a href="<?= $this->get_access_log_url( 'previous' ) ?>">
                Access Log &mdash; Production Site &mdash; Previous
            </a>]<br>
            [<a href="<?= $this->get_error_log_url( true ) ?>" target="_blank">
                Error Log &mdash; Production Site
            </a>]<br>
            [<a href="<?= $this->get_error_log_url( false ) ?>" target="_blank">
                Error Log &mdash; Staging Site
            </a>]
        </p>
        <p>
            <b>NOTE:</b> Save this URL somewhere you can get to even when WordPress is completely unavailable.  That way if you completely break your blog, you can still discover what's wrong.
			This is also available in your <a href="<?php echo get_option('wpe-install-userportal','https://my.wpengine.com'); ?>"><?php echo get_option('wpe-install-menu_title','WP Engine'); ?> User Portal</a>.
        </p>

        <hr/>

        <h2>Advanced Configuration</h2>
		<p>
			<b>With great power comes great responsibility!</b>  These tools can greatly enhance... or completely break... your
			website, so exercise caution and don't be shy about <a href="http://wpengine.zendesk.com">contacting support</a>
			if you have questions.
			<br>
			<i>Hint:</i> To test regular expressions, check out <a href="http://regexpal.com/">Regexpal</a>, a free online tool.
		</p>
           <form method="post" name="advanced" action="<?php echo esc_url( $form_url ); ?>">
           <?php wp_nonce_field( PWP_NAME . '-config' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="rand_enabled">Allow ORDER BY RAND()</label></th>
				<td>
                    <select name="rand_enabled">
                        <option value="1" <?= $this->is_rand_enabled() ? "selected" : "" ?> >Enabled</option>
                        <option value="0" <?= $this->is_rand_enabled() ? "" : "selected" ?> >Disabled</option>
                    </select>
					<div class="description">
						Normally we disable <code>ORDER BY RAND()</code> orderings in MySQL queries because this
						is a big no-no for large databases which we've seen cause massive slow-downs for dozens
						of our customers.  However, you can enable it if you know what you're doing, for example
						if you cache the results for 5-15 minutes so that you're not pummeling the database with
						these slow queries.
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="regex_html_post_process">HTML Post-Processing</label></th>
				<td>
                    <textarea name="regex_html_post_process" cols="60" rows="5"><?= htmlspecialchars($fv_regex_html_post_process) ?></textarea>
					<div class="description">
						A mapping of PHP regular expressions to replacement values which are executed on all blog
						HTML after WordPress finishes emitting the entire page.  The pattern and replacement
						behavior is in the manner of <a href="http://php.net/manual/en/function.preg-replace.php">preg_replace()</a>.
						<br><br>
						The following example removes all HTML comments in the first pattern, and causes a favicon (with any filename extension) to be
						loaded from another domain in the second pattern:
						<br>
<pre>#&lt;!--.*?--&gt;#s =>
#\bsrc="/(favicon\..*)"# => src="http://mycdn.somewhere.com/$1"</pre>
					</div>
				</td>
			</tr>
			<tr><td></td><td>
                <p class="submit submit-top">
                    <input type="submit" name="advanced" value="Save" class="button-primary" />
                </p></td>
			</tr>
		</table>
           </form>
	<?php elseif($active_tab == 'staging'): ?>	
		<form id="staging" method="post" name="options" action="<?php echo esc_url($form_url); ?>">


		<? if ( $snapshot_state['have_snapshot'] ) { ?>
			<div class="alert alert-message alert-success">
				<h3 class="wpe-callout"><i class="wpe-icon-hdd icon-hdd"></i>  Staging Status: <?= htmlspecialchars( $snapshot_state['status'] ) ?></h3>
			    	<p>
				<? if ( $snapshot_state['is_ready'] ) { ?>
				    Last staging snapshot was taken on <?= date( "Y-m-d g:i:sa", $snapshot_state['last_update'] + (get_option( "gmt_offset" ) * 60 * 60) ) ?>. Access it here: <a target="_blank" href="<?= $snapshot_state['staging_url'] ?>"><b><?= htmlspecialchars( $snapshot_state['staging_url'] ) ?></b></a>
				<? } else { ?>
				    <b>Please wait</b> while the staging area continues to be deployed.  It can take a while!  You can <a href="<?= $plugin->get_plugin_admin_url() ?>&tab=staging">refresh this page</a> to check on its progress.
				<? } ?>
			    	</p>
			</div>
		<? } ?>
			
		<h2><i class="wpe-icon-hdd icon-large icon-hdd"></i> What is a Staging Area?</h2>
		<p>
			This takes a snapshot of your blog and copies it to a "staging area" where you can test out changes without affecting your live site. There's only one staging area, so every time you click this button the old staging area is lost forever, replaced with a snapshot of your live blog.
		</p>

		<p>
			<b>Please note:</b> if you want to access your staging site via SFTP, there is a different username required. You can manage your SFTP users in your <a href="<?php echo get_option('wpe-install-userportal','https://my.wpengine.com'); ?>" target="_blank">User Portal</a>.
		</p>

		    <p class="submit submit-top">
			<?php wp_nonce_field( PWP_NAME . '-config' ); ?>

			<button type="submit" <?php if( @$snapshot_state['is_ready'] ) { echo 'data-confirm="true"'; } ?> name="snapshot" value="<?= $have_snapshot ? "Recreate" : "Create" ?> staging area" class="btn btn-primary"><i class="icon-upload icon-white"></i> Copy site from LIVE to STAGING </button>
			 <?php if( @$snapshot_state['is_ready'] AND current_user_can('administrator') ) : ?>
				<button onClick="wpe_deploy_staging();" type="button" name="deploy-from-staging" value="Deply from Staging" class="<?php if(!in_array('deploy-staging', get_user_meta($current_user->ID,'hide-pointer',false))) { echo 'wpe-pointer'; } ?> btn btn-inverse"><i class="icon-download icon-white"></i> Copy site from STAGING to LIVE </button>
			<?php endif; ?>

		    </p>
		</form>
		<form class="form" id="deploy-from-staging" style="display:none;" action="" method="post">
			<p><em>By default only your files will be copied back to LIVE. You can chose to move content by checking the tables you would like to move below. Keep in mind these tables will replace the LIVE version with the STAGING version. So for instance if you choose to move wp_posts all posts added to the LIVE site since the staging site was created will be removed. However, a checkpoint of your site will be created so you can 'undo' the changes if necessary. </em></p>
			<?php
				//tables
				global $wpdb;
				$tables = $wpdb->get_col("SHOW TABLES;" );
				$wpdb->flush();
			?>
			<p>
			<label>Database Mode</label>
			<select name="db_mode" class="chzn-select" style="width:300px;"> 
				<option value="none">Move No Tables</option>
				<option value="default">Move All Tables</option>
				<option value="tables">Select Tables to Move</option>
			</select>
			</p>
			<p class="table-select" style="display:none;">
			<label>Select Databases</label>
			<select name="tables[]" style="width:300px;" class="chzn chzn-select" multiple data-placeholder="(start typing to see a list)" >
			<?php foreach($tables as $table) : ?>
				<option value="<?php echo $table; ?>" <?php if('wp_options' == $table) echo 'selected'; ?>><?php echo $table; ?></option>
			<?php endforeach; ?>	
			</select>
			</p>
			<p class="clear">	
				<label>Email to Notify</label>
				<input type="text" class="text email" name="email" placeholder="<?php echo get_option('admin_email'); ?>" value="<?php echo get_option('admin_email'); ?>"/>
			</p>

<?php
$staging_status = $plugin->get_staging_status();
$production_version = get_bloginfo('version');
$can_push_staging = is_staging_gte($production_version, $staging_status['version']);
?>

		<?php if($can_push_staging): ?>
			<div class="submit form-actions">
				<p><buttom id="submit-deploy" name="submit-deploy" value="Submit" class="btn btn-primary" >Submit</button></p>
			</div>
                <?php else: ?>
			<div class='alert-message alert-error'>
				<br><blockquote>
				<h3>Your Staging Site is Running an Old Version of WordPress (<?php echo $staging_status['version']; ?>)</h3>
				<p>Your staging site is running an old version of WordPress, WordPress <?php echo $staging_status['version']; ?>. 
                    Before you can deploy your staging site to your production site, you need to 
                    <a target="_blank" href="<?php echo $staging_status['staging_url']; ?>/wp-admin/update-core.php">update WordPress</a> 
                    to match your production version, <?php echo $production_version; ?>. Please follow the steps to update WordPress. 
                    We recommend you also create a <a href="https://my.wpengine.com/installs/<?php echo PWP_NAME;?>/backup_points">backup point</a> before updating.</p>
				</blockquote><br>
			</div>
		<?php endif; ?>						
		</form>
	<?php endif; ?>
	</div><!--.wpe-content-wrapper-->
<?php } /* is_wpe_snapshot() */ ?>
</div><!--.wrap-->
<hr/>
<p>WP Engine Plugin v<?= WPE_PLUGIN_VERSION ?> | <a href="http://wpengine.zendesk.com" target="_blank">Support</a></p>



