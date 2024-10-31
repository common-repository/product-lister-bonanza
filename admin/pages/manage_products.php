<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$marketPlaces = array('bonanza');
$marketPlace = is_array($marketPlaces) && !empty($marketPlaces) ? $marketPlaces[0] : -1;
$marketplace = isset($_REQUEST['section']) ? sanitize_text_field ($_REQUEST['section']) : $marketPlace;

//product listing class.
require_once CED_Bonanza_Lister_DIRPATH.'admin/helper/class-ced-bnz-product-listing.php';
//feed manager helper class for handling bulk actions.
require_once CED_Bonanza_Lister_DIRPATH.'admin/helper/class-feed-manager.php';
//header file.
require_once CED_Bonanza_Lister_DIRPATH.'admin/pages/header.php';

$notices = array();

if(isset($_POST['doaction'])){

	check_admin_referer('bulk-ced_bonanza_lister_mps');
	
	$action = isset($_POST['action']) ? sanitize_text_field ($_POST['action']) : -1;

	$marketPlaces = bonanzaget_enabled_marketplaces();
	 
	$marketplace = "bonanza";
	$proIds = isset($_POST['post']) ? $_POST['post'] : array();
	$allset = true;
	
	if(empty($action) || $action== -1){
		$allset = false;
		$message = __('Please select the bulk actions to perform action!','ced-bonanza');
		$classes = "error is-dismissable";
		$notices[] = array('message'=>$message, 'classes'=>$classes);
	}
	//echo $marketplace;die;
	if(empty($marketplace) || $marketplace== -1){
		$allset = false;
		$message = __('Any marketplace is not activated!','ced-bonanza');
		$classes = "error is-dismissable";
		$notices[] = array('message'=>$message, 'classes'=>$classes);
	}
	
	if(!is_array($proIds)){
		$allset = false;
		$message = __('Please select products to perform bulk action!','ced-bonanza');
		$classes = "error is-dismissable";
		$notices[] = array('message'=>$message, 'classes'=>$classes);
	}
	if($allset){
		
		if( class_exists( 'CED_Bonanza_Lister_feed_manager' ) ){

			$feed_manager = CED_Bonanza_Lister_feed_manager::get_instance();
			$notice = $feed_manager->process_feed_request($action,$marketplace,$proIds);
			$notice_array = json_decode($notice,true);
			if(is_array($notice_array)){
				
				$message = isset($notice_array['message']) ? $notice_array['message'] : '' ;
				$classes = isset($notice_array['classes']) ? $notice_array['classes'] : 'error is-dismissable';
				$notices[] = array('message'=>$message, 'classes'=>$classes);
			}else{
				
				$message = __('Unexpected error encountered, please try again!','ced-bonanza');
				$classes = "error is-dismissable";
				$notices[] = array('message'=>$message, 'classes'=>$classes);
			}
		}
	}
}

if(count($notices))
{
	foreach($notices as $notice_array)
	{
		$message = isset($notice_array['message']) ? esc_html($notice_array['message']) : '';
		$classes = isset($notice_array['classes']) ? esc_attr($notice_array['classes']) : 'error is-dismissable';
		if(!empty($message))
		{?>
			 <div class="<?php echo $classes;?>">
			 	<?php echo $message;?>
			 </div>
		<?php 	
		}
	}
	unset($notices);
}

