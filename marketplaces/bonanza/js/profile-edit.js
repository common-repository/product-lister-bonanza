jQuery(document).ready(function()
{
	var url  = window.location.href; 
	if (url.indexOf("page=bnz-bonanza-profile&action=edit") >= 0)
	{
		renderMarketplaceAttributesSectionHTML( jQuery('select[name^=_bnz_bonanza_category]'), jQuery('select[name^=_bnz_bonanza_category]').val(), jQuery('input#profileID').val() );
	}
	var cat_name = jQuery('select[name^=_bnz_bonanza_category] option:selected').text();
	var l = '<td><input type="hidden" name = "bnz_bonanza_category_name" id="bnz_bonanza_category_name" value="'+cat_name+'"></td>';
	jQuery( 'select[name^=_bnz_bonanza_category]' ).parents('tr').append(l);
	
	 jQuery(document.body).on( 'change', 'select[name^=_bnz_bonanza_category]', function() {
	 	renderMarketplaceAttributesSectionHTML( jQuery(this), jQuery(this).val(), jQuery('input#profileID').val() );
	 	var cat_name = jQuery('select[name^=_bnz_bonanza_category] option:selected').text();
		
	 	if( jQuery(document).find('#bnz_bonanza_category_name').length > 0 ){
	 		jQuery(document).find('#bnz_bonanza_category_name').val( cat_name );
	 	} else{
	 		var l = '<td><input type="hidden" name = "bnz_bonanza_category_name" id="bnz_bonanza_category_name" value="'+cat_name+'"></td>';
	 		jQuery( this ).parents('tr').append(l);
	 	}
	 });

	function renderMarketplaceAttributesSectionHTML( thisRef, categoryID, profileID ) {
		jQuery("#ced_bonanza_lister_marketplace_loader").show();
		jQuery.ajax({
			url : ced_bonanza_lister_edit_profile_AJAX.ajax_url,
			type : 'post',
			data : {
				action : 'fetch_bonanza_attribute_for_selected_category_for_profile_section',
				categoryID : categoryID,
				profileID : profileID
			},
			success : function( response ) 
			{
				 
				var parentRef = jQuery(thisRef).parents( 'div.ced_bonanza_lister_toggle_section_wrapper' );
				if( response == 'Token unavailable' )
				{
					jQuery(parentRef).siblings('div.ced_bonanza_lister_attribute_section').find('div.ced_bonanza_lister_tabbed_section_wrapper').html('<b>Please Fetch the Bonanza Api Token</b>');
				}else{
					 
					jQuery(parentRef).siblings('div.ced_bonanza_lister_Bonanza_attribute_section').find('div.ced_bonanza_lister_tabbed_section_wrapper').html(response);
					//jQuery('.ced_bonanza_lister_tabbed_section_wrapper').html(response);
				}
				jQuery("#ced_bonanza_lister_marketplace_loader").hide();
				jQuery( document.body ).trigger( 'init_tooltips' );
			}
		});
	}
});