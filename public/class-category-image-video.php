<?php
/**
 * Public-Facing Functionality.
 *
 * @package   Category_Image_Video
 * @author    Dmitriy Chekhovkiy <chehovskiy.dima@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/cheh/
 * @copyright 2017 Dmitriy Chekhovkiy
 */

if ( ! class_exists( 'CIV_Plugin' ) ) {
	class CIV_Plugin {

		/**
		 * Plugin version, used for cache-busting of style and script file references.
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		const VERSION = '1.1.0';

		/**
		 * The variable name is used as the text domain when internationalizing strings
		 * of text. Its value should match the Text Domain file header in the main
		 * plugin file.
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		protected $plugin_slug = 'category-image-video';

		/**
		 * Instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		protected static $instance = null;

		/**
		 * Initialize the plugin by setting localization and loading public scripts
		 * and styles.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			add_filter( 'get_the_archive_description', array( $this, 'add_image_to_description' ), 12, 1 );
			add_filter( 'get_the_archive_description', array( $this, 'add_video_to_description' ), 11, 1 );

			// Load public-facing assets.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 9 );
		}

		public function add_image_to_description( $description ) {

			if ( ! is_category() ) {
				return $description;
			}

			$term_id = get_queried_object()->term_id;

			// Get category image meta.
			$image_id  = get_term_meta( $term_id, 'cvi-image', true );

			if ( empty( $image_id ) ) {
				return $description;
			}

			/**
			 * Filters the image before it is retrieved.
			 *
			 * @since 1.0.0
			 * @param bool|mixed $pre_image Value to return instead of the image.
			 *                              Default false to skip it.
			 * @param string     $image_id  Attachment ID.
			 */
			$pre_image = apply_filters( 'cvi_pre_descr_image', false, $image_id );

			if ( false !== $pre_image ) {
				return $pre_image . $description;
			}

			/**
			 * Filters the image size.
			 *
			 * @since 1.0.0
			 * @param string $image_size
			 */
			$image_size = apply_filters( 'cvi_descr_image_size', 'full' );
			$image_atts = wp_get_attachment_image_src( $image_id, $image_size );

			if ( ! is_array( $image_atts ) ) {
				return $description;
			}

			$format = '<div class="cvi-taxonomy-description__image"><img src="%s" width="%d" height="%s" alt=""></div>';
			$image  = sprintf( $format,
				esc_url( $image_atts[0] ),
				absint( $image_atts[1] ),
				absint( $image_atts[2] )
			);

			return apply_filters( 'cvi_descr_image_html', $image ) . $description;
		}

		public function add_video_to_description( $description ) {

			if ( ! is_category() ) {
				return $description;
			}

			$term_id = get_queried_object()->term_id;

			// Get category video meta.
			$video_src = get_term_meta( $term_id, 'cvi-video', true );

			if ( empty( $video_src ) ) {
				return $description;
			}

			/**
			 * Filters the video before it is retrieved.
			 *
			 * @since 1.0.0
			 * @param bool|mixed $pre_video Value to return instead of the video.
			 *                              Default false to skip it.
			 * @param string     $video_src Video URL.
			 */
			$pre_video = apply_filters( 'cvi_pre_descr_video', false, $video_src );

			if ( false !== $pre_video ) {
				return $pre_video . $description;
			}

			$oembed = wp_oembed_get( $video_src, apply_filters( 'cvi_descr_video_args', array() ) );

			if ( false === $oembed ) {
				return $description;
			}

			$format = '<div class="cvi-taxonomy-description__video cvi-embed-responsive">%s</div>';
			$video  = sprintf( $format, $oembed );

			return apply_filters( 'cvi_descr_video_html', $video ) . $description;
		}

		/**
		 * Return the plugin slug.
		 *
		 * @since  1.0.0
		 * @return Plugin slug variable.
		 */
		public function get_plugin_slug() {
			return $this->plugin_slug;
		}

		/**
		 * Enqueue public-facing assets.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_assets() {

			if ( ! is_category() ) {
				return;
			}

			wp_enqueue_style(
				$this->plugin_slug . '-plugin-styles',
				plugins_url( 'assets/css/public.css', __FILE__ ),
				array(),
				self::VERSION
			);
		}

		/**
		 * Return an instance of this class.
		 *
		 * @since  1.0.0
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}
	}
}

if ( ! function_exists( 'civ_plugin' ) ) {
	function civ_plugin() {
		return CIV_Plugin::get_instance();
	}
}
