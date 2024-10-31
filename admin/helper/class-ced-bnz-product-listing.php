<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if(!class_exists('WP_List_Table')) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * product listing related functionality on manage products page.
 *
 * @since      1.0.0
 *
 * @package    Product Lister Bonanza
 * @subpackage Product Lister Bonanza/admin/helper
 */

if( !class_exists( 'CED_Bonanza_Lister_product_lister' ) ) :

/**
 * product listing on manage product.
 *
 * product quick editing, listing and all other functionalities
 * to manage products.
 *
 * @since      1.0.0
 * @package    Product Lister Bonanza
 * @subpackage Product Lister Bonanza/admin/helper
 * @author     CedCommerce <cedcommerce.com>
 */
class CED_Bonanza_Lister_product_lister extends WP_List_Table {
	
	/**
	 * product data query response.
	 * 
	 * @since 1.0.0
	 */
	private $_loop;
	private $_current_product_id;
	private $_is_variable_product;
	private $_bnzFramework;
	
	/**
	 * all profile associative array.
	 * 
	 * @since 1.0.0
	 */
	private $_profileArray;
	
	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 */
	function __construct(){

		global $status, $page, $cedbonanzalisterhelper;
		$marketPlaces = bonanzaget_enabled_marketplaces();
		$marketPlace = is_array($marketPlaces) ? $marketPlaces[0] : "";
		$this->_bnzFramework = isset($_REQUEST['section']) ? sanitize_text_field ($_REQUEST['section'] ) : $marketPlace;
		parent::__construct( array(
				'singular'  => 'ced_bonanza_lister_mp',     
				'plural'    => 'ced_bonanza_lister_mps',   
				'ajax'      => true        
		) );
		
		wp_enqueue_script('inline-edit-post');
		wp_enqueue_script('heartbeat');
		$this->_profileArray = $cedbonanzalisterhelper->ced_bonanza_lister_profile_details(array('name'));
	}
	
	/**
	 * columns for the manage product table from
	 * where you can manage products for marketplaces.
	 * 
	 * @since 1.0.0
	 * @see WP_List_Table::get_columns()
	 */
	public function get_columns(){
		$columns = array(
				'cb'        => '<input type="checkbox" />',
				'thumb'     => '<span class="wc-image tips" data-tip="' . esc_attr__( 'Image', 'ced-bonanza' ) . '">' . __( 'Image', 'ced-bonanza' ) . '</span>',
				'name'    => __( 'Name', 'ced-bonanza' ),
				'profile' => __('Profile', 'ced-bonanza'),
				'price' => __('Selling Price', 'ced-bonanza'),
				'qty' => __('Inventory','ced-bonanza'),
		);
		$columns = apply_filters('ced_bonanza_lister_alter_columns_in_manage_product_section',$columns);
		return $columns;
	}
	
	/**
	 * supported bulk actions for managing products.
	 * 
	 * @since 1.0.0
	 * @see WP_List_Table::get_bulk_actions()
	 */
	public function bulk_actions( $which = '' ){
		global $svc_obj;
		if(!is_object($svc_obj)){
		 return;
		}
		
		if($which == 'top'):
			
			$actions = array(
					'upload'    => __( 'Upload', 'ced-bonanza' ),
					 
			);
			$marketplaces = bonanzaget_enabled_marketplaces();
			if(!count($marketplaces))
				return;
			echo '<div class="ced_bonanza_lister_top_wrapper">';
			echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . __( 'Select bulk action', 'ced-bonanza' ) . '</label>';
			echo '<select name="action" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n";
			echo '<option value="-1">' . __( 'Bulk Actions', 'ced-bonanza' ) . "</option>\n";
			
			foreach ( $actions as $name => $title ) {
				$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';
			
				echo "\t" . '<option value="' . $name . '"' . $class . '>' . $title . "</option>\n";
			}
			echo "</select>\n";
			submit_button( __( 'Apply', 'ced-bonanza' ), 'action', '', false, array( 'id' => "ced_bonanza_lister_doaction", 'name' => 'doaction' ) );
			echo "\n";
			echo '</div>';

		endif;
	}

