<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

//header file.
require_once CED_Bonanza_Lister_DIRPATH.'admin/pages/header.php';
if( isset( $_GET['oauth_token'] ) && isset( $_GET['oauth_verifier'] ) )
{
	$request_token = isset( $_GET['oauth_token'] ) ? sanitize_text_field( $_GET['oauth_token'] ) : '' ;
	$verifier = isset( $_GET['oauth_verifier'] ) ? sanitize_text_field( $_GET['oauth_verifier'] ): '' ;
	$saved_bonanza_details = get_option( 'ced_bonanza_lister_details',array() );
	$outh_secret_token = isset( $saved_bonanza_details['oauth_secret'] ) ? $saved_bonanza_details['oauth_secret'] : '';
	if( !isset( $saved_bonanza_details['access_token'] ) )
	{
		global $client_obj;
		try
		{
			$response = $client_obj->authorize( $request_token , $outh_secret_token );

			$response = $client_obj->getAccessToken( $verifier );
			if( !empty( $response ) )
			{

				$saved_bonanza_details['access_token'] = $response;
				if( isset( $response['oauth_token'] ) && isset( $response['oauth_token_secret'] ) )
					update_option( 'ced_bonanza_lister_access_token_fetched', true );
				
				update_option( 'ced_bonanza_lister_details', $saved_bonanza_details );
			}	
		}
		catch ( Exception $e )
		{
			?>
			<div class="ced_bonanza_lister_current_notice ced_bonanza_lister_validated_notice notice notice-error"><p><?php print_r($e); ?></p></div>
			<?php
		}
	}
}
 
if( true )
{
	$configSettings = apply_filters( 'ced_bonanza_lister_render_marketplace_configuration_settings', array(), 'bonanza' ); 
	$configSettingsData = $configSettings;
	$configSettings = $configSettingsData['configSettings'];
	$showUpdateButton = false;
	$marketPlaceName = 'bonanza';
	?>
	<div class="ced_bonanza_lister_wrap">
		<h2 class="ced_bonanza_lister_setting_header ced_bonanza_lister_bottom_margin"><?php _e('BONANZA CONFIGURATION','ced-bonanza')?> </h2>
		<div>
			<form method="post"
				<input type="hidden" name="ced_bonanza_lister_marketplace_configuration" value="1" >
				<table class="wp-list-table widefat fixed striped ced_bonanza_lister_config_table">
					<thead>
							
					</thead>
					<tbody>
					<?php
					if(is_array($configSettings)){
						foreach ($configSettings as $key => $value) {
							echo '<tr>';
								echo '<th class="manage-column">';
									echo $value['name'];
								echo '</th>';
								echo '<td class="manage-column">';
									if($value['type'] == 'text') {
										echo '<input id="'.$key.'" type="text" name="'.$key.'" value="'.$value['value'].'">';
									}
									do_action( 'ced_bonanza_lister_render_different_input_type' , $value['type']);
								echo '</td>';
							echo '</tr>';
						}
					}
					
					?>
					</tbody>
				</table>
			</form>
			
		</div>	
			<?php 
				$marketPlaceName = 'bonanza';
				do_action("ced_".$marketPlaceName."_additional_configuration", $marketPlaceName);
			?>
	<div>
	<?php
}