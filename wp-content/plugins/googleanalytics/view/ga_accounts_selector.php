<div class="wrap">
    <input type="hidden" name="<?php echo esc_attr( Ga_Admin::GA_SELECTED_ACCOUNT ); ?>"
           value="<?php echo esc_attr( $selected ); ?>">
    <select id="ga_account_selector"
            name="<?php echo esc_attr( Ga_Admin::GA_SELECTED_ACCOUNT ); ?>" <?php echo esc_attr( $add_manually_enabled ? 'disabled="disabled"' : '' ); ?>>
        <option><?php _e( 'Please select your Google Analytics account:' ); ?></option>
		<?php
		if ( ! empty( $selector ) ) {
			foreach ( $selector as $account ) {
				?>
                <optgroup label="<?php echo $account['name']; ?>">
					<?php foreach ( $account['webProperties'] as $property ): ?>
						<?php foreach ( $property['profiles'] as $profile ): ?>
                            <option
                                    value="<?php echo esc_attr( $account['id'] . "_" . $property['webPropertyId'] . "_" . $profile['id'] ) ?>"
								<?php echo( $selected === $account['id'] . "_" . $property['webPropertyId'] . "_" . $profile['id'] ? 'selected="selected"' : '' ); ?>><?php echo esc_html( $property['name'] . "&nbsp;[" . $property['webPropertyId'] . "][" . $profile['id'] . "]" ) ?></option>
						<?php endforeach; ?>
					<?php endforeach; ?>
                </optgroup>
				<?php
			}
		}
		?>
    </select>
</div>

