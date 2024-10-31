<?php
require_once 'save-profile-view-data.php';

$profileID = (isset($_GET['profileID'])?$_GET['profileID']:'');
$profile_data = array();
if($profileID){
	$query = "SELECT * FROM `$table_name` WHERE `id`=$profileID";
	$profile_data = $wpdb->get_results($query,'ARRAY_A');
	if(is_array($profile_data)) {
		$profile_data = isset($profile_data[0]) ? $profile_data[0] : $profile_data;
		
		/* fetcing basic information */
		$profile_name = isset($profile_data['name']) ? esc_attr($profile_data['name']) : '';
		$enable = isset($profile_data['active']) ? $profile_data['active'] : false;
		$enable = ($enable) ? "yes" : "no";
		$marketplaceName = isset($profile_data['marketplace']) ? esc_attr($profile_data['marketplace']) : 'all';
		$all_marketplaces = bonanzaget_enabled_marketplaces();
		array_unshift($all_marketplaces, 'all');

		$data = isset($profile_data['profile_data']) ? json_decode($profile_data['profile_data'],true) : array();
		
	}
}
else {
	/* fetcing basic information */
	$profile_name = isset($profile_data['name']) ? esc_attr($profile_data['name']) : '';
	$enable = isset($profile_data['active']) ? $profile_data['active'] : false;
	$enable = ($enable) ? "yes" : "no";
	$marketplaceName = isset($profile_data['marketplace']) ? esc_attr($profile_data['marketplace']) : 'null';
	$all_marketplaces = bonanzaget_enabled_marketplaces();
	array_unshift($all_marketplaces, 'all');
}

