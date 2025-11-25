<?php
/**
 * General Function
 *
 * @package YITH\AdvancedReviews
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Print a view
 *
 * @param string $view The view.
 * @param array  $args Arguments.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_get_view( string $view, array $args = array() ) {

	$view_path = trailingslashit( YITH_YWAR_VIEWS_PATH ) . $view;

	/**
	 * APPLY_FILTERS: yith_ywar_view_path
	 *
	 * Allows 3rd party to override the files in the Views folder.
	 *
	 * @param string $view_path The view absolute path.
	 * @param string $view      The view path.
	 *
	 * @return string
	 */
	$view_path = apply_filters( 'yith_ywar_view_path', $view_path, $view );

	extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
	if ( file_exists( $view_path ) ) {
		include $view_path;
	}
}

/**
 * Is admin page?
 *
 * @param array|string|bool $options The options.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_is_admin_page( $options ): bool {
	$screen_id = yith_ywar_get_current_screen_id();
	$is_page   = false;

	if ( $screen_id ) {

		if ( true === $options ) {
			$is_page = true;
		} else {
			$options = (array) $options;

			foreach ( $options as $option ) {
				$parts   = explode( '/', $option );
				$id      = $parts[0] ?? false;
				$tab     = $parts[1] ?? false;
				$sub_tab = $parts[2] ?? false;

				switch ( $id ) {
					case 'all-plugin-pages':
						$is_page = yith_ywar_is_admin_page( array_merge( yith_ywar_admin_screen_ids(), array( 'panel' ) ) );
						break;
					case 'panel':
						if ( strpos( $screen_id, 'page_' . YITH_YWAR_Admin::PANEL_PAGE ) > 0 ) {
							if ( ! ! $tab ) {
								$is_page = isset( $_GET['tab'] ) && $_GET['tab'] === $tab; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
								if ( ! ! $sub_tab ) {
									$is_page = $is_page && isset( $_GET['sub_tab'] ) && "$tab-$sub_tab" === $_GET['sub_tab']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
								}
							} else {
								$is_page = true;
							}
						}
						break;
					default:
						$is_page = $screen_id === $id;
						break;
				}

				if ( $is_page ) {
					break;
				}
			}
		}
	}

	return $is_page;
}

/**
 * Retrieve the current screen ID.
 *
 * @return string|false
 * @since  2.0.0
 */
function yith_ywar_get_current_screen_id() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

	return ! ! $screen && is_a( $screen, 'WP_Screen' ) ? $screen->id : false;
}

/**
 * Retrieve a module path.
 *
 * @param string $module_key The module key.
 * @param string $path       The path.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_get_module_path( string $module_key, string $path ): string {
	return YITH_YWAR_Modules::get_module_path( $module_key, $path );
}

/**
 * Retrieve a module path.
 *
 * @param string $module_key The module key.
 * @param string $url        The URL.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_get_module_url( string $module_key, string $url ): string {
	return YITH_YWAR_Modules::get_module_url( $module_key, $url );
}

/**
 * Get capabilities.
 *
 * @param string $type        The type of capabilities: post | tax | single.
 * @param string $object_type The object type: you can use the post_type name, the taxonomy name, or the single cap.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_capabilities( string $type, string $object_type ): array {
	$caps = array();
	switch ( $type ) {
		case 'post':
			$caps = array(
				'edit_post'              => "edit_$object_type",
				'delete_post'            => "delete_$object_type",
				'edit_posts'             => "edit_{$object_type}s",
				'edit_others_posts'      => "edit_others_{$object_type}s",
				'publish_posts'          => "publish_{$object_type}s",
				'read_private_posts'     => "read_private_{$object_type}s",
				'delete_posts'           => "delete_{$object_type}s",
				'delete_private_posts'   => "delete_private_{$object_type}s",
				'delete_published_posts' => "delete_published_{$object_type}s",
				'delete_others_posts'    => "delete_others_{$object_type}s",
				'edit_private_posts'     => "edit_private_{$object_type}s",
				'edit_published_posts'   => "edit_published_{$object_type}s",
				'create_posts'           => "create_{$object_type}s",
			);

			break;
		case 'single':
			$caps = array( $object_type );
	}

	return $caps;
}

/**
 * Add capabilities.
 *
 * @param array              $caps  The capabilities to add.
 * @param string|array|false $roles The roles to add the capability. Default: admin and shop_manager.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_add_capabilities( array $caps, $roles = false ) {
	$roles = false === $roles ? array( 'administrator', 'shop_manager' ) : (array) $roles;

	foreach ( $roles as $role ) {
		$the_role = get_role( $role );
		if ( $the_role ) {
			foreach ( $caps as $cap ) {
				$the_role->add_cap( $cap );
			}
		}
	}
}

/**
 * Remove capabilities.
 *
 * @param array              $caps  The capabilities to remove.
 * @param string|array|false $roles The roles to add the capability. Default: admin and shop_manager.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_remove_capabilities( array $caps, $roles = false ) {
	$roles = false === $roles ? array( 'administrator', 'shop_manager' ) : (array) $roles;

	foreach ( $roles as $role ) {
		$the_role = get_role( $role );
		if ( $the_role ) {
			foreach ( $caps as $cap ) {
				$the_role->remove_cap( $cap );
			}
		}
	}
}

/**
 * Return plugin admin screen ids.
 * Useful to enqueue correct styles/scripts in plugin's pages.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_admin_screen_ids(): array {
	return apply_filters( 'yith_ywar_admin_screen_ids', array() );
}

/**
 * Is this module active?
 *
 * @param string $module_key The module key.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_is_module_active( string $module_key ): bool {
	return YITH_YWAR()->modules()->is_module_active( $module_key );
}

/**
 * Send an email through Mandrill.
 *
 * @param string $to          Email to.
 * @param string $subject     Email subject.
 * @param string $message     Email message.
 * @param string $headers     Email headers.
 * @param array  $attachments Email attachments.
 *
 * @return bool
 * @throws \MailchimpTransactional\ApiException Error when sending.
 * @since  2.0.0
 */
