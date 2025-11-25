<?php
/**
 * Rating stars template
 *
 * @var array $field
 * @package YITH\AdvancedReviews\Views\Panel\Fields
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $value, $custom_attributes, $data, $criteria ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'value', 'custom_attributes', 'data', 'criteria' );
?>

<div id="<?php echo esc_attr( $field_id ); ?>-div" class="yith-ywar-multi-rating-wrapper wrapper <?php echo esc_attr( $class ); ?>" <?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?> <?php yith_plugin_fw_html_data_to_string( $data, true ); ?>>
	<?php foreach ( $criteria as $criterion_id ) : ?>
		<?php
		$criterion = get_term_by( 'term_id', $criterion_id, YITH_YWAR_Post_Types::CRITERIA_TAX );
		$rating    = array(
			'id'    => $field_id . '_' . $criterion->slug,
			'name'  => $name . "[$criterion->term_id]",
			'type'  => 'yith-ywar-rating',
			'value' => isset( $value[ $criterion_id ] ) ? $value[ $criterion_id ] : '',
		);
		?>
		<label for="<?php echo esc_attr( $field_id . '_' . $criterion->slug ); ?>-div"> <?php echo esc_html( $criterion->name ); ?></label>
		<?php yith_plugin_fw_get_field( $rating, true, true ); ?>
	<?php endforeach; ?>
</div>
