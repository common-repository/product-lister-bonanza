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

	if( !class_exists( 'CED_Bonanza_Lister_feed_manager' ) ) :

	/**
	 * woo-marketplace feed exchange functionality.
	*
	* upload/list/archive/unarchive feeds from woocommerce
	* to bonanza.
	*
	* @since      1.0.0
	* @package    Product Lister Bonanza
	* @subpackage Product Lister Bonanza/admin/helper
	* @author     CedCommerce <cedcommerce.com>
	*/
	class CED_Bonanza_Lister_feed_manager{

		/**
		 * The Instace of CED_Bonanza_Lister_feed_manager.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      $_instance   The Instance of CED_Bonanza_Lister_feed_manager class.
		 */
		private static $_instance;

		/**
		 * CED_Bonanza_Lister_feed_manager Instance.
		 *
		 * Ensures only one instance of CED_Bonanza_Lister_feed_manager is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @return CED_Bonanza_Lister_feed_manager instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		
		/**
		 * handle product management actions.
		 *
		 * handling all product management actions i.e. upload/archive/unarchive products
		 * on different frameworks.
		 *
		 * @since 1.0.0
		 * @return bool true|false.
		 */
		public function process_feed_request($action='',$marketplace='',$proIds=array()){
			
			if(empty($action) || empty($marketplace) || !is_array($proIds)){
				$message = __('either bulk-action/marketplace/products missing for performing the bulk action, please try again!','ced-bonanza');
				$classes = "error is-dismissable";
				$error = array('message'=>$message,'classes'=>$classes);
				return json_encode($error);
			}else{
				switch($action){
					case 'upload':
						return $this->upload_products($marketplace,$proIds);
						break;
					 
					default:
						return $this->upload_products($marketplace,$proIds);
				}
			}
		}
		
		/**
		 * upload selected products on selected marketplace.
		 * 
		 * @since 1.0.0
		 * @param string 	$marketplace
		 * @param array 	$proIds
		 * @return json string
		 */
		public function upload_products( $marketplace='', $proIds=array(), $action='' ){
			
			if(empty($marketplace) && !is_array($proIds)){
				$message = __('either marketplace or products missing for performing the product upload, please try again!','ced-bonanza');
				$classes = "error is-dismissable";
				$error = array('message'=>$message,'classes'=>$classes);
				return json_encode($error);
			}else{
				$marketplace = trim($marketplace);
				$file_name = CED_Bonanza_Lister_DIRPATH.'marketplaces/'.$marketplace.'/class-'.$marketplace.'.php';
				if( file_exists( $file_name ) ){
					
					require_once $file_name;
					$class_name = 'CED_Bonanza_Lister_manager';
					if( class_exists( $class_name) ){
						$instance = $class_name::get_instance();
						if( !is_wp_error($instance) ){
							if(!is_null($action) && !empty($action)){
								switch ($action){
									case 'archive':
										return $instance->archive($proIds);
										break;
									case 'unarchive':
										return $instance->unarchive($proIds);
										break;
									case 'update':
										return $instance->update($proIds);
										break;
									 
									default:
										return $instance->upload($proIds);
								}
							}
							return $instance->upload($proIds);
						}else{
							$message = __('An unexpected error occured, please try again!','ced-bonanza');
							$classes = "error is-dismissable";
							$error = array('message'=>$message,'classes'=>$classes);
							return json_encode($error);
						}
					}else{
						$message = __('Class missing to perform operation, please check if extension configured successfully!','ced-bonanza');
						$classes = "error is-dismissable";
						$error = array('message'=>$message,'classes'=>$classes);
						return json_encode($error);
					}
				}else{
					$message = __('Please check if bonanza is configured correctly','ced-bonanza');
					$classes = "error is-dismissable";
					$error = array('message'=>$message,'classes'=>$classes);
					return json_encode($error);
				}
			}
			$message = __('An unexpected error occured, please try again!','ced-bonanza');
			$classes = "error is-dismissable";
			$error = array('message'=>$message,'classes'=>$classes);
			return json_encode($error);
		}
	}
	endif;