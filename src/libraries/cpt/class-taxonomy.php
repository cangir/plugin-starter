<?php
/**
 * Taxonomy Class
 *
 * @author  Dinoloper <info@dinoloper.com>
 * @package CPT
 * @version 1.0.0
 */

namespace Dinoloper\CPT;

defined( 'ABSPATH' ) || exit; // Cannot access directly.

if ( ! class_exists( 'Taxonomy' ) ) {
	/**
	 * Taxonomy
	 *
	 * Create WordPress Taxonomies easily
	 *
	 * @link    https://github.com/jjgrainger/PostTypes/
	 * @author  jjgrainger
	 * @link    https://jjgrainger.co.uk
	 * @version 2.0
	 * @license https://opensource.org/licenses/mit-license.html
	 */
	class Taxonomy {

		/**
		 * The names passed to the Taxonomy
		 *
		 * @var mixed
		 */
		public $names;

		/**
		 * The Taxonomy name
		 *
		 * @var string
		 */
		public $name;

		/**
		 * The singular label for the Taxonomy
		 *
		 * @var string
		 */
		public $singular;

		/**
		 * The plural label for the Taxonomy
		 *
		 * @var string
		 */
		public $plural;

		/**
		 * The Taxonomy slug
		 *
		 * @var string
		 */
		public $slug;

		/**
		 * Custom options for the Taxonomy
		 *
		 * @var array
		 */
		public $options;

		/**
		 * Custom labels for the Taxonomy
		 *
		 * @var array
		 */
		public $labels;

		/**
		 * PostTypes to register the Taxonomy to.
		 *
		 * @var array
		 */
		public $posttypes = array();

		/**
		 * The column manager for the Taxonomy.
		 *
		 * @var mixed
		 */
		public $columns;

		/**
		 * Create a Taxonomy.
		 *
		 * @param array $names Names.
		 * @param array $options Options.
		 * @param mixed $labels The label(s) for the Taxonomy.
		 */
		public function __construct( $names, $options = array(), $labels = array() ) {
			$this->names( $names );

			$this->options( $options );

			$this->labels( $labels );
		}

		/**
		 * Set the names for the Taxonomy.
		 *
		 * @param  mixed $names The name(s) for the Taxonomy.
		 * @return $this
		 */
		public function names( $names ) {
			if ( is_string( $names ) ) {
				$names = array( 'name' => $names );
			}

			$this->names = $names;

			// create names for the Taxonomy.
			$this->create_names();

			return $this;
		}

		/**
		 * Set options for the Taxonomy.
		 *
		 * @param  array $options Options.
		 * @return $this
		 */
		public function options( array $options = array() ) {
			$this->options = $options;

			return $this;
		}

		/**
		 * Set the Taxonomy labels
		 *
		 * @param  array $labels Labels.
		 * @return $this
		 */
		public function labels( array $labels = array() ) {
			$this->labels = $labels;

			return $this;
		}

		/**
		 * Assign a PostType to register the Taxonomy to
		 *
		 * @param  mixed $posttypes Post Types.
		 * @return $this
		 */
		public function posttype( $posttypes ) {
			$posttypes = is_string( $posttypes ) ? array( $posttypes ) : $posttypes;

			foreach ( $posttypes as $posttype ) {
				$this->posttypes[] = $posttype;
			}

			return $this;
		}

		/**
		 * Get the Column Manager for the Taxonomy.
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
		 * Register the Taxonomy to WordPress.
		 *
		 * @return void
		 */
		public function register() {
			// register the taxonomy, set priority to 9.
			// so taxonomies are registered before PostTypes.
			add_action( 'init', array( $this, 'register_taxonomy' ), 9 );

			// assign taxonomy to post type objects.
			add_action( 'init', array( $this, 'register_taxonomy_to_objects' ) );

			if ( isset( $this->columns ) ) {
				// modify the columns for the Taxonomy.
				add_filter( "manage_edit-{$this->name}_columns", array( $this, 'modify_columns' ) );

				// populate the columns for the Taxonomy.
				add_filter( "manage_{$this->name}_custom_column", array( $this, 'populate_columns' ), 10, 3 );

				// set custom sortable columns.
				add_filter( "manage_edit-{$this->name}_sortable_columns", array( $this, 'set_sortable_columns' ) );

				// run action that sorts columns on request.
				add_action( 'parse_term_query', array( $this, 'sort_sortable_columns' ) );
			}
		}

		/**
		 * Register the Taxonomy to WordPress.
		 *
		 * @return void
		 */
		public function register_taxonomy() {
			if ( ! taxonomy_exists( $this->name ) ) {
				// create options for the Taxonomy.
				$options = $this->create_options();

				// register the Taxonomy with WordPress.
				register_taxonomy( $this->name, null, $options );
			}
		}

		/**
		 * Register the Taxonomy to PostTypes.
		 *
		 * @return void
		 */
		public function register_taxonomy_to_objects() {
			// register Taxonomy to each of the PostTypes assigned.
			if ( ! empty( $this->posttypes ) ) {
				foreach ( $this->posttypes as $posttype ) {
					register_taxonomy_for_object_type( $this->name, $posttype );
				}
			}
		}

		/**
		 * Create names for the Taxonomy.
		 *
		 * @return void
		 */
		public function create_names() {
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
		 * Create options for Taxonomy.
		 *
		 * @return array Options to pass to register_taxonomy.
		 */
		public function create_options() {
			// default options.
			$options = array(
				'hierarchical'      => false,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug' => $this->slug,
				),
			);

			// replace defaults with the options passed.
			$options = array_replace_recursive( $options, $this->options );

			// create and set labels.
			if ( ! isset( $options['labels'] ) ) {
				$options['labels'] = $this->create_labels();
			}

			return $options;
		}

		/**
		 * Create labels for the Taxonomy.
		 *
		 * @return array
		 */
		public function create_labels() {
			// default labels.
			$labels = array(
				'name'                       => $this->plural,
				'singular_name'              => $this->singular,
				'menu_name'                  => $this->plural,
				'all_items'                  => "All {$plural}",
				'edit_item'                  => "Edit {$singular}",
				'view_item'                  => "View {$singular}",
				'update_item'                => "Update {$singular}",
				'add_new_item'               => "Add New {$singular}",
				'new_item_name'              => "New {$singular} Name",
				'parent_item'                => "Parent {$plural}",
				'parent_item_colon'          => "Parent {$singular}:",
				'search_items'               => "Search {$plural}",
				'popular_items'              => "Popular {$plural}",
				'separate_items_with_commas' => "Seperate {$plural} with commas",
				'add_or_remove_items'        => "Add or Remove {$plural}",
				'choose_from_most_used'      => "Choose from most used {$plural}",
				'not_found'                  => "No {$plural} found.",
			);

			return array_replace( $labels, $this->labels );
		}

		/**
		 * Modify the columns for the Taxonomy.
		 *
		 * @param  array $columns  The WordPress default columns.
		 * @return array
		 */
		public function modify_columns( $columns ) {
			$columns = $this->columns->modify_columns( $columns );

			return $columns;
		}

		/**
		 * Populate custom columns for the Taxonomy.
		 *
		 * @param  string $content Content.
		 * @param  string $column Column.
		 * @param  int    $term_id Term ID.
		 */
		public function populate_columns( $content, $column, $term_id ) {
			if ( isset( $this->columns->populate[ $column ] ) ) {
				$content = call_user_func_array( $this->columns()->populate[ $column ], array( $content, $column, $term_id ) );
			}

			return $content;
		}

		/**
		 * Make custom columns sortable.
		 *
		 * @param array $columns Default WordPress sortable columns.
		 */
		public function set_sortable_columns( $columns ) {
			if ( ! empty( $this->columns()->sortable ) ) {
				$columns = array_merge( $columns, $this->columns()->sortable );
			}

			return $columns;
		}

		/**
		 * Set query to sort custom columns.
		 *
		 * @param WP_Term_Query $query Query.
		 */
		public function sort_sortable_columns( $query ) {
			// don't modify the query if we're not in the post type admin.
			if ( ! is_admin() || ! in_array( $this->name, $query->query_vars['taxonomy'], true ) ) {
				return;
			}

			if ( ! isset( $_GET['orderby'] ) ) {
				return;
			}

			// check the orderby is a custom ordering.
			if ( array_key_exists( sanitize_title( wp_unslash( $_GET['orderby'] ) ), $this->columns()->sortable ) ) {
				// get the custom sorting options.
				$meta = $this->columns()->sortable[ $orderby_input ];

				// check ordering is not numeric.
				if ( is_string( $meta ) ) {
					$meta_key = $meta;
					$orderby  = 'meta_value';
				} else {
					$meta_key = $meta[0];
					$orderby  = 'meta_value_num';
				}

				// set the sort order.
				$query->query_vars['orderby']  = $orderby;
				$query->query_vars['meta_key'] = $meta_key;
			}
		}
	}
}
