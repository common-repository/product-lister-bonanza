(function( $ ) {
	'use strict';
	jQuery(document).ready(function(){
		 
		// bonanza toggle fields.
		jQuery(document).on('click','#ced_bnz_accordian .ced_bnz_panel_heading',function(){
			var k = jQuery(this).next().slideToggle('slow');
			jQuery('.ced_bnz_collapse').not(k).slideUp('slow');
		});

		//bonanza  price management fields.
		jQuery("#_bnz_custom_price").on('change',function(){
			if(this.checked){
				jQuery(".bnz_price_fields").show();
			}else{
				jQuery(".bnz_price_fields").hide();
			}
		});
		// bonanza stock management fields.
		jQuery("#_bnz_custom_stock").on('change',function(){
			if(this.checked){
				jQuery(".bnz_stock_fields").show();
			}else{
				jQuery(".bnz_stock_fields").hide();

			}
		});

		/* Handle New bonanza function Addition **/
		jQuery('.upload-view-toggle').on('click',function(){
			jQuery('.ced-bnz-upload-addon').slideToggle();
		});
	});
})( jQuery );