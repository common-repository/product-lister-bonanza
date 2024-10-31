<?php
if(!class_exists('CedBonanzaUpload')){
	class CedBonanzaUpload{

		private static $_instance;

		/**
		 * get_instance Instance.
		 *
		 * Ensures only one instance of CedbonanzaUpload is loaded or can be loaded.
		 *
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 * @static
		 * @return get_instance instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * This function is to upload products on bonanza
		 * @name upload()
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @link  http://www.cedcommerce.com/
		 */
		function upload( $productIds=array() ) {
			 
			if(is_array($productIds) && !empty($productIds)){
				 
				self::prepareItems($productIds);
  
				return json_encode($this->final_response);
			}
		}

		/**
		* Function to upload products on Bonanza
		*/
		public function doupload(){

			require_once(CED_Bonanza_Lister_DIRPATH_1.'bonanza/lib/class-bonanza-request.php');
	        $bonanzaRequest = new BonanzaRequest();
	        $saved_bonanza_details = get_option( 'ced_bonanza_lister_details', array() );
			$ced_bonanza_lister_dev_id = isset( $saved_bonanza_details['details']['ced_bonanza_lister_dev_id'] ) ? esc_attr( $saved_bonanza_details['details']['ced_bonanza_lister_dev_id'] ) : '';
			$ced_bonanza_lister_cert_id = isset( $saved_bonanza_details['details']['ced_bonanza_lister_cert_id'] ) ? esc_attr( $saved_bonanza_details['details']['ced_bonanza_lister_cert_id'] ) : '';
	        $authToken = get_option('bonanza_tocken_details',true);
			$authToken = isset( $authToken['authToken'] ) ? $authToken['authToken'] : '';
			$args = array("item" => $this->data);
	
			$args=json_encode($args,true);
	        $uploading_data=array();
            $uploading_data['data']=$args;
            $uploading_data['server_host']=$_SERVER['HTTP_HOST'];
            $uploading_data['tocken']=$authToken;
            $uploading_data['dev_id']=$ced_bonanza_lister_dev_id;
            $uploading_data['cert_id']=$ced_bonanza_lister_cert_id;
            $uploading_data['action']='uploadProduct';
           
            $json_response=$bonanzaRequest->sendCurlPostMethod('http://demo.cedcommerce.com/woocommerce/marketplaces/bonanza/marketplaces-bonanza.php',$uploading_data);
             
			$this->uploadResponse = $json_response;
			 
            return $json_response;
			
		}

		/**
		* Function to update proudcts details once it has already uploaded
		* and having its own listing id
		*/
		public function doupdate($listing_id_to_update){
			global $svc_obj;
			 
			$authToken = get_option('bonanza_tocken_details',true);
			$authToken = isset( $authToken['authToken'] ) ? $authToken['authToken'] : '';
			$args = array("item" => $this->data);
			$args['requesterCredentials']['bonanzleAuthToken'] = $authToken;
		 
			$json_response = $svc_obj->reviseFixedPriceItem($listing_id_to_update,$args);
		 
			$this->uploadResponse = $json_response;
			 
            return $json_response;
  
		}

		/**
		* Function to prepare items to get product ready to upload
		*/
		public function prepareItems($productIds){
			 
			if(is_array($productIds) && !empty($productIds)){

				$this->error_message = "";
				foreach ($productIds as $productId) {
					
					$_product = wc_get_product( $productId );
					$image_id = get_post_thumbnail_id( $_product->get_id() );
					$productType = $_product->get_type();
					 
					$check_if_uploaded = get_post_meta($productId, "ced_bonanza_lister_listing_id", true);
					 
					if(empty($check_if_uploaded)) {

						$preparedData = $this->getFormatedData($productId);
						 
						$this->data = $preparedData;
						$bonanza_calls_count = get_option('bonanza_calls_count');
						if($bonanza_calls_count < 50){
							$response = json_decode(self::doupload(), true);
						}  
						else{
							$this->error_message = __('Maximum Upload Limit Reached. Switch to Premium Version','ced-bonanza');
						}
						if(isset($response['ack'])){

							$is_success = $response['ack'];
						}
						 
						if(isset($response['status']) && $response['status'] =='600' ){
							$this->success_message = __('Maximum Upload Limit Reached. Switch to Premium Version','ced-bonanza');
							update_option('bonanza_calls_limit','exceeded');
						}
						 
						if(isset($is_success) && $is_success != 'Success') {
							$this->error_message.= __("Failed to upload Product ID# ",'ced-bonanza').$productId;
							 
						}
						elseif(isset($is_success) && $is_success == 'Success'){
							$this->success_message = __(" Product ID# ",'ced-bonanza').$productId.__(' uploaded successfully!','ced-bonanza');
							if(isset($response['addFixedPriceItemResponse']['itemId'])){

								update_post_meta($productId,"ced_bonanza_lister_listing_id", $response['addFixedPriceItemResponse']['itemId'] );
								$bonanza_calls_count=get_option('bonanza_calls_count',true);
								if($bonanza_calls_count==null || $bonanza_calls_count==''){
									$bonanza_calls_count = 0;
								}else{
									$bonanza_calls_count =$bonanza_calls_count+1;
								}
								update_option('bonanza_calls_count',$bonanza_calls_count);

							}else{
								$this->success_message = __("Request Failed",'ced-bonanza');
							}
							
							update_post_meta($productId,"ced_bonanza_lister_listing_status", 'PUBLISHED' );
						 
						}
					}
					else {
							 
							$preparedData = $this->getFormatedData($productId);
							 
							$this->data = $preparedData;
							$listing_id_to_update=get_post_meta($productId,"ced_bonanza_lister_listing_id", true);
							$response = json_decode(self::doupdate($listing_id_to_update), true);
							 
							$is_success = $response['ack'];
							 
							if(isset($is_success) && $is_success != 'Success') {
								$this->error_message.= __(". Product ID# ",'ced-bonanza').$productId;
								 
							}
							elseif(isset($is_success) && $is_success == 'Success'){
							 	$this->success_message = __("Product ID# ",'ced-bonanza').$productId.__(' updated successfully!','ced-bonanza');
								 
								update_post_meta($productId,"ced_bonanza_lister_listing_status", 'PUBLISHED' );
							} 
					}			
				}
				if($this->error_message != "") {
									
					$message = $this->error_message;
					$notice['message'] = $message;
					$notice['classes'] = 'error is-dismissable';
					  
					$this->final_response = $notice;
					
				}
				else {
					
					$notice['message'] = $this->success_message;
					$notice['classes'] = 'notice notice-success is-dismissable';
					$this->final_response = $notice;
				}
			}
		}
 		
 		/**
 		* Function to get all formated data that need to upload on Bonanza
 		*/
		public function getFormatedData($productId){
			if($productId){
				$product = wc_get_product( $productId );
				$status_revr = $product->get_status();
				if($status_revr == 'publish'){
				$args = array();
				$product_data = $product->get_data();
				 
				$productType = $product->get_type();
 
				$this->fetchAssignedProfileDataOfProduct( $productId );
				$isEnableToSendInventoryOne = get_option('ced_bonanza_lister_sync_on_inventory_zero', false);
				$stock_status = $product_data['stock_status'];
				if( $stock_status == 'instock') {
					 
					$stock_qty = (int)$product_data['stock_quantity'];
					if( $isEnableToSendInventoryOne=='on' && $stock_qty == 0){
						$stock_qty = 1;
					}
					 
				}
				else {
					
					if($isEnableToSendInventoryOne=='on'){
						$stock_qty = 1;	 
						$args['has_inventory'] = true;
					}
					
				}
				 
				if($product_data['description']==""){
					$description = $product_data['short_description'];

				}else{
					$description = $product_data['description'];

				}
			
				if( $productType == 'variation' && $description == '') {
					$parent_id = $product->get_parent_id();
					$product_parent = wc_get_product( $parent_id );
					$product_parent_data = $product_parent->get_data();

					$description = $product_parent_data['description'];
				}
				
				$price = (float)$product_data['price'];
			   	$title = $product_data['name'];
				$args['title'] = $title;
				$args['quantity'] = $stock_qty;
				$args['description'] = $description;
				$args['price'] = $price;
				 
				$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $productId ), 'single-post-thumbnail' );
				if( $productType == 'variation' ) {
					$parent_id = $product->get_parent_id();
					$product_parent = wc_get_product( $parent_id );
					$attachment_ids 			= 	$product_parent->get_gallery_image_ids();
				}
				else {
					$attachment_ids 			= 	$product->get_gallery_image_ids();
				}
				

				$alternate_image_urls 		= 	array();
				$alternate_image_urls[] = $image_url[0];
				if(count($attachment_ids))
				{
					foreach( $attachment_ids as $attachment_id )
					{
						//Get URL of Gallery Images - default wordpress image sizes
						$alternate_image_urls[] = wp_get_attachment_url( $attachment_id );
					}
				} 
				 
				$category = $this->fetchMetaValueOfProduct($productId,'_bnz_bonanza_category');
  
					$args['pictureDetails']['pictureURL'] = $alternate_image_urls;
 
					$args['primaryCategory']['categoryId'] = $category;
			  		$args['shippingDetails']['shippingServiceOptions']['freeShipping']=true;
					$categoryID=$category;
					
					$specificsFieldsValues=array();
					if(!empty($categoryID)){
						$categoryTraits = get_option( 'category_traits_'.$categoryID, array() );

						if(isset($categoryTraits) && is_array($categoryTraits)){
							$categoryTraitsArrayFields=array();
							foreach ($categoryTraits as $key => $categoryTraitsFiels) {
								
								$specificsFieldsValue= $this->fetchMetaValueOfProduct($productId,'_ced_bonanza_lister_'.$categoryID.'_'.$categoryTraitsFiels['id']);
								$customSpecificsFieldsValue= $this->fetchMetaValueOfProduct($productId,'_ced_bonanza_lister_cat_traits_'.$categoryID.'_'.$categoryTraitsFiels['id']);
							 
								if( isset($customSpecificsFieldsValue) && $customSpecificsFieldsValue !="" && $customSpecificsFieldsValue != null) {
									$specificsFieldsValue=$customSpecificsFieldsValue;
									  
								}
								if( isset($specificsFieldsValue) && $specificsFieldsValue !="" && $specificsFieldsValue != null) {

									 $specificsFieldsValues[]=array($categoryTraitsFiels['label'],$specificsFieldsValue);
								}
							}
						}

					}

	  				$upc = $this->fetchMetaValueOfProduct($productId,'_ced_bonanza_lister_upc');
					if( isset($upc) && $upc != null) {
						$upc_args= $upc;
						$specificsFieldsValues[]=array('upc',$upc_args);
					}
					$sku = $this->fetchMetaValueOfProduct($productId,'_ced_bonanza_lister_sku');
					if( isset($sku) && $sku != null) {
						$args['sku'] = $sku;
					}
					
					$args['itemSpecifics']['specifics']=$specificsFieldsValues;
					 
					return $args;
				}
			}
		}
 
		/**
		 * This function fetches data in accordance with profile assigned to product.
		 * @name fetchAssignedProfileDataOfProduct()
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @link  http://www.cedcommerce.com/
		 */
		function fetchAssignedProfileDataOfProduct( $product_id ) {
			global $wpdb;
			$table_name = $wpdb->prefix.CED_Bonanza_Lister_PREFIX.'_bonanzaprofiles';
			$profileID = get_post_meta( $product_id, 'ced_bonanza_lister_profile', true);
			$profile_data = array();
			if( isset($profileID) && !empty($profileID) && $profileID != "" ) {
				$this->isProfileAssignedToProduct = true;
				$profileid = $profileID;
				$query = "SELECT * FROM `$table_name` WHERE `id`=$profileid";
				$profile_data = $wpdb->get_results($query,'ARRAY_A');
				if(is_array($profile_data)) {
					$profile_data = isset($profile_data[0]) ? $profile_data[0] : $profile_data;
					$profile_data = isset($profile_data['profile_data']) ? json_decode($profile_data['profile_data'],true) : array();
				}
			}
			else {
				$this->isProfileAssignedToProduct = false;
			}
			$this->profile_data = $profile_data;
		}
		
		/**
		 * This function fetches meta value of a product in accordance with profile assigned and meta value available.
		 * @name fetchMetaValueOfProduct()
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @link  http://www.cedcommerce.com/
		 */
		
		function fetchMetaValueOfProduct( $product_id, $metaKey ) {
		
			if(isset($this->isProfileAssignedToProduct) && $this->isProfileAssignedToProduct) {
				
				$_product = wc_get_product($product_id);
				if( WC()->version < '3.0.0' ){
					if( $_product->product_type == "variation" ) {
						$parentId = $_product->parent->id;
					}
					else {
						$parentId = "0";
					}
				}else{
					if( $_product->get_type() == "variation" ) {
						$parentId = $_product->get_parent_id();
					}
					else {
						$parentId = "0";
					}
				}
					
				if(!empty($this->profile_data) && isset($this->profile_data[$metaKey])) {
					$tempProfileData = $profileData = $this->profile_data[$metaKey];
		
					if( isset($tempProfileData['default']) && !empty($tempProfileData['default']) && $tempProfileData['default'] != "" && !is_null($tempProfileData['default']) ) {
						$value = $tempProfileData['default'];
					}
					else if( isset($tempProfileData['metakey']) && !empty($tempProfileData['metakey']) && $tempProfileData['metakey'] != "" && !is_null($tempProfileData['metakey']) ) {
							
						//if woo attribute is selected
						if (strpos($tempProfileData['metakey'], 'bnz_pattr_') !== false) {
		
							$wooAttribute = explode('bnz_pattr_', $tempProfileData['metakey']);
							$wooAttribute = end($wooAttribute);
								
							if( WC()->version < '3.0.0' )
							{
								if( $_product->product_type == "variation" ) {
									$attributes =  $_product->get_variation_attributes() ;
									if(isset($attributes['attribute_pa_'.$wooAttribute]) && !empty($attributes['attribute_pa_'.$wooAttribute])) {
										$wooAttributeValue = $attributes['attribute_pa_'.$wooAttribute];
										if( $parentId != "0" ){
											$product_terms = get_the_terms($parentId, 'pa_'.$wooAttribute);
										}
										else {
											$product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
										}
									}
									else {
										$wooAttributeValue = $_product->get_attribute( 'pa_'.$wooAttribute );
			
										$wooAttributeValue = explode(",", $wooAttributeValue);
										$wooAttributeValue = $wooAttributeValue[0];
			
										if( $parentId != "0" ) {
											$product_terms = get_the_terms($parentId, 'pa_'.$wooAttribute);
										}
										else {
											$product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
										}
									}
										
									if(is_array($product_terms) && !empty($product_terms)) {
										foreach ($product_terms as $tempkey => $tempvalue) {
											if($tempvalue->slug == $wooAttributeValue ) {
												$wooAttributeValue = $tempvalue->name;
												break;
											}
										}
										if( isset($wooAttributeValue) && !empty($wooAttributeValue) ) {
											$value = $wooAttributeValue;
										}
										else {
											$value = get_post_meta( $product_id, $metaKey, true );
										}
									}
									else {
										$value = get_post_meta( $product_id, $metaKey, true );
									}
								}
								else {
									$wooAttributeValue = $_product->get_attribute( 'pa_'.$wooAttribute );
									$product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
									if(is_array($product_terms) && !empty($product_terms)) {
										foreach ($product_terms as $tempkey => $tempvalue) {
											if($tempvalue->slug == $wooAttributeValue ) {
												$wooAttributeValue = $tempvalue->name;
												break;
											}
										}
										if( isset($wooAttributeValue) && !empty($wooAttributeValue) ) {
											$value = $wooAttributeValue;
										}
										else {
											$value = get_post_meta( $product_id, $metaKey, true );
										}
									}
									else {
										$value = get_post_meta( $product_id, $metaKey, true );
									}
								}
							}else{
								if( $_product->get_type() == "variation" ) {
									$attributes =  $_product->get_variation_attributes() ;
									if(isset($attributes['attribute_pa_'.$wooAttribute]) && !empty($attributes['attribute_pa_'.$wooAttribute])) {
										$wooAttributeValue = $attributes['attribute_pa_'.$wooAttribute];
										if( $parentId != "0" ){
											$product_terms = get_the_terms($parentId, 'pa_'.$wooAttribute);
										}
										else {
											$product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
										}
									}
									else {
										$wooAttributeValue = $_product->get_attribute( 'pa_'.$wooAttribute );
			
										$wooAttributeValue = explode(",", $wooAttributeValue);
										$wooAttributeValue = $wooAttributeValue[0];
			
										if( $parentId != "0" ) {
											$product_terms = get_the_terms($parentId, 'pa_'.$wooAttribute);
										}
										else {
											$product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
										}
									}
										
									if(is_array($product_terms) && !empty($product_terms)) {
										foreach ($product_terms as $tempkey => $tempvalue) {
											if($tempvalue->slug == $wooAttributeValue ) {
												$wooAttributeValue = $tempvalue->name;
												break;
											}
										}
										if( isset($wooAttributeValue) && !empty($wooAttributeValue) ) {
											$value = $wooAttributeValue;
										}
										else {
											$value = get_post_meta( $product_id, $metaKey, true );
										}
									}
									else {
										$value = get_post_meta( $product_id, $metaKey, true );
									}
								}
								else {
									$wooAttributeValue = $_product->get_attribute( 'pa_'.$wooAttribute );
									$product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
									if(is_array($product_terms) && !empty($product_terms)) {
										foreach ($product_terms as $tempkey => $tempvalue) {
											if($tempvalue->slug == $wooAttributeValue ) {
												$wooAttributeValue = $tempvalue->name;
												break;
											}
										}
										if( isset($wooAttributeValue) && !empty($wooAttributeValue) ) {
											$value = $wooAttributeValue;
										}
										else {
											$value = get_post_meta( $product_id, $metaKey, true );
										}
									}
									else {
										$value = get_post_meta( $product_id, $metaKey, true );
									}
								}
							}
						}
						else {
		
							$value = get_post_meta( $product_id, $tempProfileData['metakey'], true );
							if($tempProfileData['metakey'] == '_thumbnail_id'){
								$value = wp_get_attachment_image_url( get_post_meta( $product_id,'_thumbnail_id',true), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $product_id,'_thumbnail_id',true), 'thumbnail' ) : '';
							}
							if( !isset($value) || empty($value) || $value == "" || is_null($value) || $value == "0" || $value == "null") {
								if( $parentId != "0" ) {
		
									$value = get_post_meta( $parentId, $tempProfileData['metakey'], true );
									if($tempProfileData['metakey'] == '_thumbnail_id'){
										$value = wp_get_attachment_image_url( get_post_meta( $parentId,'_thumbnail_id',true), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $parentId,'_thumbnail_id',true), 'thumbnail' ) : '';
									}
		
									if( !isset($value) || empty($value) || $value == "" || is_null($value) ) {
										$value = get_post_meta( $product_id, $metaKey, true );
		
									}
								}
								else {
									$value = get_post_meta( $product_id, $metaKey, true );
								}
							}
		
						}
					}
					else {
						$value = get_post_meta( $product_id, $metaKey, true );
					}
				}
				else {
					$value = get_post_meta( $product_id, $metaKey, true );
				}
			}
			else {
				$value = get_post_meta( $product_id, $metaKey, true );
			}
			
			return $value;
		}
		  
		/**
		 * function to include dependencies
		 *
		 * @name renderDependency
		 * @return boolean
		 */
		public function renderDependency($file){
			if($file != null || $file != ""){
				require_once "$file";
				return true;
			}
				return false;
		}
	}
}