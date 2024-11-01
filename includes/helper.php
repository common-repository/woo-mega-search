<?php
/**
 * Core Helper functions
 * @version 1.0.0
 */
if( ! function_exists( 'add_action' ) ) {
	header('HTTP/1.0 403 Forbidden');
	die("<h1>Forbidden</h1><br><br><p>Go Away!</p><hr><p>Just Go Away!!!</p>");
}
function rox_wcms_product_add_to_cart_url_ajax_fx( $url, \WC_Product $product ) {
	remove_filter( 'woocommerce_product_add_to_cart_url', __FUNCTION__, 10 );
	if( $product->is_type( 'simple' ) ) {
		if( strpos( $url, 'ajax' ) !== FALSE ) {
			$url = $product->is_purchasable() && $product->is_in_stock() ? add_query_arg( array( 'add-to-cart' => $product->get_id() ), get_permalink( $product->get_id() ) ) : get_permalink( $product->get_id() );
		}
	}
	return $url;
}
function rox_wcms_product_add_to_cart_button_args( $args, \WC_Product $product ) {
	remove_filter( 'rox_wcms_product_add_to_cart_button_args', __FUNCTION__, 10 );
	$args['class'] .= ' rox-wcms-btn';
	$args['class'] = trim( $args['class'] );
	return $args;
}
/**
 * Get Product Ratign markup
 * @param int $ID		current product id
 * @return string|null
 */
function rox_product_rating_html( $ID = NULL ) {
	if( ! $ID ) $ID = get_the_ID();
	$product = wc_get_product( $ID );
	if( $product instanceof WC_Product ) {
		$ratingHtml = wc_get_star_rating_html( $product->get_average_rating(), $product->get_rating_count() );
		return apply_filters( 'rox_wcms_rating_html', sprintf( '<div class="rox-wcms-item-rating"><div class="star-rating">%s</div></div>', $ratingHtml ), $product->get_average_rating(), $product->get_rating_count() );
	}
	return;
}
/**
 * Get Product discount
 *
 * @param int $ID		    Product ID
 * @param int $precision    The optional number of decimal digits to round to.
 * @param boolean $markup
 * @return string|float|false
 */
function rox_product_discount( $ID = NULL, $precision = 0, $markup = true ) {
	if( ! $ID ) $ID = get_the_ID();
	$product = wc_get_product( $ID );
	$discount = 0;
	if( $product instanceof WC_Product ) {
		$sale_price = $product->get_sale_price();
		$regular_price = $product->get_regular_price();
		if( ! empty( $sale_price ) && $regular_price > $sale_price  ) {
			$discount = ( ( $regular_price - $sale_price ) /$regular_price) * 100;
			$discount = round( $discount, $precision );
		}
	}
	if( $discount > 0 ) {
		if( $markup ) {
			return apply_filters( 'rox_wcms_product_discount' , '<span class="discount">'.$discount.'%</span>', $discount, $product->get_id() );
		} else return $discount;
	}
	return false;
}
/**
 * Get Product Price
 * @param int $ID		current product id
 * @return string|null					woocommerce product price
 */
function rox_product_price( $ID = NULL ) {
	if( ! $ID ) $ID = get_the_ID();
	$product = wc_get_product( $ID );
	if( $product instanceof WC_Product ) return $product->get_price_html();
	return;
}
/**
 * Check if product in WC cart
 * @param int $product_id
 * @param int $veriation_id
 * @return bool
 */
function rox_check_product_in_cart( $product_id, $veriation_id = 0 ) {
    $product_id   = absint( $product_id );
    $variation_id = absint( $veriation_id );
    if ( 'product_variation' === get_post_type( $product_id ) ) {
        $variation_id = $product_id;
        $product_id   = wp_get_post_parent_id( $variation_id );
    }
    // Generate a ID based on product ID, variation ID, variation data, and other cart item data.
    $cart_id = WC()->cart->generate_cart_id( $product_id, $variation_id, array(), array() );
    // Find the cart item key in the existing cart.
    $cart_item_key = WC()->cart->find_product_in_cart( $cart_id );
    if( ! empty( $cart_item_key ) ) return true;
    return false;
}
/**
 * Retrieve the classes for the body element as an array.
 * @param string|array  $class One or more classes to add to the class list.
 * @return array        Array of classes.
 */
