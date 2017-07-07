<?php
/**
 * campaign endpoint.
 *
 * @version     1.5.0
 * @package     Charitable/Classes/Charitable_Campaign_Endpoint
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_Campaign_Endpoint' ) ) :

	/**
	 * Charitable_Campaign_Endpoint
	 *
	 * @abstract
	 * @since       1.5.0
	 */
	class Charitable_Campaign_Endpoint extends Charitable_Endpoint {

		/**
		 * @var     string
		 */
		const ID = 'campaign';

		/**
		 * Return the endpoint ID.
		 *
		 * @return 	string
		 * @since 	1.5.0
		 */
		public static function get_endpoint_id() {
			return self::ID;
		}

		/**
		 * Return the endpoint URL.
		 *
		 * @global 	WP_Rewrite $wp_rewrite
		 * @param 	array      $args
		 * @return  string
		 * @since   1.5.0
		 */
		public function get_page_url( $args = array() ) {

			$campaign_id  = array_key_exists( 'campaign_id', $args ) ? $args['campaign_id'] : get_the_ID();

			return get_permalink( $campaign_id );

		}

		/**
		 * Return whether we are currently viewing the endpoint.
		 *
		 * @global  WP_Query $wp_query
		 * @param 	array    $args
		 * @return  boolean
		 * @since   1.5.0
		 */
		public function is_page( $args = array() ) {

			global $wp_query;

			if ( ! $wp_query->is_singular( Charitable::CAMPAIGN_POST_TYPE ) ) {
				return false;
			}

			return ! array_key_exists( 'donate', $wp_query->query_vars );

		}

		/**
		 * Get the content to display for the endpoint.
		 *
		 * @param 	string $content
		 * @return  string
		 * @since   1.5.0
		 */
		public function get_content( $content ) {

			if ( ! charitable_is_main_loop() ) {
				return $content;
			}

			/**
			 * If this is the donation form, and it's showing on a separate page, return the content.
			 */
			if ( charitable_is_page( 'campaign_donation_page' ) ) {

				if ( 'separate_page' == charitable_get_option( 'donation_form_display', 'separate_page' ) ) {
					return $content;
				}

				if ( false !== get_query_var( 'donate', false ) ) {
					return $content;
				}
			}

			/**
			 * If you do not want to use the default campaign template, use this filter and return false.
			 *
			 * @uses    charitable_use_campaign_template
			 */
			if ( ! apply_filters( 'charitable_use_campaign_template', true ) ) {
				return $content;
			}

			/**
			 * Remove ourselves as a filter to prevent eternal recursion if apply_filters( 'the_content' )
			 * is called by one of the templates.
			 */
			remove_filter( 'the_content', 'charitable_template_campaign_content' );

			ob_start();

			charitable_template( 'content-campaign.php', array( 'content' => $content, 'campaign' => charitable_get_current_campaign() ) );

			$content = ob_get_clean();

			add_filter( 'the_content', 'charitable_template_campaign_content' );

			return $content;

		}

		/**
		 * Return the body class to add for the endpoint.
		 *
		 * @return 	string
		 * @since 	1.5.0
		 */
		public function get_body_class() {

			return 'campaign-donation-page';

		}
	}

endif;
