<?php
/**
 * Plugin tools.
 *
 * @package   Category_Image_Video
 * @author    Dmitriy Chekhovkiy <chehovskiy.dima@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/cheh/
 * @copyright 2017 Dmitriy Chekhovkiy
 */

if ( ! class_exists( 'CIV_Plugin_Tools' ) ) {
	class CIV_Plugin_Tools {

		/**
		 * Retrieve a video provider by URL.
		 *
		 * @since  1.0.0
		 * @param  string $src
		 * @return string
		 */
		public static function get_video_provider( $src ) {
			// Patterns copy from /wp-includes/media.php
			$yt_pattern    = '#^https?://(?:www\.)?(?:youtube\.com/watch|youtu\.be/)#';
			$vimeo_pattern = '#^https?://(.+\.)?vimeo\.com/.*#';

			if ( preg_match( $yt_pattern, $src ) ) {
				return 'youtube';
			}

			if ( preg_match( $vimeo_pattern, $src ) ) {
				return 'vimeo';
			}

			return 'embed';
		}

		/**
		 * Extract the YouTube Video ID from a URL.
		 *
		 * @link   https://gist.github.com/ghalusa/6c7f3a00fd2383e5ef33
		 * @since  1.0.0
		 * @param  string $src
		 * @return string|bool
		 */
		public static function get_youtube_video_id( $src ) {
			$pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';

			if ( ! preg_match( $pattern, $src, $match ) ) {
				return false;
			}

			if ( empty( $match[1] ) ) {
				return false;
			}

			return $match[1];
		}

		/**
		 * Extract the Vimeo Video ID from a URL.
		 *
		 * @link   https://gist.github.com/anjan011/1fcecdc236594e6d700f
		 * @since  1.0.0
		 * @param  string $src
		 * @return string|bool
		 */
		public static function get_vimeo_video_id( $src ) {
			$pattern = '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im';

			if ( ! preg_match( $pattern, $src, $match ) ) {
				return false;
			}

			if ( empty( $match[3] ) ) {
				return false;
			}

			return $match[3];
		}

		/**
		 * Retrieve a vimeo thumbnail URL.
		 *
		 * @since  1.0.0
		 * @param  string $id
		 * @param  init   $term_id
		 * @return string|WP_Error
		 */
		public static function get_vimeo_thumbnail( $id, $term_id ) {
			$thumbnail_url = get_transient( 'cvi_vimeo_thumbnail_for_cat_' . $term_id );

			if ( false === $thumbnail_url ) {
				$url           = sprintf( 'https://vimeo.com/api/v2/video/%s.json', $id );
				$response      = wp_remote_get( $url );
				$response_code = wp_remote_retrieve_response_code( $response );

				if ( '' === $response_code ) {
					return new WP_Error;
				}

				$result = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( ! is_array( $result ) ) {
					return new WP_Error;
				}

				if ( empty( $result[0]['thumbnail_medium'] ) ) {
					return new WP_Error;
				}

				$thumbnail_url = $result[0]['thumbnail_medium'];

				// Save cache for vimeo thumbnail.
				set_transient( 'cvi_vimeo_thumbnail_for_cat_' . $term_id, $thumbnail_url, HOUR_IN_SECONDS );
			}

			return $thumbnail_url;
		}
	}
}
