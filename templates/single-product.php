<?php
/**
 * Single Product layout
 */
if( ! function_exists( 'add_action' ) ) {
	header('HTTP/1.0 403 Forbidden');
	die("<h1>Forbidden</h1><br><br><p>Go Away!</p><hr><p>Just Go Away!!!</p>");
}
?>
<div <?php rox_wcms_single_item_class(); ?> data-product_id="<?php the_ID(); ?>">
	<?php do_action( 'rox-wcms-item-before', get_the_id() ); ?>
	<?php if( has_post_thumbnail() ) { ?>
    <div class="item-image">
    	<a href="<?php the_permalink(); ?>">
        	<?php the_post_thumbnail(); ?>
        </a>
    </div>
    <?php } ?>
    <div class="item-content product-<?php the_ID(); ?>">
        <?php echo rox_product_discount(); ?>
        <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
        <div class="item-price">
        	<a href="<?php the_permalink(); ?>">
            	<?php echo rox_product_price(); ?>
            </a>
        </div>
    </div>
    <?php do_action( 'rox-wcms-item-after', get_the_ID() ); ?>
</div>
<?php
// End of file single-product.php