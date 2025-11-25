<?php
/**
 * Rating stars template
 *
 * @var array $field
 * @package YITH\AdvancedReviews\Views\Panel\Fields
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $value, $custom_attributes, $data ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'value', 'custom_attributes', 'data' );
?>

<div id="<?php echo esc_attr( $field_id ); ?>-div" class="yith-ywar-rating-wrapper wrapper <?php echo esc_attr( $class ); ?>" data-val="<?php echo esc_attr( $value ); ?>" <?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?> <?php yith_plugin_fw_html_data_to_string( $data, true ); ?>>
	<div class="stars<?php echo( 0 !== (int) $value ? ' selected' : '' ); ?>">
		<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
			<span data-value="<?php echo esc_attr( $i ); ?>" class="star-<?php echo esc_attr( $i ); ?><?php echo( $i === (int) $value ? ' active' : '' ); ?>"></span>
		<?php endfor; ?>
	</div>
	<input id="<?php echo esc_attr( $field_id ); ?>" class="rating-value" type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
</div>