echo '<div class="ced_bonanza_lister_wrap ced_bonanza_lister_wrap_opt">';
	echo '<div class="back"><a href="'.get_admin_url().'admin.php?page=bnz-bonanza-profile">'.__('Go Back','ced-bonanza').'</a></div>';
	?>
	<?php
	global $cedbonanzalisterhelper;
	if(!session_id()) {
		session_start();
	}
	if(isset($_SESSION['ced_bonanza_lister_validation_notice'])) {
		$value = $_SESSION['ced_bonanza_lister_validation_notice'];
		$cedbonanzalisterhelper->bnz_print_notices($value);
		unset($_SESSION['ced_bonanza_lister_validation_notice']);
	}
	?>
	<div class="ced_bonanza_lister_toggle_section_wrapper">
		<div class="ced_bonanza_lister_toggle">
			<span><?php _e( 'Instruction To Use', 'ced-bonanza' ); ?></span>	
		</div>	
		<div class="ced_bonanza_lister_toggle_div ced_bonanza_lister_instruct">
			<p><?php _e( 'Profile can be created to assign similar type of values and categories to multiple products.', 'ced-bonanza' ); ?></p>
			<p><?php _e( '1. Use "Select Product And Corresponding MetaKeys" section to select the metakeys of product you consider can be useful in mapping. This step is not always necessary. If you have done it before, you can skip it for the next time you create a profile.', 'ced-bonanza' ); ?></p>
			<p><?php _e( '2. Under "Profile Information" sections "BASIC" tab, you have option to setup basic information for your profile. Here you can give your profile a name and enable/disable it.', 'ced-bonanza' ); ?></p>
			 
			<p><span class="ced_required_green_color"> <?php _e( '3. Under "Profile Information" sections "ADVANCE" tab, you have option to select marketplaces category, for which you want to create profile for. As soon as you select marketplace category, you are good to go to next sections. Sections below "Profile Information" are marketplace specific and depends upon the selected category of marketplace.', 'ced-bonanza' ); ?></span></p>
			<p><span class="ced_required_green_color"><?php _e( '4. If you have read above instructions carefully, you are good to go.', 'ced-bonanza' ); ?></span></p>

		</div>
	</div>
	<?php
	echo '<form method="post">';
	$products_IDs = array();
	$all_products = new WP_Query( 
		array(
			'post_type' => array('product', 'product_variation'),
			'post_status' => 'publish',
			'posts_per_page' => 10
			) 
		);
	$products = $all_products->posts;
	$selectedProID  = $all_products->posts['0']->ID;
	foreach ( $products as $product ) {
		$product_IDs[] = $product->ID;
	}
	
	if(isset($data['selected_product_id'])) {
		$selectedProID = $data['selected_product_id'];
		$selectedProName = $data['selected_product_name'];
	}
	else{
		$selectedProID = $product_IDs[0];
		$selectedProName = '';
	}
	
	?>
	<div class="ced_bonanza_lister_toggle_section_wrapper">
		<div class="ced_bonanza_lister_toggle">
			<span><?php _e('Select Product And Corresponding MetaKeys','ced-bonanza'); ?></span>	
		</div>	
		<div class="ced_bonanza_lister_toggle_div">
				<input type="hidden" name="profileID" id="profileID"value="<?php echo $profileID;?>">
				<div class="ced_bonanza_lister_pro_search_div">
					<div class="ced_bonanza_lister_inline_box">
						<label for="ced_bonanza_lister_pro_search_box"><?php _e('Type Product Name Here','ced-bonanza'); ?></label>
						<div class="ced_bonanza_lister_wrap_div">
							<input type="hidden" name="selected_product_id" id="selected_product_id" value="<?php echo $selectedProID;?>">
							<input type="text" autocomplete="off" id="ced_bonanza_lister_pro_search_box" name="ced_bonanza_lister_pro_search_box" placeholder="Product Name" value="<?php echo $selectedProName; ?>"/>
							<div id="ced_bonanza_lister_suggesstion_box"></div>
						</div>
						<img class="ced_bonanza_lister_ajax_pro_search_loader" src="<?php echo CED_Bonanza_Lister_URL.'admin/images/ajax-loader.gif'?>">
					</div>	
				</div>
				<?php  bonanzarenderMetaKeysTableOnProfilePage($selectedProID); ?>
		</div>
	</div>

	<div class="ced_bonanza_lister_toggle_section_wrapper">
		<div class="ced_bonanza_lister_toggle">
			<span><?php _e('Profile Information','ced-bonanza'); ?></span>	
		</div>	
		<div class="ced_bonanza_lister_toggle_div">
			<div class="ced_bonanza_lister_tabbed_head_wrapper">
				<ul>
					<li class="active"><?php _e('Basic','ced-bonanza'); ?></li>
					<li><?php _e('Advance','ced-bonanza'); ?></li>	
				</ul>
			</div>
			<div class="ced_bonanza_lister_tabbed_section_wrapper">
				<div class="ced_bonanza_lister_cmn active">
					<input type="hidden" name="profileID" id="profileID"value="<?php echo $profileID;?>">
					<table class="wp-list-table widefat fixed striped">
						<tbody>
						</tbody>
						<tbody>
							<tr>
								<th>
									<?php 
									_e('Profile Name','ced-bonanza'); 
									$attribute_description = 'Give a name to your profile here.';
									echo wc_help_tip( __( $attribute_description, 'ced-bonanza' ) ); 
									?>
								</th>
								<td>
									<input type="text" name="profile_name" value="<?php echo $profile_name; ?>">
								</td>
							</tr>
							<tr>
								<th>
									<?php
									_e('Enable Profile','ced-bonanza'); 
									$attribute_description = __('Make profile status enable/disable here.','ced-bonanza');
									echo wc_help_tip( __( $attribute_description, 'ced-bonanza' ) ); 
									?>
								</th>
								<?php $checked = ($enable == "yes") ? 'checked="checked"' : ''; ?>
								<td>
									<input type="checkbox" name="enable" id="ced_bonanza_lister_enable_marketpalce" <?php echo $checked;?> > <label for="ced_bonanza_lister_enable_marketpalce"><?php _e('Enable Profile','ced-bonanza');?></label>
								</td>
							</tr>
						</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>	
				<div class="ced_bonanza_lister_cmn">
					<?php
					$pFieldInstance = CED_Bonanza_Lister_product_fields::get_instance();
					if(is_wp_error($pFieldInstance)){
						$message = _e('Something went wrong please try again later!','ced-bonanza');
						wp_die($message);
					}
					$fields = $pFieldInstance->get_custom_fields('required',false);
					?>
					<table class="wp-list-table widefat fixed striped">
						<tbody>
						</tbody>
						<tbody>
							<?php
							$requiredInAnyCase = array('_bnz_id_type','_bnz_id_val','_bnz_brand');
							global $global_CED_Bonanza_Lister_Render_Attributes;
							$marketPlace = "ced_bonanza_lister_required_common";
							$productID = 0;
							$categoryID = '';
							$indexToUse = 0;
							$selectDropdownHTML= bonanzarenderMetaSelectionDropdownOnProfilePage();
							
							foreach ($fields as $value) {
								$isText = true;
								$field_id = trim($value['fields']['id'],'_');
								if(in_array($value['fields']['id'], $requiredInAnyCase)) {
									$attributeNameToRender = ucfirst($value['fields']['label']);
									$attributeNameToRender .= '<span class="ced_bonanza_lister_wal_required"> [ Required ]</span>';
								}
								else {
									$attributeNameToRender = ucfirst($value['fields']['label']);
								}
							
								$default = isset($data[$value['fields']['id']]['default']) ? $data[$value['fields']['id']]['default'] : '';
								echo '<tr>';
								echo '<td>';
								if( $value['type'] == "_select" ) {
									$valueForDropdown = $value['fields']['options'];
									if($value['fields']['id'] == '_bnz_id_type'){
										unset($valueForDropdown['null']);
									}
									$valueForDropdown = apply_filters('ced_bonanza_lister_alter_data_to_render_on_profile', $valueForDropdown, $field_id);
									$global_CED_Bonanza_Lister_Render_Attributes->renderDropdownHTML($field_id,$attributeNameToRender,$valueForDropdown,$categoryID,$productID,$marketPlace,$value['fields']['description'],$indexToUse,array('case'=>'profile','value'=>$default));
									$isText = false;
								}else if( $value['type'] == "_multi_select" ) {
									$valueForDropdown = $value['fields']['options'];
									if($value['fields']['id'] == '_bnz_id_type'){
										unset($valueForDropdown['null']);
									}
									$valueForDropdown = apply_filters('ced_bonanza_lister_alter_data_to_render_on_profile', $valueForDropdown, $field_id);
									$global_CED_Bonanza_Lister_Render_Attributes->renderDropdownHTML($field_id,$attributeNameToRender,$valueForDropdown,$categoryID,$productID,$marketPlace,$value['fields']['description'],$indexToUse,array('case'=>'profile','value'=>$default));
									$isText = false;
								}
								else if( $value['type'] == "_text_input" ) {
									$global_CED_Bonanza_Lister_Render_Attributes->renderInputTextHTML($field_id,$attributeNameToRender,$categoryID,$productID,$marketPlace,$value['fields']['description'],$indexToUse,array('case'=>'profile','value'=>$default));
								}
								else {
									do_action('ced_bonanza_lister_render_extra_data_on_profile', $value, $pFieldInstance);
									$isText = false;
								} 
								echo '</td>';
								echo '<td>';
								if($isText) {
									$previousSelectedValue = 'null';
									if( isset($data[$value['fields']['id']]['metakey']) && $data[$value['fields']['id']]['metakey'] != 'null') {
										$previousSelectedValue = $data[$value['fields']['id']]['metakey'];
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
			</div>	
		</div>
	</div>

	<?php
	$enableMarketPlaces = array();
	 $enableMarketPlaces = array('Bonanza');
	foreach ($enableMarketPlaces as $marketPlaceKey) {
		echo '<div class="ced_bonanza_lister_toggle_section_wrapper ced_bonanza_lister_Bonanza_attribute_section">';
			echo '<div class="ced_bonanza_lister_toggle">';
				echo '<span>'.strtoupper(__('Bonanza','ced-bonanza')).'</span>';	
			echo '</div>';
			echo '<div class="ced_bonanza_lister_toggle_div">';
				?>
				<div class="ced_bonanza_lister_tabbed_head_wrapper">
					<ul>
						<!-- <li class="active"><?php _e( 'Category Specific', 'ced-bonanza' ); ?></li> -->
						 <li class="active"><?php _e( 'Framework Specific', 'ced-bonanza' ); ?></li>	 
					</ul>
				</div>
				<div class="ced_bonanza_lister_tabbed_section_wrapper">
					<div id="ced_bonanza_lister_Bonanza_attribute_section_id">
					</div>	
				</div>
				<?php
			echo '</div>';
		echo '</div>';
	}
	?>
	<div class="">
		<p class="ced_bonanza_lister_button_right">
			<input class="button button-ced_bonanza_lister" value="<?php _e('Save Profile','ced-bonanza'); ?>" name="saveProfile" type="submit">
		</p>
	</div>
	<?php
	echo '</form>';
echo '</div>';
?>
