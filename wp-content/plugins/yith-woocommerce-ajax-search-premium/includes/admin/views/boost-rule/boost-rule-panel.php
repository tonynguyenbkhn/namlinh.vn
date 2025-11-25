<?php
/**
 * Show the template for the boost rule config
 *
 * @package YITH WooCommerce Ajax Search
 * @since   3.0.0
 * @author  YITH <plugins@yithemes.com>
 *
 * @var array $data The data config.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = ywcas()->settings->get_boost_rule_panel_options();

?>
<form>
	<div id="ywcas-boost-rule-panel">

		<?php
		foreach ( $options as $key => $option ) :

			$value           = $data[ $key ] ?? $option['default'];
			$label           = ! empty( $option['label'] ) ? $option['label'] : '';
			$desc            = ! empty( $option['desc'] ) ? $option['desc'] : '';
			$data_deps       = ! empty( $option['data'] ) ? $option['data'] : array();
			$option['value'] = $value;
			$option['name']  = 'boost_rule[' . $key . ']';
			$option['id']    = $key;
			$required        = isset( $option['required'] ) && $option['required'];
			$required_class  = $required ? 'yith-plugin-fw--required' : '';
			unset( $option['default'] );
			unset( $option['label'] );
			unset( $option['desc'] );
			unset( $option['data'] );

			?>
			<div class="ywcas-boost-rule-row <?php echo esc_attr( $required_class ); ?>" <?php echo yith_plugin_fw_html_data_to_string( $data_deps ); ?> >
				<div class="ywcas-boost-rule-label">
					<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
				</div>
				<div class="ywcas-boost-rule-field">
					<?php yith_plugin_fw_get_field( $option, true ); ?>
					<?php
					if ( ! empty( $desc ) ) :
						?>
						<div class="ywcas-boost-rule-desc"><?php echo wp_kses_post( $desc ); ?></div>
						<?php
					endif;
					?>
				</div>
			</div>

			<?php
		endforeach;
		?>
		<input type="hidden" id="post_ID" name="boost_rule[id]" value="<?php echo esc_attr( $data['id'] ); ?>">
		<?php echo $data['id'] > 0 ? wp_nonce_field( 'update-post_' . $data['id'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( $data['id'] > 0 && ! empty( get_post_meta( $data['id'], '_edit_lock', true ) ) ) { ?>
			<input type="hidden" id="active_post_lock" value="<?php echo esc_attr( get_post_meta( $data['id'], '_edit_lock', true ) ); ?>"/>
			<?php
		}
		?>
		<div class="ywcas-boost-action">
			<button class="yith-plugin-fw__button--primary yith-plugin-fw__button--xxl"><?php esc_html_e( 'Save rule', 'yith-woocommerce-ajax-search' ); ?></button>
		</div>
	</div>
</form>
