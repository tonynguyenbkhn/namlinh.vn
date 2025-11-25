<?php
/**
 * Custom Checklist template
 *
 * @var array $field
 * @package YITH\AdvancedReviews\Views\Panel\Fields
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $value, $placeholder ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'value', 'placeholder' );

?>
<div class="yith-ywar-analytics-terms-div" style="vertical-align: top; margin-bottom: 3px;" id="<?php echo esc_attr( $field_id ); ?>">
	<input type="hidden" id="<?php echo esc_attr( $field_id ); ?>" class="yith-ywar-analytics-terms-values" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
	<span class="yith-ywar-analytics-terms-value-list select2 select2-container select2-container--default">
		<span class="selection">
			<span class="select2-selection select2-selection--multiple" style="min-height: 0!important; font-size: 0;">
				<ul class="select2-selection__rendered">
				</ul>
			</span>
		</span>
		<div class="yith-ywar-analytics-terms-ajax">
			<input style="border:none!important; box-shadow:none!important;" type="text" id="yith-ywar-analytics-terms-new-element-<?php echo esc_attr( $field_id ); ?>" class="yith-ywar-analytics-terms-insert select2-input form-input-tip" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
		</div>
	</span>
</div>
