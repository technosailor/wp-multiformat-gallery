<?php
/*
Plugin Name: WP Multiformat Galleries
Plugin URI:  https://github.com/technosailor/wp-multiformat-gallery
Description: Renders WordPress galleries in a variety of ways
Version:     0.1-alpha
Author:      Aaron Brazell
Author URI:  http://technosailor.com
Text Domain: wp-multiformat-galleries
Domain Path: /langs
*/

define( 'WPMFG_VERSION', '0.1-alpha' );
define( 'WPMFG_URL', plugin_dir_url( __FILE__ ) );
define( 'WPMFG_PATH', dirname( __FILE__ ) . '/' );
define( 'WPMFG_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPMFG_CLASS_DIR', WPMFG_PATH . 'classes/' );

class WP_Multiformat_Galleries {

	public function __construct() {
		$this->_hooks();
	}

	protected function _hooks() {

		// Enable Localization
		add_action( 'plugins_loaded', array( $this, 'i18n' ) );

		// Filter the core WordPress gallery output
		add_filter( 'post_gallery', array( $this, 'gallery_filter' ), 10, 3 );

		// Add necessary JavaScript assets
		add_action( 'wp_enqueue_scripts', array( $this, 'js' ) );
	}

	public function i18n() {
		load_plugin_textdomain( 'wp-multiformat-galleries', false, WPMFG_PATH . '/langs' );
	}

	public function js() {
		wp_register_script( 'cycle2', 'http://malsup.github.io/min/jquery.cycle2.min.js', array( 'jquery' ) );
		wp_register_script( 'cycle2-carousel', 'http://malsup.github.io/min/jquery.cycle2.carousel.min.js', array( 'jquery', 'cycle2' ) );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'cycle2' );
		wp_enqueue_script( 'cycle2-carousel' );
	}

	public function gallery_filter( $output, $attributes, $instance ) {

		if( ! isset( $attributes['ids'] ) ) {
			return $output;
		}

		$asset_ids = explode( ',', $attributes['ids'] );
		$media_assets = new WP_Query( array(
			'post__in'                  => $asset_ids,
			'post_type'                 => 'attachment',
			'no_found_rows'             => true,
			'update_post_term_cache'    => false,
			'posts_per_page'            => apply_filters( 'wpmfg/posts-per-page', 100 ),
		) );

		if( ! $media_assets->have_posts() ) {
			return $output;
		}

		$output = '<ul class="slides">';
		while( $media_assets->have_posts() ) {

			$media_assets->the_post();


		}

		$output .= '</ul>';
		return apply_filters( 'wpmfg/carousel-render-template', $output );
	}

	protected function _template( $image_url, $credit, $caption ) {
		global $allowed_tags;

		$html = '<li class="slide">';
			$html .= '<span class="img-wrapper">';
				$html .= sprintf( '<img src="%1$s" alt="%2$s" />', esc_url( $image_url ), esc_attr( $caption ) );
				$html .= sprintf( '<p>%s</p>', wp_kses( $credit, $allowed_tags ) );
				$html .= '<span class="img-caption">';
					$html .= wp_kses( $caption, $allowed_tags );
				$html .= '</span>';
			$html .= '</span>';
		$html .= '</li>';

		return apply_filters( 'wpmfg/single-image-render-template', $html );
	}
}

/* $attr
array (
  'columns' => '5',
  'ids' => '977,1261,1071,1042,1041,1040,1039,1038,1024,1022,967,963,906,907,904,905,842,827,811,807,771,770,769,768',
  'orderby' => 'post__in',
  'include' => '977,1261,1071,1042,1041,1040,1039,1038,1024,1022,967,963,906,907,904,905,842,827,811,807,771,770,769,768',
)
*/