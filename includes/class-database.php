<?php
/**
 * Database handler class.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * Database handler class
 */
class Database {

	/**
	 * Insert a record into the database.
	 *
	 * @param string $table Table name (without prefix).
	 * @param array  $data  Data to insert.
	 * @return int|false The number of rows affected, or false on error.
	 */
	public static function insert( $table, $data ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_' . $table;

		// Sanitize and prepare data.
		$data = self::sanitize_data( $data );

		$result = $wpdb->insert( $table_name, $data );

		if ( false === $result ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Update a record in the database.
	 *
	 * @param string $table Table name (without prefix).
	 * @param array  $data  Data to update.
	 * @param array  $where Conditions for the WHERE clause.
	 * @return int|false The number of rows affected, or false on error.
	 */
	public static function update( $table, $data, $where ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_' . $table;

		// Sanitize and prepare data.
		$data = self::sanitize_data( $data );

		return $wpdb->update( $table_name, $data, $where );
	}

	/**
	 * Delete a record from the database.
	 *
	 * @param string $table Table name (without prefix).
	 * @param array  $where Conditions for the WHERE clause.
	 * @return int|false The number of rows affected, or false on error.
	 */
	public static function delete( $table, $where ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_' . $table;

		return $wpdb->delete( $table_name, $where );
	}

	/**
	 * Get a single row from the database.
	 *
	 * @param string $table Table name (without prefix).
	 * @param array  $where Conditions for the WHERE clause.
	 * @return object|null The row object, or null if not found.
	 */
	public static function get_row( $table, $where = array() ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_' . $table;
		$sql        = "SELECT * FROM $table_name";

		if ( ! empty( $where ) ) {
			$sql .= ' WHERE ';
			$conditions = array();
			foreach ( $where as $key => $value ) {
				$conditions[] = "$key = '" . esc_sql( $value ) . "'";
			}
			$sql .= implode( ' AND ', $conditions );
		}

		return $wpdb->get_row( $sql );
	}

	/**
	 * Get multiple rows from the database.
	 *
	 * @param string $table   Table name (without prefix).
	 * @param array  $where   Conditions for the WHERE clause.
	 * @param string $orderby Order by column.
	 * @param string $order   Order direction (ASC or DESC).
	 * @param int    $limit   Number of rows to return.
	 * @param int    $offset  Number of rows to skip.
	 * @return array Array of row objects.
	 */
	public static function get_results( $table, $where = array(), $orderby = 'id', $order = 'DESC', $limit = 0, $offset = 0 ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_' . $table;
		$sql        = "SELECT * FROM $table_name";

		if ( ! empty( $where ) ) {
			$sql .= ' WHERE ';
			$conditions = array();
			foreach ( $where as $key => $value ) {
				if ( is_array( $value ) ) {
					// Handle IN clause.
					$placeholders = implode( ',', array_fill( 0, count( $value ), '%s' ) );
					$conditions[] = "$key IN ($placeholders)";
				} else {
					$conditions[] = "$key = '" . esc_sql( $value ) . "'";
				}
			}
			$sql .= implode( ' AND ', $conditions );
		}

		if ( ! empty( $orderby ) ) {
			$sql .= " ORDER BY " . esc_sql( $orderby ) . " " . strtoupper( esc_sql( $order ) );
		}

		if ( $limit > 0 ) {
			$sql .= " LIMIT $limit";
			if ( $offset > 0 ) {
				$sql .= " OFFSET $offset";
			}
		}

		return $wpdb->get_results( $sql );
	}

	/**
	 * Count records in the database.
	 *
	 * @param string $table Table name (without prefix).
	 * @param array  $where Conditions for the WHERE clause.
	 * @return int The number of records.
	 */
	public static function count( $table, $where = array() ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_' . $table;
		$sql        = "SELECT COUNT(*) FROM $table_name";

		if ( ! empty( $where ) ) {
			$sql .= ' WHERE ';
			$conditions = array();
			foreach ( $where as $key => $value ) {
				$conditions[] = "$key = '" . esc_sql( $value ) . "'";
			}
			$sql .= implode( ' AND ', $conditions );
		}

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Sanitize input data.
	 *
	 * @param array $data Data to sanitize.
	 * @return array Sanitized data.
	 */
	private static function sanitize_data( $data ) {
		$sanitized = array();

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$sanitized[ $key ] = $value;
			} elseif ( is_null( $value ) ) {
				$sanitized[ $key ] = null;
			} else {
				$sanitized[ $key ] = sanitize_text_field( $value );
			}
		}

		return $sanitized;
	}

	/**
	 * Check if a record exists.
	 *
	 * @param string $table Table name (without prefix).
	 * @param array  $where Conditions for the WHERE clause.
	 * @return bool True if record exists, false otherwise.
	 */
	public static function exists( $table, $where ) {
		return self::count( $table, $where ) > 0;
	}
}
