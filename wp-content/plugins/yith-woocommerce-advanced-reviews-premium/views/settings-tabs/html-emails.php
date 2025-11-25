<?php
/**
 * Emails tab content.
 *
 * @var YITH_YWAR_Email[] $emails
 * @package YITH\AdvancedReviews\Views\SettingsTabs
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

$columns = array(
	'name'    => esc_html_x( 'Email', '[Admin panel] Column name - Setting name', 'yith-woocommerce-advanced-reviews' ),
	'sent'    => esc_html_x( 'Receiver', '[Admin panel] Column name - Event that triggers the email', 'yith-woocommerce-advanced-reviews' ),
	'status'  => esc_html_x( 'Active', '[Admin panel] Column name - Status', 'yith-woocommerce-advanced-reviews' ),
	'actions' => '',
);

$settings_tabs = array(
	'layout'        => esc_html_x( 'Layout', '[Admin panel] Email settings tab name', 'yith-woocommerce-advanced-reviews' ),
	'content'       => esc_html_x( 'Content', '[Admin panel] Email settings tab name', 'yith-woocommerce-advanced-reviews' ),
	'configuration' => esc_html_x( 'Configuration', '[Admin panel] Email settings tab name', 'yith-woocommerce-advanced-reviews' ),
);

?>
<div class="yith-ywar-emails">
	<div class="yith-ywar-emails__headings">
		<?php foreach ( $columns as $key => $column ) : ?>
			<div class="yith-ywar-emails__heading yith-ywar-emails__heading-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></div>
		<?php endforeach; ?>
	</div>
	<div class="yith-ywar-emails__list">
		<?php foreach ( $emails as $email_key => $email ) : ?>
			<div class="yith-ywar-emails__email" data-email="<?php echo esc_attr( $email_key ); ?>" data-email-id="<?php echo esc_attr( $email->id ); ?>">
				<div class="yith-ywar-emails__email__head">
					<?php foreach ( $columns as $key => $column ) : ?>
						<div class="yith-ywar-emails__email__column yith-ywar-emails__email__column-<?php echo esc_attr( $key ); ?>">
							<?php
							switch ( $key ) {
								case 'name':
									echo '<strong>' . esc_html( $email->get_title() ) . '</strong>';
									break;
								case 'sent':
									echo $email->is_customer_email() ? esc_html_x( 'Customer', '[Admin panel] Email receiver label', 'yith-woocommerce-advanced-reviews' ) : ( 'yith-ywar-new-reply' === $email->id ? esc_html_x( 'To review author', '[Admin panel] Email receiver label', 'yith-woocommerce-advanced-reviews' ) : esc_html_x( 'Admin', '[Admin panel] Email receiver label', 'yith-woocommerce-advanced-reviews' ) );
									echo '<br />';
									echo esc_html( $email->get_description_to_show_in_settings_list() );
									break;
								case 'status':
									yith_plugin_fw_get_field(
										array(
											'type'  => 'onoff',
											'value' => $email->is_enabled(),
											'class' => 'yith-ywar-emails__email__toggle-active',
										),
										true
									);
									break;
								case 'actions':
									yith_plugin_fw_get_component(
										array(
											'class'  => 'yith-ywar-emails__email__toggle-editing',
											'type'   => 'action-button',
											'action' => 'edit',
											'icon'   => 'edit',
											'title'  => esc_html_x( 'Edit', '[Global] Generic edit button text', 'yith-woocommerce-advanced-reviews' ),
											'url'    => '#',
										),
										true
									);
									break;
								default:
									break;
							}
							?>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="yith-ywar-emails__email__options">
					<form class="yith-ywar-emails__email__options__form">
						<ul class="yith-plugin-fw__tabs">
							<?php foreach ( $settings_tabs as $key => $label ) : ?>
								<li class="yith-plugin-fw__tab <?php echo esc_attr( $key ); ?>">
									<a class="yith-plugin-fw__tab__handler" href="#tab-panel-<?php echo esc_attr( $email->id ) . '-' . esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
						<div class="yith-plugin-ui yith-plugin-fw yith-ywar-emails__email__options__container">
							<?php foreach ( $settings_tabs as $tab_key => $tab_label ) : ?>
								<div class="yith-plugin-fw__tab-panel yith-plugin-fw__panel__section__content" id="tab-panel-<?php echo esc_attr( $email->id ) . '-' . esc_attr( $tab_key ); ?>">
									<?php foreach ( $email->get_email_options( $tab_key ) as $key => $field ) : ?>
										<?php echo $email->generate_fields_html( $key, $field ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php endforeach; ?>
								</div>
							<?php endforeach; ?>
							<div class="yith-ywar-emails__email__preview__container">
								<div class="yith-ywar-emails__email__preview <?php echo esc_attr( $email->id ); ?>">
									<?php
									$email->object = yith_ywar_get_test_values( $email->id );
									$email->init_placeholders_before_sending();
									$string = $email->style_inline( $email->get_content() );

									echo yith_ywar_prune_preview_email_content( $string ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>
								</div>
							</div>
						</div>
						<div class="yith-ywar-emails__email__actions">
							<span class="yith-ywar-emails__email__save yith-plugin-fw__button yith-plugin-fw__button--primary yith-plugin-fw__button--xl" data-save-message="<?php echo esc_html_x( 'Save', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ); ?>">
								<svg class="yith-ywar-emails__email__save__saved-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" role="img">
									<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
								</svg>
								<span class="yith-ywar-emails__email__save__text">
									<?php echo esc_html_x( 'Save', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ); ?>
								</span>
							</span>
						</div>
					</form>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
