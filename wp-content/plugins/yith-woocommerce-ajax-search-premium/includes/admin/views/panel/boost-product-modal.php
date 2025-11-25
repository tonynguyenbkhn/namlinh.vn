<?php
/**
 * This file is the template to configure the content of the modal to boost products
 *
 * @package YITH WooCommerce Ajax Search
 * @since   2.1.0
 * @author  YITH <plugins@yithemes.com>
 *
 * @var $products ;
 */

$products = $products ?? wc_get_products(
	array(
		'limit'  => 20,
		'status' => 'publish',
	)
);

?>

<div id="ywcas-modal-content" style="display:none">
	<form id="ywcas-boost-product-form">
		<div class="ywcas-field-wrapper">
			<?php

			yith_plugin_fw_get_field(
				array(
					'id'                => 'ywcas-boost-product-search',
					'type'              => 'text',
					'custom_attributes' => array(
						'placeholder' => __( 'Search a product', 'yith-woocommerce-ajax-search' ),
					),
				),
				true
			);
			?>
		</div>

		<div class="ywcas-boost-product-search-results ywcas-field-wrapper">
			<div class="ywcas-boost-product-search-results-wrapper">
				<?php
				foreach ( $products as $product ) :
					$post_thumbnail_id = $product->get_image_id();
					$img_src           = $post_thumbnail_id ? wc_get_gallery_image_html( $post_thumbnail_id, true ) : wc_placeholder_img_src( 'woocommerce_single' );
					if ( $post_thumbnail_id ) {
						$html = wp_get_attachment_image( $post_thumbnail_id, array( 50, 50 ) );
					} else {
						$html = sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
					}
					?>
					<div class="ywcas-boost-product" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
						<?php
						yith_plugin_fw_get_field(
							array(
								'id'    => 'ywcas-boost-product-checked',
								'name'  => 'ywcas-boost-product-checked[' . $product->get_id() . ']',
								'class' => 'ywcas-boost-product-checked',
								'type'  => 'checkbox',
							),
							true
						);
						?>
						<div class="ywcas-boost-product-thumb"> <?php echo( $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<div class="ywcas-boost-product-name"><?php echo esc_html( $product->get_title() ); ?></div>
					</div>

				<?php endforeach; ?>
			</div>

		</div>
		<small class="boost-product-error"><?php esc_html_e( 'Please, select a product.', 'yith-woocommerce-ajax-search' ); ?></small>
		<div class="ywcas-field-wrapper ywcas-boost-value-wrapper">
			<label for="ywcas-boost-value"><?php esc_html_e( 'Boost value:', 'yith-woocommerce-ajax-search' ); ?></label>
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'    => 'ywcas-boost-value',
					'name'  => 'ywcas-boost-value',
					'type'  => 'number',
					'min'   => 0.1,
					'max'   => 50,
					'step'  => 0.1,
					'value' => 2,
				),
				true
			);
			?>
			<small class="description"><?php esc_html_e( 'Enter a value (min 0.1 - max 50) to use as multiplier for search scores.', 'yith-woocommerce-ajax-search' ); ?></small>
		</div>

		<button id="ywcas-add-boost-product" class="button-primary" style="float:right"><?php esc_html_e( 'Add', 'yith-woocommerce-ajax-search' ); ?> </button>
	</form>
</div>
