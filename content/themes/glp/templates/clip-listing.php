<?php global $clip; ?>
<article class="participant-clip-listing">
    <div class="row">
    	<div class="clip-thumbnail span2" data-clip-id="<?php echo $clip->ID; ?>"><img src="http://img.youtube.com/vi/<?php the_field('youtube_id', $clip->ID); ?>/0.jpg"></div>
    	<div class="span4">
    		<h5 class="clip-title"><?php echo $clip->post_title; ?></h5>
    		<p class="clip-duration"><?php the_field('duration',$clip->ID); ?></p>
    		<?php if ($download_url = get_field('download_url',$clip->ID)) : ?><a class="" href="<?php echo $download_url; ?>"><i class="icon icon-white icon-arrow-down"></i> Download</a><?php endif; ?>
                <?php if (is_user_logged_in()) : global $current_user; get_currentuserinfo(); ?>
                <a class="btn-toggle" data-toggle-type="queue" data-user-id="<?php echo $current_user->ID; ?>" data-clip-id="<?php echo $clip->ID; ?>"><?php echo apply_filters('clip_toggle_queue_status', $text, $clip->ID, $current_user->ID); ?></a>
                <a class="btn-toggle" data-toggle-type="favorite" data-user-id="<?php echo $current_user->ID; ?>" data-clip-id="<?php echo $clip->ID; ?>"><?php echo apply_filters('clip_toggle_favorite_status', $text, $clip->ID, $current_user->ID); ?></a>
                <?php endif; ?>
    	</div>
    </div>
</article>
