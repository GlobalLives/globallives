<div id="ga_access_code_modal" class="ga-modal" tabindex="-1">
    <div class="ga-modal-dialog">
        <div class="ga-modal-content">
            <div class="ga-modal-header">
                <span id="ga_close" class="ga-close">&times;</span>
                <h4 class="ga-modal-title"><?php _e( 'Please paste the access code obtained from Google below:' ) ?></h4>
            </div>
            <div class="ga-modal-body">
                <div id="ga_code_error" class="ga-alert ga-alert-danger" style="display: none;"></div>
                <label for="ga_access_code"><strong><?php _e( 'Access Code' ); ?></strong>:</label>
                &nbsp;<input id="ga_access_code_tmp" type="text"
                             placeholder="<?php _e( 'Paste your access code here' ) ?>"/>
                <div class="ga-loader-wrapper">
                    <div class="ga-loader"></div>
                </div>
            </div>
            <div class="ga-modal-footer">
                <button id="ga_btn_close" type="button" class="button">Close</button>
                <button type="button" class="button-primary"
                        id="ga_save_access_code"
                        onclick="ga_popup.saveAccessCode( event )"><?php _e( 'Save Changes' ); ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php echo $data[ 'debug_modal' ] ?>
<div class="wrap ga-wrap">
    <h2>Google Analytics - <?php _e( 'Settings' ); ?></h2>
    <div class="ga_container">
	    <?php if ( ! empty( $data['error_message'] ) ) : ?>
		    <?php echo $data['error_message']; ?>
	    <?php endif; ?>
        <form id="ga_form" method="post" action="options.php">
	        <?php settings_fields( 'googleanalytics' ); ?>
            <input id="ga_access_code" type="hidden"
                   name="<?php echo esc_attr( Ga_Admin::GA_OAUTH_AUTH_CODE_OPTION_NAME ); ?>" value=""/>
            <table class="form-table">
                <tr valign="top">
	                <?php if ( ! empty( $data['popup_url'] ) ): ?>
                        <th scope="row">
                            <label <?php echo ( ! Ga_Helper::are_features_enabled() ) ? 'class="label-grey ga-tooltip"' : '' ?>><?php echo _e( 'Google Profile' ) ?>
                                :
                                <span class="ga-tooltiptext ga-tt-abs"><?php _e( $tooltip ); ?></span>
                            </label>
                        </th>
                        <td <?php echo ( ! Ga_Helper::are_features_enabled() ) ? 'class="ga-tooltip"' : ''; ?>>
	                        <?php echo $data[ 'auth_button' ] ?>
                            <span class="ga-tooltiptext"><?php _e( $tooltip ); ?></span>
			                <?php if ( ! empty( $data[ Ga_Admin::GA_WEB_PROPERTY_ID_MANUALLY_OPTION_NAME ] ) ): ?>
                                <div class="ga_warning">
                                    <strong><?php _e( 'Notice' ) ?></strong>:&nbsp;<?php _e( 'Please uncheck the "Manually enter Tracking ID" option to authenticate and view statistics.' ); ?>
                                </div>
			                <?php endif; ?>
                        </td>
	                <?php endif; ?>

	                <?php if ( ! empty( $data['ga_accounts_selector'] ) ): ?>
                        <th scope="row"><?php echo _e( 'Google Analytics Account' ) ?>:</th>
                        <td>
                            <?php echo $data['ga_accounts_selector']; ?>
                        </td>
	                <?php endif; ?>

                </tr>

                <tr valign="top">

                    <th scope="row">
                        <div class="checkbox">
                            <label class="ga_checkbox_label <?php echo Ga_Helper::get_code_manually_label_classes() ?>"
                                   for="ga_enter_code_manually"> <input
			                        <?php if ( Ga_Helper::are_features_enabled() ) : ?>
                                        onclick="ga_events.click( this, ga_events.codeManuallyCallback( <?php echo Ga_Helper::are_features_enabled() ? 1 : 0; ?> ) )"
			                        <?php endif; ?>
                                        type="checkbox"
			                        <?php echo ( ! Ga_Helper::are_features_enabled() ) ? 'disabled="disabled"' : ''; ?>
                                        name="<?php echo esc_attr( Ga_Admin::GA_WEB_PROPERTY_ID_MANUALLY_OPTION_NAME ); ?>"
                                        id="ga_enter_code_manually"
                                        value="1"
			                        <?php echo( ( $data[ Ga_Admin::GA_WEB_PROPERTY_ID_MANUALLY_OPTION_NAME ] || ! Ga_Helper::are_terms_accepted() ) ? 'checked="checked"' : '' ); ?>/>&nbsp;
		                        <?php _e( 'Manually enter Tracking ID' ) ?>
                                <span class="ga-tooltiptext ga-tt-abs"><?php _e( $tooltip ); ?></span>
                            </label>
	                        <?php if ( ! Ga_Helper::are_features_enabled() ) : ?>
                                <input id="ga_enter_code_manually_hidden" type="hidden"
                                       name="<?php echo esc_attr( Ga_Admin::GA_WEB_PROPERTY_ID_MANUALLY_OPTION_NAME ); ?>"
                                       value="1"/>
	                        <?php endif; ?>
                        </div>
                    </th>
                    <td></td>
                </tr>
                <tr valign="top"
                    id="ga_manually_wrapper" <?php echo( ( $data[ Ga_Admin::GA_WEB_PROPERTY_ID_MANUALLY_OPTION_NAME ] || ! Ga_Helper::are_features_enabled() ) ? '' : 'style="display: none"' ); ?> >

                    <th scope="row"><?php _e( 'Tracking ID' ) ?>:</th>
                    <td>
                        <input type="text"
                               name="<?php echo esc_attr( Ga_Admin::GA_WEB_PROPERTY_ID_MANUALLY_VALUE_OPTION_NAME ); ?>"
                               value="<?php echo esc_attr( $data[ Ga_Admin::GA_WEB_PROPERTY_ID_MANUALLY_VALUE_OPTION_NAME ] ); ?>"
                               id="ga_manually_input"/>&nbsp;
                        <div class="ga_warning">
                            <strong><?php _e( 'Warning' ); ?></strong>:&nbsp;<?php _e( 'If you enter your Tracking ID manually, Analytics statistics will not be shown.' ); ?>
                            <br>
	                        <?php _e( 'We strongly recommend to authenticate with Google using the button above.' ); ?>
                        </div>
                    </td>

                </tr>

                <tr valign="top" id="ga_roles_wrapper">
                    <th scope="row">
                        <label <?php echo ( ! Ga_Helper::are_features_enabled() ) ? 'class="label-grey ga-tooltip"' : '' ?>><?php _e( 'Exclude Tracking for Roles' ) ?>
                            :
                            <span class="ga-tooltiptext ga-tt-abs"><?php _e( $tooltip ); ?></span>
                        </label>
                    </th>
                    <td>


	                    <?php
	                    if ( ! empty( $data['roles'] ) ) {
		                    $roles = $data['roles'];
		                    foreach ( $roles as $role ) {
			                    ?>
                                <div class="checkbox">
                                    <label class="ga_checkbox_label <?php echo ( ! Ga_Helper::are_features_enabled() ) ? 'label-grey ga-tooltip' : ''; ?>"
                                           for="checkbox_<?php echo $role['id']; ?>">
                                        <input id="checkbox_<?php echo $role['id']; ?>" type="checkbox"
	                                        <?php echo ( ! Ga_Helper::are_features_enabled() ) ? 'disabled="disabled"' : ''; ?>
                                               name="<?php echo esc_attr( Ga_Admin::GA_EXCLUDE_ROLES_OPTION_NAME . "[" . $role['id'] . "]" ); ?>"
                                               id="<?php echo esc_attr( $role['id'] ); ?>"
	                                        <?php echo esc_attr( ( $role['checked'] ? 'checked="checked"' : '' ) ); ?> />&nbsp;
		                                <?php echo esc_html( $role['name'] ); ?>
                                        <span class="ga-tooltiptext"><?php _e( $tooltip ); ?></span>
                                    </label>
                                </div>
			                    <?php
		                    }
	                    }
	                    ?>

                    </td>
                </tr>
                <tr valign="top">
                  <td colspan="2">
                    <p>If you experience an issue with this plugin, we are here to help! You can visit our <a href="https://googleanalytics.zendesk.com/hc/en-us">support portal</a> to find answers to the most frequently asked questions and to submit a support request. We aim to respond to everyone within one business day.</p>
              			<p>If your issue is difficult to debug, click the button below to automatically send us your error logs and debugging info.</p>
                    <p>
                        <button id="ga_debug_button" class="button button-secondary" onclick="ga_debug.open_modal( event )" >Send Debugging Info</button>
	                    <?php if ( ! empty( $data['ga_accounts_selector'] ) ): ?>
			                <?php echo $data[ 'auth_button' ] ?>
	                    <?php endif; ?>
                    </p>
                  </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Disable all features' ) ?>:</th>
                    <td>
						<label class="ga-switch">
                            <input id="ga-disable" name="<?php echo Ga_Admin::GA_DISABLE_ALL_FEATURES; ?>"
                                   type="checkbox">
							<div id="ga-slider" class="ga-slider round"></div>
						</label
					</td>
				</tr>
            </table>

            <p class="submit">
                <input type="submit" class="button-primary"
                       value="<?php _e( 'Save Changes' ) ?>"/>
            </p>
        </form>
    </div>
    <p class="ga-love-text"><?php _e( 'Love this plugin?' ); ?> <a
                href="https://wordpress.org/support/plugin/googleanalytics/reviews/#new-post"><?php _e( ' Please help spread the word by leaving a 5-star review!' ); ?> </a>
    </p>
</div>
<script type="text/javascript">
    const GA_DISABLE_FEATURE_URL = '<?php echo Ga_Helper::create_url(admin_url(Ga_Helper::GA_SETTINGS_PAGE_URL), array(Ga_Controller_Core::ACTION_PARAM_NAME => 'ga_action_disable_all_features')); ?>';
    const GA_ENABLE_FEATURE_URL = '<?php echo Ga_Helper::create_url(admin_url(Ga_Helper::GA_SETTINGS_PAGE_URL), array(Ga_Controller_Core::ACTION_PARAM_NAME => 'ga_action_enable_all_features')); ?>';
    jQuery(document).ready(function () {
        ga_switcher.init('<?php echo $data[ Ga_Admin::GA_DISABLE_ALL_FEATURES ]; ?>');
    });
</script>
