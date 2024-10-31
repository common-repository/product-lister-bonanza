<div class="ced_bonanza_lister_overlay">
	<div class = "ced_bonanza_lister_hidden_profile_section ced_bonanza_lister_wrap">
		<p class="ced_bonanza_lister_button_right">
			<span class="ced_bonanza_lister_overlay_cross ced_bonanza_lister_white_txt">X</span>
		<p>
		<h2 class="ced_bonanza_lister_setting_header"><?php _e("Select profile for this product","ced_bonanza_lister");?></h2>
		<label class="ced_bonanza_lister_white_txt"><?php _e('Available Profile','ced_bonanza_lister');?></label>
			<?php 
			global $wpdb;
			$wpdb->prefix.CED_Bonanza_Lister_PREFIX.'_bonanzaprofiles';

			$table_name = $wpdb->prefix.CED_Bonanza_Lister_PREFIX.'_bonanzaprofiles';
			 
			$query = "SELECT `id`, `name` FROM `$table_name` WHERE `active` = 1";
			$profiles = $wpdb->get_results($query,'ARRAY_A');
			if(count($profiles)){?>
			<select class="ced_bonanza_lister_profile_select">
				<option value="0"> --<?php _e('select','ced-bonanza');?>-- </option>
			<?php 
				foreach($profiles as $profileInfo){
					$profileId = isset($profileInfo['id']) ? intval($profileInfo['id']) : 0;
					$profileName = isset($profileInfo['name']) ? $profileInfo['name'] : '';
					if($profileId){
						?>
						<option value = "<?php echo $profileId; ?>"><?php echo $profileName; ?></option>
						<?php 
					}
				}
				?>
				</select>
				<button type = "button" data-prodid = "" class="ced_bonanza_lister_save_profile button button-ced_bonanza_lister"><?php _e("Save profile")?></button>
				<?php 
			}else{
			?>
			<p class="ced_bonanza_lister_white_txt"><?php _e('No any profile available to assign, please create a profile and came back to assing!','ced-bonanza');?></p>
		<?php }?>
	</div>
</div>