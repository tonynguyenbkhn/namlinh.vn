<?php
/**
 * Trait that implements singleton behaviour on a class
 *
 * @package YITH\AdvancedReviews\Traits
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * YITH_YWAR_Trait_Singleton trait.
 *
 * @since   2.0.0
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\AdvancedReviews\Traits
 */
trait YITH_YWAR_Trait_Singleton {

	/**
	 * Single instance of the class
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Returns single instance of the class
	 *
	 * @return self
	 * @since  2.0.0
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}
}
