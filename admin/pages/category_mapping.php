<?php
	// If this file is called directly, abort.
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	 
	//header file.
	require_once CED_Bonanza_Lister_DIRPATH.'admin/pages/header.php';

	$activated_marketplaces	 = bonanzaget_enabled_marketplaces();

	?>
	<div id="ced_bonanza_lister_marketplace_loader" class="loading-style-bg">
		<img src="<?php echo plugin_dir_url(__dir__);?>/images/BigCircleBall.gif">
	</div>
	<?php 
	$authToken = get_option('bonanza_tocken_details',true);
	$authToken = isset( $authToken['authToken'] ) ? $authToken['authToken'] : '';
	if($authToken !='' && !empty($authToken) ){
		$count = 1;
		echo '<div class="ced_bonanza_lister_wrap">';
		foreach($activated_marketplaces as $marketplace){
			
			$file_path = CED_Bonanza_Lister_DIRPATH.'marketplaces/'.$marketplace.'/partials/ced-bonanza-cat-mapping.php';
			if(file_exists($file_path)){
				require_once $file_path;
			}
			$count++;
		}
		echo '</div>';
	}else{
		echo '<h3> '.__('First of all, You need to configure Bonanza','ced-bonanza').'</h3>';
	} 
?>