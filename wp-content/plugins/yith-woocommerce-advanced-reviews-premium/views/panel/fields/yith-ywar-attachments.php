<?php
/**
 * Review attachments template
 *
 * @var array $field
 * @package YITH\AdvancedReviews\Views\Panel\Fields
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

list ( $field_id, $name, $value ) = yith_plugin_fw_extract( $field, 'id', 'name', 'value' );

$attachment_ids = ! empty( $value ) ? array_filter( $value ) : array();

wp_enqueue_media(); // Late enqueue media scripts.
wp_enqueue_script( 'wp-media-utils' );
?>
<div class="yith-ywar-attachments">
	<div class="attachments-list <?php echo( empty( $value ) ? 'empty' : '' ); ?>">
		<?php foreach ( $attachment_ids as $attachment_id ) : ?>
			<div class="single-attachment attachment-<?php echo esc_attr( $attachment_id ); ?>">
				<a target="_blank" href="<?php echo esc_url( get_edit_post_link( $attachment_id ) ); ?>">
					<?php echo wp_get_attachment_image( $attachment_id, array( 80, 80 ), true ); ?>
				</a>
				<span class="delete-button yith-icon-trash yith-plugin-fw__tips" data-item_id="<?php echo esc_attr( $attachment_id ); ?>" data-tip="<?php echo esc_html_x( 'Delete attachment', '[Admin panel] button label', 'yith-woocommerce-advanced-reviews' ); ?>"></span>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="new-attachment yith-plugin-fw__tips" data-tip="<?php echo esc_html_x( 'Add attachment', '[Admin panel] button label', 'yith-woocommerce-advanced-reviews' ); ?>">
		<i class="yith-icon yith-icon-plus"></i>
	</div>
	<input id="<?php echo esc_attr( $field_id ); ?>" class="attachment-values" type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( implode( ',', $value ) ); ?>"/>
</div>
