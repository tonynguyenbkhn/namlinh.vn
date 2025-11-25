<?php
/**
 * The template for displaying warranty options.
 *
 * @package WooCommerce_Warranty\Templates
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once WOOCOMMERCE_WARRANTY_ABSPATH . 'includes/class-warranty-list-table.php';
?>
<div class="wrap woocommerce">
	<style type="text/css">
		table.toplevel_page_warranties #status { width: 200px; }
		.wc-updated {width: 95%; margin: 5px 0 15px; background-color: #ffffe0; border-color: #e6db55; padding: 0 .6em; -webkit-border-radius: 3px; border-radius: 3px; border-width: 1px; border-style: solid;}
		.wc-updated p {margin: .5em 0 !important; padding: 2px;}
		#tiptip_holder #tiptip_content { max-width: 350px; }
		.inline-edit-col h4 {margin-top: 15px;}
	</style>
	<h2><?php esc_html_e( 'RMA Requests', 'woocommerce-warranty' ); ?></h2>
<?php
$get_data = warranty_request_get_data();
if ( isset( $get_data['updated'] ) ) {
	echo '<div class="updated"><p>' . esc_html( $get_data['updated'] ) . '</p></div>';
}
$warranty_table = new Warranty_List_Table();
$warranty_table->prepare_items();
?>
	<form action="admin.php" method="get" style="margin-top: 20px;">
		<input type="hidden" name="page" value="warranties" />

		<p class="search-box">
			<label class="screen-reader-text" for="search"><?php esc_html_e( 'Search', 'woocommerce-warranty' ); ?>:</label>
			<input type="search" id="search" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_html_e( 'RMA Number, Order ID, or Name', 'woocommerce-warranty' ); ?>" />
			<?php submit_button( __( 'Search', 'woocommerce-warranty' ), 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
		</p>
	</form>

	<?php $warranty_table->display(); ?>
</div>
