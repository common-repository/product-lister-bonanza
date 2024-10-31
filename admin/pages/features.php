<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
//this page is used to display all features for comparing between Bonaza product lister free version with it paid version functionality
$section = 'prerequesites'; 
$pre_active = "";
$step_active = "";;
if(isset($_GET['section']))
{
	$section = sanitize_text_field ( $_GET['section'] );
	if($section == 'prerequesites')
	{
		$pre_active = "current";
	}	
	else
	{
		$step_active = "current";
	}	
}	
else 
{
	$pre_active = "current";
}		
require_once CED_Bonanza_Lister_DIRPATH.'admin/pages/header.php';?>
<div class="ced_bonanza_lister_prerequisite_wrapper">
	<div class="ced_bonanza_lister_wrap">
		<a   href="https://cedcommerce.com/woocommerce-extensions/woocommerce-bonanza-integration"><h2 class="ced_bonanza_lister_setting_header"><?php _e('Purchase Bonaza-WooCommerce Integration Paid Version','ced-bonanza')?></h2></a>
		<div class = "ced-bonanza_prerequisite_table_wrap wrap">			
			<table class="wp-list-table widefat fixed striped">
				<tr>
					<th colspan="3"><b><?php _e('Feature','ced-bonanza')?></b></th>
					<th>
						<b><?php _e('Free Version','ced-bonanza')?></b>
					</th>
					<th>
						<b><?php _e('Paid Version','ced-bonanza')?></b>
					</th>
				</tr>
				
				<tr>
					<th colspan="5"></th>
				</tr>
				<tr>
					<td colspan="3"><b><?php _e('Bonanza Configuration','ced-bonanza');?></b></td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
				</tr>
 
				<tr>
					<td colspan="3"><b><?php _e('Categories Mapping','ced-bonanza')?></b></td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Look For Categories Update','ced-bonanza')?></b></td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
				</tr>
				<tr>
					<td colspan="3"><b><?php _e('Profile Assignment','ced-bonanza')?></b></td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
				</tr>
				<tr>

					<td colspan="3"><b><?php _e('Product Upload','ced-bonanza')?></b></td>
					<td>
						<?php _e('5 products only', 'ced-bonanza')?>
					</td>
					<td>
						<?php _e('unlimited products Upload', 'ced-bonanza')?>
					</td>
				</tr>
				<tr>
					<td colspan="3"><b><?php _e('Simple Products Uplaod on Bonaza','ced-bonanza')?></b></td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Variable Products Uplaod on Bonaza','ced-bonanza')?></b></td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
				</tr>
			  
				<tr>
					<td colspan="3"><b><?php _e('Auto Price And Inventory Syn On Bonaza','ced-bonanza')?></b></td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Bulk Profile Assignment','ced-bonanza')?></b></td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
				</tr>		
				<tr>
					<td colspan="3"><b><?php _e('Bulk Products Upload By Specific WooCommerce Categories On Bonaza','ced-bonanza')?></b></td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
				</tr>
				<tr>
					<td colspan="3"><b><?php _e('Fetch Order Details From Bonaza','ced-bonanza')?></b></td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Order Status','ced-bonanza')?></b></td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
				</tr>
					<tr>
					<td colspan="3"><b><?php _e('Add extra shipping methods','ced-bonanza')?></b></td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
				</tr>
  
				<tr>
					<td colspan="3"><b><?php _e('Prerequisites','ced-bonanza')?></b></td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
					<td>
						<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
					</td>
				</tr>

			</table>
			<br/>
		</div>
		<a   href="https://cedcommerce.com/woocommerce-extensions/woocommerce-bonanza-integration"><h2 class="ced_bonanza_lister_setting_header"><?php _e('Purchase Bonaza-WooCommerce Integration Paid Version','ced-bonanza')?></h2></a>
	</div>
</div>