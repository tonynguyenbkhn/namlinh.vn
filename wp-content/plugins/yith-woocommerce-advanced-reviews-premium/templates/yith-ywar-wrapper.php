<?php
/**
 * Review wrapper
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/yith-ywar-wrapper.php.
 *
 * @package YITH\AdvancedReviews\Templates
 * @var WC_Product           $product    The current product.
 * @var YITH_YWAR_Review_Box $review_box The current review box.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * DO_ACTION: yith_ywar_before_reviews_container
 *
 * Adds an action in the review template before the content div.
 *
 * @param WC_Product           $product    The current product.
 * @param YITH_YWAR_Review_Box $review_box The current review box.
 */
do_action( 'yith_ywar_before_reviews_container', $product, $review_box );
?>
	<div id="yith-ywar-main-wrapper-<?php echo esc_attr( $product->get_id() ); ?>" class="yith-ywar-main-wrapper" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" data-review-box="<?php echo esc_attr( $review_box->get_id() ); ?>">
		<?php
		/**
		 * DO_ACTION: yith_ywar_before_reviews
		 *
		 * Adds an action in the review template just inside of the content div.
		 *
		 * @param WC_Product           $product    The current product.
		 * @param YITH_YWAR_Review_Box $review_box The current review box.
		 */
		do_action( 'yith_ywar_before_reviews', $product, $review_box );
		?>
		<div class="yith-ywar-reviews-wrapper">
			<?php
			/**
			 * DO_ACTION: yith_ywar_before_reviews_list
			 *
			 * Adds an action just before the review list.
			 *
			 * @param WC_Product           $product    The current product.
			 * @param YITH_YWAR_Review_Box $review_box The current review box.
			 */
			do_action( 'yith_ywar_before_reviews_list', $product, $review_box );
			?>
			<div class="yith-ywar-reviews-list"></div>
			<?php
			/**
			 * DO_ACTION: yith_ywar_after_reviews_list
			 *
			 * Adds an action just after the review list.
			 *
			 * @param WC_Product           $product    The current product.
			 * @param YITH_YWAR_Review_Box $review_box The current review box.
			 */
			do_action( 'yith_ywar_after_reviews_list', $product, $review_box );
			?>
		</div>
		<?php
		/**
		 * DO_ACTION: yith_ywar_after_reviews
		 *
		 * Adds an action in the review template before the end of the content div.
		 *
		 * @param WC_Product           $product    The current product.
		 * @param YITH_YWAR_Review_Box $review_box The current review box.
		 */
		do_action( 'yith_ywar_after_reviews', $product, $review_box );
		?>
	</div>
<?php
/**
 * DO_ACTION: yith_ywar_after_reviews_container
 *
 * Adds an action in the review template after the content div.
 *
 * @param WC_Product           $product    The current product.
 * @param YITH_YWAR_Review_Box $review_box The current review box.
 */
do_action( 'yith_ywar_after_reviews_container', $product, $review_box );
