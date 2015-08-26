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

new WP_Multiformat_Galleries();

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

		if( empty( $asset_ids ) ) {
			return $output;
		}

		$output = sprintf( '<span class="cycle-prev" id="prev">%s</span>', apply_filters('wpmfg/previous-icon', '<span class="dashicons dashicons-arrow-left"></span>' ) );
		$output .= sprintf( '<span class="cycle-next" id="next">%s</span></span>', apply_filters( 'wpmfg/next-icon', '<span class="dashicons dashicons-arrow-right">' ) );

		$output .= '<ul class="slides cycle-slideshow"
			data-cycle-fx="scrollHorz"
    		data-cycle-slides="> li"
    		data-cycle-prev="#prev"
        	data-cycle-next="#next"
        	data-cycle-carousel-fluid="true"
        	data-cycle-carousel-visible="3"
        	data-cycle-auto-height="3:2"
        	data-cycle-fx="scrollHorz"
        	data-cycle-tile-vertical="false"
        >';

		foreach( $asset_ids as $asset_id ) {

			$image_url = wp_get_attachment_image_src( $asset_id, 'large' );
			$image_url = ( isset( $image_url[0] ) ) ? $image_url[0] : false;

			if( ! $image_url ) {
				continue;
			}

			$caption = 'This is a caption';
			$credit = get_post_meta( $asset_id, '_wp_attachment_image_alt', true );

			$output .= $this->_template( $image_url, $credit, $caption );

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
