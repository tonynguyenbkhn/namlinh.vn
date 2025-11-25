<?php
/**
 * The template for displaying warranty options.
 *
 * @package WooCommerce_Warranty\Templates
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<tr valign="top">
	<td colspan="2" class="categories-warranty-container">
		<table class="wp-list-table widefat fixed striped posts warranty-category-table <?php echo esc_attr( $value['class'] ); ?>" style="min-width: 400px;">
			<thead>
			<tr>
				<th scope="col" id="col_category" class="manage-column column-category" style="padding-left: 20px;"><?php esc_html_e( 'Category', 'woocommerce-warranty' ); ?></th>
				<th scope="col" id="col_warranty" class="manage-column column-warranty"><?php esc_html_e( 'Warranty', 'woocommerce-warranty' ); ?></th>
			</tr>
			</thead>
			<tbody id="categories_list" data-nonce="<?php echo esc_attr( wp_create_nonce( 'warranty_category_update_inline' ) ); ?>">
			<?php
			foreach ( $categories as $category ) :
				$category_id = $category->term_id;
				$warranty    = isset( $warranties[ $category_id ] ) ? $warranties[ $category_id ] : array();

				if ( empty( $warranty ) ) {
					$warranty = $default_warranty;
				}

				$default = isset( $warranty['default'] ) ? $warranty['default'] : false;
				$label   = isset( $warranty['label'] ) ? $warranty['label'] : '';
				?>
				<tr id="row_<?php echo esc_attr( $category_id ); ?>" data-id="<?php echo esc_attr( $category_id ); ?>">
					<td>
						<a href="#" data-target="edit_<?php echo esc_attr( $category_id ); ?>" class="editinline">
							<strong><?php echo esc_html( $category->name ); ?></strong>
						</a>
					</td>
					<td class="warranty-string">
						<?php echo $default ? '<em>Default warranty</em>' : esc_html( warranty_get_warranty_string( 0, $warranty ) ); ?>
					</td>
				</tr>
				<tr class="hidden"></tr>
				<tr id="edit_<?php echo esc_attr( $category_id ); ?>" data-id="<?php echo esc_attr( $category_id ); ?>" class="inline-edit-row inline-edit-row-post inline-edit-product quick-edit-row quick-edit-row-post inline-edit-product alternate inline-editor">
					<td class="colspanchange" colspan="2">
						<fieldset class="inline-edit-col-left">
							<div class="inline-edit-col">
								<h4><?php esc_html_e( 'Warranty Settings', 'woocommerce-warranty' ); ?></h4>

								<div class="inline-edit-group">
									<label class="alignleft">
										<input type="checkbox" name="category_warranty_default[<?php echo esc_attr( $category_id ); ?>]" data-id="<?php echo esc_attr( $category_id ); ?>" <?php checked( true, $default ); ?> class="default_toggle" value="yes" />
										<span class="checkbox-title"><?php esc_html_e( 'Default warranty', 'woocommerce-warranty' ); ?></span>
									</label>
								</div>

								<label class="alignleft">
									<span class="title"><?php esc_html_e( 'Type', 'woocommerce-warranty' ); ?></span>
												<span class="input-text-wrap">
													<select name="category_warranty_type[<?php echo esc_attr( $category_id ); ?>]" class="warranty-type warranty_<?php echo esc_attr( $category_id ); ?>" id="warranty_type_<?php echo esc_attr( $category_id ); ?>" data-id="<?php echo esc_attr( $category_id ); ?>">
														<option <?php selected( $warranty['type'], 'no_warranty' ); ?> value="no_warranty"><?php esc_html_e( 'No Warranty', 'woocommerce-warranty' ); ?></option>
														<option <?php selected( $warranty['type'], 'included_warranty' ); ?> value="included_warranty"><?php esc_html_e( 'Warranty Included', 'woocommerce-warranty' ); ?></option>
														<option <?php selected( $warranty['type'], 'addon_warranty' ); ?> value="addon_warranty"><?php esc_html_e( 'Warranty as Add-On', 'woocommerce-warranty' ); ?></option>
													</select>
												</span>
								</label>
								<br class="clear" />

								<label class="alignleft show_if_included_warranty show_if_addon_warranty">
									<span class="title"><?php esc_html_e( 'Label', 'woocommerce-warranty' ); ?></span>
											<span class="input-text-wrap">
												<input type="text" name="category_warranty_label[<?php echo esc_attr( $category_id ); ?>]" value="<?php echo esc_attr( $label ); ?>" class="input-text sized warranty-label warranty_<?php echo esc_attr( $category_id ); ?>" id="warranty_label_<?php echo esc_attr( $category_id ); ?>">
											</span>
								</label>
								<br class="clear" />

								<label class="alignleft included-form">
									<span class="title"><?php esc_html_e( 'Validity', 'woocommerce-warranty' ); ?></span>
												<span class="input-text-wrap">
													<?php $warranty_length = isset( $warranty['length'] ) ? $warranty['length'] : ''; ?>
													<select name="category_included_warranty_length[<?php echo esc_attr( $category_id ); ?>]" class="select short included-warranty-length warranty_<?php echo esc_attr( $category_id ); ?>" id="included_warranty_length_<?php echo esc_attr( $category_id ); ?>">
														<option <?php selected( 'included_warranty_lifetime', $warranty['type'] . '_' . $warranty_length ); ?> value="lifetime"><?php esc_html_e( 'Lifetime', 'woocommerce-warranty' ); ?></option>
														<option <?php selected( 'included_warranty_limited', $warranty['type'] . '_' . $warranty_length ); ?> value="limited"><?php esc_html_e( 'Limited', 'woocommerce-warranty' ); ?></option>
													</select>
												</span>
								</label>
								<br class="clear" />

								<div class="inline-edit-group included-form" id="limited_warranty_row_<?php echo esc_attr( $category_id ); ?>">
									<label class="alignleft">
										<span class="title"><?php esc_html_e( 'Length', 'woocommerce-warranty' ); ?></span>
													<span class="input-text-wrap">
														<input type="text" class="input-text sized warranty_<?php echo esc_attr( $category_id ); ?>" size="3" name="category_limited_warranty_length_value[<?php echo esc_attr( $category_id ); ?>]" value="<?php echo 'included_warranty' === $warranty['type'] ? esc_attr( $warranty['value'] ) : ''; ?>" style="width: 50px;">
													</span>
									</label>
									<label class="alignleft">
										<?php $warranty_duration = isset( $warranty['duration'] ) ? $warranty['duration'] : ''; ?>
										<select name="category_limited_warranty_length_duration[<?php echo esc_attr( $category_id ); ?>]" class="warranty_<?php echo esc_attr( $category_id ); ?>" style="vertical-align: baseline;">
											<option <?php selected( 'included_warranty_days', $warranty['type'] . '_' . $warranty_duration ); ?>value="days"><?php esc_html_e( 'Days', 'woocommerce-warranty' ); ?></option>
											<option <?php selected( 'included_warranty_weeks', $warranty['type'] . '_' . $warranty_duration ); ?>value="weeks"><?php esc_html_e( 'Weeks', 'woocommerce-warranty' ); ?></option>
											<option <?php selected( 'included_warranty_months', $warranty['type'] . '_' . $warranty_duration ); ?>value="months"><?php esc_html_e( 'Months', 'woocommerce-warranty' ); ?></option>
											<option <?php selected( 'included_warranty_years', $warranty['type'] . '_' . $warranty_duration ); ?>value="years"><?php esc_html_e( 'Years', 'woocommerce-warranty' ); ?></option>
										</select>
									</label>
								</div>

							</div>
						</fieldset>

						<fieldset class="inline-edit-col-left">
							<div class="inline-edit-col addon-form">

								<div class="inline-edit-group">
									<label class="alignleft">
										<?php
										$no_warranty_option = isset( $warranty['no_warranty_option'] ) ? $warranty['no_warranty_option'] : '';
										?>
										<input type="checkbox" name="category_addon_no_warranty[<?php echo esc_attr( $category_id ); ?>]" id="addon_no_warranty" value="yes" <?php checked( 'yes', $no_warranty_option ); ?> class="checkbox warranty_<?php echo esc_attr( $category_id ); ?>" />
										<span class="checkbox-title"><?php esc_html_e( '"No Warranty" option', 'woocommerce-warranty' ); ?></span>
									</label>
								</div>

								<a style="float: right;" href="#" class="button btn-add-addon">&plus;</a>

								<div class="inline-edit-group">
									<table class="widefat">
										<thead>
										<tr>
											<th><?php esc_html_e( 'Cost', 'woocommerce-warranty' ); ?></th>
											<th><?php esc_html_e( 'Duration', 'woocommerce-warranty' ); ?></th>
											<th width="50">&nbsp;</th>
										</tr>
										</thead>
										<tbody class="addons-tbody">
										<?php
										if ( isset( $warranty['addons'] ) ) {
											foreach ( $warranty['addons'] as $addon ) :
												?>
											<tr>
												<td valign="middle">
													<span class="input"><b>+</b> <?php echo esc_html( $currency ); ?></span>
													<input type="text" name="category_addon_warranty_amount[<?php echo esc_attr( $category_id ); ?>][]" class="input-text sized warranty_<?php echo esc_attr( $category_id ); ?>" size="2" value="<?php echo esc_attr( $addon['amount'] ); ?>" />
												</td>
												<td valign="middle">
													<input type="text" class="input-text sized warranty_<?php echo esc_attr( $category_id ); ?>" size="2" name="category_addon_warranty_length_value[<?php echo esc_attr( $category_id ); ?>][]" value=" <?php echo 'addon_warranty' === $warranty['type'] ? esc_attr( $addon['value'] ) : ''; ?>" />
													<select name="category_addon_warranty_length_duration[<?php echo esc_attr( $category_id ); ?>][]" class="warranty_<?php echo esc_attr( $category_id ); ?>">
														<option <?php selected( 'addon_warranty_days', $warranty['type'] . '_' . $addon['duration'] ); ?> value="days"><?php esc_html_e( 'Days', 'woocommerce-warranty' ); ?></option>
														<option <?php selected( 'addon_warranty_weeks', $warranty['type'] . '_' . $addon['duration'] ); ?> value="weeks"><?php esc_html_e( 'Weeks', 'woocommerce-warranty' ); ?></option>
														<option <?php selected( 'addon_warranty_months', $warranty['type'] . '_' . $addon['duration'] ); ?> value="months"><?php esc_html_e( 'Months', 'woocommerce-warranty' ); ?></option>
														<option <?php selected( 'addon_warranty_years', $warranty['type'] . '_' . $addon['duration'] ); ?> value="years"><?php esc_html_e( 'Years', 'woocommerce-warranty' ); ?></option>
													</select>
												</td>
												<td><a class="button warranty_addon_remove" href="#">&times;</a></td>
											</tr>
																					<?php
										endforeach;
										}
										?>
										</tbody>

									</table>
								</div>
							</div>
						</fieldset>

						<p class="submit inline-edit-save">
							<a class="button-primary updateinline" data-target="edit_<?php echo esc_attr( $category_id ); ?>" href="#"><?php esc_html_e( 'Update', 'woocommerce-warranty' ); ?></a>
							<a class="button-secondary editinline" data-target="edit_<?php echo esc_attr( $category_id ); ?>" href="#"><?php esc_html_e( 'Close', 'woocommerce-warranty' ); ?></a>
							<br class="clear">
						</p>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<script type="text/html" id="addon_tpl">
			<tr>
				<td valign="middle">
					<span class="input"><b>+</b> <?php echo esc_html( $currency ); ?></span>
					<input type="text" name="category_addon_warranty_amount[{id}][]" class="input-text sized" size="2" value="" />
				</td>
				<td valign="middle">
					<input type="text" class="input-text sized" size="2" name="category_addon_warranty_length_value[{id}][]" value="" />
					<select name="category_addon_warranty_length_duration[{id}][]">
						<option value="days"><?php esc_html_e( 'Days', 'woocommerce-warranty' ); ?></option>
						<option value="weeks"><?php esc_html_e( 'Weeks', 'woocommerce-warranty' ); ?></option>
						<option value="months"><?php esc_html_e( 'Months', 'woocommerce-warranty' ); ?></option>
						<option value="years"><?php esc_html_e( 'Years', 'woocommerce-warranty' ); ?></option>
					</select>
				</td>
				<td><a class="button warranty_addon_remove" href="#">&times;</a></td>
			</tr>
		</script>
	</td>
</tr>
