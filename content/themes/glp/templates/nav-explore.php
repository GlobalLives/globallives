<nav id="nav-explore" class="navbar">
	<div class="nav-explore-inner navbar-inner container">
		<ul class="nav">
			<li>
				<a class="btn btn-gridview active"><i class="icon icon-th-large icon-white"></i></a>
				<a class="btn btn-mapview"><i class="icon icon-globe icon-white"></i></a>
			</li>
			<li>Series
				<select name="series">
					<option>All</option>
<?php $serieses = get_terms('series'); foreach( $serieses as $series ) : ?>
					<option value="<?php echo $series->slug; ?>"><?php echo $series->name; ?></option>
<?php endforeach; ?>
				</select>
			</li>
			<li>Gender 
				<select name="gender">
					<option>All</option>
<?php $genders = get_field_object('field_127'); foreach( $genders['choices'] as $k => $v ) : ?>
					<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
<?php endforeach; ?>
				</select>
			</li>
			<li>Income
				<select name="income">
					<option>All</option>
<?php $incomes = get_field_object('field_128'); foreach( $incomes['choices'] as $k => $v ) : ?>
					<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
<?php endforeach; ?>
				</select>
			</li>
			<li>Age
				<select name="age">
					<option>All</option>
<?php $ages = get_field_object('field_129'); foreach( $ages['choices'] as $k => $v ) : ?>
					<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
<?php endforeach; ?>
				</select>
			</li>
			<li><input name="proposed" type="checkbox" value="1" /> Show Proposed</li>
		</ul>
	</div>
</nav>