	/**
	 * preparing the table data for listing products
	 * so that we can manage all products form single
	 * place to all frameworks.
	 * 
	 * @since 1.0.0
	 * @see WP_List_Table::prepare_items()
	 */	
	function prepare_items() {
		global $wpdb;
	
		$per_page = apply_filters( 'ced_bonanza_lister_products_per_page', 10 );
		$post_type = 'product';
	
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
	
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$current_page = $this->get_pagenum();
		 
		$args = array(
				'post_type'           => $post_type,
				'posts_per_page'      => $per_page,
				'ignore_sticky_posts' => true,
				'paged'               => $current_page,
		);
		
		// Handle the status query
		if ( ! empty( $_REQUEST['status'] ) ) {
			$args['post_status'] = sanitize_text_field( $_REQUEST['status'] );
		}
		if ( ! empty( $_REQUEST['s'] ) ) {
			$args['s'] = sanitize_text_field( $_REQUEST['s'] );
		}
		if ( ! empty( $_REQUEST['pro_cat_sorting'] ) ) {
			$pro_cat_sorting = isset($_GET['pro_cat_sorting']) ? sanitize_text_field( $_GET['pro_cat_sorting'] ) : '';
			if( $pro_cat_sorting != '' ) {
				$selected_cat = array($pro_cat_sorting);
				$tax_query = array();
				$tax_queries = array();
				$tax_query['taxonomy'] = 'product_cat';
				$tax_query['field'] = 'id';
				$tax_query['terms'] = $selected_cat;
				$args['tax_query'][] = $tax_query;
			}	
		}
		if ( ! empty( $_REQUEST['pro_type_sorting'] ) ) {
			$pro_type_sorting = isset($_GET['pro_type_sorting']) ? sanitize_text_field( $_GET['pro_type_sorting'] ) : '';
			if( $pro_type_sorting != '' ) {
				$selected_type = array($pro_type_sorting);
				$tax_query = array();
				$tax_queries = array();
				$tax_query['taxonomy'] = 'product_type';
				$tax_query['field'] = 'id';
				$tax_query['terms'] = $selected_type;
				$args['tax_query'][] = $tax_query;
			}	
		}
		if ( ! empty( $_REQUEST['status_sorting'] ) ) {
			 
			$status_sorting = isset($_GET['status_sorting']) ? sanitize_text_field ( $_GET['status_sorting']) : '';
			$availableMarketPlaces = bonanzaget_enabled_marketplaces();
			if(is_array($availableMarketPlaces) && !empty($availableMarketPlaces)) {
				$tempsection = $availableMarketPlaces[0];
				if(isset($_GET['section'])) {
					$tempsection = esc_attr($_GET['section']);
				}
			}
			if( $status_sorting != '' ) {
			 
				$meta_query = array();
				if( $status_sorting == 'Uploaded' ) {
			  		$metaKey = 'ced_bonanza_lister_listing_status';
					$metaValue = 'PUBLISHED';
					 
					$args['orderby'] = 'meta_value_num';
					$args['order'] = 'ASC';

					$meta_query[] = array(
						'key' => $metaKey,
						 
						'compare' => "EXISTS",

					);
				}elseif($status_sorting == 'notUploaded'){
					$metaKey = 'ced_bonanza_lister_listing_status';
					$metaValue = 'PUBLISHED';
					 
					$args['orderby'] = 'meta_value_num';
					$args['order'] = 'ASC';

					$meta_query[] = array(
						'key' => $metaKey,
						'compare' => 'NOT EXISTS',
						  
					);
					 
				}
				$args['meta_query'] = $meta_query;
			}
		}
		// Get the webhooks
		$webhooks  = new WP_Query( $args );
		$this->_loop = $webhooks;
		$total_items = $webhooks->found_posts;
		$this->set_pagination_args( array(
				'total_items' => $total_items,                  
				'per_page'    => $per_page,                     
				'total_pages' => ceil($total_items/$per_page)  
		) );
	}
	
	/**
	 * displaying the marketplace listable products.
	 * 
	 * @since 1.0.0
	 * @see WP_List_Table::display_rows()
	 */
	public function display_rows(){
		 
		if( $this->has_product_data() ){
			$loop = $this->_loop;
			if($loop->have_posts()){
				while($loop->have_posts()){
					$loop->the_post();
					$string = strtolower($loop->post->post_title);
 
					$this->get_product_row_html($loop->post);
 
				}
			}
		}
	}
	
