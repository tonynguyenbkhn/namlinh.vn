<?php
/**
 * Multiple Singleton class trait.
 * Allows creating one different singleton for each called_class,
 * without needs of re-declaring the $instance property of the class
 * (as for YITH_YWAR_Trait_Singleton).
 *
 * @package YITH\AdvancedReviews\Traits
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * YITH_YWAR_Trait_Multiple_Singleton trait.
 *
 * @since   2.0.0
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\AdvancedReviews\Traits
 */
trait YITH_YWAR_Trait_Multiple_Singleton {
	/**
	 * The instances of the classes.
	 *
	 * @var self
	 */
	private static $instances = array();

	/**
	 * Constructor
	 *
	 * @return void
	 * @since  2.0.0
	 */
	protected function __construct() {
	}

	/**
	 * Get class instance.
	 *
	 * @return self
	 * @since  2.0.0
	 */
	final public static function get_instance() {
		self::$instances[ static::class ] = self::$instances[ static::class ] ?? new static();

		return self::$instances[ static::class ];
	}

	/**
	 * Prevent cloning.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	private function __clone() {
	}

	/**
	 * Prevent un-serializing.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function __wakeup() {
		yith_ywar_doing_it_wrong( get_called_class() . '::' . __FUNCTION__, 'Unserializing instances of this class is forbidden.', '3.0' );
	}
}
