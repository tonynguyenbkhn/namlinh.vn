<?php
/**
 * Single product Rating
 *
 * @package YITH\AdvancedReviews\Templates\Loop
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

global $product;

yith_ywar_get_rating_html( $product );
