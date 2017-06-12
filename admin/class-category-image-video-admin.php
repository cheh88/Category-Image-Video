<?php
/**
 * Dashboard and Administrative Functionality.
 *
 * @package   Category_Image_Video_Admin
 * @author    Dmitriy Chekhovkiy <chehovskiy.dima@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/cheh/
 * @copyright 2017 Dmitriy Chekhovkiy
 */

if ( ! class_exists( 'CIV_Plugin_Admin' ) ) {
	class CIV_Plugin_Admin {

		/**
		 * Instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		protected static $instance = null;

		/**
		 * Hook suffix.
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		protected $hook_suffix = 'edit-category';

		/**
		 * Initialize the plugin by loading admin scripts & styles.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			add_action( 'category_add_form_fields', array( $this, 'render_add_fields' ), 10 );
			add_action( 'category_edit_form_fields', array( $this, 'render_edit_fields' ), 10, 2 );

			add_action( 'create_category', array( $this, 'save_meta' ), 10, 2 );
			add_action( 'edit_category', array( $this, 'save_meta' ), 10, 2 );

			// Load admin stylesheet and javascript.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		}

		/**
		 * Enqueue admin-specific stylesheet and javascript.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_admin_assets() {
			$screen = get_current_screen();

			if ( $this->hook_suffix !== $screen->id ) {
				return;
			}

			$plugin_slug = civ_plugin()->get_plugin_slug();

			wp_enqueue_media();

			wp_enqueue_style(
				$plugin_slug . '-admin-styles',
				plugins_url( 'assets/css/admin.css', __FILE__ ),
				array(),
				CIV_Plugin::VERSION
			);

			wp_register_script(
				$plugin_slug . '-admin-script',
				plugins_url( 'assets/js/admin.js', __FILE__ ),
				array( 'jquery' ),
				CIV_Plugin::VERSION
			);

			$l10n = apply_filters( 'cvi_l10n_data', array(
				'media_frame_title' => esc_html__( 'Category Image', 'category-image-video' ),
				'media_frame_btn'   => esc_html__( 'Set category image', 'category-image-video' ),
			) );

			wp_localize_script( $plugin_slug . '-admin-script', 'cvi', $l10n );
			wp_enqueue_script( $plugin_slug . '-admin-script' );
		}

		/**
		 * Render add term form fields.
		 *
		 * @since  1.0.0
		 * @param  string $taxonomy Taxonomy name.
		 * @return void
		 */
		public function render_add_fields( $taxonomy ) {
			wp_nonce_field( basename( __FILE__ ), 'cvi_categoty_meta_nonce' );

			$fields = $this->get_custom_fields_name();

			foreach ( ( array ) $fields as $key => $field ) {

				if ( 'image' === $key ) {
					$html = $this->get_image_field( $field, false, $taxonomy );
					include( 'views/form-field.php' );

				} elseif ( 'video' === $key ) {
					$html = $this->get_video_field( $field, false, $taxonomy );
					include( 'views/form-field.php' );

				} else {
					do_action( 'cvi_render_add_custom_fields', $field, $taxonomy );
				}
			}
		}

		/**
		 * Render edit term form fields
		 *
		 * @since  1.0.0
		 * @param  object $term     Current term object.
		 * @param  string $taxonomy Taxonomy name.
		 * @return void
		 */
		public function render_edit_fields( $term, $taxonomy ) {
			wp_nonce_field( basename( __FILE__ ), 'cvi_categoty_meta_nonce' );

			$fields = $this->get_custom_fields_name();

			foreach ( ( array ) $fields as $key => $field ) {

				if ( 'image' === $key ) {
					$html = $this->get_image_field( $field, $term, $taxonomy );
					include( 'views/form-table-row.php' );

				} elseif ( 'video' === $key ) {
					$html = $this->get_video_field( $field, $term, $taxonomy );
					include( 'views/form-table-row.php' );

				} else {
					do_action( 'cvi_render_edit_custom_fields', $field, $term, $taxonomy );
				}
			}
		}

		/**
		 * Get image control field.
		 *
		 * @since  1.0.0
		 * @param  array  $field_args
		 * @param  mixed  $term     Current term object.
		 * @param  string $taxonomy Current taxonomy name.
		 * @return string
		 */
		public function get_image_field( $field_args, $term, $taxonomy ) {
			// See if there's a media ID already saved as term meta.
			$image_id = false !== $term ? get_term_meta( $term->term_id, $field_args['name'], true ) : null;

			// Get the image src.
			$image_src = wp_get_attachment_image_src( $image_id, 'thumbnail' );

			// For convenience, see if the array is valid.
			$image_is_set = is_array( $image_src );

			ob_start();
			include( 'views/image.php' );
			return ob_get_clean();
		}

		/**
		 * Get video control field.
		 *
		 * @since  1.0.0
		 * @param  array  $field_args
		 * @param  mixed  $term     Current term object.
		 * @param  string $taxonomy Current taxonomy name.
		 * @return string
		 */
		public function get_video_field( $field_args, $term, $taxonomy ) {
			$value  = false !== $term ? get_term_meta( $term->term_id, $field_args['name'], true ) : '';
			$format = '<input type="text" value="%2$s" name="%1$s" id="%1$s">';
			$field  = sprintf( $format, esc_attr( $field_args['name'] ), esc_attr( $value ) );

			if ( empty( $value ) ) {
				return $field;
			}

			$provider   = CIV_Plugin_Tools::get_video_provider( $value );
			$remove_btn = sprintf( '<p class="hide-if-no-js"><button type="button" id="cvi-video-remove" class="button button-secondary">%s</button></p>', esc_html__( 'Remove', 'category-image-video' ) );
			$format     = '<div id="cvi-video-container">%s</div>' ;
			$preview    = '';

			switch ( $provider ) {
				case 'youtube':
					$video_id = CIV_Plugin_Tools::get_youtube_video_id( $value );

					if ( ! $video_id ) {
						$field .= sprintf( '<div class="notice notice-error inline"><p>%s</p></div>', esc_html__( 'Please enter a valid YouTube URL.', 'category-image-video' ) );

						return $field . $remove_btn;
					}

					$thumbnail = sprintf( '<img src="http://img.youtube.com/vi/%s/mqdefault.jpg" alt="">', $video_id );
					$preview   = sprintf( $format, $thumbnail );
					break;

				case 'vimeo':
					$video_id = CIV_Plugin_Tools::get_vimeo_video_id( $value );

					if ( ! $video_id ) {
						$field .= sprintf( '<div class="notice notice-error inline"><p>%s</p></div>', esc_html__( 'Please enter a valid Vimeo URL.', 'category-image-video' ) );

						return $field . $remove_btn;
					}

					$thumbnail_url = CIV_Plugin_Tools::get_vimeo_thumbnail( $video_id, $term->term_id );

					if ( is_wp_error( $thumbnail_url ) ) {
						return $field . $remove_btn;
					}

					$thumbnail = sprintf( '<img src="%s" alt="">', esc_url( $thumbnail_url ) );
					$preview   = sprintf( $format, $thumbnail );

					break;

				default:
					$preview = wp_oembed_get( $value, array(
						'width' => 350,
					) );

					if ( ! $preview ) {
						$field .= sprintf( '<div class="notice notice-error inline"><p>%s</p></div>', esc_html__( 'This video provider not supported.', 'category-image-video' ) );

						return $field . $remove_btn;
					}

					break;
			}

			return $preview . $field . $remove_btn;
		}

		/**
		 * Save additional taxonomy meta on edit or create category.
		 *
		 * @since  1.0.0
		 * @param  int $term_id Term ID.
		 * @param  int $tt_id   Term taxonomy ID.
		 * @return bool
		 */
		public function save_meta( $term_id, $tt_id ) {

			if ( ! isset( $_POST['cvi_categoty_meta_nonce'] )
				|| ! wp_verify_nonce( $_POST['cvi_categoty_meta_nonce'], basename( __FILE__ ) )
			) {
				return;
			}

			$fields = $this->get_custom_fields_name();

			foreach ( $fields as $key => $field ) {

				if ( ! isset( $_POST[ $field['name'] ] ) ) {
					continue;
				}

				$old_value = get_term_meta( $term_id, $field['name'], true );
				$new_value = sanitize_text_field( $_POST[ $field['name'] ] );

				if ( $old_value && '' === $new_value ) {
					delete_term_meta( $term_id, $field['name'] );

				} else if ( $old_value !== $new_value ) {
					update_term_meta( $term_id, $field['name'], $new_value );
				}

				if ( 'video' === $key ) {
					delete_transient( sanitize_key( $old_value ) );
				}
			}

			// Clear cached vimeo thumbnail.
			delete_transient( 'cvi_vimeo_thumbnail_for_cat_' . $term_id );
		}

		/**
		 * Retrieve a fields set.
		 *
		 * @since 1.0.0
		 */
		public function get_custom_fields_name() {
			return apply_filters( 'cvi_get_custom_fields_name', array(
				'image' => array(
					'name'        => 'cvi-image',
					'label'       => esc_html__( 'Image', 'category-image-video' ),
					'description' => esc_html__( 'Uploading image from Media Library', 'category-image-video' ),
				),
				'video' => array(
					'name'        => 'cvi-video',
					'label'       => esc_html__( 'Video', 'category-image-video' ),
					'description' => esc_html__( 'Enter a link to video from YouTube/Vimeo', 'category-image-video' ),
				),
			), $this );
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
