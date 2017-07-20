<?php
/**
 * @package WP Smush
 *
 * @version 2.4
 *
 * @author Umesh Kumar <umesh@incsub.com>
 *
 * @copyright (c) 2016, Incsub (http://incsub.com)
 */
if ( ! class_exists( 'WpSmushShare' ) ) {

	class WpSmushShare {
		function __construct() {}

		function share_widget() {
			global $wpsmushit_admin;
			$savings     = $wpsmushit_admin->stats;

			//If there is any saving, greater than 1Mb, show stats
			if ( empty( $savings ) || empty( $savings['bytes'] ) || $savings['bytes'] <= 1048576 || $savings['total_images'] <= 1 || ! is_super_admin() ) {
				return false;
			}
			$message   = sprintf( esc_html__( "%s, you've smushed %s%d%s images and saved %s%s%s in total. Help your friends save bandwidth easily, and help me in my quest to Smush the internet!", "wp-smushit" ), $wpsmushit_admin->get_user_name(), '<span class="smush-share-image-count">', $savings['total_images'], '</span>', '<span class="smush-share-savings">', $savings['human'], '</span>' );
			$share_msg = sprintf( esc_html__( 'I saved %s%s%s on my site with WP Smush ( %s ) - wanna make your website light and faster?', "wp-smushit" ) , '<span class="smush-share-savings">', $savings['human'], '</span>', urlencode( "https://wordpress.org/plugins/wp-smushit/" ) ); ?>
			<section class="dev-box" id="wp-smush-share-widget">
			<div class="box-content roboto-medium">
				<p class="wp-smush-share-message"><?php echo $message; ?></p>
				<div class="wp-smush-share-buttons-wrapper">
					<!-- Twitter Button -->
					<a href="https://twitter.com/intent/tweet?text=<?php echo esc_attr( $share_msg ); ?>"
					   class="button wp-smush-share-button" id="wp-smush-twitter-share">
						<i class="dev-icon dev-icon-twitter"></i><?php esc_html_e( "TWEET", "wp-smushit" ); ?></a>
					<!-- Facebook Button -->
					<a href="http://www.facebook.com/sharer.php?s=100&p[title]=WP Smush&p[url]=http://wordpress.org/plugins/wp-smushit/"
					   class="button wp-smush-share-button" id="wp-smush-facebook-share">
						<i class="dev-icon dev-icon-facebook"></i><?php esc_html_e( "SHARE", "wp-smushit" ); ?></a>
					<a href="whatsapp://send?text='<?php echo esc_attr( $share_msg ); ?>'"
					   class="button wp-smush-share-button"
					   id="wp-smush-whatsapp-share">
						<?php esc_html_e( "WhatsApp", "wp-smushit" ); ?></a>
				</div>
			</div>
			</section><?php
		}

	}

	global $wpsmush_share;
	$wpsmush_share = new WpSmushShare();
}