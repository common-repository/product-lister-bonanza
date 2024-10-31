<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * single product managment related functionality helper class.
 *
 * @since      1.0.0
 *
 * @package    Product Lister Bonanza
 * @subpackage Product Lister Bonanza/admin/helper
 */

if( !class_exists( 'CED_Bonanza_Lister_product_fields' ) ) :

/**
 * single product related functionality.
 *
 * Manage all single product related functionality required for listing product on marketplaces.
 *
 * @since      1.0.0
 * @package    Product Lister Bonanza
 * @subpackage Product Lister Bonanza/admin/helper
 * @author     CedCommerce <cedcommerce.com>
 */
class CED_Bonanza_Lister_product_fields{
	
	/**
	 * The Instace of CED_Bonanza_Lister_product_fields.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      $_instance   The Instance of CED_Bonanza_Lister_product_fields class.
	 */
	private static $_instance;
	
	/**
	 * CED_Bonanza_Lister_product_fields Instance.
	 *
	 * Ensures only one instance of CED_Bonanza_Lister_product_fields is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return CED_Bonanza_Lister_product_fields instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Adding tab on product edit page.
	 * 
	 * @since 1.0.0
	 * @param array   $tabs   single product page tabs.
	 * @return array  $tabs
	 */
	public function bnz_required_fields_tab( $tabs ){
		
		$tabs['bnz_bonanza_required_fields'] = array(
			'label'  => __( 'Bonanza', 'ced-bonanza' ),
			'target' => 'ced_bonanza_lister_fields',
			'class'  => array( 'show_if_simple','ced_bonanza_lister_required_fields' ),
		);
			
		return $tabs;
	}
	
	/**
	 * Fields on bnz Required Fields product edit page tab.
	 * 
	 * @since 1.0.0
	 */
	public function bnz_required_fields_panel() 
	{
		global $post;
		if ( $terms = wp_get_object_terms( $post->ID, 'product_type' ) ) {
				$product_type = sanitize_title( current( $terms )->name );
		} else {
				$product_type = apply_filters( 'default_product_type', 'simple' );
		}
		
		if($product_type == 'simple' ){
			require_once CED_Bonanza_Lister_DIRPATH.'admin/partials/bnz_product_fields.php';
		}
	}

	/* For Variable Product */
	function bnz_render_product_fields_html_for_variations( $loop, $variation_data, $variation ) {
		include CED_Bonanza_Lister_DIRPATH.'admin/partials/bnz_product_fields.php';
	}
	/**
	* Function to render variation html
	*/
	function bnz_render_variation_html($field_array,$loop,$variation) {
		$requiredInAnyCase = array('_bnz_bonanza_id_type','_bnz_bonanza_id_val','_bnz_bonanza_brand');
		$type = esc_attr($field_array['type']);
		if( $type == '_text_input' ) {
			$previousValue = get_post_meta ( $variation->ID, $field_array['fields']['id'], true );
			
			if(in_array($field_array['fields']['id'], $requiredInAnyCase)) {
				$nameToRender = ucfirst($field_array['fields']['label']);
				$nameToRender .= '<span class="ced_bonanza_lister_wal_required"> [ '.__("Required", "ced-bonanza").' ]</span>';
				$field_array['fields']['label'] = $nameToRender;
			}
			
			?>
			<p class="form-field _bnz_brand_field ">
				<label for="<?php echo $field_array['fields']['id']; ?>"><?php echo $field_array['fields']['label']; ?></label>
				<input class="short" name="<?php echo $field_array['fields']['id']; ?>[<?php echo $loop; ?>]" id="<?php echo $field_array['fields']['id']; ?>" value="<?php echo $previousValue; ?>" placeholder="" <?php if($type == 'number') {echo 'type="number"';}else{echo 'type="text"';} ?>> 
				<?php 
				if($field_array['fields']['desc_tip'] == '1') {
					$description = $field_array['fields']['description'];
					echo wc_help_tip( __( $description, 'woocommerce' ) );
				} 
				?>
			</p>
			<?php
		}
		else if( $type == '_select' ) {
			$previousValue = get_post_meta ( $variation->ID, $field_array['fields']['id'], true );
			
			if(in_array($field_array['fields']['id'], $requiredInAnyCase)) {
				$nameToRender = ucfirst($field_array['fields']['label']);
				$nameToRender .= '<span class="ced_bonanza_lister_wal_required"> [ '.__("Required", "ced-bonanza").' ]</span>';
				$field_array['fields']['label'] = $nameToRender;
			}

			?>
			<p class="form-field _bnz_id_type_field ">
				<label for="<?php echo $field_array['fields']['id']; ?>"><?php echo $field_array['fields']['label']; ?></label>
				<select id="<?php echo $field_array['fields']['id']; ?>" name="<?php echo $field_array['fields']['id']; ?>[<?php echo $loop; ?>]" class="select short">
					<?php
					foreach ($field_array['fields']['options'] as $key => $value) {
						if($previousValue == $key) {
							echo '<option value="'.$key.'" selected="selected">'.$value.'</option>';
						}
						else {
							echo '<option value="'.$key.'">'.$value.'</option>';
						}
					}
					?>
				</select> 
				<?php 
				if($field_array['fields']['desc_tip'] == '1') {
					$description = $field_array['fields']['description'];
					echo wc_help_tip( __( $description, 'woocommerce' ) );
				} 
				?>
			</p>
		<?php
		}					
	}
	
