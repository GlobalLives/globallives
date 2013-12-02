<div class="wpe-notices updated wpe-notices-<?php echo $class; ?>" title="<?php echo $id; ?>">
	<div class="dismissable"><img src="<?php echo $icon; ?>" id="dismiss-it" alt="<?php _e('Dismiss this message','wpengine'); ?>"></div>
	<p class="wpe-notices-<?php echo $class; ?>">
		<strong><?php echo ucwords( $class ); ?>: </strong><?php _e($message,'wpengine'); ?>
	</p>
</div>
