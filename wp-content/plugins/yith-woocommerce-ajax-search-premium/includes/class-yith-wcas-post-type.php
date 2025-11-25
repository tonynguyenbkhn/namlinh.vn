<?php
/**
 * This class manage the post type registration
 *
 * @package YITH/Search
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The class manage the boost rule post type
 */
class YITH_WCAS_Post_Type {
	use YITH_WCAS_Trait_Singleton;


	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ), 10 );
		add_action( 'plugins_loaded', array( $this, 'load_custom_table' ), 20 );
		add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_post_updated_messages' ), 10, 2 );
	}


	/**
	 * Register the custom post type
	 *
	 * @return void
	 */
	public function register_post_type() {
		$name          = _x( 'Boost Rules', 'Post Type General Name', 'yith-woocommerce-ajax-search' );
		$singular_name = _x( 'Boost Rule', 'Post Type General Name', 'yith-woocommerce-ajax-search' );

		$labels = array(
			'name'               => $name,
			'singular_name'      => $singular_name,
			'menu_name'          => $singular_name,
			'parent_item_colon'  => '',
			'all_items'          => __( 'All Boost Rules', 'yith-woocommerce-ajax-search' ),
			'view_item'          => __( 'View Boost Rule', 'yith-woocommerce-ajax-search' ),
			'add_new_item'       => __( 'Create rule', 'yith-woocommerce-ajax-search' ),
			'add_new'            => __( 'Create rule', 'yith-woocommerce-ajax-search' ),
			'edit_item'          => $singular_name,
			'update_item'        => __( 'Update Boost Rule', 'yith-woocommerce-ajax-search' ),
			'search_items'       => __( 'Search Boost rule', 'yith-woocommerce-ajax-search' ),
			'not_found'          => '',
			'not_found_in_trash' => '',
		);
		$args   = array(
			'label'               => 'ywcas_boost',
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'show_in_menu'        => false,
			'exclude_from_search' => true,
			'capability_type'     => 'post',
		);

		register_post_type( 'ywcas_boost', $args );
	}

	/**
	 * Load the post table
	 *
	 * @since 2.1.0
	 */
	public function load_custom_table() {
		require_once YITH_WCAS_INC . 'admin/class-yith-wcas-admin-boost-rules-list-table.php';
	}

	/**
	 * Specify custom bulk actions messages for different post types.
	 *
	 * @param array $bulk_messages Array of messages.
	 * @param array $bulk_counts   Array of how many objects were updated.
	 *
	 * @return array
	 * @since  2.1.0
	 */
	public function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {
		$bulk_messages['ywcas_boost'] = array(
			/* translators: %s: total boost rule deleted */
			'deleted'   => _n( '%s boost rule permanently deleted.', '%s boost rules permanently deleted.', $bulk_counts['deleted'], 'yith-woocommerce-ajax-search' ),
		);

		return $bulk_messages;
	}
}

YITH_WCAS_Post_Type::get_instance();
