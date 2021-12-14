<?php
// don't load directly
defined( 'ABSPATH' ) || die( '-1' );

class CnbButton {

    /**
     * @var string
     */
    public $id;

    /**
     * @var boolean
     */
    public $active;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;

    public $domain;

    public $actions;

    public $conditions;

    public static function setSaneDefault($button) {
        // Set some sane defaults
        if (!isset($button->options)) $button->options = new CnbButtonOptions();
        $button->options->iconBackgroundColor = !empty($button->options->iconBackgroundColor)
            ? $button->options->iconBackgroundColor
            : '#009900';
        $button->options->iconColor = !empty($button->options->iconColor)
            ? $button->options->iconColor
            : '#FFFFFF';
        $button->options->placement = !empty($button->options->placement)
            ? $button->options->placement
            : ($button->type === 'FULL' ? 'BOTTOM_CENTER' : 'BOTTOM_RIGHT');
        $button->options->scale = !empty($button->options->scale)
            ? $button->options->scale
            : '1';
    }

    public static function createDummyButton($domain = null) {
        $button = new CnbButton();
        $button->id = '';
        $button->active = false;
        $button->name = '';
        $button->type = 'SINGLE';
        $button->domain = $domain;
        $button->actions = array();

        return $button;
    }
}

class CnbButtonOptions {
    public $iconBackgroundColor;
    public $iconColor;
    public $placement;
    public $scale;
}