function get_rox_wcms_single_item_class( $class = "" ){
	$classes = array( 'rox-wcms-item' );
	$product = wc_get_product( get_the_ID() );
	$classes[] = $product->get_type();
	if( $product->is_in_stock() ) $classes[] = 'in_stock';
	if( rox_check_product_in_cart( get_the_ID() ) ) $classes[] = 'in_cart';
	if ( ! empty( $class ) ) {
		if ( !is_array( $class ) ) $class = preg_split( '#\s+#', $class );
		$classes = array_merge( $classes, $class );
	} else {
		// Ensure that we always coerce class to being an array.
		$class = array();
	}
	$classes = array_map( 'esc_attr', $classes );
	/**
	 * Filters the list of CSS body classes for the current post or page.
	 * @param array $classes An array of body classes.
	 * @param array $class   An array of additional classes added to the body.
	 * @param int $ID        Product ID
	 */
	$classes = apply_filters( 'rox_wcms_single-item_class', $classes, $class, get_the_ID() );
	
	return array_unique( $classes );
	
}
/**
 * Display the classes for searched products
 * @param string|array $class One or more classes to add to the class list.
 */
function rox_wcms_single_item_class( $class = "" ) {
	echo 'class="' . join( ' ', get_rox_wcms_single_item_class( $class ) ) . '"';
}
/**
 * Check WC Product Type
 * @param string $type
 * @param int $ID
 * @return bool
 */
function rox_wcms_is_type( $type, $ID = NULL ) {
	if( ! $ID ) $ID = get_the_ID();
	$product = wc_get_product( $ID );
	return $product->is_type( $type );
}
/**
 * Get Add to cart URL
 * @param int $ID
 * @return string
 */
function rox_wcms_add_to_cart_url( $ID = NULL ) {
	if( ! $ID ) $ID = get_the_ID();
	$href = get_permalink( $ID );
	if( rox_wcms_is_type( 'simple', $ID ) ) {
		$href = add_query_arg( array( 'add-to-cart' => $ID, ), $href );
	}
	return $href;
}
/**
 * Get Add to cart Text
 * @param int $ID
 * @return string
 */
function rox_wcms_add_to_cart_text( $ID = NULL ) {
	if( ! $ID ) $ID = get_the_ID();
	$product = wc_get_product( $ID );
	return $product->add_to_cart_text();
}
/**
 * Include Template file
 * @param string $name
 * @param array $args
 * @param bool $load_once
 * @return void
 */
function rox_wcms_get_template( $name, $args = array(), $load_once = false ) {
	RoxWCMS()->__get_template( $name, $args = array(), $load_once = false );
}
/**
 * Output admin fields.
 *
 * Loops though the woocommerce options array and outputs each field.
 *
 * @param array[] $options Opens array to output.
 * @return void
 */
