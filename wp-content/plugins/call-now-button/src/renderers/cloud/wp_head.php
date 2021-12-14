<?php
function cnb_head() {
    global $cnb_options;
    $id = isset($cnb_options['cloud_use_id']) ? $cnb_options['cloud_use_id'] : 0;
    $cnb_button_js = "\n<!-- Call Now Button " . CNB_VERSION . " by Jerry Rietveld (callnowbutton.com) [renderer:cloud, id:".esc_attr($id)."]-->\n";
    if ($id) {
        $cnb_button_js .= '<script data-cnb-version="' . CNB_VERSION . '" type="text/javascript" src="'.CnbAppRemote::cnb_get_user_base().'/' . esc_attr($id) . '.js"></script>';
    }
    echo $cnb_button_js;
}

add_action('wp_head', 'cnb_head');
