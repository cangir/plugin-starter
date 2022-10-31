<?php
/**
 * Columns Class
 *
 * @author  Dinoloper <info@dinoloper.com>
 * @package CPT
 * @version 1.0.0
 */

namespace Dinoloper\CPT;

defined( 'ABSPATH' ) || exit; // Cannot access directly.

if ( ! class_exists( 'Columns' ) ) {
	/**
	 * Columns
	 *
	 * Used to help manage a post types columns in the admin table
	 *
	 * @link    https://github.com/jjgrainger/PostTypes/
	 * @author  jjgrainger
	 * @link    https://jjgrainger.co.uk
	 * @version 2.0
	 * @license https://opensource.org/licenses/mit-license.html
	 */
	class Columns {

		/**
		 * Holds an array of all the defined columns.
		 *
		 * @var array
		 */
		public $items = array();

		/**
		 * An array of columns to add.
		 *
		 * @var array
		 */
		public $add = array();

		/**
		 * An array of columns to hide.
		 *
		 * @var array
		 */
		public $hide = array();

		/**
		 * An array of columns to reposition.
		 *
		 * @var array
		 */
		public $positions = array();

		/**
		 * An array of custom populate callbacks.
		 *
		 * @var array
		 */
		public $populate = array();

		/**
		 * An array of columns that are sortable.
		 *
		 * @var array
		 */
		public $sortable = array();

		/**
		 * Set the all columns
		 *
		 * @param array $columns an array of all the columns to replace.
		 */
		public function set( $columns ) {
			$this->items = $columns;
		}

		/**
		 * Add a new column
		 *
		 * @param array  $columns Columns.
		 * @param string $label   the label for the column.
		 */
		public function add( $columns, $label = null ) {

			if ( ! is_array( $columns ) ) {
				$columns = array( $columns => $label );
			}

			foreach ( $columns as $column => $label ) {
				if ( is_null( $label ) ) {
					$label = str_replace( array( '_', '-' ), ' ', ucfirst( $column ) );
				}

				$this->add[ $column ] = $label;
			}

			return $this;
		}

		/**
		 * Add a column to hide
		 *
		 * @param  string $columns Columns.
		 */
		public function hide( $columns ) {
			if ( ! is_array( $columns ) ) {
				$columns = array( $columns );
			}

			foreach ( $columns as $column ) {
				$this->hide[] = $column;
			}

			return $this;
		}

		/**
		 * Set a custom callback to populate a column
		 *
		 * @param  string $column   the column slug.
		 * @param  mixed  $callback callback function.
		 */
		public function populate( $column, $callback ) {
			$this->populate[ $column ] = $callback;

			return $this;
		}

		/**
		 * Define the postion for a columns
		 *
		 * @param  string $columns  an array of columns.
		 */
		public function order( $columns ) {
			foreach ( $columns as $column => $position ) {
				$this->positions[ $column ] = $position;
			}

			return $this;
		}

		/**
		 * Set columns that are sortable
		 *
		 * @param string $sortable Sortable columns.
		 */
		public function sortable( $sortable ) {
			foreach ( $sortable as $column => $options ) {
				$this->sortable[ $column ] = $options;
			}

			return $this;
		}

		/**
		 * Check if an orderby field is a custom sort option.
		 *
		 * @param string $orderby  the orderby value from query params.
		 */
		public function is_sortable( $orderby ) {
			if ( is_string( $orderby ) && array_key_exists( $orderby, $this->sortable ) ) {
				return true;
			}

			foreach ( $this->sortable as $column => $options ) {
				if ( is_string( $options ) && $options === $orderby ) {
					return true;
				}
				if ( is_array( $options ) && isset( $options[0] ) && $options[0] === $orderby ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Get meta key for an orderby.
		 *
		 * @param string $orderby  the orderby value from query params.
		 */
		public function sortable_meta( $orderby ) {
			if ( array_key_exists( $orderby, $this->sortable ) ) {
				return $this->sortable[ $orderby ];
			}

			foreach ( $this->sortable as $column => $options ) {
				if ( is_string( $options ) && $options === $orderby ) {
					return $options;
				}
				if ( is_array( $options ) && isset( $options[0] ) && $options[0] === $orderby ) {
					return $options;
				}
			}

			return '';
		}

		/**
		 * Modify the columns for the object
		 *
		 * @param  array $columns WordPress default columns.
		 * @return array           The modified columns.
		 */
		public function modify_columns( $columns ) {
			// if user defined set columns, return those.
			if ( ! empty( $this->items ) ) {
				return $this->items;
			}

			// add additional columns.
			if ( ! empty( $this->add ) ) {
				foreach ( $this->add as $key => $label ) {
					$columns[ $key ] = $label;
				}
			}

			// unset hidden columns.
			if ( ! empty( $this->hide ) ) {
				foreach ( $this->hide as $key ) {
					unset( $columns[ $key ] );
				}
			}

			// if user has made added custom columns.
			if ( ! empty( $this->positions ) ) {
				foreach ( $this->positions as $key => $position ) {
					// find index of the element in the array.
					$index = array_search( $key, array_keys( $columns ), true );
					// retrieve the element in the array of columns.
					$item = array_slice( $columns, $index, 1 );
					// remove item from the array.
					unset( $columns[ $key ] );

					// split columns array into two at the desired position.
					$start = array_slice( $columns, 0, $position, true );
					$end   = array_slice( $columns, $position, count( $columns ) - 1, true );

					// insert column into position.
					$columns = $start + $item + $end;
				}
			}

			return $columns;
		}
	}
}
