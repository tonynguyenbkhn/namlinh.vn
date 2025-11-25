<?php
/**
 * Error management Function
 *
 * @package YITH\AdvancedReviews
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Should I show noticed for deprecated functions/hooks?
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_show_deprecated_notices(): bool {
	return ! ! apply_filters( 'yith_ywar_show_deprecated_notices', true );
}

/**
 * Should I show noticed for deprecated functions/hooks?
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_debug_errors_mode(): string {
	$available_modes = array( 'trigger_error', 'wc_logger', 'error_log' );
	$mode            = 'trigger_error';
	$conditions      = array(
		'is_ajax'             => is_ajax(),
		'is_rest_api_request' => ( is_callable( array( wc(), 'is_rest_api_request' ) ) && wc()->is_rest_api_request() ),
	);

	if ( $conditions['is_ajax'] || $conditions['is_rest_api_request'] ) {
		$mode = 'error_log';
	}

	$filtered_mode = apply_filters( 'yith_ywar_debug_errors_mode', $mode, $conditions );
	if ( in_array( $filtered_mode, $available_modes, true ) ) {
		$mode = $filtered_mode;
	}

	return $mode;
}

/**
 * Should I show noticed for deprecated functions/hooks?
 *
 * @param string $message The message to be shown.
 * @param string $mode    The debug errors mode.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_debug_errors_trigger( string $message, $mode = false ) {
	$mode = ! ! $mode ? $mode : yith_ywar_debug_errors_mode();

	switch ( $mode ) {
		case 'wc_logger':
			wc_get_logger()->error( $message, array( 'source' => 'yith-ywar-debug-errors' ) );
			break;
		case 'error_log':
		default:
			error_log( $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			break;
	}
}

/**
 * Wrapper for deprecated functions, so we can apply some extra logic.
 *
 * @param string $func        Function used.
 * @param string $version     Version the message was added in.
 * @param string $replacement Replacement for the called function.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_deprecated_function( string $func, string $version, string $replacement = '' ) {
	if ( yith_ywar_show_deprecated_notices() ) {
		$backtrace   = ' Backtrace: ' . wp_debug_backtrace_summary(); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_wp_debug_backtrace_summary
		$errors_mode = yith_ywar_debug_errors_mode();

		if ( 'trigger_error' === $errors_mode ) {
			_deprecated_function( $func, $version, $replacement ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			do_action( 'deprecated_function_run', $func, $replacement, $version );
			$log_string = "The $func function is deprecated since version $version.";

			$log_string .= $replacement ? " Replace with $replacement." : '';
			$log_string .= $backtrace;

			yith_ywar_debug_errors_trigger( $log_string, $errors_mode );
		}
	}
}

/**
 * Wrapper for deprecated hook so we can apply some extra logic.
 *
 * @param string $hook        The hook that was used.
 * @param string $version     The plugin version that deprecated the hook.
 * @param string $replacement The hook that should have been used.
 * @param string $message     A message regarding the change.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_deprecated_hook( string $hook, string $version, string $replacement = '', string $message = '' ) {
	if ( yith_ywar_show_deprecated_notices() ) {
		$backtrace   = ' Backtrace: ' . wp_debug_backtrace_summary(); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_wp_debug_backtrace_summary
		$errors_mode = yith_ywar_debug_errors_mode();

		if ( 'trigger_error' === $errors_mode ) {
			_deprecated_hook( $hook, $version, $replacement, $message ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			do_action( 'deprecated_hook_run', $hook, $replacement, $version, $message );

			$message    = empty( $message ) ? '' : ' ' . $message;
			$log_string = "$hook is deprecated since version $version";

			$log_string .= $replacement ? "! Use $replacement instead." : ' with no alternative available.';
			$log_string .= $message;
			$log_string .= $backtrace;

			yith_ywar_debug_errors_trigger( $log_string, $errors_mode );
		}
	}
}

/**
 * Wrapper for deprecated filter hook so we can apply some extra logic.
 *
 * @param string $hook        The hook that was used.
 * @param string $version     The plugin version that deprecated the hook.
 * @param string $replacement The hook that should have been used.
 * @param string $message     A message regarding the change.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_deprecated_filter( string $hook, string $version, string $replacement = '', string $message = '' ) {
	if ( has_filter( $hook ) ) {
		yith_ywar_deprecated_hook( $hook . ' filter', $version, $replacement, $message );
	}
}

/**
 * Wrapper for deprecated action hook so we can apply some extra logic.
 *
 * @param string $hook        The hook that was used.
 * @param string $version     The plugin version that deprecated the hook.
 * @param string $replacement The hook that should have been used.
 * @param string $message     A message regarding the change.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_deprecated_action( string $hook, string $version, string $replacement = '', string $message = '' ) {
	if ( has_action( $hook ) ) {
		yith_ywar_deprecated_hook( $hook . ' action', $version, $replacement, $message );
	}
}

/**
 * Fires a deprecated action, printing a notice, only if used.
 *
 * @param string $hook        The name of the action hook.
 * @param array  $args        Function arguments to be passed to do_action().
 * @param string $version     The plugin version that deprecated the hook.
 * @param string $replacement The hook that should have been used.
 * @param string $message     A message regarding the change.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_do_deprecated_action( string $hook, array $args, string $version, string $replacement = '', string $message = '' ) {
	if ( ! has_action( $hook ) ) {
		return;
	}

	yith_ywar_deprecated_hook( $hook . ' action', $version, $replacement, $message );
	do_action_ref_array( $hook, $args );
}

/**
 * Wrapper for _doing_it_wrong().
 *
 * @param string $func    Function used.
 * @param string $message Message to log.
 * @param string $version Version the message was added in.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_doing_it_wrong( string $func, string $message, string $version ) {
	$message .= ' Backtrace: ' . wp_debug_backtrace_summary(); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_wp_debug_backtrace_summary

	$errors_mode = yith_ywar_debug_errors_mode();

	if ( 'trigger_error' === $errors_mode ) {
		_doing_it_wrong( $func, $message, $version ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		do_action( 'doing_it_wrong_run', $func, $message, $version );

		$log_string = "$func was called incorrectly. $message. This message was added in version $version.";

		yith_ywar_debug_errors_trigger( $log_string, $errors_mode );
	}
}

/**
 * Trigger an error.
 *
 * @param string $message Message to log.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_error( string $message ) {
	yith_ywar_debug_errors_trigger( $message );
}

/**
 * Deprecated value
 *
 * @deprecated 2.0.0
 */
! defined( 'YITH_YWAR_POST_TYPE' ) && define( 'YITH_YWAR_POST_TYPE', YITH_YWAR_Post_Types::REVIEWS );
