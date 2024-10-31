<?php
if(!class_exists('Ced_Bonanza_ajax_handler')){
	class Ced_Bonanza_ajax_handler{
		
		/**
		 * construct
		 * @version 1.0.0
		 */
		public function __construct()
		{			
			 
			add_action( 'wp_ajax_ced_bonanza_lister_authorize', array ( $this, 'ced_bonanza_lister_authorize' ));
			add_action( 'wp_ajax_ced_bonanza_lister_fetchCat', array ( $this, 'ced_bonanza_lister_fetchCat' ));
			add_action( 'wp_ajax_ced_bonanza_lister_process_bonanza_cat', array ( $this, 'ced_bonanza_lister_process_bonanza_cat' ));
			add_action(	'wp_ajax_ced_bonanza_lister_process_bonanza_cat', array($this,'ced_bonanza_lister_product_cat'));
			add_action('ced_bonanza_lister_required_fields_process_meta_simple', array($this,'ced_bonanza_lister_required_fields_process_meta_simple'), 11, 1 );
			add_action('ced_bonanza_lister_required_fields_process_meta_variable', array($this,'ced_bonanza_lister_required_fields_process_meta_variable'), 11, 1 );
			 
			add_action(	'wp_ajax_fetch_bonanza_attribute_for_selected_category_for_profile_section', array($this,'fetch_bonanza_attribute_for_selected_category_for_profile_section'));
			add_filter( 'bnz_save_additional_profile_info', array( $this, 'bnz_save_additional_profile_info' ), 11, 1 );
			add_filter('ced_bonanza_lister_extra_bulk_actions',array($this,'ced_bonanza_lister_extra_action'),10,1);
		 
			add_action('wp_ajax_ced_bnz_get_product_template_html', array($this,'ced_bnz_get_product_template_html'));
			 
		}
		 
		public function ced_bnz_get_product_template_html()
		{
			 
			$template_id = sanitize_text_field ($_POST['template_id']);
			if( $template_id != '' || $template_id != null ){
				$templates = get_option( 'ced_bonanza_lister_templates' , array() );
				if( is_array($templates) && !empty( $templates ) ){
					foreach ($templates as $key => $value) {
						if( $value['ID'] == $template_id )
						{
							 
							$html = $value['TemplateXML'];
							$html = str_replace('<![CDATA[', '', $html);
							$html = str_replace(']]>', '', $html);
							echo $html;die;
						}
					}
				}
			}
			die;
		}
	 
		public function ced_bonanza_lister_extra_action($actions){
			$actions['update'] = "Update";
			$actions['verify'] = "Verify";
			return $actions;
		}
		
		/**
		 * Save Profile Information
		 *
		 * @name bnz_save_additional_profile_info
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 */
		
		public function bnz_save_additional_profile_info( $profile_data ) {
			if(isset($_POST['ced_bonanza_lister_attributes_ids_array'])) {
				foreach ($_POST['ced_bonanza_lister_attributes_ids_array'] as $key ) {
					if(isset($_POST[$key])) {
						$fieldid = isset($key) ? $key : '';
						$fieldvalue = isset($_POST[$key]) ? sanitize_text_field ( $_POST[$key][0]) : null;
						$fieldattributemeta = isset($_POST[$key.'_attibuteMeta']) ? sanitize_text_field ($_POST[$key.'_attibuteMeta']) : null;
						$profile_data[$fieldid] = array('default'=>$fieldvalue,'metakey'=>$fieldattributemeta);
					}
				}
			}
			return $profile_data;
		}
		
		/**
		 * Function to get category specifics on profile edit page
		 * @name fetch_bonanza_attribute_for_selected_category_for_profile_section
		 */
		public function fetch_bonanza_attribute_for_selected_category_for_profile_section() 
		{
			 
			if(isset($_POST['profileID'])) {
				$profileid = sanitize_text_field ($_POST['profileID']);
			}
			global $wpdb;
			$table_name = $wpdb->prefix.CED_Bonanza_Lister_PREFIX.'_bonanzaprofiles';
			$profile_data = array();
			if($profileid){
				$query = "SELECT * FROM `$table_name` WHERE `id`=$profileid";
				$profile_data = $wpdb->get_results($query,'ARRAY_A');
				 
				if(is_array($profile_data)) {
					$profile_data = isset($profile_data[0]) ? $profile_data[0] : $profile_data;
					$profile_data = isset($profile_data['profile_data']) ? json_decode($profile_data['profile_data'],true) : array();
				}
			}
			
			/* select dropdown setup */
			$attributes		=	wc_get_attribute_taxonomies();
			$attrOptions	=	array();
			$addedMetaKeys = get_option('CedbnzProfileSelectedMetaKeys', false);
			
			if($addedMetaKeys && count($addedMetaKeys) > 0){
				foreach ($addedMetaKeys as $metaKey){
					$attrOptions[$metaKey]	=	$metaKey;
				}
			}
			if(!empty($attributes)){
				foreach($attributes as $attributesObject){
					$attrOptions['bnz_pattr_'.$attributesObject->attribute_name]	=	$attributesObject->attribute_label;
				}
			}
			/* select dropdown setup */
			
			$categoryID = isset($_POST['categoryID']) ? sanitize_text_field ($_POST['categoryID']) : "";
			$productID = isset($_POST['productID']) ? sanitize_text_field ($_POST['productID']) : "";

			$categoryTraits = get_option( 'category_traits_'.$categoryID, array() );
 
			/* render framework specific fields */
			$pFieldInstance = CED_Bonanza_Lister_product_fields::get_instance();
			$framework_specific =$pFieldInstance->get_custom_fields('framework_specific',false,$categoryID);
			 
			if(is_array($framework_specific)) {
			$framework_specific=$framework_specific['0'];
			$attributesList = $framework_specific['bonanza'];
				?>
					<div class="ced_bonanza_lister_cmn ced_bnz_att_cat_val">
					<table class="wp-list-table widefat fixed striped">
						<tbody>
						</tbody>
						<tbody>
							<?php
							global $global_CED_Bonanza_Lister_Render_Attributes;
							$marketPlace = "ced_bonanza_lister_required_common";
							$productID = 0;
							$categoryID = '';
							$indexToUse = 0;
							$selectDropdownHTML= bonanzarenderMetaSelectionDropdownOnProfilePage();
						 
							$attributesList =$framework_specific;
						 
							foreach ($attributesList as $value) {
								$isText = true;
								$field_id = trim($value['fields']['id'],'_');
								$default = isset($profile_data[$value['fields']['id']]) ? $profile_data[$value['fields']['id']] : '';
								$default = $default['default'];
								echo '<tr>';
								echo '<td>';
								if( $value['type'] == "_select" ) {
									$valueForDropdown = $value['fields']['options'];
									$tempValueForDropdown = array();
									foreach ($valueForDropdown as $key => $_value) {
										$tempValueForDropdown[$_value] = $_value;
									}
									$valueForDropdown = $tempValueForDropdown;
									
									$global_CED_Bonanza_Lister_Render_Attributes->renderDropdownHTML($field_id,ucfirst($value['fields']['label']),$valueForDropdown,$categoryID,$productID,$marketPlace,$value['fields']['description'],$indexToUse,array('case'=>'profile','value'=>$default));
									$isText = false;
								}
								else {
									$global_CED_Bonanza_Lister_Render_Attributes->renderInputTextHTML($field_id,ucfirst($value['fields']['label']),$categoryID,$productID,$marketPlace,$value['fields']['description'],$indexToUse,array('case'=>'profile','value'=>$default));
								}
								echo '</td>';
								echo '<td>';
								if($isText) {
									$previousSelectedValue = 'null';
									if( isset($profile_data[$value['fields']['id']]) && $profile_data[$value['fields']['id']] != 'null') {
										$previousSelectedValue = $profile_data[$value['fields']['id']]['metakey'];
									}
									$updatedDropdownHTML = str_replace('{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML);
									$updatedDropdownHTML = str_replace('value="'.$previousSelectedValue.'"', 'value="'.$previousSelectedValue.'" selected="selected"', $updatedDropdownHTML);
									echo $updatedDropdownHTML;
								}
								echo '</td>';
								echo '</tr>';
							}	
							?>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
					</div>
					<?php
				}
				else {
					echo '<div>';
					_e('No Framework Specific Field','ced-bonanza');
					echo '</div>';
				}
			 
			wp_die();
		}
		
		/**
		 * @name ced_bonanza_lister_autharization
		 * function to handle authorization reuqest
		 * 
		 * @version 1.0.0
		 */
		public function ced_bonanza_lister_authorize()
		{
			$nonce = isset($_POST['_nonce']) ? sanitize_text_field ($_POST['_nonce']) : "";
			if($nonce == 'do_bonanza_authorize')
			{
				$saved_bonanza_details = get_option('ced_bonanza_lister_details', array());
				$ced_bonanza_lister_dev_id=$saved_bonanza_details['details']['ced_bonanza_lister_dev_id'];
				$ced_bonanza_lister_cert_id=$saved_bonanza_details['details']['ced_bonanza_lister_cert_id'];
				require_once CED_Bonanza_Lister_DIRPATH_1.'bonanza/lib/Bonanza/svc.php';
				$_bonanzaAutoloader = new svc( $ced_bonanza_lister_dev_id , $ced_bonanza_lister_cert_id );

				$svc_obj = $_bonanzaAutoloader;
				$token= $svc_obj->fetchtoken();
				$is_authorized='false';
				if(isset($token['authToken'])){
					$is_authorized='true';
					update_option('bonanza_tocken_details',$token);
				}else{
					update_option('bonanza_tocken_details',array());

				}
				 
				$response=$is_authorized;
				if( $response=='true' )
				{ 
					 
					echo json_encode( array( 'status'=>'200', 'message' => 'Saved successfully' , 'login_url' => $token['authenticationURL']  ) );
					die;
				}
				else
				{
					echo json_encode(array( 'status'=>'201', 'response' => 'Unable to Auhtorise' ));
					die;
				}
			}
			if( $nonce == 'do_save_credentials' )
			{
				$saved_bonanza_details = get_option( 'ced_bonanza_lister_details',array() );
				$ced_bonanza_lister_dev_id = isset( $_POST['ced_bonanza_lister_dev_id'] ) ? sanitize_text_field ($_POST['ced_bonanza_lister_dev_id'] ) : '';
				$ced_bonanza_lister_cert_id = isset( $_POST['ced_bonanza_lister_cert_id'] ) ? sanitize_text_field( $_POST['ced_bonanza_lister_cert_id'] ): '';
				 
				if( $ced_bonanza_lister_cert_id != '' &&  $ced_bonanza_lister_dev_id != '')
				{
					$saved_bonanza_details['details']['ced_bonanza_lister_cert_id'] = $ced_bonanza_lister_cert_id;
					$saved_bonanza_details['details']['ced_bonanza_lister_dev_id'] = $ced_bonanza_lister_dev_id;
					 
					update_option( 'ced_bonanza_lister_details', $saved_bonanza_details );
					echo json_encode(array( 'status'=>'200', 'message' => 'Saved successfully' ));
					die;
				}
				else
				{
					echo json_encode(array( 'status'=>'401', 'message' => 'Saved successfully' ));
					die;
				}
			}
		}
		
		/**
		 * @name ced_bonanza_lister_fetchCat
		 * function to request for category fetching
		 *
		 * @version 1.0.0
		 */
		public function ced_bonanza_lister_fetchCat()
		{
			$nonce = isset($_POST['_nonce']) ? sanitize_text_field ($_POST['_nonce']) : "";
			$nextLevelCategories = array();

			if($nonce == "ced_bonanza_lister_fetch")
			{
				$saved_bonanza_details = get_option( 'ced_bonanza_lister_details',array() );
				$ced_bonanza_lister_keystring = isset( $saved_bonanza_details['details']['ced_bonanza_lister_keystring'] ) ? esc_attr( $saved_bonanza_details['details']['ced_bonanza_lister_keystring'] ) : '';
				$ced_bonanza_lister_shared_string = isset( $saved_bonanza_details['details']['ced_bonanza_lister_shared_string'] ) ? esc_attr( $saved_bonanza_details['details']['ced_bonanza_lister_shared_string'] ) : '';

				try{
					global $svc_obj;
					$categories = $svc_obj->getCategories($_POST['catDetails']['catID']);
					print_r($categories);
				}
				catch (Exception $e)
				{
					print_r($e);die;
				}

				if( is_array( $categories ) && !empty( $categories ) )
				{
					$folderName = CED_Bonanza_Lister_DIRPATH.'marketplaces/bonanza/lib/json/';
					$catFirstLevelFile = $folderName.'categoryLevel-1.json';
					file_put_contents( $catFirstLevelFile, json_encode($categories) );
					echo json_encode(array( 'status'=>'200', 'response'=>'Category Fetched Successfully' ));
					die;
				}
				echo json_encode(array( 'status'=>'201', 'response'=>'Unable to Fetch Categories' ));
				die;
			}
			else if( $nonce == 'ced_bonanza_lister_fetch_next_level' )
			{
				$catDetails = isset( $_POST['catDetails'] ) ? $_POST['catDetails'] : array();
				$catLevel = isset( $catDetails['catLevel'] ) ? sanitize_text_field ($catDetails['catLevel']) : '';
				$catID = isset( $catDetails['catID'] ) ? sanitize_text_field( $catDetails['catID']) : '' ;
				$catName = isset( $catDetails['catName'] ) ? sanitize_text_field ($catDetails['catName'] ) : '' ;
				$parentCatName = isset( $catDetails['parentCatName'] ) ? sanitize_text_field ( $catDetails['parentCatName'] ) : '' ;

				if( $catID != '' )
				{
					$saved_bonanza_details = get_option( 'ced_bonanza_lister_details',array() );
					$ced_bonanza_lister_keystring = isset( $saved_bonanza_details['details']['ced_bonanza_lister_keystring'] ) ? esc_attr( $saved_bonanza_details['details']['ced_bonanza_lister_keystring'] ) : '';
					$ced_bonanza_lister_shared_string = isset( $saved_bonanza_details['details']['ced_bonanza_lister_shared_string'] ) ? esc_attr( $saved_bonanza_details['details']['ced_bonanza_lister_shared_string'] ) : '';
					$api_url = 'https://openapi.bonanza.com/v2/';
				 
					$selectedCategories = get_option( 'ced_bonanza_lister_selected_categories', array() );
 
					global $svc_obj;
					$categories = $svc_obj->getCategories($catID);
					$categories = json_decode($categories,true);

					if( is_array( $categories ) && !empty( $categories['getCategoriesResponse']['categoryArray'] ) )
					{
						$nextLevelCategories = $categories['getCategoriesResponse']['categoryArray'];
						if( is_array( $nextLevelCategories ) && !empty( $nextLevelCategories ) )
						{
							echo json_encode( array( 'status' => '200', 'nextLevelCat' => $nextLevelCategories, 'selectedCat' => array_unique($selectedCategories) ) );
							wp_die();
						} 
					}

				}
			}
			wp_die();
		}

		/**
		 * function to process selected categories
		 * @name ced_bonanza_lister_process_bonanza_cat
		 * 
		 */
		public function ced_bonanza_lister_process_bonanza_cat(){
			$nonce = isset($_POST['_nonce']) ?  ($_POST['_nonce']) : false;
			if($nonce == 'ced_bonanza_lister_save'){
				$cat = isset($_POST['cat']) ? ( $_POST['cat'] ) : false;
				$catID = isset($cat['catID']) ? $cat['catID'] : false;
				$catName = isset($cat['catName']) ? $cat['catName'] : false;
				if($catID && $catName){
					$savedCategories = get_option('ced_bonanza_lister_selected_categories');
					$savedCategories = isset($savedCategories) ? $savedCategories :array();
					$savedCategories[$catID]=$catName;

					global $svc_obj;
					$categoryTraits = $svc_obj->getCategoryTraits( $catID );
					$categoryTraits = json_decode($categoryTraits, true);
					if( is_array( $categoryTraits ) && !empty($categoryTraits['getCategoryTraitsResponse']['traitArray']) )
					{
						update_option( 'category_traits_'.$catID, $categoryTraits['getCategoryTraitsResponse']['traitArray'] );
					}

					if(update_option('ced_bonanza_lister_selected_categories', array_unique($savedCategories))){
						echo json_encode(array('status'=>'200'));die;
					}
					echo json_encode(array('status'=>'400'));die;
				}
				echo json_encode(array('status'=>'401'));die;
			}
			if($nonce == 'ced_bonanza_lister_remove'){
				$cat = isset($_POST['cat']) ?  ($_POST['cat']) : false;
				$catID = isset($cat['catID']) ? $cat['catID'] : false;
				if($catID){
					$savedCategories = get_option('ced_bonanza_lister_selected_categories');
					$savedCategories = isset($savedCategories) ? $savedCategories :array();
					if(is_array($savedCategories) && !empty($savedCategories)){
						foreach ($savedCategories as $key=>$value){
							if($key == $catID){
								unset($savedCategories[$key]);
							}
						}
					}
					if(update_option('ced_bonanza_lister_selected_categories', array_unique($savedCategories))){
						echo json_encode(array('status'=>'200'));die;
					}
					echo json_encode(array('status'=>'400'));die;
				}
				echo json_encode(array('status'=>'401'));die;
			}
		}

		/**
		 * function to save product bonanza category and render its speciics
		 * 
		 * @name ced_bonanza_lister_product_cat
		 * 
		 */
		public function ced_bonanza_lister_product_cat(){
			$nonce = '';
			if($nonce == ''){
				$catID = isset($_POST['categoryID']) ? sanitize_text_field ( $_POST['categoryID']) : "";
				$productID = isset($_POST['productID']) ? sanitize_text_field ($_POST['productID']) : "";
				$toSave = array();
				$toSave['category'] = $catID;
				$bonanzaDetails = get_option('ced_bonanza_lister_details');
				$token = $bonanzaDetails['token']['bonanzaAuthToken'];
				$siteID = $bonanzaDetails['siteID'];
				if($token == "" || $token == null || $siteID == ""){
					echo json_encode(array('status'=>'401','reason'=>'Token unavailable'));die;
				}
				$file = CED_Bonanza_Lister_DIRPATH.'marketplaces/bonanza/lib/cedGetcategories.php';
				$renderDependency = $this->renderDependency($file);
				if($renderDependency){
					$cedCatInstance = CedGetCategories::get_instance($siteID,$token);
					$getCatSpecifics = $cedCatInstance->_getCatSpecifics($catID);
					$limit = array('ConditionEnabled','ConditionValues');
					$getCatFeatures = $cedCatInstance->_getCatFeatures($catID,$limit);
					$getCatFeatures = isset($getCatFeatures['Category']) ? $getCatFeatures['Category'] : false;
					$toRender = $this->renderAttributes($catID,$productID,$getCatSpecifics, $getCatFeatures);
					if($getCat && is_array($getCat)){
						echo json_encode(array('status'=>'200','nextLevelCat'=>$getCat, 'savedCat'=>array_unique($savedCategories)));die;
					}
					echo json_encode(array('status'=>'201','reason'=>'permission denied'));die;
				}
			}
		}

		/**
		 * function to render specifics
		 * 
		 * @name renderAttributes
		 * 
		 */
		public function renderAttributes($catID,$productID,$catSpecifics, $getCatFeatures){
			$productID = $productID;
			$categoryID = $catID;
			$recomendations = $catSpecifics['Recommendations']['NameRecommendation'];
			$tempRecommendation = array();
			if(!isset($recomendations[0])){
				$tempRecommendation[0] = $recomendations;
				$recomendations = $tempRecommendation;
			}
			$catFeatureSavingForvalidation = array();
			$catFeatureSavingForvalidation = get_option( 'ced_bonanza_lister_req_feat', array() );
			global $global_CED_Bonanza_Lister_Render_Attributes;
			$marketPlace = 'ced_bonanza_lister_attributes_ids_array';
			$_product = wc_get_product($productID);
			$indexToUse = '0';
			if(isset($_POST['indexToUse'])) {
				$indexToUse = sanitize_text_field ($_POST['indexToUse']);
			}
			echo '<div class="ced_bonanza_lister_attribute_section">';
			echo '<div class="ced_bonanza_lister_toggle_section">';
			echo '<div class="ced_bonanza_lister_toggle">';
			echo '<span>Bonanza Attributes</span>';
			echo '<span class="ced_ump_circle_loderimg"><img class="ced_bonanza_lister_circle_img" src='.CED_Bonanza_Lister_URL.'admin/images/circle.png></span>';
			echo '</div>';
			echo '<div class="ced_bonanza_lister_toggle_div ced_attr_wrapper">';
			foreach ($recomendations as $key => $recomendation) {
				if($recomendation['ValidationRules']['SelectionMode'] == 'SelectionOnly') {
					$valueForDropdown = $recomendation['ValueRecommendation'];
					$tempValueForDropdown = array();
					foreach ($valueForDropdown as $key => $value) {
						$tempValueForDropdown[$value['Value']] = $value['Value'];
					}
					$valueForDropdown = $tempValueForDropdown;
					$name = ucfirst($recomendation['Name']);
					if(isset($recomendation['ValidationRules']['MinValues'])){
						$catFeatureSavingForvalidation[$categoryID][] =  $recomendation['Name'];
						$name .= '<span class="ced_bonanza_lister_wal_required"> [ Required ]</span>';
					}
					if(!isset($recomendation['ValidationRules']['VariationSpecifics'])){
						$name .= '<span class="ced_bonanza_lister_wal_conditionally_required"> [ Can Be Used For Variation ]</span>';
					}
					$global_CED_Bonanza_Lister_Render_Attributes->renderDropdownHTML(urlencode($recomendation['Name']),$name,$valueForDropdown,$categoryID,$productID,$marketPlace,"",$indexToUse);
				}
				else {
					$name = $recomendation['Name'];
					if(isset($recomendation['ValidationRules']['MinValues'])){
						$catFeatureSavingForvalidation[$categoryID][] =  $recomendation['Name'];
						$name .= '<span class="ced_bonanza_lister_wal_required"> [ Required ]</span>';
					}
					if(!isset($recomendation['ValidationRules']['VariationSpecifics'])){
						$name .= '<span class="ced_bonanza_lister_wal_conditionally_required"> [  Can Be Used For Variation ]</span>';
					}
					$global_CED_Bonanza_Lister_Render_Attributes->renderInputTextHTML(urlencode($recomendation['Name']),$name,$categoryID,$productID,$marketPlace,"",$indexToUse);
				}
			}
			if($getCatFeatures){
				if(isset($getCatFeatures['ConditionValues'])) {
					$valueForDropdown = $getCatFeatures['ConditionValues']['Condition'];
					$tempValueForDropdown = array();
					foreach ($valueForDropdown as $key => $value) {
						$tempValueForDropdown[$value['ID']] = $value['DisplayName'];
					}
					$valueForDropdown = $tempValueForDropdown;
					$name = "Condition";
					if($getCatFeatures['ConditionEnabled'] == 'Required'){
						$catFeatureSavingForvalidation[$categoryID][] = "Condition";
						$name .= '<span class="ced_bonanza_lister_wal_required"> [ Required ]</span>';
					}
					$global_CED_Bonanza_Lister_Render_Attributes->renderDropdownHTML("Condition",$name,$valueForDropdown,$categoryID,$productID,$marketPlace,"",$indexToUse);
				}
			}
			update_option('ced_bonanza_lister_req_feat', $catFeatureSavingForvalidation);
			wp_die();
		}

		/**
		 * Process Meta data for Simple product
		 *
		 * @name ced_bonanza_lister_required_fields_process_meta_simple
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 */
		
		function ced_bonanza_lister_required_fields_process_meta_simple( $post_id ) {
			$marketPlace = 'ced_bonanza_lister_attributes_ids_array';
			if(isset($_POST[$marketPlace])) {
				foreach ($_POST[$marketPlace] as $key => $field_name) {
					update_post_meta( $post_id, $field_name, sanitize_text_field( $_POST[$field_name][0] ) );
				}
			}
		}

		/**
		 * Process Meta data for variable product
		 *
		 * @name ced_bonanza_lister_required_fields_process_meta_variable
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 */
		
		function ced_bonanza_lister_required_fields_process_meta_variable( $postID ) {
			$marketPlace = 'ced_bonanza_lister_attributes_ids_array';
			if(isset($_POST[$marketPlace])) {
				$attributesArray = array_unique($_POST[$marketPlace]);
				foreach ($attributesArray as $field_name) {
					foreach ($_POST['variable_post_id'] as $key => $post_id) {
						$field_name_md5  = md5( $field_name );
						if(isset($_POST[$field_name_md5][$key])) {
							update_post_meta( $post_id, $field_name, sanitize_text_field( $_POST[$field_name_md5][$key] ) );
						}
					}
				}
			}
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