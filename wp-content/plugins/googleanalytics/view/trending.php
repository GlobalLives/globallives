<div class="wrap ga-wrap">
    <h3 class="ga-trending-h3">Google Analytics</h3>
    <h2 class="ga-trending-h2"><?php _e( 'Trending content' ); ?></h2>
    <div class="ga_container <?php echo ( ! Ga_Helper::are_features_enabled() ) ? 'label-grey ga-tooltip' : '' ?>"
         id="exTab2">
		<?php if ( ! empty( $data['error_message'] ) ) : ?>
			<?php echo $data['error_message']; ?>
		<?php endif; ?>
	    <?php if ( ! empty( $data['ga_msg'] ) ) : ?>
		    <?php echo $data['ga_msg']; ?>
	    <?php endif; ?>
        <span class="ga-tooltiptext ga-tooltiptext-trending"><?php _e( $tooltip ); ?></span>
        <div class="ga-trending-loader">
            <div class="ga-trending-loader-wrapper">
                <div class="ga-loader"></div>
            </div>
            <div class="ga-trending-loading-text"><?php _e( 'Please wait. Trending Content Alerts are loading.' ); ?></div>
        </div>
	    <?php if ( Ga_Helper::are_features_enabled() && empty( $errors ) ) : ?>
		    <?php if ( ! empty( $alerts ) && empty( $alerts->error ) ) : ?>
                <div class="trending-table-container">
                    <table class="ga-table ga-table-trending">
                        <tr>
                            <th>
							    <?php _e( 'Top 5 Recent alerts' ); ?>
                            </th>
                            <th class="weight-normal">
							    <?php _e( 'Views' ); ?>
                            </th>
                            <th class="weight-normal trending-time">
							    <?php _e( 'Time Notified' ); ?>
                            </th>
                        </tr>
					    <?php foreach ( $alerts as $key => $alert ) : ?>
                            <tr>
                                <td>
                                    <a class="trending-link"
                                       href="<?php echo $alert->{"url"} ?>"><?php echo $alert->{"url"} ?></a>
                                </td>
                                <td><?php echo ( property_exists( $alert, "pageviews" ) ) ? $alert->{"pageviews"} : '0' ?></td>
                                <td><?php echo date( 'F jS, g:ia', strtotime( $alert->{"sent_at"} ) ) ?></td>
                            </tr>
						    <?php if ( $key >= 4 ) {
							    break;
						    } ?>
					    <?php endforeach; ?>
                    </table>
                </div>
		    <?php elseif ( ! empty( $alerts->error ) ) : ?>
                <div class="ga-alert ga-alert-danger">
				    <?php _e( $alerts->error ) ?>
                </div>
		    <?php else : ?>
                <div class="ga-alert ga-alert-warning">
				    <?php _e( 'You will see a history of trending content here once the first article takes off.' ) ?>
					<a class="ga-alert-link" href="http://tiny.cc/trending/"><?php _e( 'Click here to learn more' ) ?></a>
                </div>
		    <?php endif; ?>
	    <?php endif; ?>
        <div>
            <form method="post">
                <?php wp_nonce_field(Ga_Admin_Controller::ACTION_SHARETHIS_INVITE, Ga_Admin_Controller::GA_NONCE_FIELD_NAME); ?>
                <input type="hidden" name="<?php echo Ga_Controller_Core::ACTION_PARAM_NAME; ?>"
                       value="<?php echo Ga_Admin_Controller::ACTION_SHARETHIS_INVITE; ?>">
                <table>
					<tr class="ga-ta-header">
						<th>
							<?php _e( 'Trending alerts' ); ?>
						</th>
					</tr>
                    <tr>
                        <td>
							<?php _e( 'Connect your site to the Social Optimization Platform and receive these alerts via slack or email.' ); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e( 'Enter your email to receive an invite' ); ?> <input name="sharethis_invite_email"
                                                                                           type="email" value=""
		                        <?php echo ( ! Ga_Helper::are_features_enabled() ) ? 'disabled="disabled"' : ''; ?>
                                                                                           placeholder="Your email address">
                            <button <?php echo ( ! Ga_Helper::are_features_enabled() ) ? 'disabled="disabled"' : ''; ?>
                                    type="submit" class="button button-primary"><?php _e( 'Send' ); ?></button>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    <?php if( Ga_Helper::are_features_enabled() ) : ?>
		ga_trending_loader.show();
	<?php endif; ?>
</script>
