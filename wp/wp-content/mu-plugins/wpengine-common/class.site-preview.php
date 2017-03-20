<?php

namespace WPE;

class Site_Preview {

	/**
	 * The instance of this class.
	 *
	 * @var Site_Preview
	 */
	protected static $instance = null;

	/**
	 * Our custom page name.
	 *
	 * @var string
	 */
	private $pagename = 'wpe-migration-preview';

	/**
	 * Custom query argument.
	 *
	 * @var string
	 */
	private $query_var = 'wpe_migration_preview';

	/**
	 * Get the instance of this class.
	 *
	 * @return Site_Preview The instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		// Nothing here.
	}

	/**
	 * Filter the WordPress Query variables.
	 *
	 * @param array $query_vars Current array of query vars.
	 *
	 * @return array Updated array of query vars.
	 */
	public function filter_query_vars( $query_vars ) {
		$query_vars[] = $this->query_var;
		return $query_vars;
	}

	/**
	 * Determine whether to return our custom preview template.
	 *
	 * @param string $template The path of the template to include.
	 *
	 * @return string The full path to the template file.
	 */
	public function preview_template( $template ) {

		// See if this is our template page
		if ( 1 == get_query_var( $this->query_var, 0 ) ) {
			$template = dirname( WPE_PLUGIN_BASE ) . '/wpengine-common/views/wpe-migration-preview-template.php';
		}

		return $template;
	}

	/**
	 * Register our class methods with the appropriate WordPress hooks.
	 */
	public function register_hooks() {
		add_filter( 'template_include', array( $this, 'preview_template' ) );
		add_filter( 'the_posts',        array( $this, 'the_posts' ), 10, 2 );
		add_filter( 'query_vars',       array( $this, 'filter_query_vars' ) );
	}

	/**
	 * Check for our custom query and add our post to the posts array.
	 *
	 * This is done to prevent WP->handle_404() from sending a 404 header, forcing it instead to send a 200 header.
	 *
	 * @param array    $posts    The array of found posts.
	 * @param WP_Query $wp_query The WP_Query object (passed by reference).
	 *
	 * @return array The array of posts.
	 */
	public function the_posts( $posts, &$wp_query ) {
		if ( empty( $posts ) && $this->pagename == $wp_query->query_vars['pagename'] ) {
			$posts = array( $this->pagename );
		}

		return $posts;
	}
}
