<?php
function cnb_head() {
    global $cnb_options;
    $cnb_has_text = ($cnb_options['text'] == '') ? false : true;
    $cnb_is_full_width = $cnb_options['appearance'] == "full" || $cnb_options['appearance'] == "tfull";
    $cnb_button_css = "\n<!-- Call Now Button " . CNB_VERSION . " by Jerry Rietveld (callnowbutton.com) [renderer:modern]-->\n";

    $ButtonExtra = "";
    // NEW BUTTON DESIGN
    $cnb_button_shape = "width:55px; height:55px; border-radius:50%; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);transform: scale(" . esc_html($cnb_options['zoom']) . ");";
    $cnb_button_positions = array(
        'middle'    => 'bottom:15px; left:50%; margin-left:-28px;',
        'left' => 'bottom:15px; left:20px;',
        'right' => 'bottom:15px; right:20px;',
        'mleft'     => 'top:50%; margin-top:-28px; left:20px;',
        'mright'    => 'top:50%; margin-top:-28px; right:20px;',
        'tleft' => 'top:15px; left:20px;',
        'tmiddle'   => 'top:15px; left:50%; margin-left:-28px;',
        'tright' => 'top:15px; right:20px;',
    );

    if ($cnb_options['appearance'] == 'full' || $cnb_options['appearance'] == 'tfull') {
        $cnb_top_or_bottom = ($cnb_options['appearance']) == 'full' ? "bottom" : "top";

        $cnb_button_appearance = "width:100%;left:0;" . $cnb_top_or_bottom . ":0;height:60px;";

        $ButtonExtra = "body {padding-" . $cnb_top_or_bottom . ":60px;}#callnowbutton img {transform: scale(" . esc_html($cnb_options['zoom']) . ");}";
        if ($cnb_has_text) {
            $cnb_button_appearance .= "text-align:center;color:#fff; font-weight:600; font-size:120%;  overflow: hidden;";
        }
    } else {
        $cnb_button_appearance = $cnb_button_shape . $cnb_button_positions[$cnb_options['appearance']];
    }

    $cnb_label_side = ltrim(ltrim($cnb_options['appearance'], "m"), "t");

    if ($cnb_has_text && ($cnb_options['appearance'] == 'middle' || $cnb_options['appearance'] == 'tmiddle')) { // don't show the label in this situation
        $circularButtonTextCSS = "#callnowbutton span{display: none;";
    } elseif ($cnb_has_text && !$cnb_is_full_width) {
        $circularButtonTextCSS = "\n#callnowbutton span {-moz-osx-font-smoothing: grayscale; -webkit-user-select: none; -ms-user-select: none; user-select: none; display: block; width: auto; background-color: rgba(70,70,70,.9); position: absolute; ".$cnb_label_side.": 68px; border-radius: 2px; font-family: Helvetica,Arial,sans-serif; padding: 6px 8px; font-size: 13px; font-weight:700; color: #ececec; top: 15px; box-shadow: 0 1px 2px rgba(0,0,0,.15); word-break: keep-all; line-height: 1em; text-overflow: ellipsis; vertical-align: middle; }";
    } elseif (!$cnb_is_full_width) {
        $circularButtonTextCSS = "#callnowbutton span{display:none;}";
    } else {
        $circularButtonTextCSS = "";
    }

    $cnb_button_css = $cnb_button_css . '<style data-cnb-version="' . CNB_VERSION . '">';
    $cnb_button_css .= "#callnowbutton {display:none;} @media screen and (max-width:650px){#callnowbutton {display:block; position:fixed; text-decoration:none; z-index:" . zindex($cnb_options['z-index']) . ";";
    $cnb_button_css .= $cnb_button_appearance;
    if ($cnb_is_full_width) {
        $cnb_button_css .= "background:" . esc_html($cnb_options['color']) . ";display: flex; justify-content: center; align-items: center;text-shadow: 0 1px 0px rgba(0, 0, 0, 0.18);";
    } else {
        $cnb_button_css .= "background:url(data:image/svg+xml;base64," . svg(changeColor($cnb_options['color'], 'darker'), $cnb_options['iconcolor']) . ") center/35px 35px no-repeat " . esc_html($cnb_options['color']) . ";";
    }
    $cnb_button_css .= "}" . $ButtonExtra . "}" . $circularButtonTextCSS;
    $cnb_button_css .= "</style>\n";

    echo $cnb_button_css;
}

add_action('wp_head', 'cnb_head');
