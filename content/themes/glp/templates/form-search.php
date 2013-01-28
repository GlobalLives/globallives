<form name="searchform" id="searchform" action="/" method="get" class="form-inline pull-right">
	<input type="text" name="s" id="search" value="<?php the_search_query(); ?>" placeholder="Search our collection" />
	<button type="submit" class="btn" value="Go">Go</button>
	<!-- <a href="/search">Advanced Search</a> -->
</form>