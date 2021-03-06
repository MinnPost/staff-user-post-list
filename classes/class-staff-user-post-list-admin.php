<?php
/**
 * Class file for the Staff_User_Post_List_Admin class.
 *
 * @file
 */

if ( ! class_exists( 'Staff_User_Post_List' ) ) {
	die();
}

/**
 * Create default WordPress admin functionality to configure the plugin.
 */
class Staff_User_Post_List_Admin {

	protected $option_prefix;
	protected $version;
	protected $slug;
	protected $data;
	//protected $cache;

	/**
	* Constructor which sets up admin pages
	*
	* @param string $option_prefix
	* @param string $version
	* @param string $slug
	* @param object $data
	* @param object $cache
	* @throws \Exception
	*/
	public function __construct( $option_prefix, $version, $slug, $data ) {

		$this->option_prefix = $option_prefix;
		$this->version       = $version;
		$this->slug          = $slug;
		$this->data          = $data;
		//$this->cache         = $cache;

		//$this->mp_mem_transients = $this->cache->mp_mem_transients;

		$this->tabs = $this->get_admin_tabs();

		$this->add_actions();

	}

	/**
	* Create the action hooks to create the admin page(s)
	*
	*/
	public function add_actions() {
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'admin_settings_form' ) );
		}

	}

	/**
	* Create WordPress admin options page
	*
	*/
	public function create_admin_menu() {
		$capability = 'manage_staff';
		add_users_page( 'Staff User List', 'Staff List', $capability, $this->slug, array( $this, 'show_admin_page' ) );
	}


	/**
	* Create WordPress admin options page tabs
	*
	* @return array $tabs
	*
	*/
	private function get_admin_tabs() {
		$tabs = array(
			'staff_list'    => 'Staff List',
			'page_settings' => 'Page Settings',
		); // this creates the tabs for the admin
		return $tabs;
	}

	/**
	* Display the admin settings page
	*
	* @return void
	*/
	public function show_admin_page() {
		$get_data = filter_input_array( INPUT_GET, FILTER_SANITIZE_STRING );
		?>
		<div class="wrap">
			<h1><?php _e( get_admin_page_title(), 'staff-user-post-list' ); ?></h1>

			<?php
			$tabs = $this->tabs;
			$tab  = isset( $get_data['tab'] ) ? sanitize_key( $get_data['tab'] ) : 'staff_list';
			$this->render_tabs( $tabs, $tab );

			switch ( $tab ) {
				case 'page_settings':
					require_once( plugin_dir_path( __FILE__ ) . '/../templates/admin/settings.php' );
					break;
				default:
					require_once( plugin_dir_path( __FILE__ ) . '/../templates/admin/settings.php' );
					break;
			} // End switch().
			?>
		</div>
		<?php
	}

	/**
	* Render tabs for settings pages in admin
	* @param array $tabs
	* @param string $tab
	*/
	private function render_tabs( $tabs, $tab = '' ) {

		$get_data = filter_input_array( INPUT_GET, FILTER_SANITIZE_STRING );

		$current_tab = $tab;
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab_key => $tab_caption ) {
			$active = $current_tab === $tab_key ? ' nav-tab-active' : '';
			echo sprintf(
				'<a class="nav-tab%1$s" href="%2$s">%3$s</a>',
				esc_attr( $active ),
				esc_url( '?page=' . $this->slug . '&tab=' . $tab_key ),
				esc_html( $tab_caption )
			);
		}
		echo '</h2>';

		if ( isset( $get_data['tab'] ) ) {
			$tab = sanitize_key( $get_data['tab'] );
		} else {
			$tab = '';
		}
	}

	/**
	* Register items for the settings api
	* @return void
	*
	*/
	public function admin_settings_form() {

		$get_data = filter_input_array( INPUT_GET, FILTER_SANITIZE_STRING );
		$page     = isset( $get_data['tab'] ) ? sanitize_key( $get_data['tab'] ) : 'staff_list';
		$section  = isset( $get_data['tab'] ) ? sanitize_key( $get_data['tab'] ) : 'staff_list';

		require_once( plugin_dir_path( __FILE__ ) . '/../settings-functions.inc.php' );

		$all_field_callbacks = array(
			'text'       => 'display_input_field',
			'checkboxes' => 'display_checkboxes',
			'select'     => 'display_select',
			'link'       => 'display_link',
		);

		$this->staff_list( 'staff_list', 'staff_list', $all_field_callbacks );
		$this->page_settings( 'page_settings', 'page_settings', $all_field_callbacks );

	}

	/**
	* Fields for the Staff List tab
	* This runs add_settings_section once, as well as add_settings_field and register_setting methods for each option
	*
	* @param string $page
	* @param string $section
	* @param array $callbacks
	*/
	private function staff_list( $page, $section, $callbacks ) {
		$tabs = $this->tabs;
		foreach ( $tabs as $key => $value ) {
			if ( $key === $page ) {
				$title = $value;
			}
		}
		add_settings_section( $page, $title, null, $page );

		$settings = array(
			'staff_user_role' => array(
				'title'    => __( 'Staff user role', 'staff-user-post-list' ),
				'callback' => $callbacks['select'],
				'page'     => $page,
				'section'  => $section,
				'args'     => array(
					'type'     => 'select',
					'desc'     => '',
					'constant' => '',
					'items'    => $this->get_role_options(),
				),
			),
			/*'post_type'       => array(
				'title'    => __( 'Additional post type', 'staff-user-post-list' ),
				'callback' => $callbacks['select'],
				'page'     => $page,
				'section'  => $section,
				'args'     => array(
					'type'     => 'select',
					'desc'     => '',
					'constant' => '',
					'items'    => $this->get_post_type_options(),
				),
			),
			'post_meta_key'   => array(
				'title'    => __( 'Post meta key', 'staff-user-post-list' ),
				'callback' => $callbacks['text'],
				'page'     => $page,
				'section'  => $section,
				'args'     => array(
					'type'     => 'text',
					'desc'     => '',
					'constant' => '',
				),
			),
			'post_meta_value' => array(
				'title'    => __( 'Post meta value', 'staff-user-post-list' ),
				'callback' => $callbacks['text'],
				'page'     => $page,
				'section'  => $section,
				'args'     => array(
					'type'     => 'text',
					'desc'     => '',
					'constant' => '',
				),
			),*/

		);

		foreach ( $settings as $key => $attributes ) {
			$id       = $this->option_prefix . $key;
			$name     = $this->option_prefix . $key;
			$title    = $attributes['title'];
			$callback = $attributes['callback'];
			$page     = $attributes['page'];
			$section  = $attributes['section'];
			$args     = array_merge(
				$attributes['args'],
				array(
					'title'     => $title,
					'id'        => $id,
					'label_for' => $id,
					'name'      => $name,
				)
			);

			// if there is a constant and it is defined, don't run a validate function if there is one
			if ( isset( $attributes['args']['constant'] ) && defined( $attributes['args']['constant'] ) ) {
				$validate = '';
			}

			add_settings_field( $id, $title, $callback, $page, $section, $args );
			register_setting( $section, $id );
		}
	}

	/**
	* Fields for the Page Settings tab
	* This runs add_settings_section once, as well as add_settings_field and register_setting methods for each option
	*
	* @param string $page
	* @param string $section
	* @param array $callbacks
	*/
	private function page_settings( $page, $section, $callbacks ) {
		$tabs = $this->tabs;
		foreach ( $tabs as $key => $value ) {
			if ( $key === $page ) {
				$title = $value;
			}
		}
		add_settings_section( $page, $title, null, $page );

		$settings = array(
			'image_size'    => array(
				'title'    => __( 'Image size', 'staff-user-post-list' ),
				'callback' => $callbacks['select'],
				'page'     => $page,
				'section'  => $section,
				'args'     => array(
					'type'     => 'select',
					'desc'     => '',
					'constant' => '',
					'items'    => $this->get_image_sizes(),
				),
			),
			'include_bio'   => array(
				'title'    => __( 'Include bio?', 'staff-user-post-list' ),
				'callback' => $callbacks['text'],
				'page'     => $page,
				'section'  => $section,
				'args'     => array(
					'type'     => 'checkbox',
					'desc'     => '',
					'constant' => '',
				),
			),
			'bio_field'     => array(
				'title'    => __( 'Bio field', 'staff-user-post-list' ),
				'callback' => $callbacks['select'],
				'page'     => $page,
				'section'  => $section,
				'args'     => array(
					'type'     => 'select',
					'desc'     => '',
					'constant' => '',
					'items'    => $this->get_staff_fields(),
				),
			),
			'include_name'  => array(
				'title'    => __( 'Include name?', 'staff-user-post-list' ),
				'callback' => $callbacks['text'],
				'page'     => $page,
				'section'  => $section,
				'args'     => array(
					'type'     => 'checkbox',
					'desc'     => '',
					'constant' => '',
				),
			),
			'name_field'    => array(
				'title'    => __( 'Name field', 'staff-user-post-list' ),
				'callback' => $callbacks['select'],
				'page'     => $page,
				'section'  => $section,
				'args'     => array(
					'type'     => 'select',
					'desc'     => '',
					'constant' => '',
					'items'    => $this->get_staff_fields(),
				),
			),
			'include_title' => array(
				'title'    => __( 'Include title?', 'staff-user-post-list' ),
				'callback' => $callbacks['text'],
				'page'     => $page,
				'section'  => $section,
				'args'     => array(
					'type'     => 'checkbox',
					'desc'     => '',
					'constant' => '',
				),
			),
			'title_field'   => array(
				'title'    => __( 'Title field', 'staff-user-post-list' ),
				'callback' => $callbacks['select'],
				'page'     => $page,
				'section'  => $section,
				'args'     => array(
					'type'     => 'select',
					'desc'     => '',
					'constant' => '',
					'items'    => $this->get_staff_fields(),
				),
			),
			'method'        => array(
				'title'    => __( 'Custom theme method name', 'staff-user-post-list' ),
				'callback' => $callbacks['text'],
				'page'     => $page,
				'section'  => $section,
				'args'     => array(
					'type'     => 'text',
					'desc'     => __( 'If you add a method here, it will receive the $id, $image_size, $bio_field, $include_bio, $name_field, $include_name, $title_field, and $include_title values.', 'staff-user-post-list' ),
					'constant' => '',
				),
			),
		);

		foreach ( $settings as $key => $attributes ) {
			$id       = $this->option_prefix . $key;
			$name     = $this->option_prefix . $key;
			$title    = $attributes['title'];
			$callback = $attributes['callback'];
			$page     = $attributes['page'];
			$section  = $attributes['section'];
			$args     = array_merge(
				$attributes['args'],
				array(
					'title'     => $title,
					'id'        => $id,
					'label_for' => $id,
					'name'      => $name,
				)
			);

			// if there is a constant and it is defined, don't run a validate function if there is one
			if ( isset( $attributes['args']['constant'] ) && defined( $attributes['args']['constant'] ) ) {
				$validate = '';
			}

			add_settings_field( $id, $title, $callback, $page, $section, $args );
			register_setting( $section, $id );
		}
	}

	/**
	* WordPress user roles as setting field options
	*
	* @return array $items
	*/
	private function get_role_options() {
		$items = array();
		$roles = get_editable_roles();
		foreach ( $roles as $key => $role ) {
			$items[] = array(
				'value' => $key,
				'text'  => $role['name'],
			);
		}
		return $items;
	}

	/**
	* WordPress post types as setting field options
	*
	* @return array $items
	*/
	private function get_post_type_options() {
		$items = array();
		$types = get_post_types();
		foreach ( $types as $post_type ) {
			$items[] = array(
				'value' => $post_type,
				'text'  => $post_type,
			);
		}
		return $items;
	}

	/**
	* WordPress image sizes as setting field options
	*
	* @return array $items
	*/
	private function get_image_sizes() {
		$items = array();
		$sizes = get_intermediate_image_sizes();
		/*
		 * Remove filter preventing WordPress from reading the sizes, it's meant
		 * to prevent creation of intermediate files, which are not really being used.
		 */
		if ( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV ) {
			remove_filter( 'intermediate_image_sizes', 'wpcom_intermediate_sizes' );
			$sizes = get_intermediate_image_sizes();
			add_filter( 'intermediate_image_sizes', 'wpcom_intermediate_sizes' ); // Re-add the filter.
		}
		foreach ( $sizes as $image_size ) {
			$items[] = array(
				'value' => $image_size,
				'text'  => $image_size,
			);
		}
		return $items;
	}

	/**
	* Fields for the staff type as setting field options
	*
	* @return array $items
	*/
	private function get_staff_fields() {

		$items = array();

		if ( ! isset( $_GET['page'] ) || $this->slug !== $_GET['page'] ) {
			return $items;
		}

		global $wpdb;

		$role   = get_option( $this->option_prefix . 'staff_user_role', '' );
		$select = '';
		if ( '' !== $role ) {
			$select = "SELECT DISTINCT $wpdb->usermeta.meta_key FROM $wpdb->usermeta";
		}

		$post_type = get_option( $this->option_prefix . 'post_type', '' );

		if ( '' !== $post_type ) {
			$select = "SELECT DISTINCT $wpdb->postmeta.meta_key FROM $wpdb->postmeta";
		}

		if ( '' === $select ) {
			return $items;
		}

		$meta = $wpdb->get_results( $select, ARRAY_A );

		foreach ( $meta as $field ) {
			$items[] = array(
				'value' => $field['meta_key'],
				'text'  => $field['meta_key'],
			);
		}
		return $items;
	}

}
