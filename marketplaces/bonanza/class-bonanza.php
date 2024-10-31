<?php
/**
 * main class for handling reqests.
 *
 * @since      1.0.0
 *
 * @package    Product Lister Bonanza
 * @subpackage Product Lister Bonanza/marketplaces/bonanza
 */

if( !class_exists( 'CED_Bonanza_Lister_manager' ) ) :

	/**
	 * single product related functionality.
	*
	* Manage all single product related functionality required for listing product on marketplaces.
	*
	* @since      1.0.0
	* @package    Product Lister Bonanza
	* @subpackage Product Lister Bonanza/marketplaces/bonanza
	* @author     CedCommerce <cedcommerce.com>
	*/
	class CED_Bonanza_Lister_manager{
	
		/**
		 * The Instace of CED_Bonanza_Lister_bonanza_Manager.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      $_instance   The Instance of CED_Bonanza_Lister_bonanza_Manager class.
		 */
		private static $_instance;
		private static $authorization_obj;
		private static $client_obj;
		/**
		 * CED_Bonanza_Lister_bonanza_Manager Instance.
		 *
		 * Ensures only one instance of CED_Bonanza_Lister_bonanza_Manager is loaded or can be loaded.
		 *
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 * @static
		 * @return CED_Bonanza_Lister_bonanza_Manager instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		
		public $marketplaceID = 'bonanza';
		public $marketplaceName = 'Bonanza';
		 
		/**
		 * Constructor.
		 *
		 * registering actions and hooks for bonanza.
		 *
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 */
		public function __construct() 
		{
			  
			add_action('admin_init', array($this, 'ced_bonanza_lister_required_files'));
		 
		    add_filter( 'ced_bonanza_lister_render_marketplace_configuration_settings' , array( $this, 'ced_bonanza_lister_render_marketplace_configuration_settings' ), 10, 2 );
			add_action( 'ced_bonanza_lister_save_marketplace_configuration_settings' , array( $this, 'ced_bonanza_lister_save_marketplace_configuration_settings'), 10, 2 );
			add_action( 'ced_bonanza_lister_validate_marketplace_configuration_settings' , array( $this, 'ced_bonanza_lister_validate_marketplace_configuration_settings'), 10, 2 );
			
			add_action('ced_bonanza_lister_framework_product_fields',array($this,'ced_bonanza_lister_framework_product_fields'),10,2);
			 
			 
			add_filter( 'ced_bonanza_lister_required_product_fields', array( $this, 'add_bonanza_required_fields' ), 11, 2 );
			
			add_action( 'ced_bonanza_lister_render_different_input_type' , array( $this, 'ced_bonanza_lister_render_different_input_type'), 10, 1 );
			
			add_action('ced_bonanza_lister_required_fields_process_meta_variable', array($this,'ced_bonanza_lister_required_fields_process_meta_variable'), 11, 1 );
			/*loading scripts*/
			add_action( 'admin_enqueue_scripts',array($this,'load_bonanza_scripts'));
			add_action( 'admin_footer', array( $this, 'ced_bonanza_lister_deactivate_reason_html' ) );
			add_action( 'wp_ajax_ced_bonanza_lister_submit_reason', array( $this, 'ced_bonanza_lister_submit_reason' ) );
			 
			$this->loadDependency();
		}

		/**
		* Function to create global bonaza object
		*/
		public function loadDependency()
		{
			 
			$saved_bonanza_details = get_option( 'ced_bonanza_lister_details', array() );
			 
			$ced_bonanza_lister_dev_id = isset( $saved_bonanza_details['details']['ced_bonanza_lister_dev_id'] ) ? esc_attr( $saved_bonanza_details['details']['ced_bonanza_lister_dev_id'] ) : '';
			$ced_bonanza_lister_cert_id = isset( $saved_bonanza_details['details']['ced_bonanza_lister_cert_id'] ) ? esc_attr( $saved_bonanza_details['details']['ced_bonanza_lister_cert_id'] ) : '';
			$authToken = get_option('bonanza_tocken_details',true);
			$authToken = isset( $authToken['authToken'] ) ? $authToken['authToken'] : '';
 			if($authToken !='' && !empty($authToken) ){
				require_once CED_Bonanza_Lister_DIRPATH_1.'bonanza/lib/Bonanza/svc.php';
				$_bonanzaAutoloader = new svc($ced_bonanza_lister_dev_id,$ced_bonanza_lister_cert_id,$authToken);

				$svc_obj = $_bonanzaAutoloader;
				$GLOBALS['svc_obj'] = $svc_obj;
				global $svc_obj;
 			}

		}
		 
		/**
		 * Marketplace Configuration Setting
		 *
		 * @name ced_bonanza_lister_render_marketplace_configuration_settings
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 */
		function ced_bonanza_lister_render_marketplace_configuration_settings( $configSettings, $marketplaceID )
		{
			if( $marketplaceID != $this->marketplaceID )
			{
				return $configSettings;
			}
			else
			{
				$configSettings=array();
		
				$saved_bonanza_details = get_option( 'ced_bonanza_lister_details', array() );
				
				$ced_bonanza_lister_dev_id = isset( $saved_bonanza_details['details']['ced_bonanza_lister_dev_id'] ) ? $saved_bonanza_details['details']['ced_bonanza_lister_dev_id']  : '';
				$ced_bonanza_lister_cert_id = isset( $saved_bonanza_details['details']['ced_bonanza_lister_cert_id'] ) ?   $saved_bonanza_details['details']['ced_bonanza_lister_cert_id'] : '';
			 
				$configSettings['configSettings'] = array(
						'ced_bonanza_lister_dev_id' => array(
								'name' => __('Bonanza Developer Id', 'ced-bonanza'),
								'type' => 'text',
								'value' => $ced_bonanza_lister_dev_id
						),
						'ced_bonanza_lister_cert_id' => array(
								'name' => __('Bonapitit Certificate Id', 'ced-bonanza'),
								'type' => 'text',
								'value' => $ced_bonanza_lister_cert_id
						),
						 
						'ced_bonanza_lister_save_credentials_button' => array(
								'name' => __('Save Credentials', 'ced-bonanza'),
								'type' => 'ced_bonanza_lister_save_credentials_button',
								'value' => ''
						)	,
						'ced_bonanza_lister_authorize_details' => array(
								'name' => __('Authorize Your Account', 'ced-bonanza'),
								'type' => 'ced_bonanza_lister_validate_button',
								'value' => ''
						),
					);
				
				$configSettings['showUpdateButton'] = false;
				$configSettings['marketPlaceName'] = $this->marketplaceName;
				return $configSettings;
			}
		}

		/**
		 * render different input types.
		 */
		function ced_bonanza_lister_render_different_input_type( $type ) {
			if( $type == 'ced_bonanza_lister_validate_button' ) {
				echo "<input type='button' class='ced_bonanza_lister_authorize button button-primary' value='".__('Authorize','ced-bonanza')."'>";
			}
			if( $type == 'ced_bonanza_lister_save_credentials_button' ) {
				echo "<input type='button' class='ced_bonanza_lister_save_credentials_button button button-primary' value='".__("Save Credentials","ced-bonanza")."' name='ced_bonanza_lister_save_credentials_button'>";
			}
			if($type == "ced_bonanza_lister_site"){
				require_once plugin_dir_path( __FILE__ ).'/lib/bonanzaConfig.php';
				$bonanzaConfig = new bonanzaconfig;
				$bonanzaConfigInstance = $bonanzaConfig->get_instance();
				$bonanzaSites = $bonanzaConfigInstance->getbonanzasites();
				$optionbonanzasites = "";
				$selectedSiteId = get_option('ced_bonanza_lister_details',false);
				$selectedSiteId = isset($selectedSiteId['siteID']) ? $selectedSiteId['siteID'] : false;
				if(is_array($bonanzaSites) && !empty($bonanzaSites)){
					foreach ($bonanzaSites as $sites){
						if($selectedSiteId != "" && $selectedSiteId == $sites['siteID']){
							$selected = "selected";
						}else {
							$selected = "";
						}
						$optionbonanzasites .= "<option ".$selected." value='".$sites['siteID']."'>".$sites['name']."</option>";
					}
				}
				echo "<select class='ced_bonanza_lister_select_site'>".$optionbonanzasites."</select>";
			}
		}

		/**
		 * Validate Marketplace Configuration Setting
		 *
		 * @name ced_bonanza_lister_validate_marketplace_configuration_settings
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 */
		
		public function ced_bonanza_lister_validate_marketplace_configuration_settings( $configSettingsToSave, $marketplaceID ) {
			global $cedbonanzalisterhelper;
			try
			{
				if( $marketplaceID == $this->marketplaceID )
				{
					delete_option('ced_bonanza_lister_validate_'.$this->marketplaceID);
					$saved_bonanza_details = get_option( 'ced_bonanza_lister_configuration', true );
		
					$bonanza_service_url = isset($saved_bonanza_details['service_url']) ? sanitize_text_field( $saved_bonanza_details['service_url'] ) : '';
					$bonanza_marketplace_id = isset($saved_bonanza_details['marketplace_id']) ? sanitize_text_field( $saved_bonanza_details['marketplace_id'] ) : '';
					$bonanza_merchant_id = isset($saved_bonanza_details['merchant_id']) ? sanitize_text_field( $saved_bonanza_details['merchant_id'] ) : '';
					$bonanza_key_id = isset($saved_bonanza_details['key_id']) ? sanitize_text_field( $saved_bonanza_details['key_id'] ) : '';
					$bonanza_secret_key = isset($saved_bonanza_details['secret_key']) ? sanitize_text_field( $saved_bonanza_details['secret_key'] ) : '';
					$bonanza_auth_token = isset($saved_bonanza_details['auth_token']) ? sanitize_text_field( $saved_bonanza_details['auth_token'] ) : '';
						
					if($bonanza_service_url && $bonanza_marketplace_id && $bonanza_merchant_id && $bonanza_key_id && $bonanza_secret_key && $bonanza_auth_token)
					{
						$this->bonanza_lib->setFeedStatuses(array( "_DONE_"));
						$this->bonanza_lib->fetchFeedSubmissions(); //this is what actually sends the request
						$list = $this->bonanza_lib->getFeedList();
						if(isset($list) && is_array($list))
						{
							update_option('ced_bonanza_lister_validate_'.$this->marketplaceID,"yes");
							$notice['message'] = __('Configuration setting is Validated Successfully','ced-bonanza');
							$notice['classes'] = "notice notice-success";
							$validation_notice[] = $notice;
							$cedbonanzalisterhelper->bnz_print_notices($validation_notice);
						}
					}
					else
					{
						$notice['message'] = __('Consumer Id and Private Key can\'t be blank','ced-bonanza');
						$notice['classes'] = "notice notice-error";
						$validation_notice[] = $notice;
						$cedbonanzalisterhelper->bnz_print_notices($validation_notice);
						unset($validation_notice);
					}
				}
			}
			catch(Exception $e)
			{
				$message = $e->getMessage();
				 
				$notice['message'] = __("API Cerdentials is not valid. Please check again.", 'ced-bonanza');
				$notice['classes'] = "notice notice-error";
				$validation_notice[] = $notice;
				$cedbonanzalisterhelper->bnz_print_notices($validation_notice);
				unset($validation_notice);
			}
		}

		/**
		 * Save Marketplace Configuration Setting
		 *
		 * @name ced_bonanza_lister_save_marketplace_configuration_settings
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 */
		
		function ced_bonanza_lister_save_marketplace_configuration_settings( $configSettingsToSave, $marketplaceID )
		{
			global $cedbonanzalisterhelper;
			if( $marketplaceID == $this->marketplaceID )
			{
				$bonanza_service_url = isset($configSettingsToSave['ced_bonanza_lister_service_url']) ? sanitize_text_field( $configSettingsToSave['ced_bonanza_lister_service_url'] ) : '';
				$bonanza_marketplace_id = isset($configSettingsToSave['ced_bonanza_lister_marketplace_id']) ? sanitize_text_field( $configSettingsToSave['ced_bonanza_lister_marketplace_id'] ) : '';
				$bonanza_merchant_id = isset($configSettingsToSave['ced_bonanza_lister_merchant_id']) ? sanitize_text_field( $configSettingsToSave['ced_bonanza_lister_merchant_id'] ) : '';
				$bonanza_key_id = isset($configSettingsToSave['ced_bonanza_lister_key_id']) ? sanitize_text_field( $configSettingsToSave['ced_bonanza_lister_key_id'] ) : '';
				$bonanza_secret_key = isset($configSettingsToSave['ced_bonanza_lister_secret_key']) ? sanitize_text_field( $configSettingsToSave['ced_bonanza_lister_secret_key'] ) : '';
				$bonanza_auth_token = isset($configSettingsToSave['ced_bonanza_lister_auth_token']) ? sanitize_text_field( $configSettingsToSave['ced_bonanza_lister_auth_token'] ) : '';
		 
				if($bonanza_service_url && $bonanza_marketplace_id && $bonanza_merchant_id && $bonanza_key_id && $bonanza_secret_key && $bonanza_auth_token)
				{
					$bonanza_configuration = array();
					$bonanza_configuration['service_url'] = $bonanza_service_url;
					$bonanza_configuration['marketplace_id'] = $bonanza_marketplace_id;
					$bonanza_configuration['merchant_id'] = $bonanza_merchant_id;
					$bonanza_configuration['key_id'] = $bonanza_key_id;
					$bonanza_configuration['secret_key'] = $bonanza_secret_key;
					$bonanza_configuration['auth_token'] = $bonanza_auth_token;
					update_option( 'ced_bonanza_lister_configuration', $bonanza_configuration );
					$notice['message'] = __('Credentials saved successfully','ced-bonanza');
					$notice['classes'] = "notice notice-success";
					$validation_notice[] = $notice;
					$cedbonanzalisterhelper->bnz_print_notices($validation_notice);
					unset($validation_notice);
				}
				else
				{
					$notice['message'] = __('Fields can not be blank','ced-bonanza');
					$notice['classes'] = "notice notice-error";
					$validation_notice[] = $notice;
					$cedbonanzalisterhelper->bnz_print_notices($validation_notice);
					unset($validation_notice);
				}
				update_option("ced_bonanza_lister_save_".$this->marketplaceID,"yes");
				update_option("ced_bonanza_lister_validate_".$this->marketplaceID,"no");
			}
		}
		/*
		* Function to manage reason for deactivation
		*/
		public function ced_bonanza_lister_submit_reason()
		{
			$looking_for = $_POST['looking_for'];
			$want_more = $_POST['want_more'];
			$was_bug = $_POST['was_bug'];

			$content = 'Its not for you --> '.$looking_for." Was Complex --> ".$want_more." Was having Bug --> ".$was_bug;
			$subject = "Deactivated the Plugin Product Lister Bonanza";
			wp_mail( 'developer@cedcommerce.com', $subject, $content );
			die;
		}

		/*
		* Html for Deactivation Reason Popup
		*/
		public function ced_bonanza_lister_deactivate_reason_html()
		{
			?>
			<div class="ced_bonanza_lister_deactivate_reason_main_wrapper">
				<div class="ced_bonanza_lister_deactivate_reason_popup_wrapper">
					
					<div class="ced_bonanza_lister_deactivate_reason_popup_heading">
						<h3><?php _e( 'Is this not the plugin you were looking for. Help us to make it one for you! ', 'ced-bonanza_lister' ); ?></h3>
					</div>
					
					<div class="ced_bonanza_lister_deactivate_reason_popup_body">
						
						<input type="hidden" class="ced_bonanza_lister_deactivation_url"></input>
						<table class="ced_bonanza_lister_deactivate_reason_popup_table">
							<tbody>
								<tr>
									<th><?php _e( "It's not for Me!" , 'ced-bonanza_lister' ); ?></th>
									<td>
										<input id="ced_bonanza_lister_looking_for" type="radio" name="ced_bonanza_lister_deactivate_reason" value=""></input>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Was the plugin complex to use?' , 'ced-bonanza_lister' ); ?></th>
									<td>
										<input id="ced_bonanza_lister_was_complex" type="radio" name="ced_bonanza_lister_deactivate_reason" value=""></input>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Did you find any Bug?' , 'ced-bonanza_lister' ); ?></th>
									<td>
										<input id="ced_bonanza_lister_was_bug" type="radio" name="ced_bonanza_lister_deactivate_reason" value=""></input>
									</td>
								</tr>
								<tr>								
									<td colspan="2">
										<input type="button" class="button button-primary ced_bonanza_lister_skip_reason" value="<?php _e( 'Skip', 'ced-bonanza_lister' ); ?>"></input>
										<input type="button" value="<?php _e( 'Submit', 'ced-bonanza_lister' ); ?>" class="button button-primary ced_bonanza_lister_submit_reason"></input>
									</td>
								</tr>
							</tbody>
						</table>
						
					</div>
				</div>
			</div>
			<?php
			
		}

		/**
		 * Include all required files 
		 */
		public function ced_bonanza_lister_required_files(){
			if(is_file(CED_Bonanza_Lister_DIRPATH.'marketplaces/bonanza/partials/class-ajax-handler.php')){
				require_once CED_Bonanza_Lister_DIRPATH.'marketplaces/bonanza/partials/class-ajax-handler.php';
				$ajaxhandler = new Ced_Bonanza_ajax_handler();
			}
			if(is_file(CED_Bonanza_Lister_DIRPATH.'marketplaces/bonanza/partials/class-bonanza-upload.php')){
				require_once CED_Bonanza_Lister_DIRPATH.'marketplaces/bonanza/partials/class-ajax-handler.php';
			}
		}

		/**
		 * function to enqueue scripts
		 * @name load_bonanza_scripts
		 * 
		 * @version 1.0.0
		 * 
		 */
		public function load_bonanza_scripts(){
			$screen    = get_current_screen();
			$screen_id    = $screen ? $screen->id : '';
			$param = isset($_GET['marketplaceID']) ? sanitize_text_field( $_GET['marketplaceID']) : "";
			$action = isset($_GET['action']) ?  sanitize_text_field ($_GET['action']) : "";
			$page = isset($_GET['page']) ? sanitize_text_field( $_GET['page'] ) : "";
			wp_enqueue_style( 'ced_bonanza_lister_css', plugin_dir_url( __FILE__ ) . 'css/bonanza.css' );
			 
			if( $screen_id == 'toplevel_page_bnz-bonanza-main' )
			{
				wp_register_script( 'ced_bonanza_lister_auth', plugin_dir_url( __FILE__ ) . 'js/authorization.js', array( 'jquery' ), time(), true );
				$localization_params = array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'admin_url'=> get_admin_url(),
				);
				wp_localize_script( 'ced_bonanza_lister_auth', 'ced_bonanza_lister_auth', $localization_params );
				wp_enqueue_script('ced_bonanza_lister_auth');
				/**
				 ** woocommerce scripts to show tooltip :: start
				 */
					
				wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
				wp_enqueue_style( 'woocommerce_admin_menu_styles' );
				wp_enqueue_style( 'woocommerce_admin_styles' );
					
				$suffix = '';
				wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION );
				wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), WC_VERSION, true );
				wp_enqueue_script( 'woocommerce_admin' );

				wp_enqueue_style( 'ced-bonanza-style-jqueru-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css' );
				wp_enqueue_script( 'jquery-ui-datepicker' );
				/**
				 ** woocommerce scripts to show tooltip :: end
				 */
			}
			$locale  = localeconv();
			$decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';

			$params = array(
				/* translators: %s: decimal */
				'i18n_decimal_error'                => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'woocommerce' ), $decimal ),
				/* translators: %s: price decimal separator */
				'i18n_mon_decimal_error'            => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'woocommerce' ), wc_get_price_decimal_separator() ),
				'i18n_country_iso_error'            => __( 'Please enter in country code with two capital letters.', 'woocommerce' ),
				'i18_sale_less_than_regular_error'  => __( 'Please enter in a value less than the regular price.', 'woocommerce' ),
				'decimal_point'                     => $decimal,
				'mon_decimal_point'                 => wc_get_price_decimal_separator(),
				'strings' => array(
					'import_products' => __( 'Import', 'woocommerce' ),
					'export_products' => __( 'Export', 'woocommerce' ),
				),
				'urls' => array(
					'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
					'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
				),
			);

			wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );
			wp_register_script( 'ced_bonanza_lister_cat', plugin_dir_url( __FILE__ ) . 'js/category.js', array( 'jquery' ), time(), true );
			$localization_params = array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'plugins_url'=> CED_Bonanza_Lister_URL,
			);
			wp_localize_script( 'ced_bonanza_lister_cat', 'ced_bonanza_lister_cat', $localization_params );
			wp_enqueue_script('ced_bonanza_lister_cat');
			
			$screen    = get_current_screen();
			$screen_id    = $screen ? $screen->id : '';
			if ( in_array( $screen_id, array( 'edit-product','product' ) ) ) {
				wp_register_script( 'ced_bonanza_lister_edit_product', plugin_dir_url( __FILE__ ) . 'js/product-edit.js',array( 'jquery' ), time(), true);
				global $post;
				if( !empty($post) )
				{
					wp_localize_script( 'ced_bonanza_lister_edit_product', 'ced_bonanza_lister_edit_product_script_AJAX', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'product_id' => $post->ID
					));
				}
				wp_enqueue_script('ced_bonanza_lister_edit_product');
			}
			
			if( $screen_id == 'bonanza_page_bnz-bonanza-profile' && isset($_GET['action'])){
					
				wp_register_script( 'ced_bonanza_lister_profile_edit', plugin_dir_url(__FILE__) . 'js/profile-edit.js',array( 'jquery' ), time(), true);
				wp_localize_script( 'ced_bonanza_lister_profile_edit', 'ced_bonanza_lister_edit_profile_AJAX', array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
				));
				wp_enqueue_script('ced_bonanza_lister_profile_edit');
			}
		  
			if( $screen_id == 'bonanza_page_bnz-bonanza-manage_feedback' )
			{
				wp_register_script( 'ced_bonanza_lister_manage_feedback', CED_Bonanza_Lister_URL . 'admin/js/ced_bonanza_lister_manage_feedback.js',array( 'jquery' ), time(), true);
				wp_localize_script( 'ced_bonanza_lister_manage_feedback', 'ced_bonanza_lister_manage_feedback', array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
				));
				wp_enqueue_script('ced_bonanza_lister_manage_feedback');
			}
			
		}
		
		/**
		 * Function to category selection field on product single page
		 * 
		 * @name add_bonanza_required_fields
		 */
		public function add_bonanza_required_fields($fields=array(),$post=''){
			$newInedx = array();
			$postId = isset($post->ID) ? intval($post->ID) : 0;
			$selectedbonanzaCategories = get_option('ced_bonanza_lister_selected_categories');
			$selectedbonanzaCategories = (is_array($selectedbonanzaCategories) && !empty($selectedbonanzaCategories)) ? $selectedbonanzaCategories : array();
			$selectedbonanzaCategories = $newInedx + $selectedbonanzaCategories;
			 
			$fields[] = array(
				'type' => '_multi_select',
				'id' => '_bnz_bonanza_category',
				'fields' => array(
						'id' => '_bnz_bonanza_category',
						'label' => __( 'Bonanza Category', 'ced-bonanza' ).'<span class="ced_bonanza_lister_required ced_required_red_color"> [ '.__( 'Required', 'ced-bonanza' ).' ]</span>',
						'options' => $selectedbonanzaCategories,
						'desc_tip' => true,
						'description' => __( 'Identify the category specification. There is only one category can be used for any single item. NOTE: Once an item is created, this information cannot be updated.', 'ced-bonanza' )
				),
			);
			return $fields;
		}

		/**
		 * Bonanza Product Fields
		 *
		 * @name ced_bonanza_lister_framework_product_fields
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 */
		
		function ced_bonanza_lister_framework_product_fields($framework_fields, $post)
		{

			$categoryTraits = get_option( 'category_traits_91247', array() );
			if(isset($categoryTraits) && is_array($categoryTraits)){
				$categoryTraitsArrayFields=array();
				foreach ($categoryTraits as $key => $categoryTraitsFiels) {
					
					 $categoryTraitsFielsfinalValues=array();
					 if (isset($categoryTraitsFiels['traitValues']) && is_array($categoryTraitsFiels['traitValues'])) {
					  	foreach ($categoryTraitsFiels['traitValues'] as $key => $categoryTraitsFielsValues) {
					  		 $categoryTraitsFielsfinalValues[]=$categoryTraitsFielsValues['name'];
					  	}
					 }
					 
					if($categoryTraitsFiels['htmlInputType']=='dropdown' || $categoryTraitsFiels['htmlInputType']=='checkbox_set'){
						$categoryTraitsArrayFields[]=array(
								'type' => '_select',
								'id' => '_ced_bonanza_lister_'.$categoryTraitsFiels['label'],
								'fields' => array(
										'id'                => '_ced_bonanza_lister_condition',
										'label'             => __( $categoryTraitsFiels['label'], 'ced-bonanza' ).'<span class="ced_bonanza_lister_required ced_required_red_color"> [ '.__( 'Required', 'ced-bonanza' ).' ]</span>',
										'desc_tip'          => true,
										'description'       => __( $categoryTraitsFiels['label'], 'ced-bonanza' ),
										'type'              => 'select',
										'options'			=>  $categoryTraitsFielsfinalValues,
										'class'				=> ''
								)
						);
					}
					if($categoryTraitsFiels['htmlInputType']=='textfield'){
						$categoryTraitsArrayFields[]=array(
								'type' => '_text_input',
								'id' => '_ced_bonanza_lister_sku',
								'fields' => array(
										'id'                => '_ced_bonanza_lister_'.$categoryTraitsFiels['label'],
										'label'             => __( $categoryTraitsFiels['label'], 'ced-bonanza' ).'<span class="ced_bonanza_lister_required ced_required_red_color"> [ '.__( 'Required', 'ced-bonanza' ).' ]</span>',
										'desc_tip'          => true,
										'description'       => __( $categoryTraitsFiels['label'], 'ced-bonanza' ),
										'type'              => 'text',
										'class'				=> ''
								)
						);
					}
				}
			}
			$bonanzaSpecificFields[] = $categoryTraitsArrayFields;
		
			
			$framework_fields= $bonanzaSpecificFields;
			return $framework_fields;
		}
		 
		/**
		 * Upload selected products on Bonanza.
		 *
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 * @param array $proIds
		 */
		public function upload($proIds=array(), $isWriteXML=true)
		{
			if(file_exists(CED_Bonanza_Lister_DIRPATH.'marketplaces/bonanza/partials/class-bonanza-upload.php')){
				require CED_Bonanza_Lister_DIRPATH.'marketplaces/bonanza/partials/class-bonanza-upload.php';
				$bonanzaUploadInstance = CedBonanzaUpload :: get_instance();
			 
				$uploadRequest = $bonanzaUploadInstance->upload($proIds);
			 
				return $uploadRequest;
			}
		}
	}
endif;
?>