	/**
	* Function to get variable products variation html
	*/
	public function get_product_row_html_variation($var_post,$product_id){
		$_product = wc_get_product( $var_post->ID );
		$this->_current_product_id = $product_id;
		$this->_is_variable_product	= true;
		$columns = $this->get_columns();
		echo '<tr id="post-'.$product_id.'" class="ced_bonanza_lister_inline_edit">';
		$firstTime = false;
		foreach($columns as $column_id => $column_name){
			if(!$firstTime) {
				$firstTime = true;
				echo '<td></td>';
				continue;
			}
			$this->print_variation_column_data($column_id, $var_post, $_product);
		}
		echo '</tr>';
	}

	/**
	 * get product row html.
	 * 
	 * @since 1.0.0
	 */
	public function get_product_row_html($post){
		$_product = wc_get_product( $post->ID );
		if(is_wp_error($_product))
			return;
		if(WC()->version < "3.0.0")
		{
			$product_id = $_product->id;
			$this->_current_product_id = $product_id;
			$this->_is_variable_product	= false;

			$columns = $this->get_columns();
			if($_product->product_type == 'variable') { 
				global $cedbonanzalisterhelper;
				$columnsCount = count($columns);
				$columnsCount = $columnsCount-4;
				$selectedMarketplace = $this->_bnzFramework;
				$items_in_queue = get_option( 'ced_bonanza_lister_'.$selectedMarketplace.'_upload_queue', array() );
				if( in_array($product_id, $items_in_queue) ) {
					$selectedPreviously = 'checked="checked"';
				}
				else {
					$selectedPreviously = '';
				}
				$allow_split_variation = get_post_meta( $product_id , 'ced_bonanza_lister_allow_split_variation', true );
				if( $allow_split_variation == 'yes' ) {
					$selectedSplitPreviously = 'checked="checked"';
				}
				else {
					$selectedSplitPreviously = '';
				}

				echo '<tr>';
					echo '<td colspan="2"><input id="cb-select-'.$product_id.'" name="post[]" value="'.$product_id.'" type="checkbox"><strong><a class="row-title" href="javascript:void(0)">'.$post->post_title.'</a></strong></td>';
					$isProfileAssigned = get_post_meta($product_id,'ced_bonanza_lister_profile',true);
					echo '<td colspan="4">';
					$profile_name = $cedbonanzalisterhelper->ced_bonanza_lister_profile_details(array('id'=>$isProfileAssigned));
					if( isset($isProfileAssigned) && !empty($isProfileAssigned) && $isProfileAssigned && !empty($profile_name) ){
						echo $profile_name;
						echo '<img width="16" height="16" src="'.CED_Bonanza_Lister_URL.'admin/images/remove.png" data-prodid="'.$post->ID.'" class="bnz_remove_profile ced_bonanza_lister_IsReady">';
					}else{
						echo '<a href="javascript:void(0);" data-proid="'.$product_id.'" class="ced_bonanza_lister_profile ced_required_red_color" title="Assign profile to this item">'.__('Not Assigned','ced-bonanza').'</a>';
					}
					echo '</td>';
					 
					echo '<td>';
						echo '<center>';
						echo '<input type="checkbox" class="ced_bonanza_lister_marketplace_allow_split_variation" data-id="'.$product_id.'" data-marketplace="'.$selectedMarketplace.'" '.$selectedSplitPreviously.'>';
						echo '</center>';
					echo '</td>';
				echo '</tr>';
				$variations = $_product->get_available_variations();
				foreach ($variations as $variation) {
					$product_id = $variation['variation_id'];
					$var_post = get_post($product_id);
					$this->get_product_row_html_variation($var_post,$product_id);
				}
			 }
			else{
				echo '<tr id="post-'.$product_id.'" class="ced_bonanza_lister_inline_edit">';
				foreach($columns as $column_id => $column_name){
					$this->print_column_data($column_id, $post, $_product);
				}
				echo '</tr>';
			}
		}
		else{
			$product_id = $_product->get_id();
			$this->_current_product_id = $product_id;
			$this->_is_variable_product	= false;

			$columns = $this->get_columns();
			if($_product->get_type() == 'variable') { 
				global $cedbonanzalisterhelper;
				$columnsCount = count($columns);
				$columnsCount = $columnsCount-4;
				$selectedMarketplace = $this->_bnzFramework;
				$items_in_queue = get_option( 'ced_bonanza_lister_'.$selectedMarketplace.'_upload_queue', array() );
				if( in_array($product_id, $items_in_queue) ) {
					$selectedPreviously = 'checked="checked"';
				}
				else {
					$selectedPreviously = '';
				}
				$allow_split_variation = get_post_meta( $product_id , 'ced_bonanza_lister_allow_split_variation', true );
				if( $allow_split_variation == 'yes' ) {
					$selectedSplitPreviously = 'checked="checked"';
				}
				else {
					$selectedSplitPreviously = '';
				}

				echo '<tr>';
					echo '<td colspan="2"><input id="cb-select-'.$product_id.'" name="post[]" value="'.$product_id.'" type="checkbox"><strong><a class="row-title" href="javascript:void(0)">'.$post->post_title.'</a></strong></td>';
					$isProfileAssigned = get_post_meta($product_id,'ced_bonanza_lister_profile',true);
					echo '<td colspan="1">';
					$profile_name = $cedbonanzalisterhelper->ced_bonanza_lister_profile_details(array('id'=>$isProfileAssigned));
 					 
					echo '</td>';

					echo '<td colspan="3">';
					$profile_name = $cedbonanzalisterhelper->ced_bonanza_lister_profile_details(array('id'=>$isProfileAssigned));
					if( isset($isProfileAssigned) && !empty($isProfileAssigned) && $isProfileAssigned && !empty($profile_name) ){
						echo $profile_name;
						echo '<img width="16" height="16" src="'.CED_Bonanza_Lister_URL.'admin/images/remove.png" data-prodid="'.$post->ID.'" class="bnz_remove_profile ced_bonanza_lister_IsReady">';
					}else{
						echo '<a href="javascript:void(0);" data-proid="'.$product_id.'" class="ced_bonanza_lister_profile ced_required_red_color" title="Assign profile to this item" >'.__('Not Assigned','ced-bonanza').'</a>';
					}
					echo '</td>';
				 
				 
				echo '</tr>';
				$variations = $_product->get_available_variations();
				foreach ($variations as $variation) {
					$product_id = $variation['variation_id'];
					$var_post = get_post($product_id);
					$this->get_product_row_html_variation($var_post,$product_id);
				}
			 }
			else{
				echo '<tr id="post-'.$product_id.'" class="ced_bonanza_lister_inline_edit">';
				foreach($columns as $column_id => $column_name){
					$this->print_column_data($column_id, $post, $_product);
				}
				echo '</tr>';
			}
		}
	}
	
