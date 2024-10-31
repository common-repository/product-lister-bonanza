/**
 * @version 1.0.0
 */
var cedbonanzaChildWindow;
var ajaxUrl = ced_bonanza_lister_auth.ajax_url;
jQuery(document).ready(function(){

	if( jQuery( '.ced_bonanza_lister_vacation_dates' ).length > 0 )
	{
		jQuery( '.ced_bonanza_lister_vacation_dates' ).datepicker();
	}

	jQuery( document.body ).trigger( 'init_tooltips' );
	jQuery('.ced_bonanza_lister_token').attr('disabled',true);
	jQuery('.ced_bonanza_lister_config_table').children('thead').empty();
	 
	jQuery(document.body).on('click', '.ced_bonanza_lister_save_credentials_button', function(){
		jQuery('#ced_bonanza_lister_marketplace_loader').show();
		
		var data = {'action':'ced_bonanza_lister_authorize',
					'_nonce':'do_save_credentials',
					'ced_bonanza_lister_dev_id' : jQuery( '#ced_bonanza_lister_dev_id' ).val(),
					'ced_bonanza_lister_cert_id' : jQuery( '#ced_bonanza_lister_cert_id' ).val(),
					};
		jQuery.post(ajaxurl, data,function(response){
			
			if(response != "" && response != null && response != false){
				var response = jQuery.parseJSON(response);

				if(response.status == "200")
				{
					jQuery('#ced_bonanza_lister_marketplace_loader').hide(); 
					jQuery('.ced_bonanza_lister_pages_wrapper').children('form').append('<div class="ced_bonanza_lister_current_notice ced_bonanza_lister_validated_notice notice notice-success"><p>Details Saved Successfully.</p></div>');
					setTimeout( function(){ jQuery( '.ced_bonanza_lister_validated_notice' ).remove(); }, 4000 );
					
				}
				else if(response.status == '401')
				{
					jQuery('#ced_bonanza_lister_marketplace_loader').hide();
					jQuery('.ced_bonanza_lister_pages_wrapper').children('form').append('<div class="ced_bonanza_lister_current_notice ced_bonanza_lister_validated_notice notice notice-error"><p>Please fill in the Credentails.</p></div>');
					setTimeout( function(){ jQuery( '.ced_bonanza_lister_validated_notice' ).remove(); }, 4000 );
				}
			}
			jQuery('#ced_bonanza_lister_marketplace_loader').hide();
		});
	})
	jQuery(document.body).on('click', '.ced_bonanza_lister_authorize', function(){
		jQuery('#ced_bonanza_lister_marketplace_loader').show();
		jQuery('.ced_bonanza_lister_token').attr('disabled',true);
		var data = {'action':'ced_bonanza_lister_authorize',
					'_nonce':'do_bonanza_authorize',
					'ced_bonanza_lister_keystring' : jQuery( '#ced_bonanza_lister_keystring' ).val(),
					'ced_bonanza_lister_shared_string' : jQuery( '#ced_bonanza_lister_shared_string' ).val(),
					};
		jQuery.post(ajaxurl, data,function(response){
			 
			jQuery('#ced_bonanza_lister_marketplace_loader').hide();
			if(response != "" && response != null && response !== false)
			{
				var response = jQuery.parseJSON(response);
				if(response.status == "200")
				{
					jQuery('#ced_bonanza_lister_marketplace_loader').hide(); 
					jQuery('.ced_bonanza_lister_pages_wrapper').children('form').append('<div class="ced_bonanza_lister_current_notice ced_bonanza_lister_validated_notice notice notice-success"><p>Credentails validated successfully.</p></div>');
					setTimeout( function(){ jQuery( '.ced_bonanza_lister_validated_notice' ).remove(); }, 4000 );
					jQuery('.ced_bonanza_lister_token').attr('disabled',true);
					window.location.href = response.login_url;
				}
				else if(response.status == '201')
				{
					jQuery('#ced_bonanza_lister_marketplace_loader').hide();
					jQuery('.ced_bonanza_lister_pages_wrapper').children('form').append('<div class="ced_bonanza_lister_current_notice notice notice-error"><p>'+response.response+'.</p></div>');
					setTimeout( function(){ jQuery( '.ced_bonanza_lister_current_notice' ).remove(); }, 4000 );
				}
				
			}else{
				jQuery('.ced_bonanza_lister_current_notice').hide();
				jQuery('.ced_bonanza_lister_pages_wrapper').children('form').append('<div class="ced_bonanza_lister_current_notice notice notice-error"><p>Action Failed</p></div>');
				setTimeout( function(){ jQuery( '.ced_bonanza_lister_current_notice' ).remove(); }, 4000 );
			}
			
		});
	})
});