<?php
/**
 * See all search result template
 * @package RoxWCMS
 * @version 1.0.0
 *
 */
if( ! function_exists( 'add_action' ) ) {
	header('HTTP/1.0 403 Forbidden');
	die("<h1>Forbidden</h1><br><br><p>Go Away!</p><hr><p>Just Go Away!!!</p>");
}
echo apply_filters( 'rox_wcms_see_all_results_html', sprintf(
	'<div class="rox-search-see-all"><a href="%s">%s %s</a></div>',
	$seeAllProduct,
	__( 'See all results.', 'woo-mega-search' ),
	get_roxWooMegaSearchSvgIcon( array( 'icon' => 'rox-right-d-arrow' ) )
) );
// End of file see-all-results.php