// Common toggle code
jQuery(document).ready(function(){

	setTimeout( function(){ jQuery('.ced_bonanza_lister_current_notice').remove(); }, 5000 );

	jQuery( '#ced_bonanza_lister_save_payment_details' ).on( 'click', function(e){
		e.preventDefault();
		var f = 0;
		jQuery( '.ced_bonanza_lister_payment_method input' ).each(function(){
			var val = jQuery(this).val();
			if( val == "PayPal" && jQuery(this).is(':checked') ){
				f = 1;
				var email_add = jQuery("input[name='paymentdetails[paypalEmail]']").val();
				if( email_add == '' || email_add == null )
				{
					var errorHtml = '<div class="notice notice-error bnz_paypal_error_email"><p>Please fill in the Paypal email address.</p></div>';
					jQuery('.ced_bonanza_lister_return_address').children('form').append(errorHtml);
					setTimeout( function(){ jQuery( '.bnz_paypal_error_email' ).remove() } , 3000 );
				}
				else
				{
					jQuery( '.ced_bonanza_lister_save_payment_data' ).submit();
				}
			}
		});
		if( f == 0 )
		{
			jQuery( '.ced_bonanza_lister_save_payment_data' ).submit();
		}
	} );

	jQuery(document).on('click','.ced_bonanza_lister_toggle',function(){
		jQuery(this).next('.ced_bonanza_lister_toggle_div').slideToggle('slow');
	});
	if( jQuery( '.products_from_bonanza' ).length > 0 )
	{
		jQuery( '.products_from_bonanza' ).DataTable();
	}
});

//Bonanza Paid NOtice 
jQuery( document  ).on( 'click','.ced_bonanza_lister_get_offer_banner', function(e){
	
    e.preventDefault();
    jQuery( '.ced_bonanza_lister_share_email_wrapper' ).show();
} );

jQuery( document  ).on( 'click', '.ced_bonanza_lister_share_email_popup_cancel', function(){
    jQuery( '.ced_bonanza_lister_share_email_wrapper' ).hide();
} );

jQuery( document  ).on( 'click', '.ced_bonanza_lister_share_email_button', function(){
    var email_id = jQuery( '#ced_bonanza_lister_offer_email_id' ).val();
    jQuery.post(
        common_action_handler.ajax_url,
        {
            'action': 'ced_bonanza_lister_share_email',
            'email_id' : email_id,
        },
        function(response){

            jQuery( '.ced_bonanza_lister_share_email_wrapper' ).hide();
        }
    );
} );
// Market Place JQuery End
//jquery for file status.
jQuery(document).ready(function(){
	jQuery(document).on('click','.ced_bonanza_lister_updateFileInfo',function(){
		var requestId = jQuery(this).attr('requestid');
		var marketplace = jQuery(this).attr('framework');
		var fileId = jQuery(this).attr('fileId');
		if(!requestId.length || !marketplace.length || !fileId.length){
			alert("An unexpected error occured, please try again later.");
			return;
		}
		
		jQuery.post(
				common_action_handler.ajax_url,
				{
					'action': 'bnz_get_file_status',
					'requestId' : requestId,
					'fileId' : fileId,
					'marketplace' : marketplace
				},
				function(response){
					alert(response);
				}
		);
	});
 
	jQuery(document).on('change','.ced_bonanza_lister_select_cat_profile',function(){
		jQuery(".bnz_current_cat_prof").remove();
		var currentThis = jQuery(this);
		var catId  = jQuery(this).parent('td').attr('data-catId');
		var profId = jQuery(this).find(':selected').val();

		if(catId == null || typeof catId === "undefined" || catId == null || profId == "" || typeof profId === "undefined" || profId == null || profId == "--Select Profile--")
		{
			return;
		}
		jQuery('#ced_bonanza_lister_marketplace_loader').show();
		jQuery.post(
				common_action_handler.ajax_url,
				{
					'action': 'ced_bonanza_lister_select_cat_prof',
					'catId' : catId,
					'profId' : profId,
				},
				function(response)
				{
					jQuery('#ced_bonanza_lister_marketplace_loader').hide();
					response = jQuery.parseJSON(response);
					if(response.status == "success")
					{
						currentThis.parent('td').next('td').text(response.profile);
						var successHtml = '<div class="notice notice-success bnz_current_cat_prof ced_bonanza_lister_current_notice"><p>Profile Assigned Successfully.</p></div>';
						jQuery('.ced_bonanza_lister_pages_wrapper').children('form').append(successHtml);
						setTimeout( function(){ jQuery( '.ced_bonanza_lister_current_notice' ).remove(); }, 4000 );
					}
					else{
						var errorHtml = '<div class="notice ced_bonanza_lister_current_notice notice-error bnz_current_cat_prof"><p>Unable to assign profile. Please Try again later!</p></div>';
						jQuery('.ced_bonanza_lister_pages_wrapper').children('form').append(errorHtml);
						setTimeout( function(){ jQuery( '.ced_bonanza_lister_current_notice' ).remove(); }, 4000 );
					}
				}
		);
	});	
	 
	jQuery("#bnz_bulk_act_category").change(function(){
		var catid = jQuery(this).val();
		jQuery.post(
				common_action_handler.ajax_url,
				{
					'action': 'ced_bonanza_lister_select_cat_bulk_upload',
					'catId' : catid,
				},
				function(response)
				{
					if(response.result == 'success')
					{
						var product = response.data;
						var preselect = jQuery("#bnz_bulk_act_product").val();
						var option = '';
						for(key in product)
						{
							select = '';
							if(preselect)
							{	
								if(preselect.indexOf(key) != -1)
								{
									select='selected="selected"';
								}	
							}
							option += '<option value="'+key+'" '+select+'>'+product[key]+'</option>';
						}	
						jQuery("#bnz_bulk_act_product").html(option);
						jQuery("#bnz_bulk_act_product").select2();

						jQuery("#bnz_bulk_act_product_select").html(option);
						jQuery("#bnz_bulk_act_product_select").select2();
					}	
				},
				'json'
			);	
	});
 
});
//js for popup while deactivating plugin for asking reason to deactivate it

