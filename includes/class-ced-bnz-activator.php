<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Product Lister Bonanza
 * @subpackage Product Lister Bonanza/includes
 */
class CED_Bonanza_Lister_Activator {

	/**
	 * Activation actions.
	 *
	 * All required actions on plugin activation.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		require_once plugin_dir_path(__FILE__).'ced_bnz_core_functions.php';
		if(ced_bonanza_lister_check_woocommerce_active()){

			self::create_tables();
		}
	}
	
	/**
	 * Tables necessary for this plugin.
	 * 
	 * @since 1.0.0
	 */
	private static function create_tables(){
		
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		define( 'CED_Bonanza_Lister_TABLE_PREFIX' , 'ced_bonanza_lister' );
		$prefix = $wpdb->prefix . CED_Bonanza_Lister_TABLE_PREFIX;
		$table_name = "{$prefix}_bonanzaprofiles";
		// profile table
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $create_profile = "CREATE TABLE {$prefix}_bonanzaprofiles (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,`name` VARCHAR(255) NOT NULL DEFAULT '',active bool NOT NULL DEFAULT true,marketplace VARCHAR(255) DEFAULT 'bonanza',profileID VARCHAR(255) DEFAULT 'bonanza',profile_data longtext DEFAULT NULL,profile_required_attribute longtext DEFAULT NULL,PRIMARY KEY (id));";
            dbDelta( $create_profile );
        }
		
		update_option('ced_bonanza_lister_database_version',CED_Bonanza_Lister_VERSION);
	}
}
?>