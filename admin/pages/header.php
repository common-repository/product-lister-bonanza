<?php
	// If this file is called directly, abort.
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	
	global $cedbonanzalisterhelper;
	$current_page = 'bnz-bonanza';

	if($current_page=="bnz-bonanza"){
	 
		$authToken = get_option('bonanza_tocken_details',true);
		$authToken = isset( $authToken['authToken'] ) ? $authToken['authToken'] : '';
		if($authToken =='' || empty($authToken)){
			 
		$message = __('Configuration details of Bonanza either empty or not validated successfully, please validate the configuration otherwise some processes might not work properly.','ced_bonanza_lister');
		$classes = "notice notice-error";
		$validation_notice[] = array('message'=>$message, 'classes'=>$classes);

		global $cedbonanzalisterhelper;
		$cedbonanzalisterhelper->bnz_print_notices($validation_notice);
		unset($validation_notice);
	 
		}
	}

	if(isset($_GET['page'])){
		$current_page = sanitize_text_field( $_GET['page'] );
	}
	?>
	<div class="wrap woocommerce ced_bonanza_lister_pages_wrapper">
		<form novalidate="novalidate" action="" method="post" class="ced_bonanza_lister_header_tabs">
			<h2 class="nav-tab-wrapper woo-nav-tab-wrapper ced_bonanza_lister_nav_tab_wrapper">
				<a href	= "<?php get_admin_url()?>admin.php?page=bnz-bonanza-main" class="nav-tab <?php if( $current_page == 'bnz-bonanza-main' ) : ?> nav-tab-active<?php endif; ?>"><?php _e('Configuration','ced-bonanza');?></a>
				
				<a href	= "<?php get_admin_url() ?>admin.php?page=bnz-bonanza-cat-map" class="nav-tab <?php if($current_page == 'bnz-bonanza-cat-map' ) : ?>nav-tab-active<?php endif; ?> "><?php _e('Category Mapping','ced-bonanza');?></a>
				
				<a href	= "<?php get_admin_url() ?>admin.php?page=bnz-bonanza-profile" class="nav-tab <?php if($current_page == 'bnz-bonanza-profile' ) : ?>nav-tab-active<?php endif; ?> "><?php  _e('Profile','ced-bonanza');?></a>  
				
				<a href	= "<?php get_admin_url() ?>admin.php?page=bnz-bonanza-pro-mgmt" class="nav-tab <?php if($current_page == 'bnz-bonanza-pro-mgmt' ) : ?>nav-tab-active<?php endif; ?> "><?php _e('Manage Products','ced-bonanza');?></a>
				 
				 <a href	= "<?php get_admin_url() ?>admin.php?page=bnz-bonanza-prerequisites" class="nav-tab <?php if($current_page == 'bnz-bonanza-prerequisites' ) : ?>nav-tab-active<?php endif; ?> "><?php _e('Prerequisites','ced-bonanza');?></a> 
				 <a href	= "<?php get_admin_url() ?>admin.php?page=bnz-bonanza-features" class="nav-tab <?php if($current_page == 'bnz-bonanza-features' ) : ?>nav-tab-active<?php endif; ?> "><?php _e('Features','ced-bonanza');?></a> 
			</h2>
		</form>
	</div>
	<?php 
		$need_to_fetch = get_option( 'ced_bonanza_lister_fetch_categories_as_site_changed', '' );
		if( $need_to_fetch == true )
		{
			?>
			<div class="ced_bonanza_lister_need_to_fetch_categories notice notice-warning">
				<p><?php _e( 'The Store Location has changed. You need to Fetch the Categories again! ', 'ced-bonanza' ); ?><a href="javascript:void(0);"id="ced_bonanza_lister_fetch_cat"><?php _e('Click Here', 'ced-bonanza'); ?></a><?php _e( ' to fetch the categories', 'ced-bonanza' ); ?></p>
			</div>
			<?php
		}
	?>
	<div id="ced_bonanza_lister_marketplace_loader" class="loading-style-bg">
		<img src="<?php echo plugin_dir_url(__dir__);?>/images/BigCircleBall.gif">
	</div>

	<div class="ced_bonanza_lister_share_email_main_wrapper">
        <div class="ced_bonanza_lister_share_email_wrapper" >
            <img width="20px" height="20px" class="ced_bonanza_lister_share_email_popup_cancel" src="<?php  echo plugin_dir_url(__dir__);?>images/crosss.png">
            <div class="ced_bonanza_lister_share_email_heading">
                <h2><?php _e( 'Share your E-mail to get discount!', 'ced-bonanza' ); ?></h2>
            </div>
            <div class="ced_bonanza_lister_share_email_body">
                <input placeholder="<?php _e( 'Enter you E-mail Id', 'ced-bonanza' ); ?>" type="email" id="ced_bonanza_lister_offer_email_id" name="bonanza_lister_get_offer_email"></input>
                <button class="button button-ced_bonanza_lister ced_bonanza_lister_share_email_button"><?php _e( 'SHARE', 'ced-bonanza' ) ?></button>
            </div>
        </div>
    </div>