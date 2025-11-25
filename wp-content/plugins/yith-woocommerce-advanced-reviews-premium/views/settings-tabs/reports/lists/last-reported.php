<?php
/**
 * Reports widget "Last Reported" content.
 *
 * @package YITH\AdvancedReviews\Views\SettingsTabs\Reports\Lists
 * @var array $values The values.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

$url = add_query_arg(
	array(
		'post_type'   => YITH_YWAR_Post_Types::REVIEWS,
		'post_status' => 'ywar-reported',
	),
	admin_url( 'edit.php' )
);

?>
<?php foreach ( $values as $review_id ) : ?>
	<?php
	$review       = yith_ywar_get_review( $review_id );
	$full_text    = $review->get_content();
	$short_text   = strlen( $full_text ) > 100 ? substr( $full_text, 0, 100 ) . '(...)' : $full_text;
	$product_name = wc_get_product( $review->get_product_id() )->get_name();
	$info         = sprintf( '%1$s - <a href="%2$s">%3$s</a>', $product_name, get_edit_post_link( $review->get_id() ), esc_html_X( 'Read review', '[Admin panel] read review link text', 'yith-woocommerce-advanced-reviews' ) );
	?>
	<div class="reported-review">
		<div class="content"><?php echo wp_kses_post( $short_text ); ?></div>
		<div class="info">
			<?php
			/* translators: %s product name and link */
			printf( esc_html_x( 'on %s', '[Admin panel] reported review product. Example "on My Product"', 'yith-woocommerce-advanced-reviews' ), wp_kses_post( $info ) );
			?>
		</div>
	</div>
<?php endforeach; ?>
<a class="see-all" href="<?php echo esc_url( $url ); ?>"> <?php echo esc_html_x( 'View all reported reviews', '[Admin panel] view all reported review link text', 'yith-woocommerce-advanced-reviews' ); ?></a>