function yith_ywar_mandrill_send( string $to, string $subject, string $message, string $headers, array $attachments ): bool {

	if ( ! class_exists( 'MailchimpTransactional' ) ) {
		require_once YITH_YWAR_INCLUDES_DIR . '/third-party/autoload.php';
	}

	$api_key       = yith_ywar_get_option( 'ywar_mandrill_apikey' );
	$from_name     = wp_specialchars_decode( esc_html( get_option( 'woocommerce_email_from_name' ) ), ENT_QUOTES );
	$from_email    = sanitize_email( get_option( 'woocommerce_email_from_address' ) );
	$headers_array = explode( '\r\n', $headers );
	$headers       = array();

	foreach ( $headers_array as $item ) {
		$headers_row                = explode( ': ', $item );
		$headers[ $headers_row[0] ] = $headers_row[1];
	}

	try {
		$mandrill = new MailchimpTransactional\ApiClient();
		$mandrill->setApiKey( $api_key );

		$response = $mandrill->messages->send(
			array(
				'message' => array(
					'html'        => $message,
					'subject'     => $subject,
					'from_email'  => apply_filters( 'wp_mail_from', $from_email ),
					'from_name'   => apply_filters( 'wp_mail_from_name', $from_name ),
					'to'          => array(
						array(
							'email' => $to,
							'type'  => 'to',
						),
					),
					'headers'     => $headers,
					'attachments' => $attachments,
				),
			)
		);

		if ( 'sent' !== $response[0]->status ) {
			throw new \MailchimpTransactional\ApiException( 'Error' );
		}

		return true;
	} catch ( \MailchimpTransactional\ApiException $e ) {
		yith_ywar_error( $e->getMessage() );

		return false;
	}
}