function rox_wcms_output_admin_fields( $options ) {
	foreach ( $options as $value ) {
		if ( ! isset( $value['type'] ) ) continue;
		if ( ! isset( $value['id'] ) ) $value['id'] = '';
		if ( ! isset( $value['title'] ) ) $value['title'] = isset( $value['name'] ) ? $value['name'] : '';
		if ( ! isset( $value['class'] ) ) $value['class'] = '';
		if ( ! isset( $value['css'] ) ) $value['css'] = '';
		if ( ! isset( $value['default'] ) ) $value['default'] = '';
		if ( ! isset( $value['desc'] ) ) $value['desc'] = '';
		if ( ! isset( $value['desc_tip'] ) ) $value['desc_tip'] = false;
		if ( ! isset( $value['placeholder'] ) ) $value['placeholder'] = '';
		if ( ! isset( $value['suffix'] ) ) $value['suffix'] = '';
		// Custom attribute handling.
		$custom_attributes = array();
		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
		// Description handling.
		$field_description = rox_wc_get_field_description( $value );
		$description       = $field_description['description'];
		$tooltip_html      = $field_description['tooltip_html'];
		// Switch based on type.
		switch ( $value['type'] ) {
			case 'title': // Section Titles.
				if ( ! empty( $value['title'] ) ) echo '<h2>' . esc_html( $value['title'] ) . '</h2>';
				if ( ! empty( $value['desc'] ) ) {
					echo '<div id="' . esc_attr( sanitize_title( $value['id'] ) ) . '-description">';
					echo wp_kses_post( wpautop( wptexturize( $value['desc'] ) ) );
					echo '</div>';
				}
				echo '<table class="form-table">' . "\n\n";
				if ( ! empty( $value['id'] ) ) do_action( 'woocommerce_settings_' . sanitize_title( $value['id'] ) );
			break;
			case 'sectionend': // Section Ends.
				if ( ! empty( $value['id'] ) ) do_action( 'woocommerce_settings_' . sanitize_title( $value['id'] ) . '_end' );
				echo '</table>';
				if ( ! empty( $value['id'] ) ) do_action( 'woocommerce_settings_' . sanitize_title( $value['id'] ) . '_after' );
			break;
				// Standard text inputs and subtypes like 'number'.
			case 'text':
			case 'password':
			case 'datetime':
			case 'datetime-local':
			case 'date':
			case 'month':
			case 'time':
			case 'week':
			case 'number':
			case 'email':
			case 'url':
			case 'tel':
				$option_value = woocommerce_settings_get_option( $value['id'], $value['default'] );
			?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
					</th>
					<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
						<input
							name="<?php echo esc_attr( $value['id'] ); ?>"
							id="<?php echo esc_attr( $value['id'] ); ?>"
							type="<?php echo esc_attr( $value['type'] ); ?>"
							style="<?php echo esc_attr( $value['css'] ); ?>"
							value="<?php echo esc_attr( $option_value ); ?>"
							class="<?php echo esc_attr( $value['class'] ); ?>"
							placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
							/><?php echo esc_html( $value['suffix'] ); ?> <?php echo $description; // WPCS: XSS ok. ?>
					</td>
				</tr>
			<?php
			break;
			case 'color': // Color picker.
			$option_value = woocommerce_settings_get_option( $value['id'], $value['default'] );
			?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
					</th>
					<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">&lrm;
						<span class="rox_colorpickpreview" style="background: <?php echo esc_attr( $option_value ); ?>">&nbsp;</span>
						<input
							name="<?php echo esc_attr( $value['id'] ); ?>"
							id="<?php echo esc_attr( $value['id'] ); ?>"
							type="text"
							dir="ltr"
							style="<?php echo esc_attr( $value['css'] ); ?>"
							value="<?php echo esc_attr( $option_value ); ?>"
							class="<?php echo esc_attr( $value['class'] ); ?>rox_colorpick"
							placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
							/>&lrm; <?php echo $description; // WPCS: XSS ok. ?>
							<div id="colorPickerDiv_<?php echo esc_attr( $value['id'] ); ?>" class="rox_colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>
					</td>
				</tr>
			<?php
			break;
			case 'textarea': // Textarea.
				$option_value = woocommerce_settings_get_option( $value['id'], $value['default'] );
			?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
					</th>
					<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
						<?php echo $description; // WPCS: XSS ok. ?>

						<textarea
							name="<?php echo esc_attr( $value['id'] ); ?>"
							id="<?php echo esc_attr( $value['id'] ); ?>"
							style="<?php echo esc_attr( $value['css'] ); ?>"
							class="<?php echo esc_attr( $value['class'] ); ?>"
							placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
							><?php echo esc_textarea( $option_value ); // WPCS: XSS ok. ?></textarea>
					</td>
				</tr>
			<?php
			break;
			case 'select': // Select boxes.
			case 'multiselect':
				$option_value = woocommerce_settings_get_option( $value['id'], $value['default'] );
			?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
					</th>
					<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
						<select
							name="<?php echo esc_attr( $value['id'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
							id="<?php echo esc_attr( $value['id'] ); ?>"
							style="<?php echo esc_attr( $value['css'] ); ?>"
							class="<?php echo esc_attr( $value['class'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
							<?php echo 'multiselect' === $value['type'] ? 'multiple="multiple"' : ''; ?>
							>
							<?php
							foreach ( $value['options'] as $key => $val ) {
								?>
								<option value="<?php echo esc_attr( $key ); ?>"
									<?php

									if ( is_array( $option_value ) ) {
										selected( in_array( (string) $key, $option_value, true ), true );
									} else {
										selected( $option_value, (string) $key );
									}

								?>
								>
								<?php echo esc_html( $val ); ?></option>
								<?php
							}
							?>
						</select> <?php echo $description; // WPCS: XSS ok. ?>
					</td>
				</tr>
			<?php
			break;
			case 'radio': // Radio inputs.
				$option_value = woocommerce_settings_get_option( $value['id'], $value['default'] );
			?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
					</th>
					<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
						<fieldset>
							<?php echo $description; // WPCS: XSS ok. ?>
							<ul>
							<?php
							foreach ( $value['options'] as $key => $val ) {
								$label = '';
								if( is_array( $val ) && isset( $val['svg_icon'] ) ) {
									$iconArgs = array(
											'icon'  => $val['svg_icon'],
									);
									if( isset( $val['title'] ) ) {
										$iconArgs['title'] = $val['title'];
									}
									$label .= sprintf( '<span class="icon">%s</span>', get_roxWooMegaSearchSvgIcon( $iconArgs ) );
								} else {
									$label .= $val;
								}
								?>
								<li>
									<label><input
										name="<?php echo esc_attr( $value['id'] ); ?>"
										value="<?php echo esc_attr( $key ); ?>"
										type="radio"
										style="<?php echo esc_attr( $value['css'] ); ?>"
										class="<?php echo esc_attr( $value['class'] ); ?>"
										<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
										<?php checked( $key, $option_value ); ?>
										/> <?php echo $label; ?></label>
								</li>
								<?php
							}
							?>
							</ul>
						</fieldset>
					</td>
				</tr>
			<?php
			break;
			case 'checkbox': // Checkbox input.
				$option_value     = woocommerce_settings_get_option( $value['id'], $value['default'] );
				$visibility_class = array();
				if ( ! isset( $value['hide_if_checked'] ) ) $value['hide_if_checked'] = false;
				if ( ! isset( $value['show_if_checked'] ) ) $value['show_if_checked'] = false;
				if ( 'yes' === $value['hide_if_checked'] || 'yes' === $value['show_if_checked'] ) $visibility_class[] = 'hidden_option';
				if ( 'option' === $value['hide_if_checked'] ) $visibility_class[] = 'hide_options_if_checked';
				if ( 'option' === $value['show_if_checked'] ) $visibility_class[] = 'show_options_if_checked';
				if ( ! isset( $value['checkboxgroup'] ) || 'start' === $value['checkboxgroup'] ) {
			?>
				<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
					<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?></th>
					<td class="forminp forminp-checkbox">
						<fieldset><?php
				} else { ?>
						<fieldset class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
				<?php }
				if ( ! empty( $value['title'] ) ) { ?>
							<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ); ?></span></legend><?php
				} ?>
							<label for="<?php echo esc_attr( $value['id'] ); ?>">
								<input
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									type="checkbox"
									class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
									value="1"
									<?php checked( $option_value, 'yes' ); ?>
									<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
								/> <?php echo $description; // WPCS: XSS ok. ?>
							</label> <?php echo $tooltip_html; // WPCS: XSS ok. ?>
				<?php
				if ( ! isset( $value['checkboxgroup'] ) || 'end' === $value['checkboxgroup'] ) { ?>
						</fieldset>
					</td>
				</tr>
				<?php
				} else { ?>
						</fieldset>
			<?php }
			break;
			case 'image_width': // Image width settings. @todo deprecate and remove in 4.0. No longer needed by core.
				$image_size       = str_replace( '_image_size', '', $value['id'] );
				$size             = wc_get_image_size( $image_size );
				$width            = isset( $size['width'] ) ? $size['width'] : $value['default']['width'];
				$height           = isset( $size['height'] ) ? $size['height'] : $value['default']['height'];
				$crop             = isset( $size['crop'] ) ? $size['crop'] : $value['default']['crop'];
				$disabled_attr    = '';
				$disabled_message = '';

				if ( has_filter( 'woocommerce_get_image_size_' . $image_size ) ) {
					$disabled_attr    = 'disabled="disabled"';
					$disabled_message = '<p><small>' . esc_html__( 'The settings of this image size have been disabled because its values are being overwritten by a filter.', 'woocommerce' ) . '</small></p>';
				}
			?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html . $disabled_message; // WPCS: XSS ok. ?></label>
					</th>
					<td class="forminp image_width_settings">
						<input name="<?php echo esc_attr( $value['id'] ); ?>[width]" <?php echo $disabled_attr; // WPCS: XSS ok. ?> id="<?php echo esc_attr( $value['id'] ); ?>-width" type="text" size="3" value="<?php echo esc_attr( $width ); ?>" /> &times; <input name="<?php echo esc_attr( $value['id'] ); ?>[height]" <?php echo $disabled_attr; // WPCS: XSS ok. ?> id="<?php echo esc_attr( $value['id'] ); ?>-height" type="text" size="3" value="<?php echo esc_attr( $height ); ?>" />px
						<label><input name="<?php echo esc_attr( $value['id'] ); ?>[crop]" <?php echo $disabled_attr; // WPCS: XSS ok. ?> id="<?php echo esc_attr( $value['id'] ); ?>-crop" type="checkbox" value="1" <?php checked( 1, $crop ); ?> /> <?php esc_html_e( 'Hard crop?', 'woocommerce' ); ?></label>
					</td>
				</tr>
			<?php
			break;
			case 'single_select_page': // Single page selects.
				$args = array(
					'name'             => $value['id'],
					'id'               => $value['id'],
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'class'            => $value['class'],
					'echo'             => false,
					'selected'         => absint( woocommerce_settings_get_option( $value['id'], $value['default'] ) ),
					'post_status'      => 'publish,private,draft',
				);
				if ( isset( $value['args'] ) ) $args = wp_parse_args( $value['args'], $args );
			?>
				<tr valign="top" class="single_select_page">
					<th scope="row" class="titledesc">
						<label><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
					</th>
					<td class="forminp">
						<?php echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'woocommerce' ) . "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) ); // WPCS: XSS ok. ?> <?php echo $description; // WPCS: XSS ok. ?>
					</td>
				</tr>
			<?php
			break;
			case 'single_select_country': // Single country selects.
				$country_setting = (string) woocommerce_settings_get_option( $value['id'], $value['default'] );
				if ( strstr( $country_setting, ':' ) ) {
					$country_setting = explode( ':', $country_setting );
					$country         = current( $country_setting );
					$state           = end( $country_setting );
				} else {
					$country = $country_setting;
					$state   = '*';
				}
			?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
					</th>
					<td class="forminp"><select name="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" data-placeholder="<?php esc_attr_e( 'Choose a country&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Country', 'woocommerce' ); ?>" class="wc-enhanced-select">
						<?php WC()->countries->country_dropdown_options( $country, $state ); ?>
					</select> <?php echo $description; // WPCS: XSS ok. ?>
					</td>
				</tr>
			<?php
			break;
			case 'multi_select_countries': // Country multiselects.
				$selections = (array) woocommerce_settings_get_option( $value['id'], $value['default'] );
				if ( ! empty( $value['options'] ) ) $countries = $value['options'];
				else $countries = WC()->countries->countries;
				asort( $countries );
			?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
					</th>
					<td class="forminp">
						<select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" style="width:350px" data-placeholder="<?php esc_attr_e( 'Choose countries&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Country', 'woocommerce' ); ?>" class="wc-enhanced-select">
							<?php
							if ( ! empty( $countries ) ) {
								foreach ( $countries as $key => $val ) {
									echo '<option value="' . esc_attr( $key ) . '"' . wc_selected( $key, $selections ) . '>' . esc_html( $val ) . '</option>'; // WPCS: XSS ok.
								}
							}
							?>
						</select> <?php echo ( $description ) ? $description : ''; // WPCS: XSS ok. ?> <br /><a class="select_all button" href="#"><?php esc_html_e( 'Select all', 'woocommerce' ); ?></a> <a class="select_none button" href="#"><?php esc_html_e( 'Select none', 'woocommerce' ); ?></a>
					</td>
				</tr>
			<?php
			break;
			case 'relative_date_selector': // Days/months/years selector.
				$periods      = array(
					'days'   => __( 'Day(s)', 'woocommerce' ),
					'weeks'  => __( 'Week(s)', 'woocommerce' ),
					'months' => __( 'Month(s)', 'woocommerce' ),
					'years'  => __( 'Year(s)', 'woocommerce' ),
				);
				$option_value = wc_parse_relative_date_option( woocommerce_settings_get_option( $value['id'], $value['default'] ) );
			?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
					</th>
					<td class="forminp">
						<input
							name="<?php echo esc_attr( $value['id'] ); ?>[number]"
							id="<?php echo esc_attr( $value['id'] ); ?>"
							type="number"
							style="width: 80px;"
							value="<?php echo esc_attr( $option_value['number'] ); ?>"
							class="<?php echo esc_attr( $value['class'] ); ?>"
							placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
							step="1"
							min="1"
							<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
						/>&nbsp;
						<select name="<?php echo esc_attr( $value['id'] ); ?>[unit]" style="width: auto;">
							<?php foreach ( $periods as $value => $label ) echo '<option value="' . esc_attr( $value ) . '"' . selected( $option_value['unit'], $value, false ) . '>' . esc_html( $label ) . '</option>'; ?>
						</select> <?php echo ( $description ) ? $description : ''; // WPCS: XSS ok. ?>
					</td>
				</tr>
			<?php
			break;
			case 'switch':
				if( empty( $value['default'] ) || ! is_numeric( $value['default'] ) ) $value['default'] = 0;
				if( is_numeric( $value['default'] ) && $value['default'] == 1 ) $value['default'] = 1;
				else $value['default'] = 0;
				$option_value = woocommerce_settings_get_option( $value['id'], $value['default'] );
				$on = isset( $value['text']['on'] ) ? $value['text']['on'] : __( 'On', 'woo-mega-search' );
				$off = isset( $value['text']['off'] ) ? $value['text']['off'] : __( 'On', 'woo-mega-search' );
			?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
					</th>
					<td class="forminp">
						<div class="switch">
							<label>
								<input type="checkbox" class="switch_control" name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" value="1"<?php checked( $option_value, 1 ); ?>>
								<span class="label" data-on="<?php echo $on; ?>" data-off="<?php echo $off; ?>"></span>
								<span class="handle"></span>
							</label>
						</div>
						<?php echo ( $description ) ? $description : ''; // WPCS: XSS ok. ?>
					</td>
				</tr>
			<?php
			break;
			default: // Default: run an action.
				do_action( 'woocommerce_admin_field_' . $value['type'], $value );
			break;
		}
	}
}
/**
 * Helper function to get the formatted description and tip HTML for a
 * given form field. Plugins can call this when implementing their own custom
 * settings types.
 *
 * @param  array $value The form field value array.
 * @return array The description and tip as a 2 element array.
 */
