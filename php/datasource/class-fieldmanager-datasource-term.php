<?php

class Fieldmanager_Datasource_Term extends Fieldmanager_Datasource {

	/**
	 * @var string|array
	 * Taxonomy name or array of taxonomy names
	 */
	public $taxonomy = null;

	/**
	 * @var array
	 * Helper for taxonomy-based option sets; arguments to find terms
	 */
	public $taxonomy_args = array();

	/**
	 * @var boolean
	 * Sort taxonomy hierarchically and indent child categories with dashes?
	 */
	public $taxonomy_hierarchical = false;

	/**
	 * @var int
	 * How far to descend into taxonomy hierarchy (0 for no limit)
	 */
	public $taxonomy_hierarchical_depth = 0;

	/**
	 * @var boolean
	 * Pass $append = true to wp_set_object_terms?
	 */
	public $append_taxonomy = False;

	/**
	 * @var string
	 * If true, additionally save taxonomy terms to WP's terms tables.
	 */
	public $taxonomy_save_to_terms = True;

	/**
	 * @var string
	 * If true, only save this field to the taxonomy tables, and do not serialize in the FM array.
	 */
	public $only_save_to_taxonomy = False;

	/**
	 * @var boolean
	 * Build this datasource using AJAX
	 */
	public $use_ajax = True;

	/**
	 * Constructor
	 */
	public function __construct( $options = array() ) {
		global $wp_taxonomies;

		parent::__construct( $options );
		if ( !is_array( $this->taxonomy ) ) $this->taxonomy = array( $this->taxonomy );
		if ( $this->only_save_to_taxonomy ) $this->taxonomy_save_to_terms = True;
		
		// make post_tag and category sortable via term_order, if they're set as taxonomies, and if
		// we're not using Fieldmanager storage
		if ( $this->only_save_to_taxonomy && in_array( 'post_tag', $this->taxonomy ) ) {
			$wp_taxonomies['post_tag']->sort = True;
		}
		if ( $this->only_save_to_taxonomy && in_array( 'category', $this->taxonomy ) ) {
			$wp_taxonomies['category']->sort = True;
		}

		// default to showing empty tags, which generally makes more sense for the types of fields
		// that fieldmanager supports
		if ( !isset( $this->taxonomy_args['hide_empty'] ) ) {
			$this->taxonomy_args['hide_empty'] = False;
		}
	}

	/**
	 * Unique among FM types, the taxonomy datasource can store data outside FM's array.
	 * This is how we add it back into the array for editing.
	 * @param Fieldmanager_Field $field
	 * @param array $values
	 * @return array $values loaded up, if applicable.
	 */
	public function preload_alter_values( Fieldmanager_Field $field, $values ) {
		if ( $this->only_save_to_taxonomy ) {
			$terms = wp_get_object_terms( $field->data_id, $this->taxonomy[0], array( 'orderby' => 'term_order' ) );
			
			if ( count( $terms ) > 0 ) {
				if ( $field->limit == 1 ) {
					return $terms[0]->term_id;
				} else {
					$ret = array();
					foreach ( $terms as $term ) {
						$ret[] = $term->term_id;
					}
					return $ret;
				}
			}
		}
		return $values;
	}

	/**
	 * Presave hook to set taxonomy data
	 * @param int[] $values
	 * @param int[] $current_values
	 * @return int[] $values
	 */
	public function presave_alter_values( Fieldmanager_Field $field, $values, $current_values ) {	
		// If this is a taxonomy-based field, must also save the value(s) as an object term
		if ( $this->taxonomy_save_to_terms && isset( $this->taxonomy ) && !empty( $values ) ) {
			// Sanitize the value(s)
			if ( !is_array( $values ) ) {
				$values = array( $values );
			}
			$tax_values = array();
			foreach ( $values as &$value ) {
				if ( !empty( $value ) ) {
					if( is_numeric( $value ) )
						$tax_values[] = $value;
					else if( is_array( $value ) )
						$tax_values = $value;
				}
			}
			$this->save_taxonomy( $tax_values, $field->data_id );
		}
		if ( $this->only_save_to_taxonomy ) return array();
		return $values;
	}

	/**
	 * Sanitize a value
	 */
	public function presave( Fieldmanager_Field $field, $value, $current_value ) {
		return intval( $value );
	}