	/**
	 * displaying product title with some links
	 * for editing, quick editing etc.
	 * 
	 * @since 1.0.0
	 * @param post object $post
	 */
	public function _colummn_title( $post,$is_variation=false ){
		
		$classes = "id column-id has-row-actions column-primary";
		$data = "data-colname=id";
		echo '<td class="'.$classes.'" '.$data.'>';
		$this->column_title($post);
		echo $this->handle_row_actions($post, 'Name', 'Name');
		echo '</td>';
	}
	
	/**
	 * Generates and displays row action links.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param object $post        Post being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary     Primary column name.
	 * @return string Row actions output for posts.
	 */
	protected function handle_row_actions( $post, $column_name, $primary ) {
		$post_type_object = get_post_type_object( $post->post_type );
		$can_edit_post = current_user_can( 'edit_post', $post->ID );
		$actions = array();
		$title = _draft_or_post_title($post);
		$listing_type = '';
		$actions['id'] = 'ID: ' . $this->_current_product_id;

		if( $post->post_type == 'product_variation' ) {
			$idToUseForLink = $post->post_parent;
		}
		else {
			$idToUseForLink = $post->ID;
		}
		$listing_type = get_post_meta( $post->ID, 'bonanza_listing_type', true );
		if ( $can_edit_post && 'trash' != $post->post_status ) {
			$actions['edit'] = '<a href="' . get_edit_post_link( $idToUseForLink, true ) . '" title="' . esc_attr( __( 'Edit this item', 'ced-bonanza' ) ) . '">' . __( 'Edit', 'ced-bonanza' ) . '</a>';
		 
			$actions['profile hide-if-no-js'] = '<a href="javascript:;" data-proid = "'.$post->ID.'" class="ced_bonanza_lister_profile" title="' . esc_attr( __( 'Assign profile to this item', 'ced-bonanza' ) ) . '">' . __( 'Profile', 'ced-bonanza' ) . '</a>';
			$is_uploaded = get_post_meta( $post->ID, 'ced_bonanza_lister_status', true );
			if( $listing_type == 'Chinese' && $is_uploaded == 'PUBLISHED' )
			{
				$actions['end_auction'] = '<a href="javascript:;" data-proid = "'.$post->ID.'" class="ced_bonanza_lister_end_auction" title="' . esc_attr( __( 'End Auction for this item', 'ced-bonanza' ) ) . '">' . __( 'End Auction', 'ced-bonanza' ) . '</a>';
			}
		}
		
		return $this->row_actions( $actions );
	}
	

