/**
 * js function for categories mapping
 */
jQuery(document).ready(function(){
	jQuery(document.body).on('click', '#ced_bonanza_lister_fetch_cat', function(){
		var url = window.location.href;
		jQuery('#ced_bonanza_lister_marketplace_loader').show();
		var data = {'action':'ced_bonanza_lister_fetchCat',
					'_nonce':'ced_bonanza_lister_fetch'
					};
		jQuery.post(ajaxurl, data,function(response){
			jQuery('#ced_bonanza_lister_marketplace_loader').hide();
			jQuery('<div class="ced_bonanza_lister_current_notice notice notice-success"><p>Category Fetched successfully.</p></div>').insertAfter('.ced_bonanza_lister_header_tabs');
			window.location.href = url;
		})
	})
	jQuery(document.body).on('click', '.ced_bonanza_lister_expand_bonanzacat', function(){
		jQuery(this).find('.ced_bonanza_lister_category_loader').show();
		var catID = jQuery(this).attr('data-catid');
		var catLevel = jQuery(this).attr('data-catLevel');
		var catName = jQuery(this).attr('data-catName');
		var parentCatName = jQuery(this).attr('data-parentCatName');
		var data = {'action':'ced_bonanza_lister_fetchCat',
					'_nonce':'ced_bonanza_lister_fetch_next_level',
					'catDetails': {'catID':catID,
								   'catLevel':catLevel,
								   'catName' : catName,
								   'parentCatName' : parentCatName
								  }
				  };
		var midVal = parseInt(catLevel)+parseInt(1);
		for(i=1;i<=5;i++){
			if(midVal < i){
				jQuery('.ced_bonanza_lister_'+i+'lvl').empty();
			}
		}
		jQuery('.ced_bonanza_lister_'+catLevel+'lvl').children('li').css('background-color','#989898');
		jQuery('.ced_bonanza_lister_'+catLevel+'lvl').children('li').children('label').css('color','#ffffff');
		jQuery(this).parent().css('background-color','#ff9800');
		jQuery(this).css('color','#ffffff');
		jQuery.post(ajaxurl, data,function(response){
			jQuery('.ced_bonanza_lister_category_loader').hide();
			var response = jQuery.parseJSON(response);
			if(response.status == '200')
			{
				var savedCat = response.selectedCat;
				var nextLevelCat = response.nextLevelCat;
				var nextList = "<h1>Level "+midVal+" Categories</h1>";
				jQuery.each( nextLevelCat, function( key, value )
				{

					if( value.leafCategory != "true" )
					{
						var checkbox = "";
						var span = '<label class="ced_bonanza_lister_expand_bonanzacat " data-parentCatName="'+catName+'" data-catName="'+value.categoryName+'" data-catId="'+value.categoryId+'" data-catLevel = "'+midVal+'"> '+value.categoryBriefName+'>> <img class="ced_bonanza_lister_category_loader" src="'+ced_bonanza_lister_cat.plugins_url+'admin/images/loading.gif" width="20px" height="20px"> </label>'
					}	
					else
					{
						var checked = "";
						if(savedCat !=  null && savedCat != ""){
							if(typeof savedCat[value.categoryId] != 'undefined'){
								checked = "checked";
							}
						}
						checkbox = '<input type="checkbox" class="ced_bonanza_lister_cat_select" id="'+value.categoryId+'" name="'+value.categoryId+'" data-catName="'+value.categoryName+'" value="'+value.categoryId+'" '+checked+'  >';
						span = '<label for = "'+value.categoryId+'" class="ced_bonanza_lister_lab">'+value.categoryBriefName+'</label>';
					}
					nextList += '<li>'+checkbox+span+'</li>';	
				} );	

				jQuery('.ced_bonanza_lister_'+midVal+'lvl').html(nextList);
			}
		})
	})
	jQuery(document.body).on('click', '.ced_bonanza_lister_cat_select', function(){
		jQuery('#ced_bonanza_lister_marketplace_loader').show();
		if(jQuery(this).is(':checked'))
		{
			var selectedCatId = jQuery(this).val();
			var selectedCatName = jQuery(this).attr('data-catName');
			var data = {'action':'ced_bonanza_lister_process_bonanza_cat',
						'_nonce':'ced_bonanza_lister_save',
						'cat' : {'catID':selectedCatId,
							 	 'catName':selectedCatName
								}
						}
		}else{
			var selectedCatId = jQuery(this).val();
			var selectedCatName = jQuery(this).attr('data-catName');
			var data = {'action':'ced_bonanza_lister_process_bonanza_cat',
						'_nonce':'ced_bonanza_lister_remove',
						'cat' : {'catID':selectedCatId,
								 'catName':selectedCatName
								}
						}
		}
		jQuery.post(ajaxurl, data,function(response){
			jQuery(".ced_bonanza_lister_current_notice").hide();
			var response = jQuery.parseJSON(response);
			if(response.status == '200'){
				jQuery('<div class="ced_bonanza_lister_current_notice notice notice-success"><p>Category saved successfully.</p></div>').insertAfter('.ced_bonanza_lister_header_tabs');
			}else{
				jQuery('<div class="ced_bonanza_lister_current_notice notice notice-error"><p>Category can not be save. Please try again.</p></div>').insertAfter('.ced_bonanza_lister_header_tabs');
			}
			jQuery('#ced_bonanza_lister_marketplace_loader').hide();
		});

	});
		
});