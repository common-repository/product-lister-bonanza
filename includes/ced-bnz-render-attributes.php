<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds a global instance to call some core functionality on-the-fly.
 *
 * @class    CED_Bonanza_Lister_Render_Attributes
 * @version  1.0.0
 * @category Class
 * @author   CedCommerce
 */
class CED_Bonanza_Lister_Render_Attributes
{
	private static $_instance;
	
	public static function getInstance()
	{
		if( !self::$_instance instanceof self )
			self::$_instance = new self;
	
			return self::$_instance;
	}

	/*
	* Function to render input text html
	*/
	function renderInputTextHTML($attribute_id,$attribute_name,$categoryID,$productID,$marketPlace,$attribute_description=null,$indexToUse,$additionalInfo=array('case'=>"product"),$conditionally_required=false,$conditionally_required_text='' ) {
		
		global $post,$product,$loop;
		$fieldName = $categoryID.'_'.$attribute_id;
		if($additionalInfo['case'] == "product") {
			$previousValue = get_post_meta ( $productID, $fieldName, true );
		}
		else{
			$previousValue = $additionalInfo['value'];
		}
		
		?>
		<p class="form-field _bnz_brand_field ">
			<input type="hidden" name="<?php echo $marketPlace.'[]'; ?>" value="<?php echo $fieldName; ?>" />
			<label for=""><?php echo $attribute_name; ?>
			</label>
			<?php
			?>
			<input class="short" name="<?php echo $fieldName.'['.$indexToUse.']'; ?>" id="" value="<?php echo $previousValue; ?>" placeholder="" type="text" /> 
			<?php
			if(!is_null($attribute_description) && $attribute_description != '') {
				echo wc_help_tip( __( $attribute_description, 'ced-bonanza' ) );
			}
			if($conditionally_required) {
				echo wc_help_tip( __( $conditionally_required_text, 'ced-bonanza' ) );
			}
			?>
		</p>
		<?php
	}
	/*
	* Function to render dropdown html
	*/
	function renderDropdownHTML($attribute_id,$attribute_name,$values,$categoryID,$productID,$marketPlace,$attribute_description=null,$indexToUse,$additionalInfo=array('case'=>"product")) {
		$fieldName = $categoryID.'_'.$attribute_id;
		if($additionalInfo['case'] == "product") {
			$previousValue = get_post_meta ( $productID, $fieldName, true );
		}
		else{
			$previousValue = $additionalInfo['value'];
		}
		?>
		<p class="form-field _bnz_id_type_field ">
			<input type="hidden" name="<?php echo $marketPlace.'[]'; ?>" value="<?php echo $fieldName; ?>" />
			<label for=""><?php echo $attribute_name; ?></label>
			<select id="" name="<?php echo $fieldName.'['.$indexToUse.']'; ?>" class="select short">
				<?php
				echo '<option value="0">-- Select --</option>';
				foreach ($values as $key => $value) {
					if($previousValue == $key) {
						echo '<option value="'.$key.'" selected>'.$value.'</option>';
					}
					else {
						echo '<option value="'.$key.'">'.$value.'</option>';
					}
				}
				?>
			</select>
			<?php
			if(!is_null($attribute_description) && $attribute_description != '') {
				echo wc_help_tip( __( $attribute_description, 'ced-bonanza' ) );
			}
			?>
		</p>
		<?php
	}
	
}
global $global_CED_Bonanza_Lister_Render_Attributes;
$global_CED_Bonanza_Lister_Render_Attributes = CED_Bonanza_Lister_Render_Attributes::getInstance();
?>