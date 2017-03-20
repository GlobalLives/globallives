<?php global $field_keys; ?>
<nav id="nav-explore" class="navbar">
	<div class="nav-explore-inner navbar-inner container">
		<ul class="nav">
			<li>
				<a class="btn btn-gridview active"><i class="icon icon-th-large icon-white"></i> <?php _e('Grid View','glp'); ?></a>
				<a class="btn btn-mapview"><i class="icon icon-globe icon-white"></i> <?php _e('Map View','glp'); ?></a>
			</li>
<?php $serieses = get_terms('series'); if( count($serieses) > 1 ) : ?>
			<li>Series
				<select name="series">
					<option>All</option>
<?php foreach( $serieses as $series ) : ?>
					<option value="<?php echo $series->slug; ?>"><?php echo $series->name; ?></option>
<?php endforeach; ?>
				</select>
			</li>
<?php endif; ?>
			<li>Gender 
				<select name="gender">
					<option>All</option>
<?php $genders = get_field_object($field_keys['participant_gender']); foreach( $genders['choices'] as $k => $v ) : ?>
					<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
<?php endforeach; ?>
				</select>
			</li>
			<li>Income
				<select name="income">
					<option>All</option>
<?php $incomes = get_field_object($field_keys['participant_income']); foreach( $incomes['choices'] as $k => $v ) : ?>
					<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
<?php endforeach; ?>
				</select>
			</li>
			<li>Age
				<select name="age">
					<option>All</option>
<?php $ages = get_field_object($field_keys['participant_age']); foreach( $ages['choices'] as $k => $v ) : ?>
					<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
<?php endforeach; ?>
				</select>
			</li>
<?php $proposed = get_posts(array('numberposts' => 1, 'post_type' => 'participant', 'meta_key' => 'proposed', 'meta_value' => 1)); if(count($proposed)) : ?>
			<li><input name="proposed" type="checkbox" /> <?php _e('Show Proposed','glp'); ?></li>
<?php endif; ?>
		</ul>
	</div>
</nav>