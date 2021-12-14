<?php

/*
* @Author 		pickplugins
* Copyright: 	2015 pickplugins
*/

if ( ! defined('ABSPATH')) exit;  // if direct access

class class_related_post_upgrade{

    public function __construct(){

		add_action( 'admin_notices', array( $this, 'review_settings' ), 12 );

		}

	public function review_settings(){
		
		
		}


	}
	
new class_related_post_upgrade();