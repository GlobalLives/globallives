<?php
/**
 * Adds WPE_Powered_By_Widget widget.
 */
class WPE_Powered_By_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'wpe_powered_by_widget', // Base ID
			__( 'Powered By WP Engine', 'wpengine' ), // Name
			array( 'description' => __( 'Easily show your love for WP Engine', 'wpengine' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		
		$wpe_common = WpeCommon::instance();
		// Set some defaults for when the widget is called directly.
		$affiliate_link = ! empty( $instance['affiliate_link'] ) ? $instance['affiliate_link'] : '';
		$theme = ! empty( $instance['theme'] ) ? $instance['theme'] : 'dark';
		
		// Get the correct image for the specified theme.
		$logo = WPE_PLUGIN_URL . '/images/wpengine_small_' . $theme . '.png';
		
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		$wpe_common->view( 'general/powered-by', array( 'affiliate_link' => $affiliate_link, 'logo' => $logo ) );
		
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		// Get our saved values, or set defaults.
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$affiliate_link = ! empty( $instance['affiliate_link'] ) ? $instance['affiliate_link'] : '';
		$theme = ! empty( $instance['theme'] ) ? $instance['theme'] : 'dark';
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( esc_attr( 'Title:' ) ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		
		<label for="<?php echo esc_attr( $this->get_field_id( 'affiliate_link' ) ); ?>"><?php _e( esc_attr( 'Affiliate Link (including https://):' ) ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'affiliate_link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'affiliate_link' ) ); ?>" type="text" value="<?php echo esc_attr( $affiliate_link ); ?>">
		
		<label for="<?php echo esc_attr( $this->get_field_id( 'theme' ) ); ?>"><?php _e( esc_attr( 'Theme:' ) ); ?></label> 
		<select class='widefat' id="<?php echo $this->get_field_id('theme'); ?>" name="<?php echo $this->get_field_name('theme'); ?>" type="text">
			<option value='dark'<?php echo ( $theme === 'dark' ) ? 'selected' : ''; ?>>
				Dark
			</option>
			<option value='light'<?php echo ( $theme === 'light' ) ? 'selected' : ''; ?>>
				Light
			</option> 
		</select> 
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['affiliate_link'] = ( ! empty( $new_instance['affiliate_link'] ) ) ? strip_tags( $new_instance['affiliate_link'] ) : '';
		$instance['theme'] = ( ! empty( $new_instance['theme'] ) ) ? strip_tags( $new_instance['theme'] ) : 'dark';

		return $instance;
	}

}