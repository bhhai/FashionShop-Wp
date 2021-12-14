<?php
/*
Plugin Name: Related Post
Plugin URI: http://wordpress.org/plugins/related-post/
Description: Display related posts under post content on single page and excerpt on archive pages.
Version: 2.0.36
Author: PickPlugins
Author URI: http://pickplugins.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 



class RelatedPost{
	
	public function __construct(){

        define('related_post_plugin_url', plugins_url('/', __FILE__)  );
        define('related_post_plugin_dir', plugin_dir_path( __FILE__ ) );
        define('related_post_wp_url', 'http://wordpress.org/plugins/related-post/' );
        define('related_post_support_url', 'http://pickplugins.com/forum' );
        define('related_post_plugin_name', 'Related Post' );

        // Classes
        require_once( related_post_plugin_dir . 'includes/class-settings.php');
        require_once( related_post_plugin_dir . 'includes/class-functions.php');
        //require_once( related_post_plugin_dir . 'includes/class-notices.php');
        require_once( related_post_plugin_dir . 'includes/class-post-meta.php');
        require_once( related_post_plugin_dir . 'includes/class-settings-tabs.php');


        require_once( related_post_plugin_dir . 'includes/class-data-upgrade.php');

        // functions
		require_once( related_post_plugin_dir . 'includes/functions.php');
        require_once( related_post_plugin_dir . 'includes/functions-settings.php');

		// Shortcodes
		require_once( related_post_plugin_dir . 'includes/shortcodes.php');


        // Hooks
		add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' ); 
		add_action( 'wp_enqueue_scripts', array( $this, '_front_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, '_admin_scripts' ) );
		add_action( 'plugins_loaded', array( $this, '_textdomain' ));

		register_activation_hook( __FILE__, array( $this, '_activation' ) );




		}

	public function _activation() {

        $related_post_settings = get_option('related_post_settings');
        $post_types_display = isset($related_post_settings['post_types_display']) ? $related_post_settings['post_types_display'] : array();


        if(empty($post_types_display)){
            $post_types = isset($related_post_settings['post_types']) ? $related_post_settings['post_types'] : array();
            $content_positions = isset($related_post_settings['content_positions']) ? $related_post_settings['content_positions'] : array();
            $excerpt_positions = isset($related_post_settings['excerpt_positions']) ? $related_post_settings['excerpt_positions'] : array();
            $paragraph_positions = isset($related_post_settings['paragraph_positions']) ? $related_post_settings['paragraph_positions'] : "";
            $headline_text = isset($related_post_settings['headline_text']) ? $related_post_settings['headline_text'] : "";
            $layout_type = isset($related_post_settings['layout_type']) ? $related_post_settings['layout_type'] : "";


            foreach ($post_types as $post_type){

                $related_post_settings['post_types_display'][$post_type]['enable'] = 'yes';

                $related_post_settings['post_types_display'][$post_type]['content_position'] = $content_positions;
                $related_post_settings['post_types_display'][$post_type]['excerpt_position'] = $excerpt_positions;
                $related_post_settings['post_types_display'][$post_type]['paragraph_positions'] = $paragraph_positions;
                $related_post_settings['post_types_display'][$post_type]['headline_text'] = $headline_text;
                $related_post_settings['post_types_display'][$post_type]['view_type'] = $layout_type;

            }





            update_option('related_post_settings', $related_post_settings);

        }




		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table = $wpdb->prefix .'related_post_stats';
		
		$sql = "CREATE TABLE IF NOT EXISTS ".$table ." (
			id int(100) NOT NULL AUTO_INCREMENT,
			from_id int(100) NOT NULL,
			to_id int(100) NOT NULL,
			date DATE NOT NULL,		
			UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );


		}
	
	
	
	public function _textdomain() {

	    // Global files
        $locale = apply_filters( 'plugin_locale', get_locale(), 'related-post' );
        load_textdomain('related-post', WP_LANG_DIR .'/related-post/related-post-'. $locale .'.mo' );

        // Plugin files
        load_plugin_textdomain( 'related-post' , false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}
	
	
	
	function _front_scripts(){




        wp_register_style('related-post', related_post_plugin_url.'assets/front/css/related-post.css');
        wp_register_style('font-awesome-5', related_post_plugin_url.'assets/front/css/font-awesome-5.css');
        wp_register_style('font-awesome-4', related_post_plugin_url.'assets/front/css/font-awesome-4.css');

        wp_register_script('owl.carousel', related_post_plugin_url.'/assets/front/js/owl.carousel.min.js' , array( 'jquery' ));
        wp_register_style('owl.carousel', related_post_plugin_url.'assets/front/css/owl.carousel.min.css');


	}
	
	
	function _admin_scripts(){


		wp_enqueue_script('related_post_js', related_post_plugin_url.'assets/admin/js/scripts.js' , array( 'jquery' ));
		wp_localize_script('related_post_js', 'related_post_ajax', array( 'related_post_ajaxurl' => admin_url( 'admin-ajax.php')));

        wp_register_style('font-awesome-5', related_post_plugin_url.'assets/front/css/font-awesome-5.css');

		// settings-tabs framework
        wp_register_script('settings-tabs', related_post_plugin_url.'assets/settings-tabs/settings-tabs.js' , array( 'jquery' ));
        wp_register_style('settings-tabs', related_post_plugin_url.'assets/settings-tabs/settings-tabs.css');



	}
	
	
	
}

new RelatedPost();