jQuery( document ).on( 'click','.deactivate',function(e){
 
		e.preventDefault();
		var href = jQuery(this).find('a').attr( 'href' );
		
		if( href.indexOf( 'product-lister-bonanza' ) >= 0 )
		{
			jQuery( '.ced_bonanza_lister_deactivation_url' ).val( href );
			jQuery( '.ced_bonanza_lister_deactivate_reason_popup_wrapper' ).show();
			jQuery( '.ced_bonanza_lister_deactivate_reason_main_wrapper' ).addClass( 'ced_bonanza_lister_wrapper' );
			jQuery( 'body' ).addClass( 'ced_bonanza_lister_popup_class' );
		}
		else
		{
			jQuery( '.ced_bonanza_lister_deactivate_reason_main_wrapper' ).removeClass( 'ced_bonanza_lister_wrapper' );
			jQuery( 'body' ).removeClass( 'ced_bonanza_lister_popup_class' );
			window.location.href = href;
		}
	} );

	jQuery( document ).on( 'click', '.ced_bonanza_lister_submit_reason', function(){
		var redirect_url = jQuery( '.ced_bonanza_lister_deactivation_url' ).val();
		if( jQuery( '#ced_bonanza_lister_looking_for' ).is( ':checked' ) ){
			var looking_for = 'yes';
		}
		if( jQuery( '#ced_bonanza_lister_was_complex' ).is( ':checked' ) ){
			var want_more = 'yes';
		}
		if( jQuery( '#ced_bonanza_lister_was_bug' ).is( ':checked' ) ){
			var was_bug = 'yes';
		}
		var reason = jQuery('#ced_bonanza_lister_reasons').text();
		var email_id = jQuery('#ced_bonanza_lister_eamil_id').text();
		jQuery.post(
			common_action_handler.ajax_url,
			{
				'action': 'ced_bonanza_lister_submit_reason',
				'looking_for' : looking_for,
				'want_more' : want_more,
				'was_bug' : was_bug,
			},
			function(response){
				jQuery( '.ced_bonanza_lister_deactivate_reason_popup_wrapper' ).hide();
				window.location.href = redirect_url;
			}
		);
	} );

	jQuery( document ).on( 'click', '.ced_bonanza_lister_skip_reason', function(){
		var redirect_url = jQuery( '.ced_bonanza_lister_deactivation_url' ).val();
		jQuery( '.ced_bonanza_lister_deactivate_reason_popup_wrapper' ).hide();
		window.location.href = redirect_url;

	} );

//product edit link
jQuery(function( $ ) {
	
	$( '#the-list' ).on( 'click', '.editinline', function() {
		
		inlineEditPost.revert();
		
		var post_id = $( this ).closest( 'tr' ).attr( 'id' );
		post_id = post_id.replace( 'post-', '' );
		var $bnz_inline_data = $( '#ced_bnz_inline_' + post_id );
		$('div', $bnz_inline_data ).each(function(index,data){
			var key = jQuery(data).attr('class');
			var value = jQuery(data).text();
			var type = jQuery(data).attr('type');
			
			if(type=='_select'){
				$( 'select[name="'+key+'"] option:selected', '.inline-edit-row' ).attr( 'selected', false ).change();
				$( 'select[name="'+key+'"] option[value="' + value + '"]' ).attr( 'selected', 'selected' ).change();
			}else{
				$( 'input[name="'+key+'"]', '.inline-edit-row' ).val( value );
			}
		});
	} );
	
	jQuery(document).ready(function(){
		 jQuery(document.body).on( 'click', 'input:checkbox[id^=cb-select-all-]', function() {
		  if(jQuery(this).is(':checked')) {
		   jQuery( 'input:checkbox[name^=post]' ).each(function() {
		    jQuery(this).attr('checked','checked');
		   });
		  }
		  else {
		   jQuery( 'input:checkbox[name^=post]' ).each(function() {
		    jQuery(this).removeAttr('checked');
		   });
		  }
		});
	});
});