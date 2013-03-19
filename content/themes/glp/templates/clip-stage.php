<?php while(have_posts()) : the_post(); ?>
		<article class="participant-clip">
                    <div id="participant-video-embed-<?php the_ID(); ?>" class="participant-video-embed" data-youtube="<?php the_field('youtube_id'); ?>" data-src="http://www.youtube.com/embed/<?php the_field('youtube_id'); ?>?showinfo=0&amp;modestbranding=1&amp;rel=0&amp;controls=0&amp"></div>
                        <div class="participant-video-controls">
                            <div class="control-slider-area-cntnr">
                                <div class="control-slider-cntnr">
                                    <a class="taggable-area"><span><?php _e('(Click to tag or comment)', 'glp'); ?></span></a>
                                    <div class="control-slider"></div>
                                </div>
                                <div class="control-time">
                                    <span class="control-time-current"><span class="time-m"></span>:<span class="time-s"></span></span>
                                    <span class="control-time-sep">&#47;</span>
                                    <span class="control-time-total"><span class="time-m"></span>:<span class="time-s"></span></span>
                                </div>
                            </div>
                            <div class="control-buttons-cntnr">
                                <a data-control="play" class="controls-play controls"><span class="icon-play icon-white"></span></a>
                                <a data-control="pause" class="controls-pause controls"><span class="icon-pause icon-white"></span></a>
                            </div>
                        </div>
			<div class="participant-video-buttons">
				<a class="btn addthis_button"><i class="icon icon-white icon-share"></i> Share</a>
				<a class="btn" href="#embed-<?php the_field('youtube_id'); ?>" data-toggle="modal">&lt;&gt; Embed</a>
				<?php if ($download_url = get_field('download_url')) : ?><a class="btn"><i class="icon icon-white icon-arrow-down"></i> Download</a><?php endif; ?>
			</div>
			<div class="modal hide" class="embed-<?php the_field('youtube_id'); ?>">
				<div class="modal-header"><?php _e('Embed code','glp'); ?></div>
				<div class="modal-body">
					<pre>&lt;iframe width=&quot;560&quot; height=&quot;315&quot; src=&quot;http://www.youtube.com/embed/<?php the_field('youtube_id'); ?>&quot; frameborder=&quot;0&quot; allowfullscreen&gt;&lt;/iframe&gt;</pre>
				</div>
				<div class="modal-footer">
				    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php _e('Close','glp'); ?></button>
				</div>
			</div>
		</article>
<?php endwhile; ?>