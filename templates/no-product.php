<?php
/**
 * No Product Found Template
 * @package RoxWCMS
 * @version 1.0.0
 *
 */
if( ! function_exists( 'add_action' ) ) {
	header('HTTP/1.0 403 Forbidden');
	die("<h1>Forbidden</h1><br><br><p>Go Away!</p><hr><p>Just Go Away!!!</p>");
}
echo apply_filters( 'rox_wcms_no_product_html', sprintf(
	'<div class="rox-search-no-product">%s <p>%s</p></div>',
	get_roxWooMegaSearchSvgIcon( array( 'icon' => 'rox-sad', 'title' => 'Nothing Found' ) ),
	__( 'No products found.', 'woo-mega-search' )
) );
// End of file no-product.php