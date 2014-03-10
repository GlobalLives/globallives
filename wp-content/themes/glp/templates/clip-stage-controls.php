<div class="participant-video-controls">
    <div class="control-slider-area-cntnr">
        <div class="control-slider-cntnr">
            <a id="taggable-area" data-toggle="popover" data-placement="top" <?php if ( !is_user_logged_in() ) { echo 'class="disabled"'; } ?>>
                <span><?php
                    if ( is_user_logged_in() ) : _e('(Click to tag or comment)', 'glp');
                    else : _e('Log in / Sign up to tag or comment', 'glp');
                    endif;
                ?></span>
            </a>
            <div class="popover-data hide">
                <div class="title"><div class="inner">Comments/Tags (<span class="time"></span>)<a class="icon-remove-circle icon-white close"></a></div></div>
                <div class="content">
                    <form method="post">
                        <div class="comment-box">
                            <input type="text" name="comment" placeholder="Comment or #tag" />
                        </div>
                    </form>
                </div>
            </div>
            <div class="clip-markers">
                <?php
                $markers = array();
                foreach ( get_comments( array( 'post_id' => get_the_ID()) ) as $comment) {
                    $time_meta = get_comment_meta($comment->comment_ID, 'clip_time', true);
                    $time = (int) ( $time_meta['m'] * 60 ) + (int) $time_meta['s'];
                    $markers[$time_meta['p']]['comments'][] = $comment;
                }
                ?>
                <?php foreach ($markers as $time => $items): ?>
                    <div id="marker-<?php echo $time; ?>" class="marker" style="left: <?php printf('%spx', $time); ?>">
                        <div class="arrow"></div>
                        <div class="hide content">
                            <?php foreach ($items['comments'] as $comment): ?>
                                <?php include(locate_template('templates/marker-comment.php')); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="control-slider"></div>
        </div>
        <div class="control-time">
            <span class="control-time-current"><span class="time-m">0</span>:<span class="time-s">00</span></span>
            <span class="control-time-sep">&#47;</span>
            <span class="control-time-total"><span class="time-m"><?php the_clip_minutes(get_the_ID()); ?></span>:<span class="time-s"><?php the_clip_seconds(get_the_ID()); ?></span></span>
        </div>
    </div>
    <div class="control-buttons-cntnr">
        <a data-control="play" class="controls btn play-pause"><span class="icon-play icon-white"></span></a>
        <a data-control="volume" class="controls btn volume">
            <span class="icon-volume-up icon-white"></span>
            <div class="volume-slider-cntnr"><div class="volume-slider"></div></div>
        </a>
        <a data-control="fullscreen" class="controls btn"><span class="icon-fullscreen icon-white"></span></a>
        <a data-control="comments" class="controls btn comments active"><span class="icon-comment icon-white"></span></a>
        <a data-control="dimmer" class="controls btn dimmer"><span class="icon-certificate icon-white"></span></a>
    </div>
</div>