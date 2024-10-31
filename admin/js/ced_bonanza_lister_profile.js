jQuery(document).ready(function(){
	jQuery(document.body).on("click",".ced_bonanza_lister_profile",function(){
		var prodId = jQuery(this).attr("data-proid");
		jQuery(".ced_bonanza_lister_save_profile").attr("data-prodid",prodId);
		jQuery(".ced_bonanza_lister_overlay").show();
	});

	jQuery( document ).on( 'click', '.ced_bonanza_lister_end_auction', function(){
		var productId = jQuery( this ).attr( 'data-proid' );
		jQuery( '#ced_bonanza_lister_marketplace_loader' ).show();
		jQuery.ajax({
			url : profile_action_handler.ajax_url,
			data : {
				action : 'ced_bonanza_lister_end_auction',
				productId : productId
			},
			datatype : 'json',
			success: function(response)
			{
				response = jQuery.parseJSON( response );
				jQuery( '#ced_bonanza_lister_marketplace_loader' ).hide();
				var htm = '<div id="message" class="bnz_bonanza_end_listing '+response.classes+'"><p>'+response.message+'</p></div>';
				jQuery( htm ).insertAfter( '#ced_bonanza_lister_marketplace_loader' );
				setTimeout( function(){
					jQuery( document ).find( '.bnz_bonanza_end_listing' ).hide();
				}, 3000 );
			}
		})
	} );
 
	jQuery(document.body).on("click",".ced_bonanza_lister_overlay_cross",function(){
		jQuery(".ced_bonanza_lister_overlay").hide();
	})
	jQuery(document.body).on("click",".bnz_remove_profile",function(){

		var proId     = jQuery(this).attr("data-prodid");
		jQuery("#ced_bonanza_lister_marketplace_loader").show();
		var profileId = 0;
		var data  = {
						"action"    : "ced_bonanza_lister_save_profile",
						"proId"     : proId,
						"profileId" : profileId
					}
		jQuery.post(
					profile_action_handler.ajax_url,
					data,
					function(response)
					{
						jQuery("#ced_bonanza_lister_marketplace_loader").hide();

						jQuery(".ced_bonanza_lister_overlay").hide();
						if(jQuery.trim(response) != "success")
						{
							alert("Failed");
						}
						else
						{
							window.location.reload();
						}	
					}
				)
			  .fail(function() {
				  jQuery("#ced_bonanza_lister_marketplace_loader").hide();
				  alert( "Failed" );

			  })
	})
	
	jQuery(document.body).on("click",".ced_bonanza_lister_save_profile",function(){

		var proId     = jQuery(this).attr("data-prodid");
		jQuery("#ced_bonanza_lister_marketplace_loader").show();

		var profileId = jQuery(".ced_bonanza_lister_profile_select option:selected").val();
		var data  = {
						"action"    : "ced_bonanza_lister_save_profile",
						"proId"     : proId,
						"profileId" : profileId
					}
		jQuery.post(
					profile_action_handler.ajax_url,
					data,
					function(response) {
						jQuery("#ced_bonanza_lister_marketplace_loader").hide();

						jQuery(".ced_bonanza_lister_overlay").hide();
						if(jQuery.trim(response) != "success") {
							alert("Failed");
						}
						else {
							window.location.reload();
						}	
					}
				)
			  .fail(function() {
				  jQuery("#ced_bonanza_lister_marketplace_loader").hide();
				  alert( "Failed" );
			  });
	});
 
	/*
	* JS CODE TO ADD PRODUCT TO QUEUE TO UPLOAD
	*/
	jQuery(document.body).on( 'click', '.ced_bonanza_lister_marketplace_add_to_upload_queue_123', function(){
		jQuery("#ced_bonanza_lister_marketplace_loader").show();
		jQuery.ajax({
			url : profile_action_handler.ajax_url,
			type : 'post',
			data : {
				action : 'ced_bonanza_lister_add_product_to_upload_queue_on_marketplace',
				marketplaceId : jQuery(this).attr('data-marketplace'),
				productId : jQuery(this).attr('data-id')
			},
			success : function( response ) 
			{
				jQuery("#ced_bonanza_lister_marketplace_loader").hide();
				jQuery('.ced_bonanza_lister_pages_wrapper').children('form').append('<div class="ced_bonanza_lister_current_notice ced_bonanza_lister_location_saved_notice notice notice-success"><p>Successfully Added to Uploading Queue.</p></div>');
				setTimeout( function(){ jQuery( '.ced_bonanza_lister_location_saved_notice' ).remove(); }, 3000 );
			}
		});
	});
 
});