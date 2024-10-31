<!-- 
	Html for categories listing 
 -->
<div class="ced_bonanza_lister_cat_mapping ced_bonanza_lister_toggle_wrapper">
	<div class="ced_bonanza_lister_toggle_section">
		<div class="ced_bonanza_lister_toggle">
			<h2><?php _e('bonanza Category','ced-bonanza');?></h2>
		</div>
		<div class="ced_bonanza_lister_cat_activate_ul ced_bonanza_lister_toggle_div">
			 
		<?php 
		$selected_categories = get_option( 'ced_bonanza_lister_selected_categories', array() );
		 
		$folderName = CED_Bonanza_Lister_DIRPATH.'marketplaces/bonanza/lib/json/';
		$catFirstLevelFile = $folderName.'categories.json';
		if(file_exists($catFirstLevelFile)){
			$catFirstLevel = file_get_contents($catFirstLevelFile);
			$catFirstLevel = json_decode($catFirstLevel,true);
			$catFirstLevel = isset( $catFirstLevel['categories'] ) ? $catFirstLevel['categories'] : array();
			if( is_array( $catFirstLevel ) && !empty( $catFirstLevel ) )
			{
				$breakPoint = floor(count($catFirstLevel)/3);
				$counter = 0;
				echo '<ul class="ced_bonanza_lister_cat_ul ced_bonanza_lister_1lvl">';
				echo '<h1>'.__('Root Categories','ced-bonanza').'</h1>';
				if(is_array($catFirstLevel))
				{
					 
					foreach ($catFirstLevel as $key => $category) 
					{
						$catName = $category['categoryName'];
						
						$checkbox = "";
						$span = '<label class="ced_bonanza_lister_expand_bonanzacat" data-catName="'.$category['categoryName'].'" data-caturl="'.$category['url'].'" data-catId="'.$category['categoryId'].'" data-catLevel = "1"> '.$catName.' >><img class="ced_bonanza_lister_category_loader" src="'.CED_Bonanza_Lister_URL.'admin/images/loading.gif" width="20px" height="20px">  </label>';
					
						echo '<li>'.$checkbox.$span.'</li>';
					}
					echo '</ul>';
					echo '<ul class="ced_bonanza_lister_cat_ul ced_bonanza_lister_2lvl"></ul>';
					echo '<ul class="ced_bonanza_lister_cat_ul ced_bonanza_lister_3lvl"></ul>';
					echo '<ul class="ced_bonanza_lister_cat_ul ced_bonanza_lister_4lvl"></ul>';
					echo '<ul class="ced_bonanza_lister_cat_ul ced_bonanza_lister_5lvl"></ul>';
					echo '<ul class="ced_bonanza_lister_cat_ul ced_bonanza_lister_6lvl"></ul>';
				}	
			}
			else
			{
				?>
				<div>
					<span><?php _e( 'Please fetch the Categories', 'ced-bonanza' ); ?></span>
				</div>
				<?php
			}
		}
		else
		{
			?>
			<div>
				<span><?php _e( 'Please fetch the Categories', 'ced-bonanza' ); ?></span>
			</div>
			<?php	
		}
					
		?>
		</div>
	</div>
</div>