	/**
	 * column title.
	 * 
	 * @since 1.0.0
	 * @param post object $post
	 */
	public function column_title( $post ) {
		global $mode;
	
		if ( $this->hierarchical_display ) {
			if ( 0 === $this->current_level && (int) $post->post_parent > 0 ) {
				$find_main_page = (int) $post->post_parent;
				while ( $find_main_page > 0 ) {
					$parent = get_post( $find_main_page );
	
					if ( is_null( $parent ) ) {
						break;
					}
	
					$this->current_level++;
					$find_main_page = (int) $parent->post_parent;
	
					if ( ! isset( $parent_name ) ) {
						/** This filter is documented in wp-includes/post-template.php */
						$parent_name = apply_filters( 'the_title', $parent->post_title, $parent->ID );
					}
				}
			}
		}
	
		$pad = str_repeat( '&#8212; ', $this->current_level );
		echo "<strong>";
	
		$format = get_post_format( $post->ID );
		if ( $format ) {
			$label = get_post_format_string( $format );
	
			$format_class = 'post-state-format post-format-icon post-format-' . $format;
	
			$format_args = array(
					'post_format' => $format,
					'post_type' => $post->post_type
			);
			echo $this->get_edit_link( $format_args, $label . ':', $format_class );
		}
	
		$can_edit_post = current_user_can( 'edit_post', $post->ID );
		$title = _draft_or_post_title($post);
	
		if ( $can_edit_post && $post->post_status != 'trash') {
			printf(
			'<a class="row-title" href="%s" aria-label="%s">%s%s</a>',
			get_edit_post_link(wp_get_post_parent_id( $post->ID )),
			/* translators: %s: post title */
			esc_attr( sprintf( ( '&#8220;%s&#8221; (Edit)' ), $title ) ),
			$pad,
			$title
			);
		} else {
			echo $pad . $title;
		}
		_post_states( $post );
	
		if ( isset( $parent_name ) ) {
			$post_type_object = get_post_type_object( $post->post_type );
			echo ' | ' . $post_type_object->labels->parent_item_colon . ' ' . esc_html( $parent_name );
		}
		echo "</strong>\n";
	
		if ( $can_edit_post && $post->post_status != 'trash' ) {
			$lock_holder = wp_check_post_lock( $post->ID );
	
			if ( $lock_holder ) {
				$lock_holder = get_userdata( $lock_holder );
				$locked_avatar = get_avatar( $lock_holder->ID, 18 );
				$locked_text = esc_html( sprintf( '%s'.__( ' is currently editing' ), $lock_holder->display_name ) );
			} else {
				$locked_avatar = $locked_text = '';
			}
			echo '<div class="locked-info"><span class="locked-avatar">' . $locked_avatar . '</span> <span class="locked-text">' . $locked_text . "</span></div>\n";
		}
	
		if ( ! is_post_type_hierarchical( $this->screen->post_type ) && 'excerpt' === $mode && current_user_can( 'read_post', $post->ID ) ) {
			the_excerpt();
		}
		get_inline_data( $post );
		$the_product = wc_get_product($post->ID);
		$hidden_fields = '<div class="hidden" id="ced_bonanza_lister_inline_' . $this->_current_product_id . '">';
		if( WC()->version < "3.0.0" ){
			$hidden_fields .= '<div class="_sku" type="_text_input">'.$the_product->sku.'</div>';
		}else{
			$hidden_fields .= '<div class="_sku" type="_text_input">'.$the_product->get_sku().'</div>';
		}
		
		if(!class_exists('CED_Bonanza_Lister_product_fields')){
			require_once CED_Bonanza_Lister_DIRPATH.'admin/helper/class-product-fields.php';
		}
		$product_fields = CED_Bonanza_Lister_product_fields::get_instance();
		$required_fields = $product_fields->get_custom_fields('required',false);
		if(is_array($required_fields)){
			foreach($required_fields as $fieldData){
				if(is_array($fieldData)){
					$id = isset($fieldData['id']) ? esc_attr($fieldData['id']) : '';
					$type = isset($fieldData['type']) ? esc_attr($fieldData['type']) : '';
					if(!empty($id) && !empty($type)){
						$hidden_fields .= '<div class="'.$id.'" type="'.$type.'">'.get_post_meta($this->_current_product_id,$id,true).'</div>';
					}
				}
			}
		}
		$hidden_fields .= '</div>';
		echo $hidden_fields;
	}
	