/**
 * Get fictitious data for preview and test emails
 *
 * @param string $email_id The email ID.
 * @param string $email    The email address to use.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_test_values( string $email_id, string $email = '' ): array {

	global $sitepress;

	$lang = $sitepress ? $sitepress->get_current_language() : '';

	$shared = array(
		'user'     => array(
			'customer_name'      => wp_get_current_user()->get( 'billing_first_name' ),
			'customer_last_name' => wp_get_current_user()->get( 'billing_last_name' ),
			'customer_email'     => $email ? $email : wp_get_current_user()->get( 'billing_email' ),
			'customer_id'        => wp_get_current_user()->ID,
		),
		'language' => $lang,
	);

	switch ( $email_id ) {
		case 'yith-ywar-request-review':
			$product_id = wc_get_products(
				array(
					'limit'   => 1,
					'orderby' => 'rand',
					'return'  => 'ids',
				)
			);
			$data       = array(
				'completed_date' => gmdate( 'Y-m-d', time() ),
				'items'          => array( reset( $product_id ) ),
				'days_ago'       => 8,
			);
			break;
		case 'yith-ywar-request-review-booking':
			$product_id = wc_get_products(
				array(
					'limit'   => 1,
					'orderby' => 'rand',
					'return'  => 'ids',
					'type'    => 'booking',
				)
			);
			$data       = array(
				'completed_date' => gmdate( 'Y-m-d', time() ),
				'items'          => array( reset( $product_id ) ),
				'days_ago'       => 8,
			);
			break;
		default:
			$product_id = wc_get_products(
				array(
					'limit'   => 1,
					'orderby' => 'rand',
					'return'  => 'ids',
				)
			);
			$data       = array(
				'product_id'        => reset( $product_id ),
				'total_reviews'     => 10,
				'remaining_reviews' => 3,
				'amount'            => 5,
				'funds_amount'      => 5,
				'coupon_code'       => 'test-coupon',
				'reviewer_info'     => array(
					'name'  => wp_get_current_user()->get( 'first_name' ),
					'email' => $email ? $email : wp_get_current_user()->get( 'user_email' ),
				),
				'review'            => array(
					'id'            => 0,
					'dummy_content' => array(
						'product_id' => reset( $product_id ),
						'text'       => 'The quick brown fox jumps over the lazy dog.',
					),
				),
			);
	}

	return $shared + $data;
}

/**
 * Get the timezone offset
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_get_time_offset(): string {

	$gmt_offset = get_option( 'gmt_offset' );
	$sign       = $gmt_offset > 0 ? '+' : '-';

	return "$sign$gmt_offset HOURS";
}

/**
 * Get product names
 *
 * @param array $product_ids The list of product IDs.
 * @param bool  $print_link  If the link should be rendered.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_product_names( array $product_ids, bool $print_link = false ): array {
	$products = array();
	foreach ( $product_ids as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product instanceof WC_Product ) {
			if ( $print_link ) {
				$products[] = '<a href="' . esc_url( get_edit_post_link( $product->get_id() ) ) . '" target="_blank">' . $product->get_name() . '</a>';
			} else {
				$products[] = $product->get_formatted_name();
			}
		}
	}

	return $products;
}

/**
 * Get category names
 *
 * @param array $category_ids The list of category IDs.
 * @param bool  $print_link   If the link should be rendered.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_category_names( array $category_ids, bool $print_link = false ): array {
	$categories = array();
	foreach ( $category_ids as $category_id ) {
		$category = get_term( $category_id, 'product_cat' );
		if ( $category instanceof WP_Term ) {
			if ( $print_link ) {
				$categories[] = '<a href="' . esc_url( get_edit_term_link( $category ) ) . '" target="_blank">' . $category->name . '</a>';
			} else {
				$categories[] = $category->name;
			}
		}
	}

	return $categories;
}

/**
 * Get tag names
 *
 * @param array $tag_ids    The list of tag IDs.
 * @param bool  $print_link If the link should be rendered.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_tag_names( array $tag_ids, bool $print_link = false ): array {
	$tags = array();
	foreach ( $tag_ids as $tag_id ) {
		$tag = get_term( $tag_id, 'product_tag' );
		if ( $tag instanceof WP_Term ) {
			if ( $print_link ) {
				$tags[] = '<a href="' . esc_url( get_edit_term_link( $tag ) ) . '" target="_blank">' . $tag->name . '</a>';
			} else {
				$tags[] = $tag->name;
			}
		}
	}

	return $tags;
}

/**
 * Get plugin option and checks for default,
 *
 * @param string $option_id The option ID.
 *
 * @return false|mixed|null
 * @since  2.0.0
 */
function yith_ywar_get_option( string $option_id ) {
	return get_option( $option_id, yith_ywar_get_default( $option_id ) );
}

/**
 * Get default for plugin option
 *
 * @param string $option_id    The option ID.
 * @param string $suboption_id The suboption ID.
 *
 * @return mixed
 * @since  2.0.0
 */
