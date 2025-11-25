<?php
/**
 * This file manage the Boost rule condition
 *
 * @package YITH\Search\Views
 * @since 3.0.0
 *
 * @var array $field The field.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$values = ! empty( $field['value'] ) && is_array( $field['value'] ) ? $field['value'] : array(
	array(
		'condition_config' => array(
			'condition_for' => 'product_cat',
		),
		'condition_type'   => 'is',
		'product_cat'      => '',
	),
);
?>
<div class="yith-plugin-fw-field-wrapper ywcas-boost-conditions-field-wrapper">
	<div class="ywcas-boost-conditions-list">
		<?php
		$index = 0;
		foreach ( $values as $value ) {
			$show_trash = $index > 0;
			include 'ywcas-boost-condition.php';
			$index ++;
		}
		?>
	</div>
	<div class="ywcas-add-new-boost-condition">
		<a href="#"><?php esc_html_e( '+ Add condition', 'yith-woocommerce-ajax-search' ); ?></a>
	</div>
</div>
