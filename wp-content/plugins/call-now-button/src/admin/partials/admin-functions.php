<?php

function cnb_get_active_tab_name() {
    // using filter_var instead of filter_input so we can do some "just in time" rewriting of the tab variable if needed
    return isset($_GET['tab']) ? filter_var( $_GET['tab'], FILTER_SANITIZE_STRING ) : 'basic_options';
}
function cnb_is_active_tab($tab_name) {
    $active_tab = cnb_get_active_tab_name();
    return $active_tab === $tab_name ? 'nav-tab-active' : '';
}

function cnb_get_changelog() {
    return array(
        '0.5.0' => 'Better button creation flow, UI improvements, small fixes',
        '0.4.7' => 'Small UI improvements',
        '0.4.2' => 'Button styling adjustments, security improvements',
        '0.4.0' => array(
            'Text bubbles for standard buttons',
            'Set the icon color',
            'Google Ads conversion tracking',
            'Tabbed admin interface',
            '6 additional button locations, small button design changes',
            'Added support articles for (nearly) all settings',
            'Control visibility on front page',
            'Plus a bunch of smaller fixes. Enjoy!'),
        '0.3.6' => 'Small validation fixes and zoom now controls icon size in full width buttons.',
        '0.3.5' => 'Small JS fix',
        '0.3.4' => 'Option to resize your button and change where it sits in the stack order (z-index).',
        '0.3.3' => 'Some small improvements.',
        '0.3.2' => 'Option to hide icon in text button, small bug fixes.',
        '0.3.1' => 'You can now add text to your button and it\'s possible to switch between including and excluding specific pages.',
        '0.3.0' => 'Option to add text to your button.',
        '0.2.1' => 'Some small fixes',
        '0.2.0' => 'The Call Now Button has a new look!'
    );
}

/**
 * Return an array of all ButtonTypes
 *
 * @return string[] array of ButtonTypes to their nice names
 */
function cnb_get_button_types() {
    return array(
        'SINGLE' => 'Single button',
        'FULL' => 'Buttonbar',
        'MULTI' => 'Multibutton',
    );
}

/**
 * Return an array of all ActionTypes
 *
 * @return string[] array of ActionType to their nice names
 */
function cnb_get_action_types() {
    return array(
        'PHONE' => 'Phone',
        'EMAIL' => 'Email',
        'ANCHOR' => 'Anchor',
        'LINK' => 'Link',
        'MAP' => 'Google Maps',
        'WHATSAPP' => 'Whatsapp',
    );
}

function cnb_get_condition_filter_types() {
    return array(
        'INCLUDE' => 'Include',
        'EXCLUDE' => 'Exclude',
    );
}

function cnb_get_condition_match_types() {
    return array(
        'SIMPLE' => 'Page path is:',
        'EXACT' => 'Page URL is:',
        'SUBSTRING' => 'Page URL contains:',
        'REGEX' => 'Page URL matches RegEx:',
    );
}

/**
 * @param $original
 *
 * @return array
 */
function cnb_create_days_of_week_array($original) {
    // If original does not exist, leave it as it is
    if ($original === null || !is_array($original)) {
        return $original;
    }
    $result = array(false, false, false, false, false, false, false);
    foreach ($result as $day_of_week_index => $day_of_week) {
        $result[$day_of_week_index] =
            isset($original[$day_of_week_index]) && $original[$day_of_week_index] === "true" ?
                true :
                $day_of_week;
        }
    return $result;
}
