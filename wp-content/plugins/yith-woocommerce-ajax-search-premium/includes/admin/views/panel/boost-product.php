<?php
/**
 * This file is the template to configure the content of the modal to boost products
 *
 * @package YITH WooCommerce Ajax Search
 * @since   2.1.0
 * @author  YITH <plugins@yithemes.com>
 * @var $show_notice
 */

$list_table = new YITH_WCAS_Admin_Boost_Product_List_Table();
$class      = '';
$message    = __( '1 rule permanently deleted.', 'yith-woocommerce-ajax-search' );
if ( isset( $_REQUEST['action'], $_REQUEST['boost'] ) && 'delete' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) {
	$class = 'show yith-plugin-fw-animate__appear-from-top';
	$boost = wp_unslash( $_REQUEST['boost'] );
	if ( count( $boost ) > 1 ) {
		$message = sprintf( __( '%d rules permanently deleted.', 'yith-woocommerce-ajax-search' ), count( $boost ) );
	}
}
?>
<div class="ywcas-boost-detail yith-plugin-ui--boxed-wp-list-style ywcas-boost-product-wrapper">
	<?php
	echo '<div class="yith-plugin-fw-wp-page-wrapper"><div class="wrap"> <div id="message" class="notice is-dismissible updated inline yith-plugin-fw-animate__appear-from-top ' . $class . '">
					<p>' . esc_html( $message ) . '</p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice.', 'yith-woocommerce-ajax-search' ) . '</span></button></div></div></div>';
	?>
    <form id="ywcas-boost-product-list-form" method="post">
		<?php
		$list_table->get_bulk_actions();
		$list_table->current_action();
		$list_table->prepare_items();
		$list_table->search_box( __( 'Search product', 'yith-woocommerce-ajax-search' ), 'ywcas-bosted-products-s' );
		$list_table->display();
		?>
    </form>
</div>