function yith_ywar_get_default( string $option_id, string $suboption_id = '' ) {

	$defaults = array(
		'ywar_review_autoapprove'            => 'no',
		'ywar_enable_visitors_vote'          => 'no',
		'ywar_show_user_country'             => 'no',
		'ywar_username_format'               => 'full',
		'ywar_enable_graph_tooltip'          => 'no',
		/* translators: %s BR tag */
		'ywar_no_reviews_text'               => sprintf( esc_html_x( 'No customer reviews available for this product%sBe the first to leave a review!', '[Admin panel] Default empty state label', 'yith-woocommerce-advanced-reviews' ), '<br />' ),
		'ywar_enable_recaptcha'              => 'no',
		'ywar_recaptcha_version'             => 'v2',
		'ywar_recaptcha_site_key'            => '',
		'ywar_recaptcha_secret_key'          => '',
		'ywar_show_attachments_gallery'      => 'yes',
		'ywar_show_replies_attachments'      => 'yes',
		'ywar_show_load_more'                => 'yes',
		'ywar_review_per_page'               => 15,
		'ywar_mandrill_enable'               => 'no',
		'ywar_mandrill_apikey'               => '',
		'ywar_summary_percentage_value'      => 'yes',
		'ywar_reviews_dialog'                => 'no',
		'ywar_avatar_name_position'          => 'above',
		'ywar_avatar_type'                   => 'image',
		'ywar_show_staff_badge'              => 'yes',
		'ywar_general_color'                 => array(
			'main'        => '#0eb7a8',
			'hover-icons' => '#ffffff',
		),
		'ywar_rating_graph_boxes'            => array(
			'background' => '#f5f5f5',
		),
		'ywar_graph_colors'                  => array(
			'default'    => '#d8d8d8',
			'accent'     => '#12a6b1',
			'percentage' => '#000000',
		),
		'ywar_stars_colors'                  => array(
			'default' => '#cdcdcd',
			'accent'  => '#dc9202',
		),
		'ywar_avatar_colors'                 => array(
			'background' => '#eaeaea',
			'initials'   => '#acacac',
		),
		'ywar_like_section_colors'           => array(
			'background'       => '#f5f5f5',
			'background-rated' => '#e3eff0',
			'icon'             => '#000000',
			'icon-rated'       => '#12a6b1',
		),
		'ywar_review_box_colors'             => array(
			'border' => '#dcdcdc',
			'shadow' => 'rgba(14, 183, 168, 0.33)',
		),
		'ywar_submit_button_colors'          => array(
			'background'       => '#0eb7a8',
			'background-hover' => '#dcdcdc',
			'text'             => '#ffffff',
			'text-hover'       => '#0eb7a8',
		),
		'ywar_load_more_button_colors'       => array(
			'background'       => '#0eb7a8',
			'background-hover' => '#dcdcdc',
			'text'             => '#ffffff',
			'text-hover'       => '#0eb7a8',
		),
		'ywar_staff_badge'                   => array(
			'label'            => esc_html_x( 'STAFF', '[Admin panel] Default badge label', 'yith-woocommerce-advanced-reviews' ),
			'background-color' => '#12a6b1',
			'text-color'       => '#ffffff',
		),
		'ywar_featured_badge'                => array(
			'label'            => esc_html_x( 'FEATURED', '[Admin panel] Default badge label', 'yith-woocommerce-advanced-reviews' ),
			'background-color' => '#c99a15',
			'text-color'       => '#ffffff',
			'border-color'     => '#f7c431',
			'shadow'           => 'rgba(247, 196, 49, 0.33)',
		),
		'ywar_user_permission'               => array(),
		'ywar_highlight_helpful_review'      => 5,
		'ywar_user_can_report_inappropriate' => 'all',
		'ywar_hide_inappropriate_review'     => 5,
		'ywar_enable_attachments'            => 'no',
		'ywar_max_attachments'               => 4,
		'ywar_attachment_type'               => array( 'jpg', 'jpeg', 'gif', 'png', 'webp' ),
		'ywar_attachment_max_size'           => 5,
		'ywar_enable_attachments_video'      => 'no',
		'ywar_max_attachments_video'         => 2,
		'ywar_attachment_type_video'         => array( 'flv', 'm4v', 'mp4', 'ogv', 'webm', 'wmv' ),
		'ywar_attachment_max_size_video'     => 10,
		'ywar_refuse_requests'               => 'no',
		'ywar_refuse_requests_label'         => esc_html_x( 'Yes, I accept to receive review reminders via email.', '[Admin panel] Default review request checkout label value', 'yith-woocommerce-advanced-reviews' ),
		'ywar_request_type'                  => 'all',
		'ywar_request_criteria'              => 'first',
		'ywar_request_number'                => 1,
		'ywar_mail_schedule_day'             => 7,
		'ywar_mail_reschedule'               => 'reschedule',
		'ywar_enable_analytics'              => 'no',
		'ywar_campaign_source'               => '',
		'ywar_campaign_medium'               => '',
		'ywar_campaign_term'                 => '',
		'ywar_campaign_content'              => '',
		'ywar_campaign_name'                 => '',
		'ywar_coupon_sending'                => 'moderated',
	);

	if ( '' !== $suboption_id ) {
		return $defaults[ $option_id ][ $suboption_id ];
	} else {
		return $defaults[ $option_id ];
	}
}

