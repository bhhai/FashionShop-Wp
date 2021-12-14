<?php
// don't load directly
defined( 'ABSPATH' ) || die( '-1' );

class CnbCondition {
    public $id;
    public $conditionType = 'URL';
    public $filterType;
    public $matchType;
    public $matchValue;
}
