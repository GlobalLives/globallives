<?php
  class glt_widget extends WP_Widget {
      function __construct() {
        parent::__construct(
	  'glt_widget', __('Google Language Translator', 'text_domain'), array( 'description' => __( 'Add the Google Language Translator website tool.', 'text_domain' ), ) 
        );
      }

      public function widget( $args, $instance ) {
	$title = apply_filters( 'widget_title', $instance['title'] );
	  echo $args['before_widget'];
	    if ( ! empty( $title ) )
	      echo $args['before_title'] . $title . $args['after_title'];
          echo do_shortcode('[google-translator]');
	  echo $args['after_widget'];
      }

      public function form( $instance ) {
	if ( isset( $instance[ 'title' ] ) ) {
	  $title = $instance[ 'title' ];
	} else {
	  $title = __( 'Translate:', 'text_domain' );
	} ?>

        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"></p>
	<?php 
      }
  
      public function update( $new_instance, $old_instance ) {
	  $instance = array();
	  $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
            return $instance;
      }
} // class glt_widget

function register_glt_widget() {
    register_widget( 'glt_widget' );
}
add_action( 'widgets_init', 'register_glt_widget' );