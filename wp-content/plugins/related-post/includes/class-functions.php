<?php

/*
* @Author 		pickplugins
* Copyright: 	2015 pickplugins
*/

if ( ! defined('ABSPATH')) exit;  // if direct access

class class_related_post_functions{

    public function __construct(){

		//add_action( 'admin_menu', array( $this, 'related_post_menu_init' ), 12 );

		}


	
	public function faq(){



		$faq['core'] = array(
							'title'=>__('Core', 'related-post'),
							'items'=>array(

											array(
												'question'=>__('How to display on archive pages ?', 'related-post'),
												'answer_url'=>'https://pickplugins.com/docs/documentation/related-post/how-to-display-on-archive-pages/',
												
												),	

	
											),

								
							);

					
		
		
		$faq = apply_filters('related_post_filter_faq', $faq);		

		return $faq;

		}		
	








	public function layout_items(){
		
		$layout_items = array(
		
							'thumbnail'=>array('name'=>__('Thumbnail','related-post'), 'options'=>array()),
							'title'=>array('name'=>__('Title','related-post'), 'options'=>array()),
							'excerpt'=>array('name'=>__('Excerpt','related-post'), 'options'=>array()),

							);
		
		$layout_items = apply_filters('related_post_filter_layout_items',$layout_items);				
						
		return $layout_items;			
							
		
		}


	}
	
new class_related_post_functions();