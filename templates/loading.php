<?php
/**
 * Ajax Waiting Placeholder
 * @package RoxWCMS
 * @version 1.0.0
 *
 */
if( ! function_exists( 'add_action' ) ) {
	header('HTTP/1.0 403 Forbidden');
	die("<h1>Forbidden</h1><br><br><p>Go Away!</p><hr><p>Just Go Away!!!</p>");
}
echo apply_filters( 'rox_wcms_loading_html', sprintf(
		'<div class="rox-search-loading">%s <p>%s</p></div>',
		get_roxWooMegaSearchSvgIcon( array( 'icon' => 'rox-reload', 'title' => 'Nothing Found' ) ),
		__( 'Give us a minute, we are searching the database...', 'woo-mega-search' )
) );
// End of file loading.php