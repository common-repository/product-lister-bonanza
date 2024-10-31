/**
 * Product edit page js file
 */
var product_id = ced_bonanza_lister_edit_product_script_AJAX.product_id;
var ajaxurl = ced_bonanza_lister_edit_product_script_AJAX.ajax_url;
 
var currentRequest = null;
jQuery(document).ready(function(){

	renderMarketplaceAttributesSectionHTML( jQuery('#_bnz_bonanza_category'), jQuery('#_bnz_bonanza_category option:selected').val() , product_id , '' );
		
	jQuery(document.body).on( 'change', '#_bnz_bonanza_category', function() {
		jQuery('.ced_ump_circle_loderimg').show();
		var selectedCatId = jQuery('#_bnz_bonanza_category option:selected').val();
		renderMarketplaceAttributesSectionHTML( jQuery(this), selectedCatId , product_id , '' );
	});

	jQuery(document.body).on( 'click', 'div.woocommerce_variation h3', function() {
		var indexToUse = jQuery(this).find('input:hidden[name^=variable_post_id]').attr('name');
		indexToUse = indexToUse.split("]")[0].split("[")[1]; 
		var product_id = jQuery(this).find('input:hidden[name^=variable_post_id]').val();
		var categoryID = jQuery(this).next().find('select[name^=_bnz_bonanza_category]').val();
		var thisRef = jQuery(this).next().find('select[name^=_bnz_bonanza_category]');
		renderMarketplaceAttributesSectionHTML( thisRef, categoryID , product_id , indexToUse );
	});

	jQuery( document ).on( 'change' , '#bnz_product_template', function(){
		var template_id = jQuery(this).val();
		jQuery('.ced_bonanza_lister_template_fetch_loader').show();
		jQuery.ajax({
			url: ced_bonanza_lister_edit_product_script_AJAX.ajax_url, 
			type: "POST",  
			data: {
				action 		:'ced_bnz_get_product_template_html',
				template_id	: template_id,
			},
			success: function(response) 
			{
				jQuery('.ced-bonanza-template-preview').html(response);
				jQuery('.ced_bonanza_lister_template_fetch_loader').hide();
			}
		});
	} );
	
	function renderMarketplaceAttributesSectionHTML( thisRef, categoryID, productID, indexToUse ) {
		jQuery.ajax({
			url : ced_bonanza_lister_edit_product_script_AJAX.ajax_url,
			type : 'post',
			data : {
				action : 'ced_bonanza_lister_process_bonanza_cat',
				categoryID : categoryID,
				productID : productID,
				indexToUse : indexToUse
			},
			success : function( response ) 
			{
				jQuery('.ced_ump_circle_loderimg').hide();
				if( jQuery(thisRef).parent().next().hasClass('ced_bonanza_lister_attribute_section') ) {
					jQuery(thisRef).parent().next().remove();
				}
				if( response == 'Token unavailable' )
				{
					jQuery(thisRef).parent().html('<b>Please fetch the Api Token</b>');
				}else{ 
					jQuery(thisRef).parent().after(response);
				}
				jQuery( document.body ).trigger( 'init_tooltips' );
			}
		});
	}
	
});	