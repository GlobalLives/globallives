<button id="ga_authorize_with_google_button" class="<?php echo ( $type == 'auth' ) ? 'button-primary' : 'button-secondary' ?>"
	<?php if ( Ga_Helper::are_features_enabled() ) : ?>
		onclick="ga_popup.authorize( event, '<?php echo esc_attr( $url ); ?>' )"
	<?php endif; ?>
	<?php echo( ( esc_attr( $manually_id ) || ! Ga_Helper::are_features_enabled()  || Ga_Helper::is_curl_disabled() ) ? 'disabled="disabled"' : '' ); ?>
><?php _e( $label ) ?>
</button>