<?php
/**
 * Show the template for the boost rule config
 *
 * @package YITH WooCommerce Ajax Search
 * @since   3.0.0
 * @author  YITH <plugins@yithemes.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script type="text/template" id="tmpl-ywcas-boost-condition">
	<?php
	$field      = array(
		'name' => 'boost_rule[conditions]',
	);
	$index      = '{{{data.index}}}';
	$show_trash = true;
	require YITH_WCAS_INC . 'admin/views/custom-fields/types/boost-conditions/ywcas-boost-condition.php';
	?>
</script>