function rox_wc_get_field_description( $value ) {
	$description  = '';
	$tooltip_html = '';
	if ( true === $value['desc_tip'] ) {
		$tooltip_html = $value['desc'];
	} elseif ( ! empty( $value['desc_tip'] ) ) {
		$description  = $value['desc'];
		$tooltip_html = $value['desc_tip'];
	} elseif ( ! empty( $value['desc'] ) ) {
		$description = $value['desc'];
	}
	if ( $description && in_array( $value['type'], array( 'textarea', 'radio', 'switch', 'text' ), true ) ) {
		$description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
	} elseif ( $description && in_array( $value['type'], array( 'checkbox' ), true ) ) {
		$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
	} elseif ( $description ) {
		$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
	}
	if ( $tooltip_html && in_array( $value['type'], array( 'checkbox', 'switch' ), true ) ) {
		$tooltip_html = '<span class="description">' . $tooltip_html . '</span>';
	} elseif ( $tooltip_html ) {
		$tooltip_html = '<span class="description">' . $tooltip_html . '</span>';
	}
	return array(
		'description'  => $description,
		'tooltip_html' => $tooltip_html,
	);
}

if( ! function_exists( 'get_roxWooMegaSearchSvgIcon' ) ) {
	/**
	 * Return SVG markup.
	 * @param array $args {
	 *     Parameters needed to display an SVG.
	 *     @type string $icon  Required SVG icon filename.
	 *     @type string $title Optional SVG title.
	 *     @type string $desc  Optional SVG description.
	 * }
	 * @return string SVG markup.
	 */
	function get_roxWooMegaSearchSvgIcon( $args ) {
		return RoxWCMSIcons::get( $args );
	}
}
if( ! function_exists( 'roxWooMegaSearchSvgIcon' ) ) {
	/**
	 * Display SVG markup.
	 * @param array $args {
	 *     Parameters needed to display an SVG.
	 *     @type string $icon  Required SVG icon filename.
	 *     @type string $title Optional SVG title.
	 *     @type string $desc  Optional SVG description.
	 * }
	 * @return void
	 */
	function roxWooMegaSearchSvgIcon( $args ) {
		echo RoxWCMSIcons::get( $args );
	}
}
if( ! function_exists( 'rox_is_woocommerce_activated' ) ) {
	/**
	 * Check WooCommerce activation
	 * @return bool
	 */
	function rox_is_woocommerce_activated() {
		return class_exists( 'WooCommerce' ) ? true : false;
	}
}
// End of file helper.php