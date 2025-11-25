<?php
/**
 * Admin View: Custom checklist type
 *
 * @package YITH\RecentlyViewedProducts\Templates\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$deps = '';
if ( ! empty( $value['deps'] ) && is_array( $value['deps'] ) ) {
	if ( empty( $value['deps']['type'] ) ) {
		$value['deps']['type'] = 'hide';
	}

	$deps = "data-dep-target=\"{$value['id']}\" data-dep-id=\"{$value['deps']['id']}\" data-dep-value=\"{$value['deps']['value']}\" data-dep-type=\"{$value['deps']['type']}\"";
}

?>
<tr valign="top" class="yith-plugin-fw-panel-wc-row text" <?php echo $deps; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
	</th>
	<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">

		<div class="ywrvp-checklist-div " style="vertical-align: top; margin-bottom: 3px; <?php echo esc_attr( $value['css'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>">
			<input type="hidden" id="<?php echo esc_attr( $value['id'] ); ?>" class="ywrvp-values" name="<?php echo esc_attr( $value['id'] ); ?>" value="<?php echo esc_html( $option_value ); ?>"/>

			<span class="ywrvp-value-list select2 select2-container select2-container--default">
				<span class="selection">
					<span class="select2-selection select2-selection--multiple">
						<ul class="select2-selection__rendered">

						</ul>
					</span>
				</span>
				<div class="ywrvp-checklist-ajax">
					<input type="text" id="ywrvp-new-element-<?php echo esc_attr( $value['id'] ); ?>" class="ywrvp-insert select2-input form-input-tip" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>" style="border: none;"/>
				</div>
			</span>
		</div>
		<span class="description"><?php echo esc_html( $value['desc'] ); ?></span>

	</td>
</tr>
