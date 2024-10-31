<?php
	/**
	 * CedCommerce
	 * @since             1.0.0
	 * @package           product-lister-bonanza
	 *
	 * @wordpress-plugin
	 * Plugin Name:       Product Lister Bonanza
	 * Description:       Configure Your Woocommerce Store to the bonanza store and sell your products easily.
	 * Version:           1.0.2
	 * Author:            CedCommerce <cedcommerce.com>
	 * Author URI:        cedcommerce.com
	 * Text Domain:       ced-bonanza
	 * Domain Path:       /languages
	 */

	// If this file is called directly, abort.
	if ( ! defined( 'WPINC' ) ) {
		die;
	}

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-ced-bnz-activator.php
	 * @name activate_ced_bonanza_lister
	 * @author CedCommerce
	 * @since 1.0.0
	 */
	
	function activate_woocommerce_bonanza_integration() {

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-ced-bnz-activator.php';
		CED_Bonanza_Lister_Activator::activate();
	}
	
	define( 'CED_Bonanza_Lister_DIRPATH_1', plugin_dir_path( __FILE__ ).'marketplaces/' );
	register_activation_hook( __FILE__, 'activate_woocommerce_bonanza_integration' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	
	require plugin_dir_path( __FILE__ ) . 'includes/class-ced-bnz.php';

	/**
	* This file includes core functions to be used globally in plugin.
	* @author CedCommerce <plugins@cedcommerce.com>
	* @link  http://www.cedcommerce.com/
	*/
	
	require_once plugin_dir_path(__FILE__).'includes/ced_bnz_core_functions.php';
	 
	/**
	 * Check WooCommerce is Installed and Active.
	 *
	 * since Product Lister Bonanza is extension for WooCommerce it's necessary,
	 * to check that WooCommerce is installed and activated or not,
	 * if yes allow extension to execute functionalities and if not
	 * let deactivate the extension and show the notice to admin.
	 * 
	 * @author CedCommerce
	 */
	if(ced_bonanza_lister_check_woocommerce_active()){

		run_ced_bnz_bonanza();
		add_action( 'admin_init', 'ced_bonanza_lister_paid_link_notice_function' );
	}else{
		
		add_action( 'admin_init', 'deactivate_ced_bonanza_lister_woo_missing' );
	}
?>