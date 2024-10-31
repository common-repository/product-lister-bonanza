<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * plugin admin pages related functionality helper class.
 *
 * @since      1.0.0
 *
 * @package    Product Lister Bonanza
 * @subpackage Product Lister Bonanza/admin/helper
 */

if( !class_exists( 'CED_Bonanza_Lister_menu_page_manager' ) ) :

/**
 * Admin pages related functionality.
 *
 * Manage all admin pages related functionality of this plugin.
 *
 * @since      1.0.0
 * @package    Product Lister Bonanza
 * @subpackage Product Lister Bonanza/admin/helper
 * @author     CedCommerce <cedcommerce.com>
 */
class CED_Bonanza_Lister_menu_page_manager{
	
	/**
	 * The Instace of CED_Bonanza_Lister_menu_page_manager.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      $_instance   The Instance of CED_Bonanza_Lister_menu_page_manager class.
	 */
	private static $_instance;
	
	/**
	 * CED_Bonanza_Lister_menu_page_manager Instance.
	 *
	 * Ensures only one instance of CED_Bonanza_Lister_menu_page_manager is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return CED_Bonanza_Lister_menu_page_manager instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Creating admin pages of Product Lister Bonanza.
	 * 
	 * @since 1.0.0
	 */
	public function create_pages(){

		add_menu_page('Bonanza', 'Bonanza', 'manage_woocommerce', 'bnz-bonanza-main', array( $this, 'ced_bonanza_lister_marketplace_page' ),'', 60 );
		
		add_submenu_page('bnz-bonanza-main', __('Configuration','ced-bonanza'), __('Configuration','ced-bonanza'), 'manage_woocommerce', 'bnz-bonanza-main', array( $this, 'ced_bonanza_lister_marketplace_page' ) );
		
		add_submenu_page('bnz-bonanza-main', __('Category Mapping','ced-bonanza'), __('Category Mapping','ced-bonanza'), 'manage_woocommerce', 'bnz-bonanza-cat-map', array( $this, 'ced_bonanza_lister_category_map_page' ) );
		
		add_submenu_page('bnz-bonanza-main', __('Profile','ced-bonanza'), __('Profile','ced-bonanza'), 'manage_woocommerce', 'bnz-bonanza-profile', array( $this, 'ced_bonanza_lister_profile_page' ) );
		
		add_submenu_page('bnz-bonanza-main', __('Manage Products','ced-bonanza'), __('Manage Products','ced-bonanza'), 'manage_woocommerce', "bnz-bonanza-pro-mgmt", array( $this, 'ced_bonanza_lister_pro_mgmt_page' ) );
		 
		add_submenu_page('bnz-bonanza-main', __('Prerequisite','ced-bonanza'), __('Prerequisite','ced-bonanza'), 'manage_woocommerce', 'bnz-bonanza-prerequisites', array( $this, 'ced_bonanza_lister_prerequisite_page' ) );
		add_submenu_page('bnz-bonanza-main', __('Features','ced-bonanza'), __('Features','ced-bonanza'), 'manage_woocommerce', 'bnz-bonanza-features', array( $this, 'ced_bonanza_lister_features_page' ) );
	} 
	
	/**
	 * Marketplaces page.
	 * 
	 * @since 1.0.0
	 */
	public function ced_bonanza_lister_marketplace_page()
	{
		 
		require_once CED_Bonanza_Lister_DIRPATH.'admin/pages/marketplaces.php';
		 
	}
	
	/**
	 * Category mapping page panel.
	 * 
	 *  @since 1.0.0
	 */
	public function ced_bonanza_lister_category_map_page(){
		
		require_once CED_Bonanza_Lister_DIRPATH.'admin/pages/category_mapping.php';
	}
	
	/**
	 * Products management page panel.
	 *
	 *  @since 1.0.0
	 */
	public function ced_bonanza_lister_pro_mgmt_page(){
	
		require_once CED_Bonanza_Lister_DIRPATH.'admin/pages/manage_products.php';
	}
	
	/**
	 * Profile page for easy product uploading.
	 * 
	 * @since 1.0.0
	 */
	public function ced_bonanza_lister_profile_page(){
		
		require_once CED_Bonanza_Lister_DIRPATH.'admin/pages/profile.php';
	}
	 
	/**
	 * prerequisite page.
	 *
	 * @since 1.0.0
	 */
	public function ced_bonanza_lister_prerequisite_page(){
		require_once CED_Bonanza_Lister_DIRPATH.'admin/pages/prerequisite.php';
	}

	/**
	 * features page.
	 *
	 * @since 1.0.0
	 */
	public function ced_bonanza_lister_features_page(){
		require_once CED_Bonanza_Lister_DIRPATH.'admin/pages/features.php';
	}
}
endif;