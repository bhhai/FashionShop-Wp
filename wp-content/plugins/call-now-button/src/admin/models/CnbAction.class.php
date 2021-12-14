<?php
// don't load directly
defined( 'ABSPATH' ) || die( '-1' );

class CnbAction {
    public $id;
    public $actionType;
    public $actionValue;
    public $labelText;
    public $schedule;

    /**
     * @var boolean
     */
    public $iconEnabled;

    public $backgroundColor;
    public $iconColor;

    /**
     * @var CnbActionProperties
     */
    public $properties;
}

class CnbActionProperties {

}
