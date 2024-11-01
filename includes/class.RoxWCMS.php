<?php
/**
 * @package RoxWCMS
 * @subpackage SearchLite
 * @version 1.0.1
 * @since 1.0.0
 */
if( ! function_exists( 'add_action' ) ) {
	header('HTTP/1.0 403 Forbidden');
	die("<h1>Forbidden</h1><br><br><p>Go Away!</p><hr><p>Just Go Away!!!</p>");
}
/**
 * 
 * The Search
 *
 */
class RoxWCMS {
	/**
	 * Plugin Slug
	 * @access Private
	 * @var string
	 */
    private $plugin_name = 'rox_wc_mega_search';
	/**
	 * Plugin Version
	 * @access Private
	 * @var string
	 */
    private $plugin_version;
	/**
	 * Plugin Settings
	 * @access Private
	 * @var string
	 */
    private $settings = [];
	/**
	 * Form ID Index
	 * @access Static Public
	 * @var string
	 */
    public static $form_idx = 1;
	/**
	 * Plugin ajax action name
	 * @access Private
	 * @var string
	 */
    private $ajax_action;
    /**
     * The single instance of the class.
     *
     * @var RoxWCMS
     */
    protected static $instance;
    
    /**
     *
     * Ensures only one instance of this class is loaded or can be loaded.
     *
     * @return RoxWCMS
     */
    public static function getInstance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
    	$this->plugin_version = ROX_WOO_SEARCH_VERSION;
	    RoxWCMSIcons::getInstance();
    	$settings = RoxWCMSSettings::getInstance();
	    /**
	     * Default Settings
	     * @param array
	     */
	    $defaultSettings = apply_filters( $this->plugin_name . '_default_settings', array(
		    'max_results'        => 5,
	    ) );
	    /**
	     * Administrator's Settings
	     * @param array
	     */
	    $this->settings = apply_filters( $this->plugin_name . '_settings', wp_parse_args( $settings->get(), $defaultSettings ) );
	    unset( $defaultSettings );
	    /**
	     * Ajax Action Name
	     * @param string
	     */
	    $this->ajax_action = apply_filters( $this->plugin_name . '_ajax_action', '__rox_wcms' );
	    $this->init_hooks();
	    /**
	     * Plugin Loaded (after plugin init)
	     */
	    do_action( $this->plugin_name . '_loaded' );
    }
    /**
     * Hook into actions and filters.
     * @return void
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'init' ), 10 );
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts'), 10 );
        // ajax response
        add_action( 'wp_ajax_'.$this->ajax_action, array( $this, 'rox_wcms_ajax_response') );
        add_action( 'wp_ajax_nopriv_'.$this->ajax_action, array( $this, 'rox_wcms_ajax_response') );
        // shortcodes
        add_shortcode( 'rox_woo_search', array( $this, 'get_search_form' ) );
        add_filter( 'get_product_search_form', array( $this, 'get_search_form' ) );
        if( $this->settings['hijack_wp_search_form'] ) {
	        add_filter( 'get_search_form', array( $this, 'get_search_form' ) );
        }
	    add_action( 'admin_notices', array( $this, '__rox_admin_notice' ) );
	    add_action( 'rox-wcms-item-after', array( $this, 'single_item_action_buttons' ), 10, 1 );
    }
    
	function __rox_admin_notice() {
		if( ! rox_is_woocommerce_activated() ) {
			// @TODO Change/Update this error message
			printf( '<div class="notice notice-error"><p>%s</p></div>', __( 'WooCommerce Mega Search needs WooCommerce. Please, install and active WooCommerce.', 'woo-mega-search' ) );
		}
	}
    /**
     * Init
     * @return void
     */
    public function init() {
        // Before init action.
        do_action( 'before_' . $this->plugin_name . '_init' );
	    // Set up localisation.
        $this->load_plugin_textdomain();
        // Init action.
        do_action( $this->plugin_name . '_init' );
    }
    /**
     * Enqueue frontend script and styles
     * @return void
     */
    public function frontend_scripts() {
        $loading = '';
        ob_start();
        $this->__get_template( 'loading' );
        $loading = ob_get_clean();
        $prefix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';
	    /**
	     *
	     */
        $rox_wcms_js_conf = apply_filters( $this->plugin_name . '_js_configs', array(
        		'rox_ajax'          => $this->ajax_url(),
        		'rox_csrf'          => wp_create_nonce( '__csrf' . $this->plugin_name ),
        		'rox_action'        => $this->ajax_action,
            	'rox_loading'       => $loading,
        		'rox_error'         => __( 'We encountered an error while processing your request. Please try again after sometime.', 'woo-mega-search' ),
	            'icons'              => array(
	            	'rox_tick'  => get_roxWooMegaSearchSvgIcon( array( 'icon' => 'rox-tick' ) ),
		            'rox_loading'  => get_roxWooMegaSearchSvgIcon( array( 'icon' => 'rox-reload' ) ),
	            ),
        ) );
        wp_enqueue_script( 'jquery' );
        wp_register_script( $this->plugin_name . '_frontend_script', $this->plugin_url( 'assets/js/scripts'.$prefix.'.js' ), array( 'jquery' ), $this->plugin_version, true );
        wp_localize_script( $this->plugin_name . '_frontend_script', 'rox_woo_search', $rox_wcms_js_conf );
        wp_enqueue_script( $this->plugin_name . '_frontend_script' );
        wp_enqueue_style( $this->plugin_name . '_frontend_style', $this->plugin_url( 'assets/css/styles.css' ), array(), $this->plugin_version );
	    $customizer = $this->__get_customized_css();
	    if( ! empty( $customizer ) ) {
		    wp_add_inline_style( $this->plugin_name . '_frontend_style', $customizer );
	    }
    }
	
	/**
	 * Get Customized Styles
	 *
	 * @return string
	 */
    private function __get_customized_css() {
    	$css = '';
	    // @todo font-color
	    // icons
	    if( isset( $this->settings['search_icon_color'] ) && $this->settings['search_icon_color'] !== '#56b1f1' ) {
		    $css .= ".rox-icon svg.icon-rox-search{fill:{$this->settings['search_icon_color']};}";
	    }
	    if( isset( $this->settings['close_icon_color'] ) && $this->settings['close_icon_color'] !== '#7f7f7f' ) {
		    $css .= ".rox-icon svg.icon-rox-close{fill:{$this->settings['close_icon_color']};}";
	    }
	    if( isset( $this->settings['no_product_icon_color'] ) && $this->settings['no_product_icon_color'] !== '#56b1f1' ) {
		    $css .= ".rox-icon svg.icon-rox-sad{fill:{$this->settings['no_product_icon_color']};}";
	    }
	    if( isset( $this->settings['reload_icon_color'] ) && $this->settings['reload_icon_color'] !== '#56b1f1' ) {
		    $css .= ".rox-icon svg.icon-rox-reload{fill:{$this->settings['reload_icon_color']};}";
	    }
    	return $css;
    }
    /**
     * Frontend Ajax Responses
     * @return void
     */
    public function rox_wcms_ajax_response() {
    	if( wp_verify_nonce( $_REQUEST['_nonce'], '__csrf' . $this->plugin_name ) ) {
		    $search = sanitize_text_field( $_REQUEST['s'] );
		    $result = $this->__do_search( $search );
		    wp_send_json_success( array(
			    'result' => $result['product'],
			    'count'  => $result['count'],
		    ) );
        } else {
            wp_send_json_error( array(
                'message' => __( 'Invalid CSRF', 'woo-mega-search' ),
            ) );
        }
    	die();
    }
	
	/**
	 * The Search.
	 * Search for products and return result data
	 * @param string $rox_wcms_request
	 * @param string $post_type
	 * @return array
	 */
    private function __do_search( $rox_wcms_request = null, $post_type = 'product' ) {
        if( empty( $rox_wcms_request ) ) {
            ob_start();
            $this->__get_template( 'no-product' );
            $results = ob_get_clean();
            return array(
                'product' => $results,
                'count'  => 0,
            );
        }
        $query = new WP_Query( array(
            'post_type'        => 'product',
            's'                => $rox_wcms_request,
            'posts_per_page'   => $this->settings['max_results'],
            'fields'           => 'ids',
        ) );
        $seeAllProduct = add_query_arg(
	        array( 's' => ( isset( $_REQUEST['s'] ) ) ? $rox_wcms_request : '', 'post_type' => 'product' ),
	        get_permalink( wc_get_page_id( 'shop' ) )
        );
        $GLOBALS['_s'] = $rox_wcms_request;
	    global $product;
        $results = '';
        ob_start();
        if( $query->have_posts() ) {
        	echo apply_filters( 'rox_wcms_result_count', sprintf(
		        '<div class="rox-result-count"><a href="%s">' . _n( '%d result for “%s”', '%d results for “%s”', $query->found_posts, 'rox-woo-search' ) . '</a></div>',
		        $seeAllProduct, $query->found_posts, esc_attr( $rox_wcms_request )
	        ), $seeAllProduct, $query->found_posts, esc_attr( $rox_wcms_request ) );
            echo '<div class="rox_items">';
            while( $query->have_posts() ) {
            	$query->the_post();
	            wc_setup_product_data( get_the_ID() );
            	$this->__get_template( 'single-product' );
            }
            wp_reset_query();
            echo '</div>';
            if( $query->found_posts > $this->settings['max_results'] ) {
            	$this->__get_template( 'see-all-results', array( 'seeAllProduct' => $seeAllProduct ) );
            }
        } else {
        	$this->__get_template( 'no-product' );
        }
        $results = ob_get_clean();
	    $_product = $product;
        return array(
            'product' => $results,
            'count'  => $query->found_posts,
        );
    }
	
	/**
	 * Return Search form for shortcode and widgets
	 *
	 * @return string
	 */
    public function get_search_form(){
        ob_start();
	    $this->__get_template( 'search-form', array( 'form_idx' => self::$form_idx ) );
	    $form = ob_get_clean();
	    self::$form_idx++;
        return sprintf( '<div class="rox-search-container woocommerce">%s<div class="rox-search-results"><div class="result-contents"></div></div></div>', $form );
    }
	
	/**
	 * callback for item_action.
	 * This will display the actions (buttons, ratings etc.).
	 * @return void
	 */
    public function single_item_action_buttons() {
    	$items = '';
    	$classes = array( 'rox-actions' );
    	$classes[] = $this->settings['product_rating'] ? $this->settings['add_to_cart']? '' : ' visible-rating' : '';
	    $classes = apply_filters( 'rox_wcms_product_action_classes', $classes, get_the_ID() );
	    if( $this->settings['add_to_cart'] ) {
		    add_filter( 'woocommerce_product_add_to_cart_url', 'rox_wcms_product_add_to_cart_url_ajax_fx', 10, 2 );
		    add_filter( 'woocommerce_loop_add_to_cart_args', 'rox_wcms_product_add_to_cart_button_args', 10, 2 );
		    ob_start();
		    woocommerce_template_loop_add_to_cart();
		    $items .= ob_get_clean();
	    }
	    if( $this->settings['product_rating'] ) {
		    $items .= rox_product_rating_html();
	    }
	    
	    $items = apply_filters( 'rox_wcms_product_action_items', $items, get_the_ID() );
	    printf( '<div class="%s">%s</div>', implode( ' ', $classes ), $items );
    }

    /**
     * Include Template file
     * @param string $name
     * @param array $args
     * @param bool $load_once
     * @return void
     */
    public function __get_template( $name, $args = array(), $load_once = false ) {
	    if ( ! empty( $args ) && is_array( $args ) ) {
		    extract( $args );
	    }
    	$fn = $name;
    	$fn = ltrim( $fn, '/' );
        $paths = array(
        		'theme' => get_template_directory() . $this->__template_path() . '/' . $fn . '.php',
        		'core' => $this->plugin_path( ROX_WOO_SEARCH_LITE_TEMPLATES . '/' . $fn . '.php' ),
        );
        foreach( $paths as $ctx => $path ) {
            $fn = apply_filters( $this->plugin_name . '_template', $path, $ctx );
            if( file_exists( $fn ) ) {
            	if( $load_once ) require_once( $fn );
                else require( $fn ); // files that need to be include in loop
                break;
            }
        }
    }
    /**
     * Get Template Content
     * @param $name
     * @return false|string
     */
    function __get_template_content( $name ) {
        ob_start();
        $this->__get_template( $name );
        return ob_get_clean();
    }
	
	/**
	 * Get Plugin Settings.
	 *
	 * @return array
	 */
    public function get_settings() {
    	return $this->settings;
    }
    /**
     * Load Language files
     * @return void
     */
    public function load_plugin_textdomain() {
    	load_plugin_textdomain( 'woo-mega-search', false, ROX_WOO_SEARCH_LITE_URL . '/languages' );
    }
    /**
     * Get the plugin url.
     * @param string $file
     * @return string
     */
    public function plugin_url( $file = null ) {
    	if( ! $file ) return untrailingslashit( ROX_WOO_SEARCH_LITE_URL );
        $file = ltrim( $file, '/' );
        return untrailingslashit( ROX_WOO_SEARCH_LITE_URL ) . '/' . $file;
    }
    /**
     * Get the plugin path.
     * @param string $file
     * @return string
     */
    public function plugin_path( $file = null ) {
    	if( ! $file ) return untrailingslashit( ROX_WOO_SEARCH_LITE_PATH );
        $file = ltrim( $file, '/' );
        return untrailingslashit( ROX_WOO_SEARCH_LITE_PATH ) . '/' . $file;
    }
    /**
     * Get the template path.
     *
     * @return string
     */
    private function __template_path() {
        $path = apply_filters( 'rox_wcms_emplate_path', 'rox_woo_search' );
        $path = ltrim( $path, '/' );
        $path = rtrim( $path, '/' );
        return $path;
    }
    /**
     * Get Ajax URL.
     *
     * @return string
     */
    public function ajax_url() {
        return admin_url( 'admin-ajax.php' );
    }
	// stop magician
    public function __get( $key ) {}
    public function __set( $key, $value ) {}
    private function __clone() {}
    private function __sleep() {}
    private function __wakeup() {}
}
// End of file class.rox.php