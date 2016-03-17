<?php
/**
 * Plugin Name: Blox - Sandbox Addon
 * Plugin URI:  https://www.bloxwp.com
 * Description: Enables the Sandbox Addon for Blox
 * Author:      Nick Diego
 * Author URI:  http://www.outermostdesign.com
 * Version:     1.0.0
 * Text Domain: blox-sandbox
 * Domain Path: languages
 *
 * Blox is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Blox is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Blox. If not, see <http://www.gnu.org/licenses/>.
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


add_action( 'plugins_loaded', 'blox_load_sandbox_addon' );
/**
 * Load the class. Must be called after all plugins are loaded
 *
 * @since 1.0.0
 */
function blox_load_sandbox_addon() {

	// If Blox is not active or if the addon class already exists, bail...
	if ( ! class_exists( 'Blox_Main' ) || class_exists( 'Blox_Sandbox_Main' ) ) {
		return;
	}

	/**
	 * Main plugin class.
	 *
	 * @since 1.0.0
	 *
	 * @package Blox
	 * @author  Nick Diego
	 */
	class Blox_Sandbox_Main {

		/**
		 * Holds the class object.
		 *
		 * @since 1.0.0
		 *
		 * @var object
		 */
		public static $instance;

		/**
		 * Plugin version, used for cache-busting of style and script file references.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * The name of the plugin.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $plugin_name = 'Blox - Sandbox Addon';
		
		/**
		 * Unique plugin slug identifier.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $plugin_slug = 'blox-sandbox';

		/**
		 * Plugin file.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $file = __FILE__;

		/**
		 * Primary class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// Load the plugin textdomain.
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
			
			// Add additional links to the plugin's row on the admin plugin page
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

			// Initialize addon's license settings field
			add_action( 'init', array( $this, 'license_init' ) );
			
			// Run sandbox content
			add_action( 'init', array( $this, 'run_sandbox_content' ) );

			// Configure and setup the Sandox settings tab and content
			add_filter( 'blox_settings_tabs', array( $this, 'add_sandbox_tab' ) );
			add_filter( 'blox_registered_settings', array( $this, 'add_sandbox_settings' ) );
			
			// Let Blox know the addon is active
			add_filter( 'blox_get_active_addons', array( $this, 'notify_of_active_addon' ), 10 );
		}
		

		/**
		 * Loads the plugin textdomain for translation.
		 *
		 * @since 1.0.0
		 */
		public function load_textdomain() {
			load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

	
		/**
		 * Adds additional links to the plugin row meta links
		 *
		 * @since 1.0.0
		 *
		 * @param array $links   Already defined meta links
		 * @param string $file   Plugin file path and name being processed
		 *
		 * @return array $links  The new array of meta links
		 */
		function plugin_row_meta( $links, $file ) {

			// If we are not on the correct plugin, abort
			if ( $file != 'blox-sandbox/blox-sandbox.php' ) {
				return $links;
			}

			$docs_link = esc_url( add_query_arg( array(
					'utm_source'   => 'admin-plugins-page',
					'utm_medium'   => 'plugin',
					'utm_campaign' => 'BloxPluginsPage',
					'utm_content'  => 'plugin-page-link'
				), 'https://www.bloxwp.com/documentation/sandbox' )
			);

			$new_links = array(
				'<a href="' . $docs_link . '">' . esc_html__( 'Documentation', 'blox-sandbox' ) . '</a>',
			);

			$links = array_merge( $links, $new_links );

			return $links;
		}


		/**
		 * Load license settings
		 *
		 * @since 1.0.0
		 */
		public function license_init() {
			
			// Setup the license
			if ( class_exists( 'Blox_License' ) ) {
				$blox_sandbox_addon_license = new Blox_License( __FILE__, 'Sandbox Addon', '1.0.0', 'Nicholas Diego', 'blox_sandbox_addon_license_key', 'https://www.bloxwp.com', 'addons' );
			}
		}
		
		
		/**
		 * Loads all of the custom sandbox php
		 *
		 * @since 1.0.0
		 */
		public function run_sandbox_content() {
		
			$content 	  = blox_get_option( 'sandbox_content', '' );
			$disable 	  = blox_get_option( 'sandbox_disable_content', '' );
			$enable_admin = blox_get_option( 'sandbox_enable_admin', '' );
			
			// If we have PHP content and it is not disabled, continue...
			if ( ! empty( $content ) && ! $disable ) {
				if ( is_admin() && ! $enable_admin ) {
					return;
				} else {
					eval( $content ); // Potentially very dangerous, users need to use with caution!
				}
			}
		}


		/**
		 * Add the Sandbox tab to the Blox settings page
		 *
		 * @since 1.0.0
		 */
		public function add_sandbox_tab( $tabs ) {
		
			$tabs['sandbox'] = __( 'Sandbox', 'blox-sandbox' );
			
			return $tabs;		
		}
		
		
		/**
		 * Add the Sandbox settings to the new Sandbox tab
		 *
		 * @since 1.0.0
		 */
		public function add_sandbox_settings( $settings ) {
			
			$settings['sandbox'] = array(
				'sandbox_content' => array(
					'id'   		  => 'sandbox_content',
					'name' 	 	  => __( 'Sandbox Custom PHP', 'blox-sandbox' ),
					'placeholder' => __( 'With great power there must also come — great responsibility... It is highly recommended that you review the Sandbox documentation prior to use.', 'blox-sandbox' ),
					'desc' 		  => sprintf( __( 'For additional information on how to effectively use the Sandbox, please check out the %1$sSandbox Documentation%2$s.', 'blox-sandbox' ), '<a href="https://www.bloxwp.com/documentation/sandbox" target="_blank">', '</a>' ),
					'type' 		  => 'textarea',
					'class' 	  => 'blox-textarea-code',
					'size' 		  => 20,
					'default' 	  => '',
				),
				'sandbox_disable_content' => array(
					'id'    	  => 'sandbox_disable_content',
					'name'  	  => __( 'Disable Custom PHP', 'blox' ),
					'label' 	  => __( 'Disable all custom PHP', 'blox-sandbox' ),
					'desc'  	  => __( 'Quickly and easily disable your custom PHP code without having to delete it.', 'blox-sandbox' ),
					'type'  	  => 'checkbox',
					'default'	  => '',
				),
				'sandbox_enable_admin' => array(
					'id'    	  => 'sandbox_enable_admin',
					'name'  	  => __( 'Run on Admin', 'blox' ),
					'label' 	  => __( 'Run custom PHP on the frontend and admin', 'blox-sandbox' ),
					'desc'  	  => sprintf( __( 'With this option unchecked, Sandbox custom PHP is only run on the frontend. So if you break something, you can always head back to this page and make the necessary changes. However, there are many functions you may want to run that need access to the admin, such as adding/removing Genesis layout configurations, widget areas, etc. These functions will not work correctly unless this box is checked. But if you check, your custom PHP code is run at all times, meaning that if you don\'t know what you doing, you can easily break both the frontend and backend of your site. %1$sYou have been warned%2$s.', 'blox-sandbox' ), '<strong>', '</strong>' ),
					'type'  	  => 'checkbox',
					'default'	  => '',
				)
			);

			return $settings;
		}


		/**
		 * Let Blox know this addon has been activated.
		 *
		 * @since 1.0.0
		 */
		public function notify_of_active_addon( $addons ) {

			$addons['sandbox_addon'] = __( 'Sandbox Addon', 'blox-sandbox' );
			return $addons;
		}


		/**
		 * Returns the singleton instance of the class.
		 *
		 * @since 1.0.0
		 *
		 * @return object The class object.
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Sandbox_Main ) ) {
				self::$instance = new Blox_Sandbox_Main();
			}

			return self::$instance;
		}
	}

	// Load the main plugin class.
	$blox_sandbox_main = Blox_Sandbox_Main::get_instance();
}