$availableMarketPlaces =array("bonanza");
if(is_array($availableMarketPlaces) && !empty($availableMarketPlaces)) {
	$section = $availableMarketPlaces[0];
	if(isset($_GET['section'])) {
		$section = esc_attr($_GET['section']);
	}
	$product_lister = new CED_Bonanza_Lister_product_lister();
	$product_lister->prepare_items();
	?>
	<div class="ced_bonanza_lister_wrap">
		<?php do_action("ced_bonanza_lister_manage_product_before_start");?>
		
		<h2 class="ced_bonanza_lister_setting_header"><?php _e('Manage Products','ced-bonanza'); ?></h2>
		
		<?php do_action("ced_bonanza_lister_manage_product_after_start");?>
		
		<form method="get" action="">
			<input type="hidden" name="page" value="<?php echo sanitize_text_field ($_REQUEST['page']) ?>" />
			<?php $product_lister->search_box(__('Search Products','ced-bonanza'), 'search_id');?>
		</form>
		<?php bonanzarenderMarketPlacesLinksOnTop('bnz-bonanza-pro-mgmt'); ?>

		<form method="get" action="">
			<input type="hidden" name="page" value="<?php echo sanitize_text_field ($_REQUEST['page']) ?>" />
			<?php
			/** Sorting By Status  **/
			$status_actions = array(
				'Uploaded'    => __( 'Uploaded', 'ced-bonanza' ),
				'notUploaded'    => __( 'Not Uploaded', 'ced-bonanza' ),
			);
			$previous_selected_status = isset($_GET['status_sorting']) ? sanitize_text_field ( $_GET['status_sorting'] ): '';
		 	
			
			$product_categories = get_terms( 'product_cat', array('hide_empty'=>false) );
		 	$temp_array = array();
		 	foreach ($product_categories as $key => $value) {
		 		$temp_array[$value->term_id] = $value->name;
		 	}
		 	$product_categories = $temp_array;
		 	$previous_selected_cat = isset($_GET['pro_cat_sorting']) ? sanitize_text_field( $_GET['pro_cat_sorting'] ): '';
		 	

		 	$product_types = get_terms( 'product_type', array('hide_empty'=>false) );
		 	$temp_array = array();
		 	foreach ($product_types as $key => $value) {
		 		if( $value->name == 'simple' || $value->name == 'variable' ) {
		 			$temp_array[$value->term_id] = ucfirst($value->name);
		 		}
		 	}
		 	$product_types = $temp_array;
		 	$previous_selected_type = isset($_GET['pro_type_sorting']) ? sanitize_text_field( $_GET['pro_type_sorting'] ): '';
		 	

			echo '<div class="ced_bonanza_lister_top_wrapper">';
				echo '<select name="status_sorting">';
				echo '<option value="">' . __( 'Product Status', 'ced-bonanza' ) . "</option>";
				foreach ( $status_actions as $name => $title ) {
					$selectedStatus = ($previous_selected_status == $name) ? 'selected="selected"' : '';
					$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';
					echo '<option '.$selectedStatus.' value="' . $name . '"' . $class . '>' . $title . "</option>";
				}
				echo "</select>";

				echo '<select name="pro_cat_sorting">';
				echo '<option value="">' . __( 'Product Category', 'ced-bonanza' ) . "</option>";
				foreach ( $product_categories as $name => $title ) {
					$selectedCat = ($previous_selected_cat == $name) ? 'selected="selected"' : '';
					$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';
					echo '<option '.$selectedCat.' value="' . $name . '"' . $class . '>' . $title . "</option>";
				}
				echo "</select>";

				echo '<select name="pro_type_sorting">';
				echo '<option value="">' . __( 'Product Type', 'ced-bonanza' ) . "</option>";
				foreach ( $product_types as $name => $title ) {
					$selectedType = ($previous_selected_type == $name) ? 'selected="selected"' : '';
					$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';
					echo '<option '.$selectedType.' value="' . $name . '"' . $class . '>' . $title . "</option>";
				}
				echo "</select>";

				submit_button( __( 'Filter', 'ced-bonanza' ), 'action', '', false, array() );
			echo '</div>';
			?>
		</form>

		<form id="ced_bonanza_lister_products" method="post">
		<?php $product_lister->views(); ?> 	
		<?php ?>	
		
		<?php $product_lister->display() ?>
		</form>
		<?php if($product_lister->has_items()):?>
			<?php  $product_lister->inline_edit(); ?>
			<?php  $product_lister->profle_section(); ?>
		<?php endif;?>
	</div>
	<?php
}
?>