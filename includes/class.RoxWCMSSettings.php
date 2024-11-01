<?php
/**
 * @package RoxWCMS
 * @subpackage SearchLite
 *
 */
if( ! function_exists( 'add_action' ) ) {
	header('HTTP/1.0 403 Forbidden');
	die("<h1>Forbidden</h1><br><br><p>Go Away!</p><hr><p>Just Go Away!!!</p>");
}

class RoxWCMSSettings {
	/**
	 * Setting page id.
	 *
	 * @var string
	 */
	protected $id = '';
	/**
	 * Setting page label.
	 *
	 * @var string
	 */
	protected $label = '';
	/**
	 * The single instance of the class.
	 * @var RoxWCMSSettings
	 */
	protected static $instance;
	
	/**
	 *
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @return RoxWCMSSettings
	 */
	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id = 'rox_wcms_settings';
		$this->label = 'Mega Search';
		add_action( 'admin_enqueue_scripts', array( $this, 'settings_page_scripts'), 10 );
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 50 );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}
	/**
	 * Get settings page ID.
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get settings page label.
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Add this page to settings.
	 *
	 * @param array $pages
	 * @return mixed
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;
		return $pages;
	}
	public function settings_page_scripts( $hook ) {
		$prefix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';
		if( $hook == 'woocommerce_page_wc-settings' && ( isset( $_GET['tab'] ) && sanitize_text_field( $_GET['tab'] ) == $this->id ) ) {
			wp_enqueue_style( 'rox-woo-search-admin-styles', RoxWCMS()->plugin_url( 'assets/css/admin'.$prefix.'.css' ), array(), ROX_WOO_SEARCH_VERSION );
			wp_enqueue_script( 'rox-woo-search-admin-scripts', RoxWCMS()->plugin_url( 'assets/js/admin'.$prefix.'.js' ), array('jquery'), ROX_WOO_SEARCH_VERSION );
			wp_enqueue_script( 'iris' );
		}
	}
	/**
	 * Get settings array.
	 * @param string $section
	 * @return array
	 */
	public function get_settings( $section = '' ) {
		$fields = array();
		if( $section == 'customizer' ) {
			$fields = array(
					'section_title' => array(
							'name'       => __( 'Customization', 'woo-mega-search' ),
							'type'       => 'title',
							'id'         => $this->id . '_section_title',
					),
					'search_icon_color' => array(
							'name'                 => __( 'Search Icon Color', 'woo-mega-search' ),
							'type'                 => 'color',
							'desc'                 => '',
							'default'              => '#56b1f1',
							'id'                   => $this->id . '_search_icon_color',
							'autoload'             => false,
							'custom_attributes'    => array(
									'autocomplete'      => 'off',
									'data-svg_icon'     => json_encode( array( get_roxWooMegaSearchSvgIcon( array('icon'=>'rox-search') ) ) ),
							),
					),
					'close_icon_color' => array(
							'name'                 => __( 'Reset Icon Color', 'woo-mega-search' ),
							'type'                 => 'color',
							'desc'                 => '',
							'default'              => '#7f7f7f',
							'id'                   => $this->id . '_close_icon_color',
							'autoload'             => false,
							'custom_attributes'    => array(
									'autocomplete'      => 'off',
									'data-svg_icon'     => json_encode( array( get_roxWooMegaSearchSvgIcon( array('icon'=>'rox-close') ) ) ),
							),
					),
					'no_product_icon_color' => array(
							'name'                 => __( 'No Product Icon Color', 'woo-mega-search' ),
							'type'                 => 'color',
							'desc'                 => '',
							'default'              => '#7f7f7f',
							'id'                   => $this->id . '_no_product_icon_color',
							'autoload'             => false,
							'custom_attributes'    => array(
									'autocomplete'      => 'off',
									'data-svg_icon'     => json_encode( array( get_roxWooMegaSearchSvgIcon( array('icon'=>'rox-sad') ) ) ),
							),
					),
					'reload_icon_color' => array(
							'name'                 => __( 'Loading Icon Color', 'woo-mega-search' ),
							'type'                 => 'color',
							'desc'                 => '',
							'default'              => '#7f7f7f',
							'id'                   => $this->id . '_reload_icon_color',
							'autoload'             => false,
							'custom_attributes'    => array(
									'autocomplete'      => 'off',
									'data-svg_icon'     => json_encode( array( get_roxWooMegaSearchSvgIcon( array('icon'=>'rox-reload', 'class'=> 'no-animation') ) ) ),
							),
					),
					'section_end' => array(
							'type'       => 'sectionend',
							'id'         => $this->id . '_section_end',
					),
			);
		} else {
			$fields = array(
					'section_title' => array(
							'name'       => __( 'General Settings', 'woo-mega-search' ),
							'type'       => 'title',
							'id'         => $this->id . '_section_title',
					),
					'short_code'  => array(
							'name'                  => __( 'Search Form Shortcode', 'woo-mega-search' ),
							'type'                  => 'text',
							'desc'                  => __( 'Use this shortcode to display the search box anywhere you want.<br>Other Uses:<br><code>&lt;?php echo do_shortcode(\'[rox_woo_search]\'); ?&gt;</code> Or <code>&lt;?php echo RoxWCMS()->get_search_form(); ?&gt;</code>', 'woo-mega-search' ),
							'default'               => '[rox_woo_search]',
							'id'                    => $this->id . '_plugin_shortcode',
							'custom_attributes'     => array(
								'readonly'      => 'readonly',
								'onClick'       => 'this.select(); return false;'
							),
							'autoload'              => false,
					),
					'max_results' => array(
							'name'                 => __( 'Max Results', 'woo-mega-search' ),
							'type'                 => 'number',
							'desc'                 => '',
							'default'              => 10,
							'id'                   => $this->id . '_max_results',
							'custom_attributes'    => array( 'required'   => 'required', ),
							'autoload'             => false,
					),
					'hijack_wp_search_form' => array(
							'name'                 => __( 'Replace Theme Search Form', 'woo-mega-search' ),
							'type'                 => 'switch',
							'desc'                 => __( 'If current theme doesnot utilize <code>get_search_form()</code> function, this option will not going to work', 'woo-mega-search' ),
							'default'              => 0,
							'id'                   => $this->id . '_hijack_wp_search_form',
							'text'                 => array(
									'on'    => __( 'Yes', 'woo-mega-search' ),
									'off'   => __( 'No', 'woo-mega-search' ),
							),
							'autoload'             => false,
					),
					'add_to_cart' => array(
							'name'                 => __( 'Show Add To Cart', 'woo-mega-search' ),
							'type'                 => 'switch',
							'desc'                 => '',
							'default'              => 1,
							'id'                   => $this->id . '_show_add_to_cart',
							'text'                 => array(
									'on'    => __( 'Yes', 'woo-mega-search' ),
									'off'   => __( 'No', 'woo-mega-search' ),
							),
							'autoload'             => false,
					),
					'product_rating' => array(
							'name'                 => __( 'Show Product Rating', 'woo-mega-search' ),
							'type'                 => 'switch',
							'desc'                 => '',
							'default'              => 1,
							'id'                   => $this->id . '_show_product_rating',
							'text'                 => array(
									'on'    => __( 'Yes', 'woo-mega-search' ),
									'off'   => __( 'No', 'woo-mega-search' ),
							),
							'autoload'             => false,
					),
					'section_end' => array(
							'type'       => 'sectionend',
							'id'         => $this->id . '_section_end',
					)
			);
		}
		return apply_filters( 'woocommerce_get_settings_' . $this->id, $fields );
	}

	/**
	 * Get sections.
	 * 
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			''             => __( 'General', 'woocommerce' ),
			'customizer'    => __( 'Customize Icon Color', 'woocommerce' ),
		);
		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}
	/**
	 * Output sections.
	 * 
	 * @return void
	 */
	public function output_sections() {
		global $current_section;
		$sections = $this->get_sections();
		if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
			return;
		}
		echo '<ul class="subsubsub">';
		$array_keys = array_keys( $sections );
		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}
		echo '</ul><br class="clear" />';
	}
	/**
	 * Output the settings.
	 * 
	 * @return void
	 */
	public function output() {
		global $current_section;
		$settings = $this->get_settings( $current_section );
		rox_wcms_output_admin_fields( $settings );
		$section_name = empty($current_section) ? 'general' : $current_section;
		/**
		 * action after current section fields
		 */
		do_action( $this->id . '_after_' . $section_name . '_fields' );
	}
	/**
	 * Get All Saved Settings
	 * @return array
	 */
	public function get() {
		$sections = array_keys( $this->get_sections() );
		$fields = array();
		foreach( $sections as $section ) {
			$fields += $this->get_settings( $section );
		}
		if( isset( $fields['section_title'] ) ) unset( $fields['section_title'] );
		if( isset( $fields['section_end'] ) ) unset( $fields['section_end'] );
		$settings = array();
		foreach( $fields as $k => $field ) {
			$defaultValue = ( isset( $field['default'] ) ) ? $field['default']: false;
			$storedValue = get_option( $field['id'], NULL );
			// get option doesn't return default value for empty options
			if( $storedValue === NULL ) $storedValue = $defaultValue;
			if( in_array( $k, array( 'add_to_cart', 'wishlist', 'product_rating', 'hijack_wp_search_form' ) ) ) {
				$storedValue = (bool) $storedValue;
			}
			$settings[$k] = $storedValue;
		}
		return $settings;
	}
	/**
	 * Save settings.
	 *
	 * @return void
	 */
	public function save() {
		global $current_section;
		$settings = $this->get_settings( $current_section );
		if( isset( $settings['short_code'] ) ) unset( $settings['short_code'] );
		WC_Admin_Settings::save_fields( $settings );
		$section_name = empty($current_section) ? 'general' : $current_section;
		/**
		 * action after updating settings for current section
		 */
		do_action( 'woocommerce_update_options_' . $this->id . '_' . $section_name );
	}
}
/**
 * Plugin Action Links
 * @param  array  $links List of existing plugin action links.
 * @return array         List of modified plugin action links.
 */
function rox_wcms_plugin_action_lists( $links ) {
	$links = array_merge( array(
		'<a href="' . esc_url( admin_url( '/admin.php?page=wc-settings&tab=rox-wcms-settings' ) ) . '">' . __( 'Settings', 'textdomain' ) . '</a>'
	), $links );
	return $links;
}
add_action( 'plugin_action_links_' . ROX_WOO_SEARCH_PLUGIN_BASENAME, 'rox_wcms_plugin_action_lists' );
add_filter( 'woocommerce_admin_settings_sanitize_option', function( $value, $option, $raw_value ) {
	if( $option['type'] == 'switch' ) {
		if( empty( $value ) ) return 0;
		else return 1;
	}
	return $value;
}, 10, 3);
// End of file class.RoxWCMSSettings.php