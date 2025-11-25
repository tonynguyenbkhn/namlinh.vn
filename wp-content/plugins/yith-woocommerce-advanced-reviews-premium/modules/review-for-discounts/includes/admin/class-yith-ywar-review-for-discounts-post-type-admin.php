<?php
/**
 * Post type admin class for Discounts.
 *
 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts\Admin
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Review_For_Discounts_Post_Type_Admin' ) ) {
	/**
	 * Class YITH_YWAR_Review_For_Discounts_Post_Type_Admin
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts\Admin
	 */
	class YITH_YWAR_Review_For_Discounts_Post_Type_Admin extends YITH_YWAR_Post_Type_Admin {

		/**
		 * The post type.
		 *
		 * @var string
		 */
		protected $post_type = YITH_YWAR_Post_Types::DISCOUNTS;

		/**
		 * YITH_YWAR_Review_For_Discounts_Post_Type_Admin constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function __construct() {
			parent::__construct();

			add_action( 'edit_form_after_title', array( $this, 'output_metabox' ), 20 );
			add_action( 'yith_ywar_post_process_discounts_meta', array( $this, 'save' ), 10, 1 );
		}

		/**
		 * Return the post_type settings placeholder.
		 *
		 * @return array Array of settings: title_placeholder, title_description, updated_messages.
		 * @since  2.0.0
		 */
		protected function get_post_type_settings(): array {
			return array(
				'title_placeholder' => esc_html_x( 'Coupon name', '[Admin panel] Field name', 'yith-woocommerce-advanced-reviews' ),
				'title_description' => esc_html_x( 'Enter a name to identify this coupon.', '[Admin panel] Field description', 'yith-woocommerce-advanced-reviews' ),
				'updated_messages'  => array(
					1  => esc_html_x( 'Coupon updated.', '[Admin panel] Post updated message', 'yith-woocommerce-advanced-reviews' ),
					4  => esc_html_x( 'Coupon updated.', '[Admin panel] Post updated message', 'yith-woocommerce-advanced-reviews' ),
					6  => esc_html_x( 'Coupon created.', '[Admin panel] Post updated message', 'yith-woocommerce-advanced-reviews' ),
					7  => esc_html_x( 'Coupon saved.', '[Admin panel] Post updated message', 'yith-woocommerce-advanced-reviews' ),
					8  => esc_html_x( 'Coupon submitted.', '[Admin panel] Post updated message', 'yith-woocommerce-advanced-reviews' ),
					10 => esc_html_x( 'Coupon draft updated.', '[Admin panel] Post updated message', 'yith-woocommerce-advanced-reviews' ),
				),
				'hide_views'        => true,
			);
		}

		/**
		 * Retrieve an array of parameters for blank state.
		 *
		 * @return array{
		 * @type string $icon_url The icon URL.
		 * @type string $message  The message to be shown.
		 * @type string $cta      The call-to-action button title.
		 * @type string $cta_icon The call-to-action button icon.
		 * @type string $cta_url  The call-to-action button URL.
		 *                        }
		 * @since  2.0.0
		 */
		protected function get_blank_state_params(): array {
			return array(
				'icon_url' => YITH_YWAR_ASSETS_URL . '/images/discounts.svg',
				'message'  => esc_html_x( "You haven't yet created discount coupons for customers who leave a review", '[Admin panel] Empty status message', 'yith-woocommerce-advanced-reviews' ),
				'cta'      => array(
					'title' => esc_html_x( 'Create coupon', '[Admin panel] Button lavel', 'yith-woocommerce-advanced-reviews' ),
					'url'   => add_query_arg( array( 'post_type' => $this->post_type ), admin_url( 'post-new.php' ) ),
				),
			);
		}

		/**
		 * Define which columns to show on this screen.
		 *
		 * @param array $columns Existing columns.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function define_columns( $columns ): array {
			if ( isset( $columns['date'] ) ) {
				unset( $columns['date'] );
			}

			$columns['title']         = esc_html_x( 'Name', '[Admin panel] Column and setting name', 'yith-woocommerce-advanced-reviews' );
			$columns['trigger']       = esc_html_x( 'Trigger', '[Admin panel] Column name', 'yith-woocommerce-advanced-reviews' );
			$columns['discount_type'] = esc_html_x( 'Discount type', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' );
			$columns['actions']       = esc_html_x( 'Actions', '[Admin panel] Column name', 'yith-woocommerce-advanced-reviews' );

			return $columns;
		}

		/**
		 * Render "Trigger" column
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_trigger_column() {

			$coupon = yith_ywar_get_discount( $this->post_id );

			if ( 'single' === $coupon->get_trigger() ) {

				$products   = yith_ywar_get_product_names( $coupon->get_trigger_product_ids() );
				$categories = yith_ywar_get_category_names( $coupon->get_trigger_product_categories() );

				if ( empty( $products ) && empty( $categories ) ) {
					echo esc_html_x( 'Review of any product', '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' );
				} else {
					$product_text  = '';
					$category_text = '';

					if ( count( $products ) === 1 ) {
						$product_text .= implode( '', $products );
					} elseif ( count( $products ) > 1 ) {
						/* translators: %s number of products */
						$product_text .= '<span class="underline">' . sprintf( esc_html_x( '%s specific products', '[Admin panel] Review box and discount description', 'yith-woocommerce-advanced-reviews' ), count( $products ) ) . '</span>';
					}

					if ( count( $categories ) === 1 ) {
						/* translators: %s name of the category */
						$category_text .= sprintf( esc_html_x( 'all products of the %s category', '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' ), implode( '', $categories ) );
					} elseif ( count( $categories ) > 1 ) {
						/* translators: %s number of categories */
						$category_text .= '<span class="underline">' . sprintf( esc_html_x( 'all products of %s categories', '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' ), count( $categories ) ) . '</span>';
					}

					/* translators: %1$s product text, %2$s categories text - Example: 2 products and 3 categories */
					$additional_text = ( $product_text && $category_text ) ? sprintf( esc_html_x( '%1$s and %2$s', '[Admin panel] String for joining other strings', 'yith-woocommerce-advanced-reviews' ), $product_text, $category_text ) : $product_text . $category_text;
					/* translators: %1$s specific product/categories */
					$trigger = sprintf( esc_html_x( 'Review on %1$s', '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' ), $additional_text );
					?>
					<span class="yith-plugin-fw__tips" data-tip="<?php echo wp_kses_post( wc_sanitize_tooltip( $this->get_column_tip( $products, $categories ) ) ); ?>"><?php echo wp_kses_post( $trigger ); ?></span>
					<?php
				}
			} else {
				/* translators: %s number of reviews written */
				echo wp_kses_post( sprintf( _nx( '%s review written', '%s reviews written', esc_attr( $coupon->get_trigger_threshold() ), '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' ), esc_html( $coupon->get_trigger_threshold() ) ) );

				if ( 'yes' === $coupon->get_trigger_enable_notify() ) {
					/* translators: %s number of reviews written */
					echo '<br />' . sprintf( esc_html_x( 'Send email notification after %s reviews', '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' ), esc_html( $coupon->get_trigger_threshold_notify() ) );
				}
			}
		}

		/**
		 * Render "Discount Type" column
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_discount_type_column() {

			$coupon        = yith_ywar_get_discount( $this->post_id );
			$products      = yith_ywar_get_product_names( $coupon->get_product_ids() );
			$categories    = yith_ywar_get_category_names( $coupon->get_product_categories() );
			$discount_type = '';

			switch ( $coupon->get_discount_type() ) {
				case 'funds':
					/* translators: %s funds amount */
					$discount_type = sprintf( esc_html_x( "Add %s to customer's funds", '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' ), wc_price( $coupon->get_funds_amount() ) );
					break;
				case 'percent':
					if ( empty( $products ) && empty( $categories ) ) {
						/* translators: %s coupon amount */
						$discount_type = sprintf( esc_html_x( '%s on cart total', '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' ), $coupon->get_amount() . '%' );
					} else {
						$product_text = '';
						if ( count( $products ) === 1 ) {
							$product_text .= implode( '', $products );
						} elseif ( count( $products ) > 1 ) {
							/* translators: %s number of products */
							$product_text .= '<span class="underline">' . sprintf( esc_html_x( '%s specific products', '[Admin panel] Review box and discount description', 'yith-woocommerce-advanced-reviews' ), count( $products ) ) . '</span>';
						}

						$category_text = '';
						if ( count( $categories ) === 1 ) {
							/* translators: %s name of the category */
							$category_text .= sprintf( esc_html_x( 'all products of the %s category', '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' ), implode( '', $categories ) );
						} elseif ( count( $categories ) > 1 ) {
							/* translators: %s number of categories */
							$category_text .= '<span class="underline">' . sprintf( esc_html_x( 'all products of %s categories', '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' ), count( $categories ) ) . '</span>';
						}
						/* translators: %1$s product text, %2$s categories text - Example: 2 products and 3 categories */
						$additional_text = ( $product_text && $category_text ) ? sprintf( esc_html_x( '%1$s and %2$s', '[Admin panel] String for joining other strings', 'yith-woocommerce-advanced-reviews' ), $product_text, $category_text ) : $product_text . $category_text;
						/* translators: %1$s coupon amount, %2$s specific product/categories - Example: 40$ on Coffee Box */
						$discount_type = sprintf( esc_html_x( '%1$s on %2$s', '[Admin panel] String for joining other strings', 'yith-woocommerce-advanced-reviews' ), $coupon->get_amount() . '%', $additional_text );
					}
					break;

				case 'fixed_cart':
					/* translators: %s coupon amount */
					$discount_type = sprintf( esc_html_x( '%s on cart total', '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' ), wc_price( $coupon->get_amount() ) );
					break;

				case 'fixed_product':
					if ( empty( $products ) && empty( $categories ) ) {
						/* translators: %s coupon amount */
						$discount_type = sprintf( esc_html_x( '%s on each product price', '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' ), wc_price( $coupon->get_amount() ) );
					} else {
						$product_text = '';
						if ( count( $products ) === 1 ) {
							$product_text .= implode( '', $products );
						} elseif ( count( $products ) > 1 ) {
							/* translators: %s number of products */
							$product_text .= '<span class="underline">' . sprintf( esc_html_x( '%s specific products', '[Admin panel] Review box and discount description', 'yith-woocommerce-advanced-reviews' ), count( $products ) ) . '</span>';
						}

						$category_text = '';
						if ( count( $categories ) === 1 ) {
							/* translators: %s name of the category */
							$category_text .= sprintf( esc_html_x( 'all products of the %s category', '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' ), implode( '', $categories ) );
						} elseif ( count( $categories ) > 1 ) {
							/* translators: %s number of categories */
							$category_text .= '<span class="underline">' . sprintf( esc_html_x( 'all products of %s categories', '[Admin panel] Discount description', 'yith-woocommerce-advanced-reviews' ), count( $categories ) ) . '</span>';
						}
						/* translators: %1$s product text, %2$s categories text - Example: 2 products and 3 categories */
						$additional_text = ( $product_text && $category_text ) ? sprintf( esc_html_x( '%1$s and %2$s', '[Admin panel] String for joining other strings', 'yith-woocommerce-advanced-reviews' ), $product_text, $category_text ) : $product_text . $category_text;
						/* translators: %1$s coupon amount, %2$s specific product/categories - Example: 40$ on Coffee Box */
						$discount_type = sprintf( esc_html_x( '%1$s on %2$s', '[Admin panel] String for joining other strings', 'yith-woocommerce-advanced-reviews' ), wc_price( $coupon->get_amount() ), $additional_text );

					}
					break;
				default:
			}

			if ( ( 'fixed_product' === $coupon->get_discount_type() || 'percent' === $coupon->get_discount_type() ) && ( count( $products ) > 0 || count( $categories ) > 0 ) ) {
				?>
				<span class="yith-plugin-fw__tips" data-tip="<?php echo wp_kses_post( wc_sanitize_tooltip( $this->get_column_tip( $products, $categories ) ) ); ?>"><?php echo wp_kses_post( $discount_type ); ?></span>
				<?php
			} else {
				echo wp_kses_post( $discount_type );
			}
		}

		/**
		 * Get the column tip content
		 *
		 * @param array $products   The products list.
		 * @param array $categories The categories List.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		private function get_column_tip( array $products, array $categories ): string {
			$products_tip   = count( $products ) > 0 ? esc_html_x( 'Products', '[Admin panel] Generic label description', 'yith-woocommerce-advanced-reviews' ) . ':<br />' . implode( '<br/>', $products ) : '';
			$categories_tip = count( $categories ) > 0 ? esc_html_x( 'Categories', '[Admin panel] Generic label description', 'yith-woocommerce-advanced-reviews' ) . ':<br />' . implode( '<br/>', $categories ) : '';
			$spacer         = count( $products ) > 0 && count( $categories ) > 0 ? '<br /><br/>' : '';

			return sprintf( '%1$s %3$s %2$s', $products_tip, $categories_tip, $spacer );
		}

		/**
		 * Output options
		 *
		 * @param WP_Post $post The current post.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function output_metabox( WP_Post $post ) {

			if ( $this->post_type !== $post->post_type ) {
				return;
			}
			$discount     = yith_ywar_get_discount( $post->ID );
			$coupon_types = wc_get_coupon_types();

			if ( yith_ywar_account_funds_enabled() ) {
				$coupon_types['funds'] = esc_html_x( 'Add funds', '[Admin panel] Discount setting option name', 'yith-woocommerce-advanced-reviews' );
			}

			$wc_url = add_query_arg(
				array(
					'page'    => 'wc-settings',
					'tab'     => 'shipping',
					'section' => 'WC_Shipping_Free_Shipping',
				),
				admin_url( 'admin.php' )
			);

			$tabs = array(
				'triggering_event'   => array(
					'label'  => esc_html_x( 'Triggering event', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
					'fields' => array(
						array(
							'id'      => 'trigger',
							'title'   => esc_html_x( 'Triggering event', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'options' => array(
								'single'   => esc_html_x( 'Single review', '[Admin panel] Discount setting option name', 'yith-woocommerce-advanced-reviews' ),
								'multiple' => esc_html_x( 'Multiple reviews', '[Admin panel] Discount setting option name', 'yith-woocommerce-advanced-reviews' ),
							),
							'desc'    => esc_html_x( 'When the coupon will be sent', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'type'    => 'select',
							'class'   => 'wc-enhanced-select',
						),
						array(
							'id'       => 'trigger_product_ids',
							'type'     => 'ajax-products',
							'title'    => esc_html_x( 'Triggering products', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'     => esc_html_x( 'Products that will give a coupon when reviewed. Leave it empty to apply to all products.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'multiple' => true,
							'deps'     => array(
								'id'    => 'trigger',
								'value' => 'single',
							),
						),
						array(
							'id'       => 'trigger_product_categories',
							'title'    => esc_html_x( 'Triggering categories', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'     => esc_html_x( 'Reviewing products in the selected categories will send a coupon. Leave it empty to apply to all categories.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'type'     => 'ajax-terms',
							'data'     => array(
								'placeholder' => esc_html_x( 'Search for a category', '[Global] Ajax field placeholder', 'yith-woocommerce-advanced-reviews' ) . '&hellip;',
								'taxonomy'    => 'product_cat',
							),
							'multiple' => true,
							'deps'     => array(
								'id'    => 'trigger',
								'value' => 'single',
							),
						),
						array(
							'id'                => 'trigger_threshold',
							'title'             => esc_html_x( 'Requested quantity', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'              => esc_html_x( 'The number of reviews that have to be written to receive the coupon.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'type'              => 'number',
							'default'           => 2,
							'min'               => 2,
							'custom_attributes' => 'required',
							'deps'              => array(
								'id'    => 'trigger',
								'value' => 'multiple',
							),
						),
						array(
							'id'    => 'trigger_enable_notify',
							'type'  => 'onoff',
							'title' => esc_html_x( 'Send notification when requested quantity is approaching', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'  => esc_html_x( 'Send an email to encourage users to continue reviewing when a certain number of reviews are posted.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'deps'  => array(
								'id'    => 'trigger',
								'value' => 'multiple',
							),
						),
						array(
							'id'                => 'trigger_threshold_notify',
							'title'             => esc_html_x( 'Initial quantity', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'              => esc_html_x( 'Set how many reviews are needed to start sending the email notifications.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'type'              => 'number',
							'default'           => 1,
							'min'               => 1,
							'max'               => 1,
							'custom_attributes' => 'required',
							'deps'              => array(
								'ids'    => 'trigger_enable_notify',
								'values' => 'yes',
								'type'   => 'hide-disable',
							),
						),
					),
				),
				'coupon_settings'    => array(
					'label'  => esc_html_x( 'Coupon settings', '[Admin panel] Discount section tab name', 'yith-woocommerce-advanced-reviews' ),
					'fields' => array(
						array(
							'title'   => esc_html_x( 'Discount type', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'type'    => 'select',
							'options' => $coupon_types,
							'default' => 'percent',
							'class'   => 'wc-enhanced-select',
							'id'      => 'discount_type',
						),
						array(
							'id'                => 'amount',
							'type'              => 'text',
							'title'             => esc_html_x( 'Coupon amount', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'              => esc_html_x( 'Coupon value.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'custom_attributes' => 'required',
							'default'           => 0,
							'deps'              => array(
								'id'    => 'discount_type',
								'value' => implode( ',', array_keys( wc_get_coupon_types() ) ),
							),
							'class'             => 'wc_input_price',
						),
						array(
							'id'    => 'free_shipping',
							'type'  => 'onoff',
							'title' => esc_html_x( 'Allow free shipping', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							/* translators: %1$s link opening tag - %2$s link closing tag */
							'desc'  => sprintf( esc_html_x( 'Enable if the coupon offers free shipping. The %1$sfree shipping method%2$s must be enabled and set to require "A valid free shipping coupon" (see the "Free shipping requires..." setting).', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ), '<a href="' . $wc_url . '">', '</a>' ),
							'deps'  => array(
								'id'    => 'discount_type',
								'value' => implode( ',', array_keys( wc_get_coupon_types() ) ),
							),
						),
						array(
							'id'                => 'expiry_days',
							'title'             => esc_html_x( 'Validity days', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'              => esc_html_x( 'Set the number of days the coupon is valid after it is created. Set to "0" for no expiration.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'type'              => 'number',
							'default'           => 0,
							'min'               => 0,
							'custom_attributes' => 'required',
							'deps'              => array(
								'id'    => 'discount_type',
								'value' => implode( ',', array_keys( wc_get_coupon_types() ) ),
							),
						),
						array(
							'id'                => 'funds_amount',
							'type'              => 'text',
							'title'             => esc_html_x( 'Funds amount', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'              => esc_html_x( 'Funds value.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'custom_attributes' => 'required',
							'default'           => 0,
							'deps'              => array(
								'id'    => 'discount_type',
								'value' => 'funds',
							),
						),
					),
				),
				'usage_restrictions' => array(
					'label'  => esc_html_x( 'Usage restrictions', '[Admin panel] Discount section tab name', 'yith-woocommerce-advanced-reviews' ),
					'fields' => array(
						array(
							'id'                => 'minimum_amount',
							'title'             => esc_html_x( 'Minimum spend', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'              => esc_html_x( 'This field allows you to set the minimum subtotal required to use the coupon.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'type'              => 'text',
							'custom_attributes' => array(
								'placeholder' => esc_html_x( 'No minimum', '[Admin panel] Discount setting placeholder', 'yith-woocommerce-advanced-reviews' ),
							),
							'class'             => 'wc_input_price',
						),
						array(
							'id'                => 'maximum_amount',
							'title'             => esc_html_x( 'Maximum spend', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'              => esc_html_x( 'This field allows you to set the maximum subtotal required to use the coupon.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'type'              => 'text',
							'custom_attributes' => array(
								'placeholder' => esc_html_x( 'No maximum', '[Admin panel] Discount setting placeholder', 'yith-woocommerce-advanced-reviews' ),
							),
							'class'             => 'wc_input_price',
						),
						array(
							'id'    => 'individual_use',
							'type'  => 'onoff',
							'title' => esc_html_x( 'Individual use only', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'  => esc_html_x( 'Check this box if the coupon cannot be used in conjunction with other coupons.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
						),
						array(
							'id'    => 'exclude_sale_items',
							'title' => esc_html_x( 'Exclude sale items', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'  => esc_html_x( 'Check this box if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are items in the cart that are not on sale.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'type'  => 'onoff',
						),
						array(
							'id'       => 'product_ids',
							'type'     => 'ajax-products',
							'title'    => esc_html_x( 'Products', '[Admin panel] Generic label description', 'yith-woocommerce-advanced-reviews' ),
							'desc'     => esc_html_x( 'Products to which the coupon will be applied, or that must be in the cart for the "Fixed cart discount" to be applied.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'data'     => array(
								'action'   => 'woocommerce_json_search_products_and_variations',
								'security' => wp_create_nonce( 'search-products' ),
							),
							'multiple' => true,
						),
						array(
							'id'       => 'excluded_product_ids',
							'type'     => 'ajax-products',
							'title'    => esc_html_x( 'Excluded products', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'     => esc_html_x( 'Products to which the coupon will not be applied, or that must not be in the cart for the "Fixed cart discount" to be applied.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'data'     => array(
								'action'   => 'woocommerce_json_search_products_and_variations',
								'security' => wp_create_nonce( 'search-products' ),
							),
							'multiple' => true,
						),
						array(
							'id'       => 'product_categories',
							'title'    => esc_html_x( 'Product categories', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'     => esc_html_x( 'Product categories to which the coupon will be applied, or that must be in the cart for the "Fixed cart discount" to be applied.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'type'     => 'ajax-terms',
							'data'     => array(
								'placeholder' => esc_html_x( 'Search for a category', '[Global] Ajax field placeholder', 'yith-woocommerce-advanced-reviews' ) . '&hellip;',
								'taxonomy'    => 'product_cat',
							),
							'multiple' => true,
						),
						array(
							'id'       => 'excluded_product_categories',
							'title'    => esc_html_x( 'Exclude categories', '[Admin panel] Discount setting name', 'yith-woocommerce-advanced-reviews' ),
							'desc'     => esc_html_x( 'Product categories to which the coupon will not be applied, or that must not be in the cart for the "Fixed cart discount" to be applied.', '[Admin panel] Discount setting description', 'yith-woocommerce-advanced-reviews' ),
							'type'     => 'ajax-terms',
							'data'     => array(
								'placeholder' => esc_html_x( 'Search for a category', '[Global] Ajax field placeholder', 'yith-woocommerce-advanced-reviews' ) . '&hellip;',
								'taxonomy'    => 'product_cat',
							),
							'multiple' => true,
						),
					),
				),
			);

			?>
			<div class="yith-ywar-discounts-tabs woocommerce yith-plugin-fw-panel">
				<div class="yith-plugin-ui yith-plugin-fw ">
					<ul class="yith-plugin-fw__tabs">
						<?php foreach ( $tabs as $key => $tab ) : ?>
							<li class="yith-plugin-fw__tab">
								<a class="yith-plugin-fw__tab__handler <?php echo esc_attr( $key ); ?>" href="#tab-panel-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $tab['label'] ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
					<?php foreach ( $tabs as $key => $tab ) : ?>
					<div class="yith-plugin-fw__tab-panel" id="tab-panel-<?php echo esc_attr( $key ); ?>">
						<div class="yith-plugin-fw__panel__section__content">
							<?php foreach ( $tab['fields'] as $field ) : ?>
								<?php
								$field['name']  = "$this->post_type[{$field['id']}]";
								$row_classes    = array(
									'yith-plugin-fw__panel__option',
									'yith-plugin-fw__panel__option--' . $field['type'],
									$field['id'],
								);
								$row_classes    = implode( ' ', array_filter( $row_classes ) );
								$field['value'] = $discount->{"get_{$field['id']}"}();
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
						</div><?php endforeach; ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Set field name
		 *
		 * @param string $name The field name.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_name_field( string $name = '' ): string {
			return $this->post_type . '[' . $name . ']';
		}

		/**
		 * Save meta on save post
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function save( int $post_id ) {
			// Disable nonce verification notice, since the nonce is already checked!
			if ( isset( $_POST[ $this->post_type ] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$form_fields = wc_clean( wp_unslash( $_POST[ $this->post_type ] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$form_fields = is_array( $form_fields ) ? $form_fields : array();
				$discount    = yith_ywar_get_discount( $post_id );

				foreach ( $form_fields as $key => $value ) {
					$discount->{"set_$key"}( $value );
				}

				$discount->save();
			}
		}

		/**
		 * Render blank state. Extend to add content.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_blank_state() {
			parent::render_blank_state();

			echo '<style>.page-title-action{ display: none !important; }</style>';
		}
	}
}

return YITH_YWAR_Review_For_Discounts_Post_Type_Admin::instance();