	/**
	 * column data for variable products.
	 * 
	 * @since 1.0.0 
	 */
	public function print_variation_column_data($column_name, $var_post, $the_product){
		
		global $cedbonanzalisterhelper;
		if( WC()->version < "3.0.0" ){
			$product_id = $the_product->id;
		}else{
			$product_id = $the_product->get_id();
		}
		$edit_link = get_edit_post_link( $var_post->ID );
		
		$classes = "$column_name column-$column_name";
		
		$data = 'data-colname="'.$column_name.'"';
		
		$selectedMarketplace = $this->_bnzFramework;
		
		$activeMarketplaces = get_option('ced_bonanza_lister_activated_marketplaces',true);
		switch ( $column_name ) {
				
			case 'thumb' :
				echo '<td class="ced_bonanza_lister_thumbnail '.$classes.'" '.$data.'>';
				echo '<a href="' . $edit_link . '">' . $the_product->get_image( 'thumbnail' ) . '</a>';
				echo '</td>';
				break;
			case 'name' :

				$this->_colummn_title($var_post);
				break;

			case 'profile':

				echo '<td class="ced_bonanza_lister_mp_td '.$classes.'" '.$data.'>';
				echo '</td>';
				break;
			case 'price':
				echo '<td class="ced_bonanza_lister_mp_td '.$classes.'" '.$data.'>';

				echo wc_price(bonanza_get_marketplace_price($var_post->ID,$selectedMarketplace));
				echo '</td>';
				break;
			case 'qty':
				echo '<td class="ced_bonanza_lister_mp_td '.$classes.'" '.$data.'>';
				 
				echo bonanza_get_marketplace_qty($var_post->ID,$selectedMarketplace);
				echo '</td>';
				break;
		   
			default :
				echo '<td class="'.$classes.'" '.$data.'>';
					do_action('ced_bonanza_lister_render_extra_column_on_manage_product_section', $column_name, $var_post, $the_product );
				echo '</td>';
				break;
		}
	}
	
