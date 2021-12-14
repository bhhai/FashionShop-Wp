<?php
function cnb_head() {
    global $cnb_options;
    $cnb_has_text = ($cnb_options['text'] == '') ? false : true;
    $cnb_button_css = "\n<!-- Call Now Button " . CNB_VERSION . " by Jerry Rietveld (callnowbutton.com) [renderer:classic]-->\n";

    $ButtonExtra = "";
    // OLD BUTTON DESIGN
    if ($cnb_options['appearance'] == 'full' || $cnb_options['appearance'] == 'middle' || $cnb_has_text) {
        $cnb_button_appearance = "width:100%;left:0;";
        $ButtonExtra = "body {padding-bottom:60px;}";
    } elseif ($cnb_options['appearance'] == 'left') {
        $cnb_button_appearance = "width:100px;left:0;border-bottom-right-radius:40px; border-top-right-radius:40px;";
    } else {
        $cnb_button_appearance = "width:100px;right:0;border-bottom-left-radius:40px; border-top-left-radius:40px;";
    }

    $cnb_button_css .= "<style data-cnb-version=\"" . CNB_VERSION . "\">#callnowbutton, #callnowbutton span {display:none;} @media screen and (max-width:650px){#callnowbutton .NoButtonText{display:none;}#callnowbutton {display:block; " .
                       $cnb_button_appearance .
                       " height:80px; position:fixed; bottom:-20px; border-top:2px solid " .
                       changeColor(esc_html($cnb_options['color']), 'lighter') .
                       "; background:url(data:image/svg+xml;base64," .
                       svg(changeColor($cnb_options['color'], 'darker'), $cnb_options['iconcolor']) .
                       ") center 2px no-repeat " .
                       esc_html($cnb_options['color']) .
                       "; text-decoration:none; box-shadow:0 0 5px #888; z-index:" .
                       zindex($cnb_options['z-index']) .
                       ";background-size:58px 58px}" .
                       $ButtonExtra .
                       "}</style>\n";

    echo $cnb_button_css;
}

add_action('wp_head', 'cnb_head');
