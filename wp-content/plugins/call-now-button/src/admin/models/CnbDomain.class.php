<?php
// don't load directly
defined( 'ABSPATH' ) || die( '-1' );

class CnbDomain {

    public $id;
    public $name;
    public $timezone;
    public $type;
    /**
     * @var CnbDomainProperties
     */
    public $properties;
    /**
     * @var boolean
     */
    public $trackGA;
    /**
     * @var boolean
     */
    public $trackConversion;
    /**
     * @var boolean
     */
    public $renew;

    /**
     *
     * This changes the object itself, settings some sane defaults in case those are missing
     *
     * @param $domain CnbDomain|null
     * @param $domain_id number|null
     */
    public static function setSaneDefault( $domain = null, $domain_id = null ) {
        if (is_wp_error($domain)) {
            return;
        }

        if ( $domain === null ) {
            $domain = new CnbDomain();
        }

        if ( strlen( $domain_id ) > 0 && $domain_id == 'new' && empty( $domain->id ) ) {
            $domain->id = null;
        }
        if ( empty( $domain->timezone ) ) {
            $domain->timezone = wp_timezone_string();
        }
        if ( empty( $domain->type ) ) {
            $domain->type = 'FREE';
        }
        if ( empty( $domain->properties ) ) {
            $domain->properties        = new CnbDomainProperties();
            $domain->properties->scale = '1';
            $domain->properties->debug = false;
        }
        if ( empty( $domain->name ) ) {
            $domain->name = null;
        }
        if ( ! isset( $domain->trackGA ) ) {
            $domain->trackGA = true;
        }
        if ( ! isset( $domain->trackConversion ) ) {
            $domain->trackConversion = true;
        }

    }
}

class CnbDomainProperties {
    /**
     * @var number
     */
    public $scale;

    /**
     * @var boolean
     */
    public $debug;
}
