<?php

class RoxWCMSIcons {
	/**
	 * The single instance of the class.
	 *
	 * @var RoxWCMSIcons
	 */
	protected static $instance;
	
	/**
	 *
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @return RoxWCMSIcons
	 */
	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * RoxWCMSIcons constructor.
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_footer', 'RoxWCMSIcons::_include_svg_icons', 9999 );
		add_action( 'admin_head', 'RoxWCMSIcons::_include_svg_icons' );
	}
	/**
	 * Add SVG definitions to the footer.
	 * @return void
	 */
	public static function _include_svg_icons() {
		// Define SVG sprite file.
		$svg_icons = RoxWCMS()->plugin_path( 'assets/images/icons.svg' );
		// If it exists, include it.
		if ( file_exists( $svg_icons ) ) {
			$svg_icons = file_get_contents( $svg_icons );
			printf( '<div style="height:0px;width:0px;">%s</div>', $svg_icons );
		}
	}
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
	public static function get( $args = array() ) {
		// Make sure $args are an array.
		if ( empty( $args ) ) {
			return __( 'Please define default parameters in the form of an array.', 'woo-mega-search' );
		}
		// Define an icon.
		if ( false === array_key_exists( 'icon', $args ) ) {
			return __( 'Please define an SVG icon filename.', 'woo-mega-search' );
		}
		// Set defaults && Parse args.
		$args = wp_parse_args( $args, array(
			'class'       => '',
			'icon'        => '',
			'title'       => '',
			'desc'        => '',
			'fallback'    => false,
		) );
		// Set classes
		$classes = array( 'icon' );
		$classes[] = 'icon-' . $args['icon'];
		if( $args['class'] ) {
			if( ! is_array( $args['class'] ) ) {
				$args['class'] = explode( ' ', $args['class'] );
			}
			$classes = array_merge( $classes, $args['class'] );
			$classes = array_unique( $classes );
			
		}
		$classes = array_map( 'esc_attr', $classes );
		$classes = join( ' ', $classes );
		// Set aria hidden.
		$aria_hidden = ' aria-hidden="true"';
		// Set ARIA.
		$aria_labelledby = '';
		/*
		 * Twenty Seventeen doesn't use the SVG title or description attributes; non-decorative icons are described with .screen-reader-text.
		 * However, child themes can use the title and description to add information to non-decorative SVG icons to improve accessibility.
		 * Example 1 with title: <?php echo twentyseventeen_get_svg( array( 'icon' => 'arrow-right', 'title' => __( 'This is the title', 'textdomain' ) ) ); ?>
		 * Example 2 with title and description: <?php echo twentyseventeen_get_svg( array( 'icon' => 'arrow-right', 'title' => __( 'This is the title', 'textdomain' ), 'desc' => __( 'This is the description', 'textdomain' ) ) ); ?>
		 * See https://www.paciellogroup.com/blog/2013/12/using-aria-enhance-svg-accessibility/.
		 */
		if ( $args['title'] ) {
			$aria_hidden     = '';
			$unique_id       = uniqid();
			$aria_labelledby = ' aria-labelledby="title-' . $unique_id . '"';
			if ( $args['desc'] ) {
				$aria_labelledby = ' aria-labelledby="title-' . $unique_id . ' desc-' . $unique_id . '"';
			}
		}
		// Begin SVG markup.
		$svg  = '<span class="rox-icon">';
		$svg .= '<svg class="'.$classes.'"' . $aria_hidden . $aria_labelledby . ' role="img">';
		// Display the title.
		if ( $args['title'] ) {
			$svg .= '<title id="title-' . $unique_id . '">' . esc_html( $args['title'] ) . '</title>';
			// Display the desc only if the title is already set.
			if ( $args['desc'] ) {
				$svg .= '<desc id="desc-' . $unique_id . '">' . esc_html( $args['desc'] ) . '</desc>';
			}
		}
		/*
		 * Display the icon.
		 * The whitespace around `<use>` is intentional - it is a work around to a keyboard navigation bug in Safari 10.
		 * See https://core.trac.wordpress.org/ticket/38387.
		 */
		$svg .= ' <use href="#icon-' . esc_html( $args['icon'] ) . '" xlink:href="#icon-' . esc_html( $args['icon'] ) . '"></use> ';
		// Add some markup to use as a fallback for browsers that do not support SVGs.
		// Use js to detect no svg support and add no-svg class to html
		// Use css to render the fallback icon (hide the use tag if necessary)
		if ( $args['fallback'] ) {
			$svg .= '<span class="svg-fallback icon-' . esc_attr( $args['icon'] ) . '"></span>';
		}
		$svg .= '</svg>';
		$svg .= '</span>';
		return $svg;
	}
}