	/**
	 * processing product meta required fields for listing
	 * product on marketplace.
	 * 
	 * @since 1.0.0
	 * @var int  $post_id
	 */
	public function bnz_required_fields_process_meta( $post_id ){
		
		if( sanitize_text_field ($_POST['product-type']) == 'variable') {

			$required_fields_ids = $this->get_custom_fields('required',true);
			$required_fields_ids[] = '_bnz_bonanza_variation_title';// for saving variation title field only in case of variation product
			$extra_fields_ids = $this->get_custom_fields('extra',true);
			$framework_fields = array();
			$framework_fields_ids = array();
			$framework_fields = $this->get_custom_fields('framework_specific',false);
			if(count($framework_fields)){
				foreach($framework_fields as $fields_data){
					if(is_array($fields_data)){
						foreach($fields_data as $fields_array){
							if(isset($fields_array['id']))
								$framework_fields_ids[] = esc_attr($fields_array['id']);
						}
					}
				}
			}

			$checkbox_meta_fields = array(
				'_bnz_bonanza_custom_price',
				'_bnz_bonanza_custom_stock',
				'_bnz_bonanza_prop65',
				'_bnz_bonanza_overage18verification'
			);

			$all_fields = array();
			$all_fields = array_merge($required_fields_ids,$extra_fields_ids,$framework_fields_ids );
			$all_fields = array_unique($all_fields);
			
			foreach ($all_fields as $field_name) {
				if (in_array($field_name, $checkbox_meta_fields)) {
					continue;
				}
				if(isset($_POST[$field_name]) && !empty($_POST[$field_name]))
				{	
					if(is_array($_POST[$field_name]))
					{	
						foreach ($_POST[$field_name] as $index => $value) {
							$product_id = sanitize_text_field ($_POST['variable_post_id'][$index]);
							update_post_meta( $product_id, $field_name, sanitize_text_field( $value ) );
						}
					}
				}
			}
			
			$var_post_id = isset($_POST['variable_post_id']) ? $_POST['variable_post_id'] : array();
			if(is_array($var_post_id)){
				foreach ($var_post_id as $index => $product_id) {
					foreach ($checkbox_meta_fields as $field_name) {
						if(isset($_POST[$field_name][$index]) && sanitize_text_field ($_POST[$field_name][$index]) == "yes") {
							update_post_meta( $product_id, $field_name, "yes" );
						}
						else {
							update_post_meta( $product_id, $field_name, "no" );
						}
					}
				}
			}
			do_action( 'ced_bonanza_lister_required_fields_process_meta_variable', $post_id );
		}
		else {
			$required_fields_ids = $this->get_custom_fields('required',true);
			$extra_fields_ids = $this->get_custom_fields('extra',true);
			$framework_fields = array();
			$framework_fields_ids = array();
			
			$framework_fields = $this->get_custom_fields('framework_specific',false);
			if(count($framework_fields)){
				foreach($framework_fields as $fields_data){
					if(is_array($fields_data)){
						foreach($fields_data as $fields_array){
							if(isset($fields_array['id']))
								$framework_fields_ids[] = esc_attr($fields_array['id']);
						}
					}
				}
			}
			$all_fields = array();
			$all_fields = array_merge($required_fields_ids,$extra_fields_ids,$framework_fields_ids );
			foreach($all_fields as $field_name){
				if(isset($_POST[$field_name])){
					update_post_meta( $post_id, $field_name, sanitize_text_field( $_POST[$field_name] ) );
				}
				else {
					update_post_meta( $post_id, $field_name, false);
				}
			}

			do_action( 'ced_bonanza_lister_required_fields_process_meta_simple', $post_id );
		}
	}

