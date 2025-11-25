<?php
/**
 * Reports widget "Most active users" content.
 *
 * @package YITH\AdvancedReviews\Views\SettingsTabs\Reports\Lists
 * @var array $values The values.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<?php foreach ( $values as $user_data ) : ?>
	<?php
	$user   = get_user_by( 'email', $user_data['email'] );
	$avatar = get_avatar_data( $user_data['email'] )['url'];

	if ( yith_ywar_customize_my_account_enabled() ) {
		preg_match( "/src='([^']+)'/mi", get_avatar( $user_data['email'] ), $match );
		$avatar = html_entity_decode( array_pop( $match ) );
	} else {
		$avatar = get_avatar_data( $user_data['email'] )['url'];
	}

	$profile_url = ! $user ? '#' : get_edit_profile_url( $user->ID );
	$name        = ! $user ? sprintf( '%1$s<br /><small>(%2$s)</small>', esc_html_x( 'Guest user', '[Admin panel] label to use when the user is not registered', 'yith-woocommerce-advanced-reviews' ), $user_data['email'] ) : sprintf( '%1$s %2$s', $user->first_name, $user->last_name );
	?>
	<a class="user-item" href="<?php echo esc_url( $profile_url ); ?>" target="_blank">
		<div class="picture">
			<img src="<?php echo esc_url( $avatar ); ?>"/>
		</div>
		<div class="title">
			<span class="user-name"><?php echo wp_kses_post( $name ); ?></span>
			<span class="review-count">
				<?php
				/* translators: %s number of reviews */
				echo wp_kses_post( sprintf( _nx( '%d review', '%d reviews', absint( $user_data['total'] ), '[Global] Review counter', 'yith-woocommerce-advanced-reviews' ), absint( $user_data['total'] ) ) );
				?>
			</span>
		</div>
	</a>
<?php endforeach; ?>
