<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\Options;

/**
 * Interface TransientsInterface
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\Options
 */
interface TransientsInterface {

	public const ADS_METRICS          = 'ads_metrics';
	public const FREE_LISTING_METRICS = 'free_listing_metrics';
	public const MC_STATUSES          = 'mc_statuses';

	public const VALID_OPTIONS = [
		self::ADS_METRICS          => true,
		self::FREE_LISTING_METRICS => true,
		self::MC_STATUSES          => true,
	];

	/**
	 * Get a transient.
	 *
	 * @param string $name    The transient name.
	 * @param mixed  $default A default value for the transient.
	 *
	 * @return mixed
	 */
	public function get( string $name, $default = null );

	/**
	 * Add or update a transient.
	 *
	 * @param string $name  The transient name.
	 * @param mixed  $value The transient value.
	 * @param int    $expiration Time until expiration in seconds.
	 *
	 * @return bool
	 */
	public function set( string $name, $value, int $expiration = 0 ): bool;

	/**
	 * Delete a transient.
	 *
	 * @param string $name The transient name.
	 *
	 * @return bool
	 */
	public function delete( string $name ): bool;

	/**
	 * Returns all available transient keys.
	 *
	 * @return array
	 *
	 * @since 1.3.0
	 */
	public static function get_all_transient_keys(): array;
}
