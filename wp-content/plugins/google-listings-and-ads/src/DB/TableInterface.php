<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Interface DBTableInterface
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\DB
 */
interface TableInterface {

	/**
	 * Install the Database table.
	 */
	public function install(): void;

	/**
	 * Determine whether the table actually exists in the DB.
	 *
	 * @return bool
	 */
	public function exists(): bool;

	/**
	 * Delete the Database table.
	 */
	public function delete(): void;

	/**
	 * Truncate the Database table.
	 */
	public function truncate(): void;

	/**
	 * Get the name of the Database table.
	 *
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * Get the columns for the table.
	 *
	 * @return array
	 */
	public function get_columns(): array;

	/**
	 * Get the primary column name for the table.
	 *
	 * @return string
	 */
	public function get_primary_column(): string;

	/**
	 * Checks whether an index exists for the table.
	 *
	 * @param string $index_name The index name.
	 *
	 * @return bool True if the index exists on the table and False if not.
	 *
	 * @since 1.4.1
	 */
	public function has_index( string $index_name ): bool;
}