/**
 * Render links in the mailbody.
 *
 * @param int    $object_id The object ID.
 * @param string $type      The object type.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_render_mailbody_link( int $object_id, string $type ): string {

	if ( 'product' === $type ) {
		$product = wc_get_product( $object_id );

		if ( ! $product ) {
			return '';
		}

		$url   = $product->get_permalink();
		$title = $product->get_title();
	} else {
		$term = get_term_by( 'id', $object_id, 'product_cat' );

		if ( ! $term ) {
			return '';
		}

		$url   = get_term_link( $term->slug, 'product_cat' );
		$title = esc_html( $term->name );
	}

	return sprintf( '<a href="%1$s">%2$s</a>', $url, $title );
}

/**
 * Wrapper function for "current_user_can"
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_current_user_can_manage() {
	return current_user_can( 'manage_woocommerce' ); //phpcs:ignore WordPress.WP.Capabilities.Unknown
}

/**
 * Prune preview email content from unnecessary tags
 *
 * @param string $content The email content.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_prune_preview_email_content( string $content ): string {
	$content       = preg_replace( '/<title>(.*)<\/title>/i', '', $content );
	$content       = preg_replace( '/<yith-wccet-style type\=\"text\/css\">(\n.*)*<\/yith-wccet-style>\n/im', '', $content );
	$content       = preg_replace( '/<!--\[if gte mso 9\]>(.*)<!\[endif\]-->/im', '', $content );
	$content       = preg_replace( '/<!--\[if !gte mso 9\]>-->\n(.*)\n<!--<!\[endif\]-->/im', '', $content );
	$tags_to_strip = array( 'body', 'html', 'head', 'meta', '!DOCTYPE' );
	foreach ( $tags_to_strip as $html_tag ) {
		$content = preg_replace( "/<\\/?$html_tag(.|\\s)*?>/", '', $content );
	}

	return $content;
}

/**
 * Returns the content of the criteria popup
 *
 * @param bool $edit CHeck if the criteria is in "edit" mode.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_criteria_popup_content( bool $edit = false ): string {

	$fields = array(
		array(
			'id'                => 'criterion_name',
			'name'              => 'criterion_name',
			'title'             => esc_html_x( 'Criterion name', '[Admin panel] Blocklist modal description', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'text',
			'custom_attributes' => array( 'required' => true ),
		),
		array(
			'id'                => 'criterion_icon',
			'name'              => 'criterion_icon',
			'title'             => esc_html_x( 'Icon', '[Admin panel] Column and setting name', 'yith-woocommerce-advanced-reviews' ),
			'desc'              => esc_html_x( 'Maximum size suggested: 60px x 60px', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'media',
			'store_as'          => 'id',
			'allow_custom_url'  => false,
			'custom_attributes' => array( 'readonly' => true ),
		),
	);

	if ( $edit ) {
		$fields[] = array(
			'id'   => 'criterion_id',
			'name' => 'criterion_id',
			'type' => 'hidden',
		);
	}

	ob_start();
	?>
	<div class="yith-plugin-fw__panel__content">
		<div class="yith-plugin-fw__panel__section__content">
			<?php foreach ( $fields as $field ) : ?>
				<?php
				$row_classes = array(
					'yith-plugin-fw__panel__option',
					'yith-plugin-fw__panel__option--' . $field['type'],
				);
				$row_classes = implode( ' ', array_filter( $row_classes ) );
				?>
				<div class="<?php echo esc_attr( $row_classes ); ?>" <?php echo wp_kses_post( yith_field_deps_data( $field ) ); ?>>
					<?php if ( isset( $field['title'] ) && '' !== $field['title'] ) : ?>
						<div class="yith-plugin-fw__panel__option__label">
							<label for="<?php echo esc_attr( ( $field['id'] ) ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></label>
						</div>
					<?php endif; ?>
					<div class="yith-plugin-fw__panel__option__content">
						<?php yith_plugin_fw_get_field( $field, true, true ); ?>
					</div>
					<?php if ( ! empty( $field['desc'] ) ) : ?>
						<div class="yith-plugin-fw__panel__option__description">
							<?php echo wp_kses_post( $field['desc'] ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php

	return ob_get_clean();
}
