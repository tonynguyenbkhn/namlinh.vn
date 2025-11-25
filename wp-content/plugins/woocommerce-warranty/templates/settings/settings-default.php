<?php
/**
 * The template for displaying warranty options.
 *
 * @package WooCommerce_Warranty\Templates
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="warranty_settings_default">

	<?php WC_Admin_Settings::output_fields( $settings['default'] ); ?>

</div>
