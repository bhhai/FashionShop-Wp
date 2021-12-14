<?php

/*
* @Author 		pickplugins
* Copyright: 	2015 pickplugins
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 

class class_related_post_post_meta{
	
	public function __construct(){

		add_action('add_meta_boxes', array($this, 'meta_boxes_related_post'));
		add_action('save_post', array($this, 'meta_boxes_related_post_save'));
		
		
	}
	
	public function meta_boxes_related_post($post_type) {

        $related_post_settings = get_option( 'related_post_settings' );
        $post_types = isset($related_post_settings['post_types']) ? $related_post_settings['post_types'] : array('post');


        //$post_types = array('post');
		if (in_array($post_type, $post_types)) {
		
			add_meta_box('related_post_metabox',
				__( 'Related Post', 'related-post' ),
				array($this, 'related_post_meta_box_function'),
				$post_type,
				'side',
				'default'
			);
				
		}
	}
	
	public function related_post_meta_box_function($post) {
 
        wp_nonce_field('related_post_nonce_check', 'related_post_nonce_check_value');
		global $post;
		
		//$pm_related_post_meta = get_post_meta( $post->ID, 'pm_related_post_meta', true );
		$related_post_ids = get_post_meta( $post->ID, 'related_post_ids', true );		
	
		//echo '<pre>'.var_export($related_post_ids, true).'</pre>';

        wp_enqueue_style( 'font-awesome-5' );


		?> 
		
		<div class="related-post-meta"> 
			
            <div class="post-list">
            
            
            	<?php
                if(!empty($related_post_ids))
				foreach( $related_post_ids as $post_id ){
					
					$post_title = get_the_title($post_id);
					
					?>
                    <div class="item">
                        <span class="remove"><i class="fas fa-times"></i></span>
                        <span class="move"><i class="fas fa-sort"></i></span>
                        <span class="title"><?php echo $post_title; ?></span>
                        <input type="hidden" name="related_post_ids[]" value="<?php echo $post_id; ?>" />

                    </div>
                    <?php
					
					
					
					
					}
				
				?>
                 
                
            </div>




			<script>
             jQuery(document).ready(function($){
                 
                    $(function() {
                        $( ".post-list" ).sortable({ handle: '.move' });
                    
                    });
                    
                });
                
            </script>
        
        	<br>
			<input placeholder="Start typing..." type="text" class="related_post_get_ids" post_id="<?php echo $post->ID; ?>" name="related_post_get_ids" value="" />
            <label><input type="checkbox" id="any_posttypes" name="any_posttypes" value="any" >Any post types</label>
        
    		<div class="suggest-post-list">
            
            </div>
            
                        
		</div>

        <style type="text/css">
            .related-post-meta{}
            .related-post-meta .item{
                display: block;
                margin: 5px 0;
            }

            .related-post-meta .remove{
                background: #fd5a0d;
                padding: 3px 7px;
                color: #fff;
                display: inline-block;
                cursor: pointer;
            }
            .related-post-meta .move{
                background: #cacaca;
                padding: 3px 8px;
                color: #fff;
                display: inline-block;
                cursor: move;
            }
            .related-post-meta .title{}

            .related-post-meta .suggest-post-list{
                margin-top: 12px;
            }
            .related-post-meta .suggest-post-list .item{
                cursor: pointer;
                margin: 4px 0;
                background: #ddd;
                padding: 5px 7px;
            }
            .related-post-meta .related_post_get_ids{
                width: 100%;
            }
            .suggest-post-list .title-text{
                display: inline-block;
                word-break: break-word;
            }

            .suggest-post-list .icon-plus{
                display: inline-block;
            }

            .suggest-post-list .icon-add{
                display: none;
            }

            .suggest-post-list .item:hover .icon-plus{
                display: none;
            }
            .suggest-post-list .item:hover .icon-add{
                display: inline-block;
            }



        </style>
		
		<?php
   	}
	
	public function meta_boxes_related_post_save($post_id){
	 
		if (!isset($_POST['related_post_nonce_check_value'])) return $post_id;
		$nonce = isset($_POST['related_post_nonce_check_value']) ? sanitize_text_field($_POST['related_post_nonce_check_value']) : '';
		if (!wp_verify_nonce($nonce, 'related_post_nonce_check')) return $post_id;

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
	 
		if ('page' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id)) return $post_id;
		} else {
			if (!current_user_can('edit_post', $post_id)) return $post_id;
		}
	 
		//$pm_related_post_meta = related_post_recursive_sanitize_arr( $_POST['pm_related_post_meta'] );
		
		if(!empty($_POST['related_post_ids'])){
			
			$related_post_ids = related_post_recursive_sanitize_arr( $_POST['related_post_ids'] );
			update_post_meta( $post_id, 'related_post_ids', $related_post_ids );
			
			}
		
	

		
		// Saving the Meta Data from ARRAY
		
	}
	
} 

new class_related_post_post_meta();