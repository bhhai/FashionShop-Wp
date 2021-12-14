<?php
if ( ! defined('ABSPATH')) exit;  // if direct access

class class_related_post_settings{

    public function __construct(){

		add_action( 'admin_menu', array( $this, '_menu_init' ), 12 );
    }

	public function settings(){
		include('menu/settings.php');

    }

	public	function _menu_init(){

		add_menu_page(__('Related Post','related-post'), __('Related Post','related-post'), 'manage_options', 'related_post_settings', array( $this, 'settings' ), 'dashicons-align-right');
    }
}
	
new class_related_post_settings();