	/**
	 * get product custom fields for preparing
	 * product data information to send on different
	 * marketplaces accoding to there requirement.
	 * 
	 * @since 1.0.0
	 * @param string  $type  required|framework_specific|common
	 * @param bool	  $ids  true|false
	 * @return array  fields array
	 */
	public static function get_custom_fields( $type, $is_fields=false,$categoryID='' ){
		global $post;
		$fields = array();
 
		 
		if($type=='required'){
			
			$required_fields = array(
				array(
						'type' => '_text_input',
						'id' => '_ced_bonanza_lister_sku',
						'fields' => array(
								'id'                => '_ced_bonanza_lister_sku',
								'label'             => __( 'SKU', 'ced-bonanza' ).'<span class="ced_bonanza_lister_required ced_required_red_color"> [ '.__( 'Required', 'ced-bonanza' ).' ]</span>',
								'desc_tip'          => true,
								'description'       => __( 'Unique identifier for product.', 'ced-bonanza' ),
								'type'              => 'text',
								'class'				=> ''
						)
				),
				array(
						'type' => '_text_input',
						'id' => '_ced_bonanza_lister_upc',
						'fields' => array(
								'id'                => '_ced_bonanza_lister_upc',
								'label'             => __( 'UPC', 'ced-bonanza' ).'<span class="ced_bonanza_lister_required ced_required_red_color"> [ '.__( 'Required', 'ced-bonanza' ).' ]</span>',
								'desc_tip'          => true,
								'description'       => __( 'Unique identifier for product.', 'ced-bonanza' ),
								'type'              => 'text',
								'class'				=> ''
						)
				),
		    
			);
			
			$fields = is_array( apply_filters('ced_bonanza_lister_required_product_fields', $required_fields, $post) ) ? apply_filters('ced_bonanza_lister_required_product_fields', $required_fields, $post) : array() ;
		}
		else if($type=='framework_specific'){
			if(empty($categoryID) && $categoryID==''){
				return;
			}
			$framework_fields = array();
			$categoryTraits = get_option( 'category_traits_'.$categoryID, array() );
			$variation_category_traits=array();
			$variation_category_traits_id=array();
			if(isset($categoryTraits) && is_array($categoryTraits)){
				$categoryTraitsArrayFields=array();
				foreach ($categoryTraits as $key => $categoryTraitsFiels) {
					
					 $categoryTraitsFielsfinalValues=array();
					 if (isset($categoryTraitsFiels['traitValues']) && is_array($categoryTraitsFiels['traitValues'])) {
					  	foreach ($categoryTraitsFiels['traitValues'] as $key => $categoryTraitsFielsValues) {
					  		 $categoryTraitsFielsfinalValues[]=$categoryTraitsFielsValues['name'];
					  	}
					 }
					if($categoryTraitsFiels['htmlInputType']=='textfield'){

						$categoryTraitsArrayFields[]=array(
								'type' => '_text_input',
								'id' => '_ced_bonanza_lister_'.$categoryID.'_'.$categoryTraitsFiels['id'],
								'fields' => array(
										'id'                => '_ced_bonanza_lister_'.$categoryID.'_'.$categoryTraitsFiels['id'],
										'label'             => __( $categoryTraitsFiels['label'], 'ced-bonanza' ).'<span class="ced_bonanza_lister_required ced_required_green_color"> [ '.__( 'Optional', 'ced-bonanza' ).' ]</span>',
										'desc_tip'          => true,
										'description'       => __( $categoryTraitsFiels['label'], 'ced-bonanza' ),
										'type'              => 'text',
										'class'				=> ''
								)
						);
					}
					
					if($categoryTraitsFiels['htmlInputType']=='dropdown' || $categoryTraitsFiels['htmlInputType']=='checkbox_set'){
						$categoryTraitsArrayFields[]=array(
							'type' => '_select',
							'id' => '_ced_bonanza_lister_'.$categoryID.'_'.$categoryTraitsFiels['id'],
							'fields' => array(
									'id'                => '_ced_bonanza_lister_'.$categoryID.'_'.$categoryTraitsFiels['id'],
									'label'             => __( $categoryTraitsFiels['label'], 'ced-bonanza' ).'<span class="ced_bonanza_lister_required ced_required_green_color"> [ '.__( 'Optional', 'ced-bonanza' ).' ]</span>',
									'desc_tip'          => true,
									'description'       => __( $categoryTraitsFiels['label'], 'ced-bonanza' ),
									'type'              => 'select',
									'options'			=>  $categoryTraitsFielsfinalValues,
									'class'				=> ''
							)
						);
					}

				}
			}
			 
			update_post_meta($categoryID,'variation_category_traits_ids',$variation_category_traits_ids);
		 
			$bonanzaSpecificFields[] = $categoryTraitsArrayFields;
			$fields = $bonanzaSpecificFields;

			return $fields;
		}
		if($is_fields){
			$fields_array = array();
			if(is_array($fields)){
		
				foreach($fields as $field_data){
					$fieldID = isset($field_data['id']) ? esc_attr($field_data['id']) : null;
					if(!is_null($fieldID))
						$fields_array[] = $fieldID;
				}
				return $fields_array;
			}else{
				return array();
			}
				
		}else{
			if(is_array($fields)){
				return $fields;
			}else{
				return array();
			}
		}
	}