	/**
	 * Save taxonomy data
	 * @param mixed[] $tax_values
	 * @return void
	 */
	public function save_taxonomy( $tax_values, $data_id ) {
	
		$tax_values = array_map( 'intval', $tax_values );
		$tax_values = array_unique( $tax_values );

		// Store the each term for this post. Handle grouped fields differently since multiple taxonomies are present.
		if ( count( $this->taxonomy ) > 1 ) {
			// Build the taxonomy insert data
			$taxonomies_to_save = array();
			foreach ( $tax_values as $term_id ) {
				$term = $this->get_term( $term_id );
				if ( empty( $taxonomies_to_save[ $term->taxonomy ] ) ) $taxonomies_to_save[ $term->taxonomy ] = array();
				$taxonomies_to_save[ $term->taxonomy ][] = $term_id;
			}
			foreach ( $taxonomies_to_save as $taxonomy => $terms ) {
				wp_set_object_terms( $data_id, $terms, $taxonomy, $this->append_taxonomy );
			}
		} else {
			wp_set_object_terms( $data_id, $tax_values, $this->taxonomy[0], $this->append_taxonomy );
		}
	}
	
	/**
	 * Get taxonomy data per $this->taxonomy_args
	 * @param $value The value(s) currently set for this field
	 * @return array[] data entries for options
	 */
	public function get_items( $fragment = Null ) {

		// If taxonomy_hierarchical is set, assemble recursive term list, then bail out.
		if ( $this->taxonomy_hierarchical ) {
			$tax_args = $this->taxonomy_args;
			$tax_args['parent'] = 0;
			$parent_terms = get_terms( $this->taxonomy, $tax_args );
			return $this->build_hierarchical_term_data( $parent_terms, $this->taxonomy_args, 0, $fragment );
		}
	
		$tax_args = $this->taxonomy_args;
		if ( !empty( $fragment ) ) $tax_args['search'] = $fragment;
		$terms = get_terms( $this->taxonomy, $tax_args );
		
		// If the taxonomy list was an array and group display is set, ensure all terms are grouped by taxonomy
		// Use the order of the taxonomy array list for sorting the groups to make this controllable for developers
		// Order of the terms within the groups is already controllable via $taxonomy_args
		// Skip this entirely if there is only one taxonomy even if group display is set as it would be unnecessary
		if ( count( $this->taxonomy ) > 1 && $this->grouped && $this->allow_optgroups ) {
			// Group the data
			$term_groups = array();
			foreach ( $this->taxonomy as $tax ) {
				$term_groups[$tax] = array();
			}
			foreach ( $terms as $term ) {
				$term_groups[$term->taxonomy][ $term->term_id ] = $term->name;
			}
			return $term_groups;
		}
		
		// Put the taxonomy data into the proper data structure to be used for display
		foreach ( $terms as $term ) {
			// Store the label for the taxonomy as the group since it will be used for display
			$stack[ $term->term_id ] = $term->name;
		}
		return $stack;
	}

	/**
	 * Helper to support recursive building of a hierarchical taxonomy list.
	 * @param array $parent_terms
	 * @param array $tax_args as used in top-level get_terms() call.
	 * @param int $depth current recursive depth level.
	 * @param string $fragment optional matching pattern
	 * @return array of terms or false if no children found.
	 */
	protected function build_hierarchical_term_data( $parent_terms, $tax_args, $depth, $stack = array(), $pattern = '' ) {
		
		// Walk through each term passed, add it (at current depth) to the data stack.
		foreach ( $parent_terms as $term ) {
			$taxonomy_data = get_taxonomy( $term->taxonomy );
			$prefix = '';
			
			// Prefix term based on depth. For $depth = 0, prefix will remain empty.
			for ( $i = 0; $i < $depth; $i++ ) {
				$prefix .= '--';
			}
			
			$stack[$term->term_id] = $prefix . ' ' . $term->name;
			
			// Find child terms of this. If any, recurse on this function.
			$tax_args['parent'] = $term->term_id;
			if ( !empty( $pattern ) ) $tax_args['search'] = $fragment;
			$child_terms = get_terms( $this->taxonomy, $tax_args );
			if ( $this->taxonomy_hierarchical_depth == 0 || $depth + 1 < $this->taxonomy_hierarchical_depth ) {
				if ( !empty( $child_terms ) ) {
					$stack = $this->build_hierarchical_term_data( $child_terms, $this->taxonomy_args, $depth + 1, $stack );
				}
			}
		}
		return $stack;
	}

	/**
	 * Translate term id to title, e.g. for autocomplete
	 * @param mixed $value
	 * @return string
	 */
	public function get_value( $value ) {
		$id = intval( $value );
		$term = $this->get_term( $id );
		return is_object( $term ) ? $term->name : '';
	}

	/**
	 * Get term by ID only, potentially using multiple taxonomies
	 * @param int $term_id
	 * @return object|null
	 */
	private function get_term( $term_id ) {
		$terms = get_terms( $this->taxonomy, array( 'include' => array( $term_id ), 'number' => 1 ) );
		return !empty( $terms[0] ) ? $terms[0] : Null;
	}

	/**
	 * Link to view a term
	 */
	public function get_view_link( $value ) {
		return '';
	}

	/**
	 * Link to edit a term
	 */
	public function get_edit_link( $value ) {
		return '';
	}

}