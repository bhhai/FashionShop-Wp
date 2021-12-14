<?php
function cnb_footer() {
    global $cnb_options;

    $cnb_tracking_id = (int) $cnb_options['tracking'];
    $cnb_conversion_id = (int) $cnb_options['conversions'];
    $cnb_tracking_id = ($cnb_tracking_id >= 0 && $cnb_tracking_id <= 3) ? $cnb_tracking_id : 0;
    $cnb_conversion_id = ($cnb_conversion_id >= 0 && $cnb_conversion_id <= 2) ? $cnb_conversion_id : 0;
    $cnb_hide_icon = isset($cnb_options['hideIcon']) && $cnb_options['hideIcon'] == 1;
    $cnb_has_text = ($cnb_options['text'] == '') ? false : true;
    $cnb_show_limited = isset($cnb_options['show']) && $cnb_options['show'] != '';
    $cnb_show_included = $cnb_options['limit'] == 'include';
    $cnb_click_tracking = $cnb_tracking_id > 0;
    $cnb_is_full_width = $cnb_options['appearance'] == 'full' || $cnb_options['appearance'] == 'tfull';
    $cnb_hide_frontpage = isset($cnb_options['frontpage']) && $cnb_options['frontpage'] == 1;
    $cnb_conversion_tracking = $cnb_conversion_id > 0;

    if ($cnb_show_limited) {
        $cnb_show_ids = explode(',', str_replace(' ', '', $cnb_options['show']));
    }

    if ($cnb_click_tracking) {
        $cnb_tracking_code[1] = '_gaq.push(["_trackEvent", "Contact", "Call Now Button", "Phone"]);';
        $cnb_tracking_code[2] = 'ga("send", "event", "Contact", "Call Now Button", "Phone");';
        $gtag_props = wp_json_encode(array(
            'event_category' => 'contact',
            'event_label' => 'phone',
            'category' => 'Call Now Button',
            'action_type' => 'PHONE',
            'button_type' => 'Single',
            'action_value' => esc_js($cnb_options['number']),
            'action_label' => esc_js($cnb_options['text']),
            'cnb_version' => CNB_VERSION));
        $cnb_tracking_code[3] = 'gtag("event", "Call Now Button", '.$gtag_props.');';
        $cnb_tracking_code = $cnb_tracking_code[$cnb_tracking_id];
    } else {
        $cnb_tracking_code = '';
    }

    if ($cnb_conversion_tracking) {
        $cnb_conversion_code[1] = 'return gtag_report_conversion("tel:' . esc_js($cnb_options['number']) . '");';
        $cnb_conversion_code[2] = 'goog_report_conversion("tel:' . esc_js($cnb_options['number']) . '");';
        $cnb_conversion_code = $cnb_conversion_code[$cnb_conversion_id];
    } else {
        $cnb_conversion_code = '';
    }

    $cnb_onclick_events = $cnb_click_tracking || $cnb_conversion_tracking ? "onclick='" .$cnb_tracking_code . $cnb_conversion_code . "'" : '';

    if (!$cnb_has_text && !$cnb_is_full_width) {
        $cnb_button_text = '<span>Call Now Button</span>';
    } elseif (!$cnb_has_text && $cnb_is_full_width) {
        $cnb_button_text = '<img alt="Call Now Button" src="data:image/svg+xml;base64,' . svg(changeColor($cnb_options['color'], 'darker'), $cnb_options['iconcolor']) . '" width="40">';
    } elseif ($cnb_hide_icon && $cnb_is_full_width) {
        $cnb_button_text = '<span style="color:' . esc_attr($cnb_options['iconcolor']) . '">' . esc_html($cnb_options['text']) . '</span>';
    } elseif ($cnb_is_full_width) {
        $cnb_button_text = '<img alt="Call Now Button" src="data:image/svg+xml;base64,' . svg(changeColor($cnb_options['color'], 'darker'), $cnb_options['iconcolor']) . '" width="40"><span style="color:' . esc_attr($cnb_options['iconcolor']) . '">' . esc_html($cnb_options['text']) . '</span>';
    } else {
        $cnb_button_text = '<span>' . str_replace(' ', '&nbsp;', esc_html($cnb_options['text'])) . '</span>';
    }

    $cnb_call_link = '<a href="tel:' . esc_attr($cnb_options['number']) . '" id="callnowbutton" ' . $cnb_onclick_events . '>' . $cnb_button_text . '</a>';

    if (is_front_page()) {
        if (!$cnb_hide_frontpage) {
            echo $cnb_call_link;
        }
    } elseif ($cnb_show_limited) {
        if ($cnb_show_included) {
            if (is_single($cnb_show_ids) || is_page($cnb_show_ids)) {
                echo $cnb_call_link;
            }
        } else {
            if (!is_single($cnb_show_ids) && !is_page($cnb_show_ids)) {
                echo $cnb_call_link;
            }
        }
    } else {
        echo $cnb_call_link;
    }
}

add_action('wp_footer', 'cnb_footer');
