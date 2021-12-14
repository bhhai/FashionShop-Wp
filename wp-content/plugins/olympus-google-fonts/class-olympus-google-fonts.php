<?php
/**
 * Main Olympus_Google_Fonts Class
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Main Olympus_Google_Fonts Class
 */
class Olympus_Google_Fonts {

	/**
	 * Initialize plugin.
	 */
	public function __construct() {
		$this->constants();
		$this->includes();
		$this->compatability();

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 1000 ); // ensure our Google Font styles load last.
		add_filter( 'wp_resource_hints', array( $this, 'resource_hints' ), 10, 2 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue' ), 100 );
		add_action( 'customize_preview_init', array( $this, 'customize_preview_enqueue' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( OGF_DIR_PATH . 'olympus-google-fonts.php' ), array( $this, 'links' ) );

		if ( ! defined( 'OGF_PRO' ) ) {
			add_action( 'customize_register', array( $this, 'remove_pro_sections' ) );
		}
	}

	/**
	 * Load plugin files.
	 */
	public function constants() {
		if ( ! defined( 'OGF_VERSION' ) ) {
			define( 'OGF_VERSION', '3.0.7' );
		}

		if ( ! defined( 'OGF_DIR_PATH' ) ) {
			define( 'OGF_DIR_PATH', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'OGF_DIR_URL' ) ) {
			define( 'OGF_DIR_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Load plugin files.
	 */
	public function includes() {
		// Custom uploads functionality.
		require_once OGF_DIR_PATH . 'includes/class-ogf-fonts-taxonomy.php';
		require_once OGF_DIR_PATH . 'admin/class-ogf-upload-fonts-screen.php';

		// Required files for building the Google Fonts URL.
		require_once OGF_DIR_PATH . 'includes/functions.php';
		require_once OGF_DIR_PATH . 'includes/class-ogf-fonts.php';

		// Required files for the customizer settings.
		require_once OGF_DIR_PATH . 'includes/customizer/panels.php';
		require_once OGF_DIR_PATH . 'includes/customizer/settings.php';
		require_once OGF_DIR_PATH . 'includes/customizer/output-css.php';

		// Required files for the Typekit integration.
		require_once OGF_DIR_PATH . 'includes/class-ogf-typekit.php';

		// Required files for the Gutenberg editor.
		require_once OGF_DIR_PATH . 'blocks/init.php';
		require_once OGF_DIR_PATH . 'includes/gutenberg/output-css.php';

		// Notifications class.
		require_once OGF_DIR_PATH . 'includes/class-ogf-notifications.php';

		// Welcome notice class.
		require_once OGF_DIR_PATH . 'includes/class-ogf-welcome.php';

		// Reset class.
		require_once OGF_DIR_PATH . 'includes/class-ogf-reset.php';

		// Classic Editor class.
		require_once OGF_DIR_PATH . 'includes/class-ogf-classic-editor.php';

		// News widget.
		require_once OGF_DIR_PATH . 'includes/class-ogf-dashboard-widget.php';

		// Admin sidebar page(s).
		require_once OGF_DIR_PATH . 'admin/class-ogf-welcome-screen.php';
	}

	/**
	 * Load plugin textdomain.
	 */
	public function compatability() {
		$current_theme      = wp_get_theme();
		$theme_author       = strtolower( esc_attr( $current_theme->get( 'Author' ) ) );
		$theme_author       = str_replace( ' ', '', $theme_author );
		$author_compat_path = OGF_DIR_PATH . '/compatibility/' . $theme_author . '.php';
		if ( file_exists( $author_compat_path ) ) {
			require_once $author_compat_path;
		}
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'olympus-google-fonts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Enqeue the Google Fonts URL.
	 */
	public function enqueue() {
		$fonts = new OGF_Fonts();

		if ( $fonts->has_google_fonts() ) {
			$url = $fonts->build_url();
			wp_enqueue_style( 'olympus-google-fonts', $url, array(), OGF_VERSION );
		}
	}

	/**
	 * Add preconnect for Google Fonts.
	 *
	 * @param array  $urls           URLs to print for resource hints.
	 * @param string $relation_type  The relation type the URLs are printed.
	 * @return array $urls           URLs to print for resource hints.
	 */
	public function resource_hints( $urls, $relation_type ) {
		if ( wp_style_is( 'olympus-google-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
			$urls[] = array(
				'href' => 'https://fonts.gstatic.com',
				'crossorigin',
			);
		}
		return $urls;
	}

	/**
	 * Register control scripts/styles.
	 */
	public function customize_controls_enqueue() {
		wp_enqueue_script( 'ogf-customize-controls', esc_url( OGF_DIR_URL . 'assets/js/customize-controls.js' ), array( 'customize-controls' ), OGF_VERSION, true );
		wp_enqueue_style( 'ogf-customize-controls', esc_url( OGF_DIR_URL . 'assets/css/customize-controls.css' ), array(), OGF_VERSION );

		wp_localize_script( 'ogf-customize-controls', 'ogf_font_array', ogf_fonts_array() );
		wp_localize_script( 'ogf-customize-controls', 'ogf_system_fonts', ogf_system_fonts() );
		wp_localize_script( 'ogf-customize-controls', 'ogf_custom_fonts', ogf_custom_fonts() );
		wp_localize_script( 'ogf-customize-controls', 'ogf_typekit_fonts', ogf_typekit_fonts() );
		wp_localize_script( 'ogf-customize-controls', 'ogf_font_variants', ogf_font_variants() );
	}

	/**
	 * Load preview scripts/styles.
	 */
	public function customize_preview_enqueue() {
		wp_enqueue_script( 'ogf-customize-preview', esc_url( OGF_DIR_URL . 'assets/js/customize-preview.js' ), array( 'jquery' ), OGF_VERSION, true );

		$elements = array_merge( ogf_get_elements(), ogf_get_custom_elements() );

		wp_localize_script( 'ogf-customize-preview', 'ogf_elements', $elements );
		wp_localize_script( 'ogf-customize-preview', 'ogf_system_fonts', ogf_system_fonts() );
		wp_localize_script( 'ogf-customize-preview', 'ogf_custom_fonts', ogf_custom_fonts() );
		wp_localize_script( 'ogf-customize-preview', 'ogf_typekit_fonts', ogf_typekit_fonts() );
	}

	/**
	 * Add custom links to plugin settings page.
	 *
	 * @param array $links Current links array.
	 */
	public function links( $links ) {
		// Customizer Settings Link.
		$customizer_url = admin_url( 'customize.php?autofocus[panel]=ogf_google_fonts' );

		$settings_link = '<a href="' . esc_url( $customizer_url ) . '">' . esc_html__( 'Settings', 'olympus-google-fonts' ) . '</a>';

		array_push( $links, $settings_link );

		if ( ! defined( 'OGF_PRO' ) ) {
			// Upgrade Link.
			$pro_link = '<a href="https://fontsplugin.com/pro-upgrade/?utm_source=plugin&utm_medium=wpadmin&utm_campaign=upsell">' . esc_html__( 'Upgrade to Pro', 'olympus-google-fonts' ) . '</a>';

			array_push( $links, $pro_link );
		}

		return $links;
	}

	/**
	 * Remove pro sections from basic version.
	 *
	 * @param object $wp_customize Access to the $wp_customize object.
	 */
	public function remove_pro_sections( $wp_customize ) {
		$wp_customize->remove_section( 'ogf_custom' );
		$wp_customize->remove_section( 'ogf_advanced__custom' );
	}

}

$gfwp = new Olympus_Google_Fonts();