	/**
	 * Custom fields html.
	 * 
	 * @since 1.0.0
	 * @param array
	 */
	public function custom_field_html($fieldsArray){
		if(is_array($fieldsArray)){
			foreach($fieldsArray as $data){
				$type = isset($data['type']) ? esc_attr($data['type']) : '_text_input';
				$fields = isset($data['fields']) ? is_array($data['fields']) ? $data['fields'] : array() : array();
				$label = isset($fields['label']) ? esc_attr($fields['label']) : '';
				$description = isset($fields['description']) ? esc_attr($fields['description']) : '';
				$desc_tip = isset($fields['desc_tip']) ? intval($fields['desc_tip']) : !empty($description) ? 1 : 0;
				$fieldvalue = isset($fields['value']) ? $fields['value'] : null;
				echo '<div class="ced_bonanza_lister_profile_field">';
				echo '<label class="ced_bonanza_lister_label">';
				echo '<span>'.$label.'</span>';
				switch($type){
					case '_select':
						$id = isset($fields['id']) ? esc_attr($fields['id']) : isset($data['id']) ? esc_attr($data['id']) : null;
						if(!is_null($id)){
							$select_values = isset($fields['options']) ? is_array($fields['options']) ? $fields['options'] : array() : array();
		
							echo '<select name="'.$id.'" id="'.$id.'">';
							if(is_array($select_values)){
								foreach($select_values as $key=>$value){
									echo '<option value="'.$key.'"'.selected($fieldvalue,$key,false).'>';
									echo $value;
									echo '</option>';
								}
							}
							echo '</select>';
						}
						break;
					 
					case '_text_input':
						$id = isset($fields['id']) ? esc_attr($fields['id']) : isset($data['id']) ? esc_attr($data['id']) : null;
						if(!is_null($id)){
							echo '<input type="text" id="'.$id.'" name="'.$id.'" value="'.$fieldvalue.'">';
						}
						break;
					 
					case '_bnz_bonanza_select':
						$id = isset($fields['id']) ? esc_attr($fields['id']) : isset($data['id']) ? esc_attr($data['id']) : null;
						$options = isset($fields['options']) ? $fields['options'] : array();
						$optionsHtml = '';
						$optionsHtml .= '<option value="null">'.__('--select--','ced-bonanza').'</option>';
						if(is_array($options)){
							foreach($options as $industry => $subcats){
									
								if(is_array($subcats)){
									$optionsHtml .= '<option value="null" class="bnz_parent" disabled>'.$industry.'</option>';
									foreach($subcats as $subcatid => $name){
											
										$optionsHtml .= '<option value="'.$subcatid.'" '.selected($fieldvalue,$subcatid,false).'>'.$name.'</option>';
									}
								}
							}
						}
						echo '<p class="form-field '.$id.'">';
						echo '<select name="'.$id.'" id="'.$id.'">';
						echo $optionsHtml;
						echo '</select>';
						echo '</p>';
						break;
				}
				echo '</div>';
			}
		}
	}

	/**
	 * Quick edit save product data from manage product
	 * page of bnz so that admin can quickly change the product
	 * entries and upload them to selected marketplace with minimal
	 * required changes.
	 * 
	 * @since 1.0.0
	 * @param int $post_id
	 * @param object $post
	 */
	public function quick_edit_save_data( $post_id, $post ){
		return;
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		
		// Don't save revisions and autosaves
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return $post_id;
		}
		
		// Check post type is product
		if ( 'product' != $post->post_type && 'product_variation' != $post->post_type ) {
			return $post_id;
		}
		
		// Check user permission
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
		 
		// Get the product and save
		$product = wc_get_product( $post );
	
		// Clear transient
		wc_delete_product_transients( $post_id );
		
		wp_die();
	}
	
	/**
	 * updated product html after quick edit 
	 * for listing on manage products page of bnz.
	 * 
	 * @since 1.0.0
	 */
	public function response_updated_product_html($post, $product){
		
		if(!class_exists('CED_Bonanza_Lister_product_lister')){
			require_once CED_Bonanza_Lister_DIRPATH.'admin/helper/class-ced-bnz-product-listing.php';
			$product_lister = new CED_Bonanza_Lister_product_lister();
			if($post->post_type == 'product_variation') {
				$variation_id = $post->ID;
				$post = get_post($post->post_parent);
				return $product_lister->get_product_row_html_variation($post,$variation_id);
			}
			else {
				return $product_lister->get_product_row_html($post);
			}
		}
		return $post->ID;
	}
}

endif;
