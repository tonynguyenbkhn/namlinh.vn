<?php
/**
 * Show the single condition field
 *
 * @package YITH\Search\Views
 *
 * @var array $field The field.
 * @var int   $index The index.
 * @var array $value The single condition value.
 * @var bool  $show_trash Show the trash icon.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = ywcas()->settings->get_boost_condition_fields();
?>
<div class="ywcas-boost-condition-wrapper">
		<?php
		foreach ( $options as $option_key => $option ) :

			$field_value     = ! empty( $value[ $option_key ] ) ? $value[ $option_key ] : $option['default'];
			$data            = ! empty( $option['data']['ywcas-conditions-deps'] ) ? array( 'ywcas-conditions-deps' => $option['data']['ywcas-conditions-deps'] ) : array();
			$option['value'] = $field_value;
			$option['name']  = $field['name'] . '[' . $index . '][' . $option_key . ']';
			$option['id']    = $field['name'] . '_' . $index . '_' . $option_key;
			unset( $option['default'] );
			unset( $option['label'] );
			unset( $option['desc'] );
			unset( $option['data']['ywcas-conditions-deps'] );

			?>
			<div class="ywcas-boost-condition-row <?php echo esc_attr( $option_key ); ?>" <?php echo yith_plugin_fw_html_data_to_string( $data ); ?>>
				<?php yith_plugin_fw_get_field( $option, true ); ?>
			</div>
			<?php
		endforeach;
		?>
	<?php
	if ( $show_trash ) {
		yith_plugin_fw_get_component(
			array(
				'type'  => 'action-button',
				'icon'  => 'trash',
				'class' => 'ywcas-delete-condition',
				'title' => __( 'Delete condition', 'yith-woocommerce-ajax-search' ),
			)
		);
	}
	?>
</div>
