<?php
/**
 * Shortcode tab template
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\RecentlyViewedProducts\Templates\Admin
 * @version 1.0.0
 * @var array $shortcode
 */

defined( 'YITH_WRVP' ) || exit;

?>

<div class="yith-plugin-fw yit-admin-panel-container">
	<div class="yit-admin-panel-content-wrap">
		<div id="plugin-fw-wc" class="yith-wrvp-shortcode-tab">
			<h2><?php esc_html_e( 'Build your own shortcode', 'yith-woocommerce-recently-viewed-products' ); ?></h2>
			<?php foreach ( $shortcode as $shortcode_key => $data ) : ?>
				<table class="form-table">
					<tbody>
					<?php
					foreach ( $data['attributes'] as $field_id => $field ) :
						$field['id']    = $field_id;
						$field['name']  = $field_id;
						$field['value'] = isset( $field['default'] ) ? $field['default'] : '';

						require YIT_CORE_PLUGIN_TEMPLATE_PATH . '/panel/woocommerce/woocommerce-option-row.php';

					endforeach;
					?>
					</tbody>
				</table>

				<h4 class="shortcode-preview-title"><?php esc_html_e( 'Copy and paste this shortcode in your page', 'yith-woocommerce-recently-viewed-products' ); ?></h4>
				<div class="shortcode-preview">
					<?php echo '[' . esc_attr( $shortcode_key ) . ']'; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
