<div id="ga_debug_modal" class="ga-modal" tabindex="-1">
	<div class="ga-modal-dialog">
		<div id="ga_debug_modal_content" class="ga-modal-content">
			<div class="ga-modal-header">
				<span id="ga_close" class="ga-close">&times;</span>
				<h4 class="ga-modal-title"><?php _e( 'Please send debug info:' ) ?></h4>
			</div>
			<div class="ga-modal-body">
                <div id="ga_debug_error" class="ga-alert ga-alert-danger" style="display: none;"></div>
                <div id="ga_debug_success" class="ga-alert ga-alert-success" style="display: none;"></div>
                <div class="ga-loader-wrapper">
                    <div class="ga-loader"></div>
                </div>
				<div class="ga-debug-form-div">
					<label for="ga_debug_email" class="ga-debug-form-label"><strong><?php _e( 'Your Email' ); ?></strong>:</label>
					<input id="ga_debug_email"  class="ga-debug-form-field" type="text" placeholder="<?php _e( 'Type your email here' ) ?>"/>
				</div>
                <div class="ga-debug-form-div">
					<label id="ga_debug_description_label" for="ga_debug_description" class="ga-debug-form-label"><strong><?php _e( 'Description of the issue' ); ?></strong>:</label>
					<textarea id="ga_debug_description" class="ga-debug-form-field" rows="4" cols="50"></textarea>
				</div>
                <div class="ga-debug-form-div">
					<label for="ga_debug_info" class="ga-debug-form-label"><strong><?php _e( 'Debug info' ); ?></strong>:</label>
					<textarea id="ga_debug_info" class="ga-debug-form-field" rows="8" cols="50"><?php echo $debug_info ?></textarea>
				</div>
			</div>
			<div class="ga-modal-footer">
				<button id="ga_btn_close" type="button" class="button">Close</button>
				<button type="button" class="button-primary"
				        id="ga-send-debug-email"
				        onclick="ga_debug.send_email( event )"><?php _e( 'Send' ); ?></button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->