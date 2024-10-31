<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
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
		<ul class="subsubsub">
			<li><a class="<?php echo $pre_active;?>" href="<?php echo admin_url();?>/admin.php?page=bnz-bonanza-prerequisites&amp;section=prerequesites"><?php _e( 'PREREQUISITES', 'ced-bonanza' ); ?></a>|</li>
			<li><a class="<?php echo $step_active;?>" href="<?php echo admin_url();?>/admin.php?page=bnz-bonanza-prerequisites&amp;section=steptofollow"><?php _e( 'TIMELINE', 'ced-bonanza' ); ?></a></li>
		</ul>
	
		<?php 
		if($section == 'prerequesites')
		{	
		?>
	 
		<div class = "ced_bonanza_lister_prerequisite_table_wrap wrap">
		 
			<table class="wp-list-table widefat fixed striped">
				<tr>
					<th colspan="4"><b><?php _e('Parameters','ced-bonanza')?></b></th>
					<th><b><?php _e('Status','ced-bonanza')?></b></th>
				</tr>
				<?php
				if (version_compare(PHP_VERSION, '5.5.5') == -1) {
					$php = "not_compatible";
				}
				else {
					$php = "compatible";
				}
				if(!extension_loaded('curl')) {
					$curl = "not_compatible";
				}
				else {
					$curl = "compatible";
				}
				$marketPlaces = ced_bonanza_lister_available_marketplace();
				$credentials = "Valid";
				$preRequisites = array(__("curl",'ced-bonanza')=>$curl, __("php version 5.5",'ced-bonanza')=>$php , __( "credentials",'ced-bonanza')=>$credentials,); 
				foreach ($preRequisites as $key=>$preRequisite) {
					?>
					<tr>
						<?php
						if($key == "credentials")
						{
						?>
							<td colspan="4"><?php _e(strtoupper($key),'ced_bonanza_lister')?></td>
							<td>
								<?php 
								foreach ($marketPlaces as $marketPlace)
								{ 
									$bonanzaDetails = get_option('bonanza_tocken_details');
									$validation = isset( $bonanzaDetails['authToken']) ?  $bonanzaDetails['authToken'] : '';
				 					 
									if($validation == "" || $validation == null || $validation == ""){
										 $validation ="Invalid";
									}else{
										$validation ="Valid";
									}
									  
								?>
									<p><?php echo strtoupper($marketPlace)?>:
											<?php
										 	if($validation == "Valid"){
											?>
											<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
											<?php }
											else{
											 
												$toUseUrl = get_admin_url();
												$toUseUrl = $toUseUrl."admin.php?page=bnz-bonanza";?>
											<a href="<?php echo $toUseUrl;?>">
												<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/cross.png">
											</a>

											<?php }?>
									</p>
								<?php 
								}?>
									 
							</td>
							<?php 
						}
						
						else{?>
							<td colspan="4"><?php echo strtoupper($key)?></td>
							<td><?php
							 	if($preRequisite == "compatible"){
								?>
								<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/check.png">
								<?php }
								else{?>
								<img src = "<?php echo CED_Bonanza_Lister_URL; ?>/admin/images/cross.png">
								<?php }?>
							 </td>
							<?php 
						}?>
					</tr>
					<?php 
					}
				?>
			</table>
 
	<br/>
	<h2 class="ced_bonanza_lister_setting_header ced_bonanza_lister_bottom_margin"><?php _e('Guidelines','ced-bonanza');?></h2>
			<table class="wp-list-table widefat fixed striped">
		
			<tr>
				<th colspan="4"><b><?php _e('Parameters','ced-bonanza')?></b></th>
				<th><b><?php _e('Status','ced-bonanza')?></b></th>
			</tr>
			<?php
			$required = array(	__('Cron','ced-bonanza') => __('Cron should be working properly on server.','ced-bonanza'),
								__('Product Identifier','ced-bonanza') => __('A valid product identifier with valid product identifier code.','ced-bonanza'),
								 
								__('Product Description','ced-bonanza') => __('Product description should be availble for the products to be uploaded.','ced-bonanza')
							);
			foreach ($required as $k=>$v){?>
			<tr>
				<td colspan = '4'><?php echo strtoupper($k);?></td>
				<td><?php echo $v;?></td>
			</tr>
			<?php }
			?>
		</table>
		<br/>
		</div>
	
		<?php 
		}
		if($section == 'steptofollow')
		{
			$marketPlaces = ced_bonanza_lister_available_marketplace();
			if(isset($marketPlaces) && !empty($marketPlaces))
			{
				?>
				<div class="ced_bonanza_lister_steptofollow">
			<h2 class="ced_bonanza_lister_setting_header"><?php _e('Your Timeline','ced-bonanza')?></h2>
		
				<table class="wp-list-table widefat fixed striped ced_bonanza_lister_steptofollow">
				<tr>
					<th>
						<b><?php _e('STEPS','ced-bonanza')?></b>
					</th>
				<?php 
				foreach($marketPlaces as $marketPlace)
				{
					?>
					<td>
						<b><?php _e( 'Steps Completed', 'ced-bonanza' );?></b>
					</td>
					<?php 
				}	
				?>
					<th>
						<b><?php _e( 'Availability', 'ced-bonanza' ); ?></b>
					</th>
				</tr>
				<tr>
					<th>
						<b><?php _e('Configuration Save','ced-bonanza')?></b>
					</th>
				 
					<td>
						<?php echo strtoupper(get_option("ced_bonanza_lister_save_".$marketPlace,__("YES",'ced-bonanza')));?>
					</td>
					 
					<td>
						<img src="<?php echo CED_Bonanza_Lister_URL.'admin/images/check.png' ?>">
					</td>
				</tr>
				<?php
					$authToken = get_option('bonanza_tocken_details',true);
					$authToken = isset( $authToken['authToken'] ) ? $authToken['authToken'] : '';
				?>
				<tr>
					<th>
						<b><?php _e('Configuration Validation','ced-bonanza')?></b>
					</th>
				 
					<td>

						<?php 
							if($authToken =='' || empty($authToken)){
				 
								_e('NO','ced-bonanza');
							}else{
								_e('YES','ced-bonanza');
							}
						?>
					</td>
					 
					<td>
						<img src="<?php echo CED_Bonanza_Lister_URL.'admin/images/check.png' ?>">
					</td>
				</tr>
				<tr>
					<th>
						<b><?php _e('Category Mapping','ced-bonanza')?></b>
					</th>
			 
					<td>
						<?php $catmap = get_option('ced_bonanza_lister_selected_categories',false);
						if(isset($catmap) && !empty($catmap))
						{
							_e( 'YES' , 'ced-bonanza' );
						}	
						else
						{
							_e( 'NO' , 'ced-bonanza' );
						}	
						?>
					</td>
				 
					<td>
						<img src="<?php echo CED_Bonanza_Lister_URL.'admin/images/check.png' ?>">
					</td>
				</tr>
				<tr>
					<th>
						<b><?php _e('Product Upload','ced-bonanza')?></b>
					</th>
				 
					<td>
						<?php
						$upload= get_option("ced_bonanza_lister_uploadfeed", false);
						$bonanza_calls_count=get_option('bonanza_calls_count',true);
						if($upload == 'products_uploaded')
						{
							_e( $bonanza_calls_count , 'ced-bonanza' );
						}elseif($bonanza_calls_count>=5){
							_e( 'limit exceeded !' , 'ced-bonanza' );
						}
						else
						{
							_e( $bonanza_calls_count , 'ced-bonanza' );
						}	
						?>
					</td>
					<td>
						<img src="<?php echo CED_Bonanza_Lister_URL.'admin/images/check.png' ?>">
					</td>
				</tr>
				</table>
				</div>
				<?php
			}
		}
		?>
	</div>
</div>