	/**
	 * printing table data.
	 * 
	 * @param string $column_name
	 * @param post object $post
	 * @param product object $the_product
	 */
	public function print_column_data( $column_name, $post, $the_product ){
		
		global $cedbonanzalisterhelper;
		if( WC()->version < "3.0.0" ){
			$product_id = $the_product->id;
		}else{
			$product_id = $the_product->get_id();
		}
		$edit_link = get_edit_post_link( $post->ID );
		
		$classes = "$column_name column-$column_name";
		
		$data = 'data-colname="'.$column_name.'"';
		
		$selectedMarketplace = $this->_bnzFramework;
		switch ( $column_name ) {
			case 'cb':
				echo '<td class="'.$classes.'" '.$data.'>';
				if ( current_user_can( 'edit_post', $post->ID ) ):
					echo '<label class="screen-reader-text" for="cb-select-'.$post->ID.'">';
					echo 'Select '._draft_or_post_title($post);
					echo '</label>';
					echo '<input id="cb-select-'.$post->ID.'" type="checkbox" name="post[]" value="'.$post->ID.'" />';
					echo '<div class="locked-indicator"></div>';
			 	endif;
			 	echo '</td>';
				break;
			case 'thumb' :
				echo '<td class="ced_bonanza_lister_thumbnail '.$classes.'" '.$data.'>';
				echo '<a href="' . $edit_link . '">' . $the_product->get_image( 'thumbnail' ) . '</a>';
				echo '</td>';
				break;
			case 'name' :
				$this->_colummn_title($post);
				break;
			
			case 'profile':
				echo '<td class="ced_bonanza_lister_mp_td '.$classes.'" '.$data.'>';
			 
				$isProfileAssigned = get_post_meta($post->ID,'ced_bonanza_lister_profile',true);
				$profile_name = $cedbonanzalisterhelper->ced_bonanza_lister_profile_details(array('id'=>$isProfileAssigned));
				if( isset($isProfileAssigned) && !empty($isProfileAssigned) && $isProfileAssigned && !empty($profile_name) ){
					
					echo $profile_name;
					echo '<img width="16" height="16" src="'.CED_Bonanza_Lister_URL.'admin/images/remove.png" data-prodid="'.$post->ID.'" class="bnz_remove_profile ced_bonanza_lister_IsReady">';
					
				}else{
					echo '<a href="javascript:void(0);" data-proid="'.$product_id.'" class="ced_bonanza_lister_profile ced_required_red_color" title="Assign profile to this item">'.__('Not Assigned','ced-bonanza').'</a>';
				}
				echo '</td>';
				break;
			case 'price':
				echo '<td class="ced_bonanza_lister_mp_td '.$classes.'" '.$data.'>';
					echo wc_price(bonanza_get_marketplace_price($post->ID,$selectedMarketplace));
				echo '</td>';
				break;
			case 'qty':
				echo '<td class="ced_bonanza_lister_mp_td '.$classes.'" '.$data.'>';
					echo bonanza_get_marketplace_qty($post->ID,$selectedMarketplace);
				echo '</td>';
				break;
			  
			case 'expected_listing_fees':
				$expected_fees = get_post_meta( $product_id, '_bnz_bonanza_listing_fee' , true );
				echo '<td class="'.$classes.'" '.$data.'>';
				if ($expected_fees != '' && $expected_fees != null) {
					echo wc_price( $expected_fees );
				}else{
					echo '-';
				}
				echo '</td>';
				break;
 
			default :
				echo '<td class="'.$classes.'" '.$data.'>';
					do_action('ced_bonanza_lister_render_extra_column_on_manage_product_section', $column_name, $post, $the_product );
				echo '</td>';
				break;
		}
	}
	
	/**
	 * caching mechanism for checking if 
	 * data available for listing.
	 * 
	 * @since 1.0.0
	 * @return boolean
	 */
	public function has_product_data(){
		return !empty($this->_loop);
	}
	
	/**
	 * items available for listing.
	 * 
	 * @since 1.0.0
	 * @see WP_List_Table::has_items()
	 */
	public function has_items(){
		 return true;
	}
	
	/**
	 * Outputs the hidden row displayed when inline editing
	 *
	 * @since 1.0.0.
	 *
	 * @global string $mode
	 */
	public function inline_edit() {
		global $mode;
		$screen = $this->screen;
		$post = get_default_post_to_edit( 'product' );
		$post_type_object = get_post_type_object( 'product' );
		$m = ( isset( $mode ) && 'excerpt' === $mode ) ? 'excerpt' : 'list';
		$can_publish = current_user_can( $post_type_object->cap->publish_posts );
	}
	
	/**
	 * Outputs the hidden profile section displayed to assign profile
	 * 
	 * @since 1.0.0.
	 *
	 * @global string $mode
	 */
	public function profle_section()
	{
		global $mode;
		$screen = $this->screen;
		$post = get_default_post_to_edit( 'product' );
		$post_type_object = get_post_type_object( 'product' );
		$m = ( isset( $mode ) && 'excerpt' === $mode ) ? 'excerpt' : 'list';
		$can_publish = current_user_can( $post_type_object->cap->publish_posts );
		require_once CED_Bonanza_Lister_DIRPATH.'admin/partials/html-profile.php';
	}

	/**
	 * prepare missing data.
	 * 
	 * @since 1.0.0
	 */
	public function printMissingData($errors=array()){
		$html = '';
		$counter = 1;
		if(is_array($errors)){
			foreach($errors as $error){
				$html .= $counter.'. '.$error.'</br>';
				$counter++;
			}
		}
		return $html;
	}
}
endif;