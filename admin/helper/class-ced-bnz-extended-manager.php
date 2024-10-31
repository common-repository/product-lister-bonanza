<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}

	/**
	 * Adds extended functionality as needed in core plugin.
	 *
	 * @class    CED_Bonanza_Lister_Extended_Manager
	 * @version  1.0.0
	 * @category Class
	 * @author   CedCommerce
	 */

	class CED_Bonanza_Lister_Extended_Manager {

		public function __construct() {
			$this->ced_bonanza_lister_extended_manager_add_hooks_and_filters();
		}
		
		/**
		 * This function hooks into all filters and actions available in core plugin.
		 * @name ced_bonanza_lister_extended_manager_add_hooks_and_filters()
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @link  http://www.cedcommerce.com/
		 */
		public function ced_bonanza_lister_extended_manager_add_hooks_and_filters() {
			add_action('admin_enqueue_scripts',array($this,'ced_bonanza_lister_extended_manager_admin_enqueue_scripts'));
			add_action('wp_ajax_fetch_all_meta_keys_related_to_selected_product', array($this,'fetch_all_meta_keys_related_to_selected_product'));
			add_action('wp_ajax_ced_bonanza_lister_searchProductAjaxify', array($this,'ced_bonanza_lister_searchProductAjaxify'));
		 
			add_action( 'wp_ajax_ced_bonanza_lister_updateMetaKeysInDBForProfile', array($this,'ced_bonanza_lister_updateMetaKeysInDBForProfile' ));
		}
		 
	 	/**
	 	* Function to update meta key in database for profile details
	 	*/
		function ced_bonanza_lister_updateMetaKeysInDBForProfile() {
			$metaKey 	=	sanitize_text_field( $_POST['metaKey'] );
			$actionToDo 	=	 sanitize_text_field ($_POST['actionToDo']);
			$allMetaKeys = get_option('CedbnzProfileSelectedMetaKeys', array());
			if($actionToDo == 'append') {
				if(!in_array($metaKey, $allMetaKeys)){
					$allMetaKeys[] = $metaKey;
				}
			}
			else{
				
				if(in_array($metaKey, $allMetaKeys)){
					if(($key = array_search($metaKey, $allMetaKeys)) !== false) {
					    unset($allMetaKeys[$key]);
					}
				}
			}
			update_option('CedbnzProfileSelectedMetaKeys', $allMetaKeys);
			wp_die();
		}
	 
		/*
		* Search product on manage product page
		*/
		function ced_bonanza_lister_searchProductAjaxify( $x='',$post_types = array( 'product' ) ) {
			global $wpdb;
			
			ob_start();
			
			$term = (string) wc_clean( stripslashes( $_POST['term'] ) );
			if ( empty( $term ) ) {
				die();
			}
			$like_term = '%' . $wpdb->esc_like( $term ) . '%';
			if ( is_numeric( $term ) ) {
				$query = $wpdb->prepare( "
						SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
						WHERE posts.post_status = 'publish'
						AND (
						posts.post_parent = %s
						OR posts.ID = %s
						OR posts.post_title LIKE %s
						OR (
						postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
						)
						)
						", $term, $term, $term, $like_term );
			} else {
				$query = $wpdb->prepare( "
						SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
						WHERE posts.post_status = 'publish'
						AND (
						posts.post_title LIKE %s
						or posts.post_content LIKE %s
						OR (
						postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
						)
						)
						", $like_term, $like_term, $like_term );
			}
			
			$query .= " AND posts.post_type IN ('" . implode( "','", array_map( 'esc_sql', $post_types ) ) . "')";
			
			$posts = array_unique( $wpdb->get_col( $query ) );
			$found_products = array();
			global $product;
			$proHTML = '';
			if ( ! empty( $posts ) ) {
				$proHTML .= '<table class="wp-list-table fixed striped" id="ced_bonanza_lister_products_matched">';
				foreach ( $posts as $post ) {
					$product = wc_get_product( $post );
					if(WC()->version < "3.0.0")
					{
						if( $product->product_type == 'variable' ) {
							$variations = $product->get_available_variations();
							foreach ($variations as $variation) {
								$proHTML .= '<tr><td product-id="'.$variation['variation_id'].'">'.get_the_title( $variation['variation_id'] ).'</td></tr>';
							}
						}
						else{
							$proHTML .= '<tr><td product-id="'.$post.'">'.get_the_title( $post ).'</td></tr>';
						}
					}else{
						if( $product->get_type() == 'variable' ) {
							$variations = $product->get_available_variations();
							foreach ($variations as $variation) {
								$proHTML .= '<tr><td product-id="'.$variation['variation_id'].'">'.get_the_title( $variation['variation_id'] ).'</td></tr>';
							}
						}
						else{
							$proHTML .= '<tr><td product-id="'.$post.'">'.get_the_title( $post ).'</td></tr>';
						}
					}
				}
				$proHTML .= '</table>';
			}
			else {
				$proHTML .= '<ul class="woocommerce-error ccas_searched_product_ul"><li class="ccas_searched_pro_list"><strong>No Matches Found</strong><br/></li></ul>';
			}	
			echo $proHTML;
			wp_die();
		}
	  
		/**
		 * This function to get all meta keys related to a product
		 * @name fetch_all_meta_keys_related_to_selected_product()
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @link  http://www.cedcommerce.com/
		 */
		function fetch_all_meta_keys_related_to_selected_product() {
			
			bonanzarenderMetaKeysTableOnProfilePage(sanitize_text_field($_POST['selectedProductId']));
			wp_die();
		}


		/**
		 * This function includes custom js needed by module.
		 * @name ced_bonanza_lister_extended_manager_admin_enqueue_scripts()
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @link  http://www.cedcommerce.com/
		 */
		public function ced_bonanza_lister_extended_manager_admin_enqueue_scripts() {
			$screen    = get_current_screen();
			$screen_id    = $screen ? $screen->id : '';
			 
			if( $screen_id == 'bonanza_page_bnz-bonanza-pro-mgmt' ){
				wp_enqueue_style('ced_bonanza_lister_manage_products_css', CED_Bonanza_Lister_URL.'/admin/css/manage_products.css');
			}

			if( $screen_id == 'bonanza_page_bnz-bonanza-cat-map' ){
				wp_enqueue_style('ced_bonanza_lister_category_mapping_css', CED_Bonanza_Lister_URL.'/admin/css/category_mapping.css');
			}
			if( $screen_id == 'bonanza_page_bnz-bonanza-profile' && isset($_GET['action']))
			{	
				wp_enqueue_script( 'ced_bonanza_lister_profile_edit_add_js', CED_Bonanza_Lister_URL.'/admin/js/profile-edit-add.js', array('jquery'), '1.0', true );
				wp_localize_script( 'ced_bonanza_lister_profile_edit_add_js', 'ced_bonanza_lister_profile_edit_add_script_AJAX', array(
					'ajax_url' => admin_url( 'admin-ajax.php' )
				));
				wp_enqueue_script( 'ced_bonanza_lister_profile_jquery_dataTables_js', CED_Bonanza_Lister_URL.'/admin/js/jquery.dataTables.min.js', array('jquery'), '1.0', true );
				wp_enqueue_style( 'ced_bonanza_lister_profile_jquery_dataTables_css', CED_Bonanza_Lister_URL.'/admin/css/jquery.dataTables.min.css');
				wp_enqueue_style( 'ced_bonanza_lister_profile_page_css', CED_Bonanza_Lister_URL.'/admin/css/profile_page_css.css');
				
				/**
				** woocommerce scripts to show tooltip :: start
				*/
				/* woocommerce style */
				wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
				wp_enqueue_style( 'woocommerce_admin_menu_styles' );
				wp_enqueue_style( 'woocommerce_admin_styles' );
				
				/* woocommerce script */
				$suffix = '';
				wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION );
				wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), WC_VERSION, true );
				wp_enqueue_script( 'woocommerce_admin' );
			} 
		}		
	}
	new CED_Bonanza_Lister_Extended_Manager();
?>