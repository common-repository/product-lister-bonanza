<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

//header file.
require_once CED_Bonanza_Lister_DIRPATH.'admin/pages/header.php';
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CED_Bonanza_Lister_Profile_Table_List extends WP_List_Table {


	/** Class constructor */
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Profile', 'ced-bonanza' ), //singular name of the listed records
			'plural'   => __( 'Profiles', 'ced-bonanza' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );
	}

	/**
	 * Retrieve bonanza profile details 
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public function get_profiles( $per_page = 5, $page_number = 1 ) {

		global $wpdb;
		$prefix = $wpdb->prefix . CED_Bonanza_Lister_PREFIX;
		$tableName = $prefix.'_bonanzaprofiles';
	
		$sql = "SELECT `id`,`name`,`active`,`marketplace` FROM `$tableName` ORDER BY `id` DESC";
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results($sql,'ARRAY_A');
		return $result;

	}

	/**
	* Function to count number of responses in result
	*/
	public function get_count( ) {
		global $wpdb;
		$prefix = $wpdb->prefix . CED_Bonanza_Lister_PREFIX;
		$tableName = $prefix.'_bonanzaprofiles';
		$sql = "SELECT * FROM `$tableName`";
		$result = $wpdb->get_results($sql,'ARRAY_A');
		return count($result);
	}
 
	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No profiles avaliable.', 'ced-bonanza' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'marketplace':
			case 'active':
				if($item[ $column_name ]){
					return __('enable','ced-bonanza');
				}else{
					return __('disable','ced-bonanza');
					
				}
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="profile_ids[]" value="%s" />', $item['id']
		);
	}
 
	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {
		$title = '<strong>' . $item['name'] . '</strong>';
		$actions = [
			'edit' => sprintf( '<a href="?page=%s&action=%s&profileID=%s">'.__('Edit','ced-bonanza').'</a>', esc_attr( $_REQUEST['page'] ), 'edit', $item['id'] ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&profileID=%s">'.__('Delete','ced-bonanza').'</a>', esc_attr( $_REQUEST['page'] ), 'delete', $item['id'] )
		];
		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'name'    => __( 'Name', 'ced-bonanza' ),
			
			'active' => __( 'Status', 'ced-bonanza' )
		];
		$columns = apply_filters( 'ced_bonanza_lister_alter_feed_table_columns', $columns );
		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return $sortable_columns = array();
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			
			'bulk-enable' => __('Enable','ced-bonanza'),
			'bulk-disable' => __('Disable','ced-bonanza'),
			'bulk-delete' => __('Delete','ced-bonanza')
		];
		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		global $wpdb;

		$per_page = apply_filters( 'ced_bonanza_lister_list_profiles_per_page', 10 );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		// Column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		
		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}
		
		$this->items = self::get_profiles( $per_page, $current_page );

		$count = self::get_count( );


		// Set the pagination
		$this->set_pagination_args( array(
			'total_items' => $count,
			'per_page'    => $per_page,
			'total_pages' => ceil( $count / $per_page )
		) );


		if(!$this->current_action()) {
			$this->items = self::get_profiles( $per_page, $current_page );
			$this->renderHTML();
		}
		else {
			$this->process_bulk_action();
		}
		
	}
	
	/**
	* Function to get changes in html
	*/
	public function renderHTML() {
		?>
		<div class="ced_bonanza_lister_wrap ced_bonanza_lister_wrap_extn">
			<h2 class="ced_bonanza_lister_setting_header"><?php _e('Profiles','ced-bonanza');?></h2>
			<?php echo '<a href="'. get_admin_url() .'admin.php?page=bnz-bonanza-profile&action=add_new" class="button button-ced_bonanza_lister page-title-action">' . __('Add Profile','ced-bonanza') . '</a>';?>
			<div>
				<?php
				global $cedbonanzalisterhelper;
				if(!session_id()) {
					session_start();
				}
				if(isset($_SESSION['ced_bonanza_lister_validation_notice'])) {
				    $value = $_SESSION['ced_bonanza_lister_validation_notice'];
				    $cedbonanzalisterhelper->bnz_print_notices($value);
				    unset($_SESSION['ced_bonanza_lister_validation_notice']);
				}
				?>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->display();
								?>
							</form>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<br class="clear">
			</div>
		</div>
		<?php
	}

	/**
	* Function to process bulk action
	*/
	public function process_bulk_action() {
		if(!session_id()) {
	        session_start();
	    }
		/** render configuration setup html of bonanza **/
		if( 'edit' === $this->current_action() || ( isset($_GET['action']) && 'edit' === sanitize_text_field ( $_GET['action']) )) {
			require_once CED_Bonanza_Lister_DIRPATH.'admin/partials/profile-view.php';
		}

		if( 'add_new' === $this->current_action() || ( isset($_GET['action']) && 'add_new' ===sanitize_text_field ( $_GET['action']) )) {
			require_once CED_Bonanza_Lister_DIRPATH.'admin/partials/profile-view.php';
		}

		if( 'delete' === $this->current_action() || ( isset($_GET['action']) && 'delete' === sanitize_text_field( $_GET['action']) ) ) {
			do_action( 'ced_bonanza_lister_before_profile_delete',  $_GET['profileID'], $this->currentMarketPlace );
			
			$profileID = sanitize_text_field( $_GET['profileID'] );
			global $wpdb;
			$prefix = $wpdb->prefix . CED_Bonanza_Lister_PREFIX;
			$tableName = $prefix.'_bonanzaprofiles';
			$deleteStatus = $wpdb->delete($tableName,array('id'=>$profileID));
			if($deleteStatus) {
				$notice['message'] = __('Profile Deleted Successfully.','ced-bonanza');
				$notice['classes'] = "notice notice-success";
				$validation_notice[] = $notice;
				$_SESSION['ced_bonanza_lister_validation_notice'] = $validation_notice;
			}
			else {
				$notice['message'] = __('Some Error Encountered.','ced-bonanza');
				$notice['classes'] = "notice notice-error";
				$validation_notice[] = $notice;
				$_SESSION['ced_bonanza_lister_validation_notice'] = $validation_notice;
			}

			do_action( 'ced_bonanza_lister_after_profile_delete', $_GET['profileID'], $this->currentMarketPlace );

			$redirectURL = get_admin_url()."admin.php?page=bnz-bonanza-profile";
			wp_redirect($redirectURL);
		}

		if( 'bulk-delete' === $this->current_action() ) {
			if(isset($_POST['profile_ids'])) {
				$feedsToDelete = $_POST['profile_ids'];

				global $wpdb;
				$prefix = $wpdb->prefix . CED_Bonanza_Lister_PREFIX;
				$tableName = $prefix.'_bonanzaprofiles';
				$sql = "DELETE FROM `".$tableName."` WHERE `id` IN (";
				foreach ($feedsToDelete as $id) {
					$sql .= sanitize_text_field ($id).',';
				}
				$sql = rtrim($sql, ",");
				$sql .= ')';
				$deleteStatus = $wpdb->query($sql);
				if($deleteStatus) {
					$notice['message'] = __('Profiles Deleted Successfully.','ced-bonanza');
					$notice['classes'] = "notice notice-success";
					$validation_notice[] = $notice;
					$_SESSION['ced_bonanza_lister_validation_notice'] = $validation_notice;
				}
				else {
					$notice['message'] = __('Some Error Encountered.','ced-bonanza');
					$notice['classes'] = "notice notice-error";
					$validation_notice[] = $notice;
					$_SESSION['ced_bonanza_lister_validation_notice'] = $validation_notice;
				}

				$redirectURL = get_admin_url()."admin.php?page=bnz-bonanza-profile";
				wp_redirect($redirectURL);
			}else{
					$notice['message'] = __('Please select atleast one profile to delete it.','ced-bonanza');
					$notice['classes'] = "notice notice-error";
					$validation_notice[] = $notice;
					$_SESSION['ced_bonanza_lister_validation_notice'] = $validation_notice;
					$redirectURL = get_admin_url()."admin.php?page=bnz-bonanza-profile";
					wp_redirect($redirectURL);
			}
		} 

		if( 'bulk-enable' === $this->current_action() ) {
			if(isset($_POST['profile_ids'])) {
				$feedsToDelete = $_POST['profile_ids'];

				global $wpdb;
				$prefix = $wpdb->prefix . CED_Bonanza_Lister_PREFIX;
				$tableName = $prefix.'_bonanzaprofiles';

				$sql = "UPDATE `".$tableName."` SET `active`='1' WHERE `id` IN (";
				foreach ($feedsToDelete as $id) {
					$sql .= sanitize_text_field ($id ).',';
				}
				$sql = rtrim($sql, ",");
				$sql .= ')';
				$queryStatus = $wpdb->query($sql);
				if($queryStatus) {
					$notice['message'] = __('Profiles Enable Successfully.','ced-bonanza');
					$notice['classes'] = "notice notice-success";
					$validation_notice[] = $notice;
					$_SESSION['ced_bonanza_lister_validation_notice'] = $validation_notice;
				}
				else {
					$notice['message'] = __('Some Error Encountered.','ced-bonanza');
					$notice['classes'] = "notice notice-error";
					$validation_notice[] = $notice;
					$_SESSION['ced_bonanza_lister_validation_notice'] = $validation_notice;
				}

				$redirectURL = get_admin_url()."admin.php?page=bnz-bonanza-profile";
				wp_redirect($redirectURL);
			}else{
					$notice['message'] = __('Please select atleast one profile to enable it.','ced-bonanza');
					$notice['classes'] = "notice notice-error";
					$validation_notice[] = $notice;
					$_SESSION['ced_bonanza_lister_validation_notice'] = $validation_notice;
					$redirectURL = get_admin_url()."admin.php?page=bnz-bonanza-profile";
					wp_redirect($redirectURL);
			}
		} 


		if( 'bulk-disable' === $this->current_action() ) {
			if(isset($_POST['profile_ids'])) {
				$feedsToDelete = $_POST['profile_ids'];

				global $wpdb;
				$prefix = $wpdb->prefix . CED_Bonanza_Lister_PREFIX;
				$tableName = $prefix.'_bonanzaprofiles';
				$sql = "UPDATE `".$tableName."` SET `active`='0' WHERE `id` IN (";
				foreach ($feedsToDelete as $id) {
					$sql .= sanitize_text_field ($id).',';
				}
				$sql = rtrim($sql, ",");
				$sql .= ')';
				$queryStatus = $wpdb->query($sql);
				if($queryStatus) {
					$notice['message'] = __('Profiles Disable Successfully.','ced-bonanza');
					$notice['classes'] = "notice notice-success";
					$validation_notice[] = $notice;
					$_SESSION['ced_bonanza_lister_validation_notice'] = $validation_notice;
				}
				else {
					$notice['message'] = __('Some Error Encountered.','ced-bonanza');
					$notice['classes'] = "notice notice-error";
					$validation_notice[] = $notice;
					$_SESSION['ced_bonanza_lister_validation_notice'] = $validation_notice;
				}

				$redirectURL = get_admin_url()."admin.php?page=bnz-bonanza-profile";
				wp_redirect($redirectURL);
			}else{
					$notice['message'] = __('Please select atleast one profile to disable it.','ced-bonanza');
					$notice['classes'] = "notice notice-error";
					$validation_notice[] = $notice;
					$_SESSION['ced_bonanza_lister_validation_notice'] = $validation_notice;
					$redirectURL = get_admin_url()."admin.php?page=bnz-bonanza-profile";
					wp_redirect($redirectURL);
			}
		}
	}
}
$ced_bonanza_lister_profile_table_list = new CED_Bonanza_Lister_Profile_Table_List();
$ced_bonanza_lister_profile_table_list->prepare_items();