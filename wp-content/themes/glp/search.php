<?php
	global $wp_query;
	$results = query_posts(array(
		's' => get_query_var('s'),
		'posts_per_page' => -1,
		'post_type' => array('clip','participant','post')
	));
	$total_results = $wp_query->found_posts;
?>
<h1 class="search-title section-title"><?php echo __('Search','glp'); ?></h1>

<div class="search-results-container page-container container">
	<div class="search-results-inner row">

		<div class="span3">
			<div class="search-sidebar">
				<h4><?php _e('Shows results for:','glp'); ?></h4>
				<div class="filter-group">
					<label class="checkbox"><input type="checkbox" name="post_type" checked value="participant" /><?php _e('Clips and participants','glp'); ?></label>
					<label class="checkbox"><input type="checkbox" name="post_type" checked value="user" />User Profiles</label>
					<label class="checkbox"><input type="checkbox" name="post_type" checked value="post" />Blog Posts</label>
					<!-- <label class="checkbox"><input type="checkbox" name="post_type" checked value="page" />Pages</label> -->
					<!-- <label class="checkbox"><input type="checkbox" name="post_type" checked value="tribe_events" />Events</label> -->
				</div>

				<hr>

				<h4><?php _e('Gender','glp'); ?></h4>
				<div class="filter-group">
<?php $genders = get_field_object($field_keys['participant_gender']); foreach( $genders['choices'] as $k => $v ) : ?>
					<label class="checkbox"><input type="checkbox" name="gender" checked value="<?php echo $k; ?>" /><?php echo $v; ?></label>
<?php endforeach; ?>
				</div>

<?php
	$themes = array();
	foreach ($results as $result) {
		if ($result->post_type == 'clip' && $tags = get_clip_tags($result->ID)) {
			foreach($tags as $tag) {
				if (array_key_exists($tag, $themes)) { $themes[$tag] += 1; }
				else { $themes[$tag] = 1; }
			}
		}
	}
	arsort($themes);
	$themes = array_keys(array_slice($themes, 0, 5));
	if ($themes) : ?>
				<h4><?php _e('Themes','glp'); ?></h4>
				<div class="filter-group">
<?php
	foreach ($themes as $theme) :
?>
					<label class="checkbox"><input type="checkbox" name="theme" checked value="<?php echo $theme; ?>" /><?php echo $theme; ?></label>
<?php endforeach; ?>
				</div>
<?php endif; ?>

				<h4><?php _e('Income','glp'); ?></h4>
				<div class="filter-group">
<?php $incomes = get_field_object($field_keys['participant_income']); foreach( $incomes['choices'] as $k => $v ) : ?>
					<label class="checkbox"><input type="checkbox" name="income" checked value="<?php echo $k; ?>" /><?php echo $v; ?></label>
<?php endforeach; ?>
				</div>

				<h4><?php _e('Age','glp'); ?></h4>
				<div class="filter-group">
<?php $ages = get_field_object($field_keys['participant_age']); foreach( $ages['choices'] as $k => $v ) : ?>
					<label class="checkbox"><input type="checkbox" name="age" checked value="<?php echo $k; ?>" /><?php echo $v; ?></label>
<?php endforeach; ?>
				</div>

				<h4><?php _e('Region','glp'); ?></h4>
				<div class="filter-group">
<?php $regions = get_field_object($field_keys['participant_continent']); foreach( $regions['choices'] as $k => $v ) : ?>
					<label class="checkbox"><input type="checkbox" name="region" checked value="<?php echo $k; ?>" /><?php echo $v; ?></label>
<?php endforeach; ?>
				</div>

<?php $serieses = get_terms('series'); if( count($serieses) > 1 ) : ?>
				<h4><?php _e('Series','glp'); ?></h4>
				<div class="filter-group">
<?php foreach( $serieses as $series ) : ?>
					<label class="checkbox"><input type="checkbox" name="series" checked value="<?php echo $series->slug; ?>"><?php echo $series->name; ?></label>
<?php endforeach; endif; ?>
				</div>

		</div>

		<div class="span9">
			<div class="search-entries">
				<h4><span class="results-found"><?php echo $total_results; ?></span> <?php _e('total results for','glp'); ?> '<?php the_search_query(); ?>'</h4>

				<?php if (!have_posts()) : ?>
				<div class="alert alert-block fade in">
					<p><?php _e('Sorry, no results were found.', 'glp'); ?></p>
				</div>
				<?php endif; ?>

<?php
	$clips_by_participant = array();
	$total_clips = 0;
	foreach ($results as $result) {
		if ($result->post_type === 'clip' && $participant = get_clip_participant($result->ID)) {
			if (!array_key_exists($participant->ID, $clips_by_participant)) {
				$clips_by_participant[$participant->ID] = array();
			}
			$clips_by_participant[$participant->ID][] = $result->ID;
			$total_clips++;
		}
	}
?>
			<h3><?php _e('Clips and participants','glp'); ?> <small><?php echo $total_clips; ?> <?php _e('results','glp'); ?></small></h3>
<?php
	foreach ($clips_by_participant as $participant_id => $participant_clips) {
		$participant = get_post($participant_id);
		include(locate_template('templates/result-participant.php'));
	}
?>

<?php
	$users = get_users(array(
		// 'search' => get_query_var('s'),
		'meta_query' => array(
			'relation' => 'OR',
			// array(
			// 	'key' => 'user_login',
			// 	'value' => get_query_var('s'),
			// 	'compare' => 'LIKE'
			// ),
			array(
				'key' => 'description',
				'value' => get_query_var('s'),
				'compare' => 'LIKE'
			),
			// array(
			// 	'key' => 'occupation',
			// 	'value' => get_query_var('s'),
			// 	'compare' => 'LIKE'
			// ),
			array(
				'key' => 'location',
				'value' => get_query_var('s'),
				'compare' => 'LIKE'
			)
		)
	));
	$total_users = count($users);
?>
			<h3><?php _e('User profiles','glp'); ?> <small><?php echo $total_users; ?> <?php _e('results','glp'); ?></small></h3>
<?php
	foreach ($users as $user) { include(locate_template('templates/result-user.php')); }
?>

<?php
	$blog_posts = array();
	$total_posts = 0;
	foreach ($results as $result) {
		if ($result->post_type === 'post') {
			$blog_posts[] = $result;
			$total_posts++;
		}
	}
?>
			<h3><?php _e('Blog posts','glp'); ?> <small><?php echo $total_posts; ?> <?php _e('results','glp');?></small></h3>
<?php
	foreach ($blog_posts as $blog_post) { include(locate_template('templates/result-post.php')); }
?>

<?php /*
	$pages = array();
	$total_pages = 0;
	foreach ($results as $result) {
		if ($result->post_type === 'page') {
			$pages[] = $result;
			$total_pages++;
		}
	}
*/ ?>
			<!-- <h3><?php _e('Pages','glp'); ?> <small><?php echo $total_pages; ?> <?php _e('results','glp');?></small></h3> -->
<?php
	//foreach ($pages as $page) { include(locate_template('templates/result.php')); }
?>

			</div>
		</div>
	</div>
</div>