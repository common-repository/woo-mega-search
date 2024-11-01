<?php
/**
 * Search Template
 * @package RoxWCMS
 * @version 1.0.0
 *
 */
if( ! function_exists( 'add_action' ) ) {
	header('HTTP/1.0 403 Forbidden');
	die("<h1>Forbidden</h1><br><br><p>Go Away!</p><hr><p>Just Go Away!!!</p>");
}
?>
	<form class="rox-search" action="<?php echo get_permalink( wc_get_page_id( 'shop' ) ); ?>" method="get">
		<div class="rox-search-box">
			<label class="sr-only" for="_rox_wcm_search_<?php echo $form_idx; ?>" ><?php _e( 'Search Products', 'woo-mega-search' ); ?></label>
			<input autocomplete="off" class="rox_wcm_search" type="search" name="s" id="_rox_wcm_search_<?php echo $form_idx; ?>" value="<?php if( isset( $_REQUEST['s'] ) ) echo esc_attr( $_REQUEST['s'] ); ?>" placeholder="<?php echo esc_attr( apply_filters( 'rox_wcms_search_placeholder', __( 'Search Products', 'woo-mega-search' ) ) ); ?>">
			<input type="hidden" name="post_type" value="product">
			<?php echo apply_filters( 'rox_wcms_form_submit_btn', sprintf(
					'<a class="submit">%s</a>',
					get_roxWooMegaSearchSvgIcon( array( 'icon' => 'rox-search', 'title' => __( 'Search', 'woo-mega-search' ), ) )
			) ); ?>
			<?php echo apply_filters( 'rox_wcms_form_reset_btn', sprintf(
				'<a class="reset">%s</a>',
				get_roxWooMegaSearchSvgIcon( array( 'icon' => 'rox-close', 'title' => __( 'Reset', 'woo-mega-search' ), ) )
			) ); ?>
		</div>
	</form>
<?php
// End of file search-form.php