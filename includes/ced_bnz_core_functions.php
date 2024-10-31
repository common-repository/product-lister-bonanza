<?php

	// If this file is called directly, abort.
	if ( ! defined( 'WPINC' ) ) {
		die;
	}

	/**
	* This function fetches metakeys of a product and render them in table form on profile page.
	* @name bonanzarenderMetaKeysTableOnProfilePage()
	* @author CedCommerce <plugins@cedcommerce.com>
	* @link  http://www.cedcommerce.com/
	*/
	function bonanzarenderMetaKeysTableOnProfilePage($productId) {
		/* fetching previously selected metakeys */
		$CedbnzProfileSelectedMetaKeys = get_option('CedbnzProfileSelectedMetaKeys', false);
		if(!is_array($CedbnzProfileSelectedMetaKeys)) {
			$CedbnzProfileSelectedMetaKeys = array();
		}

		$getPostCustom = get_post_custom($productId);
		$_product = wc_get_product($productId);
		if( WC()->version < "3.0.0" ){

			if( $_product->product_type == "variation" ) {
				$parentId = $_product->parent->id;
				$getParentPostCustom = get_post_custom($parentId);
				$getPostCustom = array_merge($getPostCustom,$getParentPostCustom);
			}
		} 
		?>
		<table class="wp-list-table widefat fixed striped" id="ced_bonanza_lister_metakeys_list">
			<thead>
				<tr>
					<th><?php _e('Meta Field Key','ced-bonanza');?></th>
					<th><?php _e('Meta Field Value','ced-bonanza');?></th>
				</tr>	
			</thead>
			<tbody>
				<?php
				if(isset($getPostCustom) && !empty($getPostCustom)){
				foreach($getPostCustom as $customPostKey => $customPostValue) {
					$value = isset($customPostValue[0]) ? $customPostValue[0] : array();
					$searialize = false;
					$data = @unserialize($value);
					if ($data !== false) {
						$searialize=true;
					}
					if(is_array($value) || is_object($value) || $searialize){
						continue;
					}
					$checked = (in_array($customPostKey, $CedbnzProfileSelectedMetaKeys)) ? "checked=checked" : "" ;
					echo '<tr>';
						echo '<td>';
						echo '<input type="checkbox" class="ced_bonanza_lister_add_del_meta_keys" name="unique_post[]" value="'.$customPostKey.'" id="'.$customPostKey.'" '.$checked.'><label for="'.$customPostKey.'">'.$customPostKey.'</label>';
						echo '</td>';
						echo '<td>';
						echo $value;
						echo '</td>';
					echo '</tr>';
				}
			}
				?>
			</tbody>
			<tfoot>
			</tfoot>
		</table>
		<?php
	}

	/**
	 * getting product id from sku
	 * @name _bonanza_bnz_get_product_by_sku
	 *
	 */
	function _bonanza_bnz_get_product_by_sku( $sku ) {
		global $wpdb;
		$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );
		if ( $product_id ) return wc_get_product( $product_id );
		return null;
	}

	/**
	* This function renders metakeys selection dropdown on profile page.
	* @name bonanzarenderMetaSelectionDropdownOnProfilePage()
	* @author CedCommerce <plugins@cedcommerce.com>
	* @link  http://www.cedcommerce.com/
	*/
	function bonanzarenderMetaSelectionDropdownOnProfilePage() {
		/* select dropdown setup */
		$attributes		=	wc_get_attribute_taxonomies();
		$attrOptions	=	array();
		$addedMetaKeys = get_option('CedbnzProfileSelectedMetaKeys', false);

		if($addedMetaKeys && count($addedMetaKeys) > 0) {
			foreach ($addedMetaKeys as $metaKey){
				$attrOptions[$metaKey]	=	$metaKey;
			}
		}
		if(!empty($attributes)){
			foreach($attributes as $attributesObject) {
				$attrOptions['bnz_pattr_'.$attributesObject->attribute_name]	=	$attributesObject->attribute_label;
			}
		}
		/* select dropdown setup */
		ob_start();
		$fieldID = '{{*fieldID}}';
		$selectId = $fieldID.'_attibuteMeta';
		echo '<select id="'.$selectId.'" name="'.$selectId.'">';
		echo '<option value="null"> -- select -- </option>';
		if(is_array($attrOptions)) {
			foreach($attrOptions as $attrKey=>$attrName):
				echo '<option value="'.$attrKey.'">'.$attrName.'</option>';
			endforeach;
		}
		echo '</select>';
		$selectDropdownHTML = ob_get_clean();
		return $selectDropdownHTML;
	}

	/**
	* This function renders different marketplaces link on top in bnz section.
	* @name bonanzarenderMarketPlacesLinksOnTop()
	* @author CedCommerce <plugins@cedcommerce.com>
	* @link  http://www.cedcommerce.com/
	*/
	function bonanzarenderMarketPlacesLinksOnTop($page='') {
		$availableMarketPlaces = bonanzaget_enabled_marketplaces();
		if(!is_array($availableMarketPlaces) || empty($availableMarketPlaces)) {
			return;	
		}
		foreach($availableMarketPlaces as $val)
		{
			$section = $val;
			break;
		}	
		if(isset($_GET['section'])) {
			$section = esc_attr($_GET['section']);
		}
		echo '<ul class="subsubsub">';
		$marketPlaces = bonanzaget_enabled_marketplaces();
		if(is_array($marketPlaces)) { 
			$counter=1;
			foreach ($marketPlaces as $marketPlace) {
				$class = '';
				if( $section == $marketPlace ) {
					$class = 'current';
				}
				$redirectURL = get_admin_url()."admin.php?page=".$page."&amp;section=".$marketPlace;
				echo '<li>';
				echo '<a href="'.$redirectURL.'" class="'.$class.'">'.strtoupper($marketPlace).'</a>'; 
				if($counter < count($marketPlaces) ){ 
					echo '|'; 
				}
				echo '</li>';
				$counter++;
			}
		}
		echo '</ul>';
	}

	/**
	* This function returns whether marketplace is enable by the admin or not.
	* @name bonanzagetMarketPlaceStatus()
	* @author CedCommerce <plugins@cedcommerce.com>
	* @link  http://www.cedcommerce.com/
	*/
	function bonanzagetMarketPlaceStatus( $marketplaceID ) {
		$activeMarketPlaces = bonanzaget_enabled_marketplaces();
		if(in_array($marketplaceID, $activeMarketPlaces)) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	* This function returns whether marketplace configuration is validated or not.
	* @name bonanzaisMarketPlaceConfigurationsValidated()
	* @author CedCommerce <plugins@cedcommerce.com>
	* @link  http://www.cedcommerce.com/
	*/
	function bonanzaisMarketPlaceConfigurationsValidated( $marketplaceID ) {
		$ced_bonanza_lister_validate_marketplace = get_option("ced_bonanza_lister_validate_".$marketplaceID,true);
		if( empty($ced_bonanza_lister_validate_marketplace) || $ced_bonanza_lister_validate_marketplace == "no" ) {
			$ced_bonanza_lister_validate_marketplace = false;
		}
		else {
			$ced_bonanza_lister_validate_marketplace = true;
		}
		return $ced_bonanza_lister_validate_marketplace;
	}

	/**
	* This function returns all the marketplaces enabled by the admin.
	* @name bonanzaget_enabled_marketplaces()
	* @author CedCommerce <plugins@cedcommerce.com>
	* @link  http://www.cedcommerce.com/
	*/
	function bonanzaget_enabled_marketplaces(){
		$activated_marketplaces = array('bonanza');
		return $activated_marketplaces;
	}

	/**
	* This function returns all the marketplaces enabled by the admin.
	* @name bonanzaget_enabled_marketplaces()
	* @author CedCommerce <plugins@cedcommerce.com>
	* @link  http://www.cedcommerce.com/
	*/
	function ced_bonanza_lister_available_marketplace($api_key=''){
		$dir = CED_Bonanza_Lister_DIRPATH.'marketplaces';
		$folders = scandir($dir, 1);
		$availableMarketPlaces = array();
		foreach ($folders as $folder) {
		    if ($folder === '.' || $folder === '..' || $folder === '.DS_Store') {
		    	continue;
		    }
		    $availableMarketPlaces[] = $folder;
		}
		return $availableMarketPlaces;
	}
 
	/**
	 * Check WooCommmerce active or not.
	 *
	 * @since 1.0.0
	 * @return bool true|false
	 */
	function ced_bonanza_lister_check_woocommerce_active(){
		
		if ( function_exists('is_multisite') && is_multisite() ){

			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ){

				return true;
			}
			return false;
		}else{
				
			if ( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ){

				return true;
			}
			return false;
		}
	}
 
	/**
	 * formatting the json_data by removing some characters
	 * found that prevents the json_decode() to work and 
	 * showing syntax error problem.
	 * 
	 * @refrenced from stackoverflow a post by kris khairallah.
	 * 
	 * @since 1.0.0
	 * @param  json  raw json data
	 * @return json  formated json data
	 */
	function ced_bonanza_lister_format_json($json_data){

		for ($i = 0; $i <= 31; ++$i) {
			$json_data = str_replace(chr($i), "", $json_data);
		}
		$json_data = str_replace(chr(127), "", $json_data);

		// This is the most common part
		// Some file begins with 'efbbbf' to mark the beginning of the file. (binary level)
		// here we detect it and we remove it, basically it's the first 3 characters
		if (0 === strpos(bin2hex($json_data), 'efbbbf')) {
			$json_data = substr($json_data, 3);
		}

		return $json_data;
	}

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_ced_bnz_bonanza() {

		$ced_bonanza_lister = new CED_bnz_Bonanza();
		$ced_bonanza_lister->run();
	}

	/**
	 * This code runs when WooCommerce is not activated,
	 * deativates the extension and displays the notice to admin.
	 *
	 * @since 1.0.0
	 */
	function deactivate_ced_bonanza_lister_woo_missing() {
		deactivate_plugins(plugin_basename("product-lister-bonanza/product-lister-bonanza.php"));
		add_action('admin_notices', 'ced_bonanza_lister_woo_missing_notice' );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}

	/**
	 * This code runs when WooCommerce is not activated,
	 * deativates the extension and displays the notice to admin.
	 *
	 * @since 1.0.0
	 */
	function ced_bonanza_lister_paid_link_notice_function() {
		$maximum_calls=get_option('bonanza_calls_limit');
	  
		if($maximum_calls=='exceeded'){
		 	add_action('admin_notices', 'ced_bonanza_lister_paid_link_notice' );
		}
	}

	/**
	 * callback function for sending notice if woocommerce is not activated.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	function ced_bonanza_lister_paid_link_notice(){
		$screen=get_current_screen();
		$screen_id=$screen->id;
		if($screen_id=='toplevel_page_bnz-bonanza-main'){
			echo '<div class="notice notice-warning is-dismissible"><p>
				<a target="blank" href="https://cedcommerce.com/woocommerce-extensions/woocommerce-bonanza-integration" class="ced_bonanza_lister_upgrade_banner">
		            <img src="'.plugin_dir_url(__dir__).'admin/images/bonanza-banner.jpg">
		        </a>
		        <a target="blank" href="https://cedcommerce.com/woocommerce-extensions/woocommerce-bonanza-integration" class="ced_bonanza_lister_get_offer_banner">
		            <img src="'.plugin_dir_url(__dir__).'admin/images/bonanza-offer.jpg">
		        </a>
			</p></div>';
		}else{
			echo '<div class="notice notice-warning is-dismissible"><p>
				<a target="blank" href="https://cedcommerce.com/woocommerce-extensions/woocommerce-bonanza-integration" class="ced_bonanza_lister_upgrade_banner_gen">
		            <img src="'.plugin_dir_url(__dir__).'admin/images/bonanza-banner-gen.jpg">
		        </a
			</p></div>';
		}
		
	}
	
	/**
	 * callback function for sending notice if woocommerce is not activated.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	function ced_bonanza_lister_woo_missing_notice(){

		echo '<div class="error"><p>' . sprintf(__('Product Lister Bonanza requires WooCommerce to be installed and active. You can download %s here.', 'ced-bonanza'), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a>') . '</p></div>';
	}

	/**
	 * checking the profile condition.
	 *
	 * @since 1.0.0
	 */
	 function bonanza_profile_validation($proID,$condition=array()){
		 if(is_array($condition)){
			 $default = isset($condition['default']) ? $condition['default'] : null;
			 $bymeta = isset($condition['metakey']) ? $condition['metakey'] : null;
			 if(!is_null($default) && strlen($default) && $default != 'null'){
				 return $default;
			 }
			 
			 if(!is_null($bymeta) && strlen($bymeta) && $bymeta != 'null'){
				 $explodeForAttribute = explode('bnz_pattr_', $bymeta);
					if(count($explodeForAttribute) > 1 && isset($explodeForAttribute[1])){
						$attrName = $explodeForAttribute[1];
						$product_terms = get_the_terms($proID, 'pa_'.$attrName);
							if(count($product_terms)){
								$first_term = isset($product_terms[0]) ? $product_terms[0] : array();
								$termName = isset($first_term->name) ? esc_attr($first_term->name) : '';
								if( !is_null($termName) && strlen($termName) ){
									return $termName;
								}
							}
					}else{
						return get_post_meta($proID,$bymeta,true);
				}
			 }
		 }
		 return 'null';
 	}
	 
	/**
	 * get profile conditions.
	 * 
	 * @since 1.0.0 
	 */
	function bonanza_get_profile_condition($pro_id){
		
		$isProfileAssigned = get_post_meta($pro_id,'ced_bonanza_lister_profile',true);
		$conditions=array();
		if(isset($isProfileAssigned) && !empty($isProfileAssigned) && $isProfileAssigned){
			$profile_info = bonanzagetProfileDetail($isProfileAssigned);
			$conditions = isset($profile_info['profile_data']) ? json_decode($profile_info['profile_data'],true) : array();
		}
		return $conditions;
	}
	
	/**
	 * get profile details.
	 *
	 * @since 1.0.0
	 */
	 function bonanzagetProfileDetail($profileId){
		global $wpdb;
		$table_name = $wpdb->prefix.CED_Bonanza_Lister_PREFIX.'_bonanzaprofiles';
		$query = "SELECT * FROM `$table_name` WHERE id=$profileId";
		$profileDetail = $wpdb->get_row($query,'ARRAY_A');
		if(is_array($profileDetail)){
			return $profileDetail;
		}else{
			return false;
		}
	 }

	/**
	 * get marketplace price.
	 * 
	 * @since 1.0.0
	 */
	function bonanza_get_marketplace_price($proId,$marketplace=''){
		
		$conditions = bonanza_get_profile_condition($proId);
		if(!is_array($conditions))
			$conditions = array();
		
		if(!is_null($marketplace) && strlen($marketplace)){
			$key = '_bnz_'.$marketplace.'_Price';
			$condition = isset($conditions[$key]) ? $conditions[$key] : false;
			
			if($condition && !is_null($condition) && count($condition) ) {
				$price = bonanza_profile_validation($proId,$condition);
				if($price && !is_null($price) && $price != 'null' && strlen($price)){
					return round($price,2);
				} 
				 
			}
			
			$umbPrice = get_post_meta($proId,'_bnz_'.$marketplace.'_Price',true);
			if($umbPrice){
				$umbPrice = round($umbPrice,2);
				return $umbPrice;
			}
		}
		
		$MarketplacePrice = get_post_meta($proId,'_bnz_bonanza_price',true);
		if($MarketplacePrice){
			return $MarketplacePrice;
		}else{
			$salePrice = get_post_meta($proId,'_sale_price',true);
			if($salePrice){
				return $salePrice;
			}else{
				$mainPrice = get_post_meta($proId,'_regular_price',true);
				if($mainPrice){
					return $mainPrice;
				}else{
					return 0;
				}
			}
		}
	}

	/**
	 * get marketplace qty.
	 *
	 * @since 1.0.0
	 */
	function bonanza_get_marketplace_qty($proId,$marketplace=''){

		$conditions = bonanza_get_profile_condition($proId);
		if(!is_array($conditions))
			$conditions = array();

		if($marketplace != null || $marketplace != ""){
			$key = '_bnz_'.$marketplace.'_Inventory';
			$condition = isset($conditions[$key]) ? $conditions[$key] : false;
			if($condition && !is_null($condition) && count($condition) ) {
				$qty = bonanza_profile_validation($proId,$condition);
				if($qty && !is_null($qty) && $qty != 'null' && strlen($qty)){
					return intval($qty);
				} 
			}
			
			$QTY = get_post_meta($proId,'_bnz_'.$marketplace.'_Inventory',true);
			if($QTY){
				return intval($QTY);
			}
		}
		
		$marketplaceStock = get_post_meta($proId,'_bnz_bonanza_stock',true);
		if($marketplaceStock){
			return intval($marketplaceStock);
		}else{
			$centralStock = get_post_meta($proId,'_stock',true);
			if($centralStock){
				return intval($centralStock);
			}else{
				return 0;
			}
		}
	}
?>