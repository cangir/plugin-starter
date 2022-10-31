<?php
/**
 * Post Type Class
 *
 * @author  Dinoloper <info@dinoloper.com>
 * @package CPT
 * @version 1.0.0
 */

namespace Dinoloper\CPT;

defined( 'ABSPATH' ) || exit; // Cannot access directly.

if ( ! class_exists( 'Post_Type' ) ) {
	/**
	 * PostType
	 *
	 * Create WordPress custom post types easily
	 *
	 * @link    https://github.com/jjgrainger/PostTypes/
	 * @author  jjgrainger
	 * @link    https://jjgrainger.co.uk
	 * @version 2.0
	 * @license https://opensource.org/licenses/mit-license.html
	 */
	class Post_Type {

		/**
		 * The names passed to the PostType
		 *
		 * @var array
		 */
		public $names;

		/**
		 * The name for the PostType
		 *
		 * @var string
		 */
		public $name;

		/**
		 * The singular for the PostType
		 *
		 * @var string
		 */
		public $singular;

		/**
		 * The plural name for the PostType
		 *
		 * @var string
		 */
		public $plural;

		/**
		 * The slug for the PostType
		 *
		 * @var string
		 */
		public $slug;

		/**
		 * Options for the PostType
		 *
		 * @var array
		 */
		public $options;

		/**
		 * Labels for the PostType
		 *
		 * @var array
		 */
		public $labels;

		/**
		 * Taxonomies for the PostType
		 *
		 * @var array
		 */
		public $taxonomies = array();

		/**
		 * Filters for the PostType
		 *
		 * @var mixed
		 */
		public $filters;

		/**
		 * The menu icon for the PostType
		 *
		 * @var string
		 */
		public $icon;

		/**
		 * The column manager for the PostType
		 *
		 * @var mixed
		 */
		public $columns;

		/**
		 * Create a PostType
		 *
		 * @param mixed $names   A string for the name, or an array of names.
		 * @param array $options An array of options for the PostType.
		 * @param array $labels Labels.
		 */
		public function __construct( $names, $options = array(), $labels = array() ) {
			// assign names to the PostType.
			$this->names( $names );

			// assign custom options to the PostType.
			$this->options( $options );

			// assign labels to the PostType.
			$this->labels( $labels );
		}

		/**
		 * Set the names for the PostType
		 *
		 * @param  mixed $names A string for the name, or an array of names.
		 * @return $this
		 */
		public function names( $names ) {
			// only the post type name is passed.
			if ( is_string( $names ) ) {
				$names = array( 'name' => $names );
			}

			// set the names array.
			$this->names = $names;

			// create names for the PostType.
			$this->create_names();

			return $this;
		}

		/**
		 * Set the options for the PostType
		 *
		 * @param  array $options An array of options for the PostType.
		 * @return $this
		 */
		public function options( array $options ) {
			$this->options = $options;

			return $this;
		}

		/**
		 * Set the labels for the PostType
		 *
		 * @param  array $labels An array of labels for the PostType.
		 * @return $this
		 */
		public function labels( array $labels ) {
			$this->labels = $labels;

			return $this;
		}

		/**
		 * Add a Taxonomy to the PostType
		 *
		 * @param  mixed $taxonomies The Taxonomy name(s) to add.
		 * @return $this
		 */
		public function taxonomy( $taxonomies ) {
			$taxonomies = is_string( $taxonomies ) ? array( $taxonomies ) : $taxonomies;

			foreach ( $taxonomies as $taxonomy ) {
				$this->taxonomies[] = $taxonomy;
			}

			return $this;
		}

		/**
		 * Add filters to the PostType
		 *
		 * @param  array $filters An array of Taxonomy filters.
		 * @return $this
		 */
		public function filters( array $filters ) {
			$this->filters = $filters;

			return $this;
		}

		/**
		 * Set the menu icon for the PostType
		 *
		 * @param  string $icon A dashicon class for the menu icon.
		 * @return $this
		 */
		public function icon( $icon ) {
			$this->icon = $icon;

			return $this;
		}

		/**
		 * Flush rewrite rules
		 *
		 * @link https://codex.wordpress.org/Function_Reference/flush_rewrite_rules
		 * @param  boolean $hard Hard.
		 * @return void
		 */
		public function flush( $hard = true ) {
			flush_rewrite_rules( $hard );
		}

		/**
		 * Get the Column Manager for the PostType
		 *
		 * @return cpt\Columns
		 */
		public function columns() {
			if ( ! isset( $this->columns ) ) {
				$this->columns = new cpt\Columns();
			}

			return $this->columns;
		}

		/**
		 * Register the PostType to WordPress
		 *
		 * @return void
		 */
		public function register() {
			// register the PostType.
			add_action( 'init', array( $this, 'register_post_type' ) );

			// register Taxonomies to the PostType.
			add_action( 'init', array( $this, 'register_taxonomies' ) );

			// modify filters on the admin edit screen.
			add_action( 'restrict_manage_posts', array( $this, 'modify_filters' ) );

			if ( isset( $this->columns ) ) {
				// modify the admin edit columns.
				add_filter( "manage_{$this->name}_posts_columns", array( $this, 'modify_columns' ), 10, 1 );

				// populate custom columns.
				add_filter( "manage_{$this->name}_posts_custom_column", array( $this, 'populate_columns' ), 10, 2 );

				// run filter to make columns sortable.
				add_filter( 'manage_edit-' . $this->name . '_sortable_columns', array( $this, 'set_sortable_columns' ) );

				// run action that sorts columns on request.
				add_action( 'pre_get_posts', array( $this, 'sort_sortable_columns' ) );
			}
		}

		/**
		 * Register the PostType
		 *
		 * @return void
		 */
		public function register_post_type() {
			// create options for the PostType.
			$options = $this->create_options();

			// check that the post type doesn't already exist.
			if ( ! post_type_exists( $this->name ) ) {
				// register the post type.
				register_post_type( $this->name, $options );
			}
		}

		/**
		 * Create the required names for the PostType
		 *
		 * @return void
		 */
		public function create_names() {
			// names required for the PostType.
			$required = array(
				'name',
				'singular',
				'plural',
				'slug',
			);

			foreach ( $required as $key ) {
				// if the name is set, assign it.
				if ( isset( $this->names[ $key ] ) ) {
					$this->$key = $this->names[ $key ];
					continue;
				}

				// if the key is not set and is singular or plural.
				if ( in_array( $key, array( 'singular', 'plural' ), true ) ) {
					// create a human friendly name.
					$name = ucwords( strtolower( str_replace( array( '-', '_' ), ' ', $this->names['name'] ) ) );
				}

				if ( 'slug' === $key ) {
					// create a slug friendly name.
					$name = strtolower( str_replace( array( ' ', '_' ), '-', $this->names['name'] ) );
				}

				// if is plural or slug, append an 's'.
				if ( in_array( $key, array( 'plural', 'slug' ), true ) ) {
					$name .= 's';
				}

				// asign the name to the PostType property.
				$this->$key = $name;
			}
		}

		/**
		 * Create options for PostType
		 *
		 * @return array Options to pass to register_post_type
		 */
		public function create_options() {
			// default options.
			$options = array(
				'public'  => true,
				'rewrite' => array(
					'slug' => $this->slug,
				),
			);

			// replace defaults with the options passed.
			$options = array_replace_recursive( $options, $this->options );

			// create and set labels.
			if ( ! isset( $options['labels'] ) ) {
				$options['labels'] = $this->create_labels();
			}

			// set the menu icon.
			if ( ! isset( $options['menu_icon'] ) && isset( $this->icon ) ) {
				$options['menu_icon'] = $this->icon;
			}

			return $options;
		}

		/**
		 * Create the labels for the PostType
		 *
		 * @return array
		 */
		public function create_labels() {

			// Friendly post type names.
			$plural   = $this->plural;
			$singular = $this->singular;
			$slug     = $this->slug;

			// default labels.
			$labels = array(
				'name'               => $plural,
				'singular_name'      => $singular,
				'menu_name'          => $plural,
				'all_items'          => "All {$plural}",
				'add_new'            => 'Add New',
				'add_new_item'       => "Add New {$singular}",
				'edit_item'          => "Edit {$singular}",
				'new_item'           => "New {$singular}",
				'view_item'          => "View {$singular}",
				'search_items'       => "Search {$plural}",
				'not_found'          => "No {$plural} found",
				'not_found_in_trash' => "No {$plural} found in Trash",
				'parent_item_colon'  => "Parent {$singular}:",
			);

			return array_replace_recursive( $labels, $this->labels );
		}

		/**
		 * Register Taxonomies to the PostType
		 *
		 * @return void
		 */
		public function register_taxonomies() {
			if ( ! empty( $this->taxonomies ) ) {
				foreach ( $this->taxonomies as $taxonomy ) {
					register_taxonomy_for_object_type( $taxonomy, $this->name );
				}
			}
		}

		/**
		 * Modify and display filters on the admin edit screen
		 *
		 * @param  string $posttype The current screen post type.
		 * @return void
		 */
		public function modify_filters( $posttype ) {
			// first check we are working with the this PostType.
			if ( $posttype === $this->name ) {
				// calculate what filters to add.
				$filters = $this->get_filters();

				foreach ( $filters as $taxonomy ) {
					// if the taxonomy doesn't exist, ignore it.
					if ( ! taxonomy_exists( $taxonomy ) ) {
						continue;
					}

					// get the taxonomy object.
					$tax = get_taxonomy( $taxonomy );

					// get the terms for the taxonomy.
					$terms = get_terms(
						array(
							'taxonomy'   => $taxonomy,
							'orderby'    => 'name',
							'hide_empty' => false,
						)
					);

					// if there are no terms in the taxonomy, ignore it.
					if ( empty( $terms ) ) {
						continue;
					}

					// start the html for the filter dropdown.
					$selected = null;

					if ( ! empty( $_GET[ $taxonomy ] ) ) { // Input var okay.
						$selected = sanitize_title( wp_unslash( $_GET[ $taxonomy ] ) ); // Input var okay.
					}

					$dropdown_args = array(
						'option_none_value' => '',
						'hide_empty'        => 0,
						'hide_if_empty'     => false,
						'show_count'        => true,
						'taxonomy'          => $tax->name,
						'name'              => $taxonomy,
						'orderby'           => 'name',
						'hierarchical'      => true,
						'show_option_none'  => "Show all {$tax->label}",
						'value_field'       => 'slug',
						'selected'          => $selected,
					);

					wp_dropdown_categories( $dropdown_args );
				}
			}
		}

		/**
		 * Calculate the filters for the PostType
		 *
		 * @return array
		 */
		public function get_filters() {
			// default filters are empty.
			$filters = array();

			// if custom filters have been set, use them.
			if ( ! is_null( $this->filters ) ) {
				return $this->filters;
			}

			// if no custom filters have been set, and there are
			// Taxonomies assigned to the PostType.
			if ( is_null( $this->filters ) && ! empty( $this->taxonomies ) ) {
				// create filters for each taxonomy assigned to the PostType.
				return $this->taxonomies;
			}

			return $filters;
		}

		/**
		 * Modify the columns for the PostType
		 *
		 * @param  array $columns  Default WordPress columns.
		 * @return array            The modified columns
		 */
		public function modify_columns( $columns ) {
			$columns = $this->columns->modify_columns( $columns );

			return $columns;
		}

		/**
		 * Populate custom columns for the PostType
		 *
		 * @param  string $column   The column slug.
		 * @param  int    $post_id  The post ID.
		 */
		public function populate_columns( $column, $post_id ) {
			if ( isset( $this->columns->populate[ $column ] ) ) {
				call_user_func_array( $this->columns()->populate[ $column ], array( $column, $post_id ) );
			}
		}

		/**
		 * Make custom columns sortable
		 *
		 * @param array $columns  Default WordPress sortable columns.
		 */
		public function set_sortable_columns( $columns ) {
			if ( ! empty( $this->columns()->sortable ) ) {
				$columns = array_merge( $columns, $this->columns()->sortable );
			}

			return $columns;
		}

		/**
		 * Set query to sort custom columns
		 *
		 * @param WP_Query $query WP Query.
		 */
		public function sort_sortable_columns( $query ) {
			// don't modify the query if we're not in the post type admin.
			if ( ! is_admin() || $query->get( 'post_type' ) !== $this->name ) {
				return;
			}

			$orderby = $query->get( 'orderby' );

			// if the sorting a custom column.
			if ( $this->columns()->is_sortable( $orderby ) ) {
				// get the custom column options.
				$meta = $this->columns()->sortable_meta( $orderby );

				// determine type of ordering.
				if ( is_string( $meta ) ) {
					$meta_key   = $meta;
					$meta_value = 'meta_value';
				} else {
					$meta_key   = $meta[0];
					$meta_value = 'meta_value_num';
				}

				// set the custom order.
				$query->set( 'meta_key', $meta_key );
				$query->set( 'orderby', $meta_value );
			}
		}
	}
}
