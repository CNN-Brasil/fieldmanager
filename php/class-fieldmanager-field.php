<?php
/**
 * Class file for Fieldmanager_Field
 *
 * @package Fieldmanager
 */

/**
 * Abstract base class containing core functionality for Fieldmanager fields.
 *
 * Fields are UI elements that allow a person to interact with data.
 */
abstract class Fieldmanager_Field {

	/**
	 * If true, throw exceptions for illegal behavior.
	 *
	 * @var bool
	 */
	public static $debug = FM_DEBUG;

	/**
	 * Indicate that the base FM assets have been enqueued so we only do it once.
	 *
	 * @var bool
	 */
	public static $enqueued_base_assets = false;

	/**
	 * How many of these fields to display, 0 for no limit.
	 *
	 * @var int
	 */
	public $limit = 1;

	/**
	 * This is no longer used.
	 *
	 * @deprecated This argument will have no impact. It only remains to avoid
	 *             throwing exceptions in code that used it previously.
	 * @var int
	 */
	public $starting_count;

	/**
	 * How many of these fields to display at a minimum, if $limit != 1. If
	 * $limit == $minimum_count, the "add another" button and the remove tool
	 * will be hidden.
	 *
	 * @var int
	 */
	public $minimum_count = 0;

	/**
	 * How many empty fields to display if $limit != 1 when the total fields in
	 * the loaded data + $extra_elements > $minimum_count.
	 *
	 * @var int
	 */
	public $extra_elements = 1;

	/**
	 * Text for add more button.
	 *
	 * @var string
	 */
	public $add_more_label = '';

	/**
	 * The name of the form element, as in 'foo' in `<input name="foo" />`.
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * Label to use for form element.
	 *
	 * @var string
	 */
	public $label = '';

	/**
	 * If true, the label and the element will display on the same line. Some
	 * elements may not support this.
	 *
	 * @var bool
	 */
	public $inline_label = false;

	/**
	 * If true, the label will be displayed after the element.
	 *
	 * @var bool
	 */
	public $label_after_element = false;

	/**
	 * Description for the form element.
	 *
	 * @var string
	 */
	public $description = '';

	/**
	 * If true, the description will be displayed after the element.
	 *
	 * @var bool
	 */
	public $description_after_element = true;

	/**
	 * Extra HTML attributes to apply to the form element. Use boolean true to
	 * apply a standalone attribute, e.g. 'required' => true.
	 *
	 * @var string|bool|array
	 */
	public $attributes = array();

	/**
	 * CSS class for form element.
	 *
	 * @var string
	 */
	public $field_class = 'element';

	/**
	 * Repeat the label for each element if $limit > 1.
	 *
	 * @var bool
	 */
	public $one_label_per_item = true;

	/**
	 * Allow draggable sorting if $limit > 1.
	 *
	 * @var bool
	 */
	public $sortable = false;

	/**
	 * HTML element to use for label.
	 *
	 * @var string
	 */
	public $label_element = 'div';

	/**
	 * Function to use to sanitize input.
	 *
	 * @var callable
	 */
	public $sanitize = 'sanitize_text_field';

	/**
	 * Functions to use to validate input.
	 *
	 * @var array Callables.
	 */
	public $validate = array();

	/**
	 * Validation rule(s) from jQuery used to validate this field, entered as a string or associative array.
	 * These rules will be automatically converted to the appropriate Javascript format.
	 * For more information see http://jqueryvalidation.org/documentation/
	 *
	 * @var string|array
	 */
	public $validation_rules;

	/**
	 * Validation messages from jQuery used by the rule(s) defined for this field, entered as a string or associative array.
	 * These rules will be automatically converted to the appropriate Javascript format.
	 * Any messages without a corresponding rule will be ignored.
	 * For more information see http://jqueryvalidation.org/documentation/
	 *
	 * @var string|array
	 */
	public $validation_messages;

	/**
	 * Makes the field required on WordPress context forms that already have built-in validation.
	 * This is necessary only for the fields used with the term add context.
	 *
	 * @var bool
	 */
	public $required = false;

	/**
	 * Data type this element is used in, generally set internally.
	 *
	 * @var string
	 */
	public $data_type = null;

	/**
	 * ID for $this->data_type, eg $post->ID, generally set internally.
	 *
	 * @var int
	 */
	public $data_id = null;

	/**
	 * Fieldmanager context handling data submitted with this field. Generally set internally.
	 *
	 * @var ?Fieldmanager_Context
	 */
	public $current_context = null;

	/**
	 * If true, save empty elements to DB (if $this->limit != 1; single elements
	 * are always saved).
	 *
	 * @var bool
	 */
	public $save_empty = false;

	/**
	 * Do not save this field (useful for fields which handle saving their own data)
	 *
	 * @var bool
	 */
	public $skip_save = false;

	/**
	 * Save this field additionally to an index.
	 *
	 * @var bool
	 */
	public $index = false;

	/**
	 * Save the fields to their own keys (only works in some contexts). Default
	 * is true.
	 *
	 * @var bool
	 */
	public $serialize_data = true;

	/**
	 * Optionally generate field from datasource. Used by Fieldmanager_Autocomplete
	 * and Fieldmanager_Options.
	 *
	 * @var Fieldmanager_Datasource
	 */
	public $datasource = null;

	/**
	 * Field name and value on which to display element. Sample:
	 *
	 *     $element->display_if = array(
	 *         'src' => 'display-if-src-element',
	 *         'value' => 'display-if-src-value',
	 *     );
	 *
	 * Multiple values are allowed if comma-separated. Sample:
	 *
	 *     $element->display_if = array(
	 *         'src' => 'display-if-src-element',
	 *         'value' => 'display-if-src-value1,display-if-src-value2'
	 *     );
	 *
	 * @var array
	 */
	public $display_if = array();

	/**
	 * Where the new item should to added (top/bottom) of the stack. Used by Add
	 * Another button "top|bottom".
	 *
	 * @var string
	 */
	public $add_more_position = 'bottom';

	/**
	 * If true, remove any default meta boxes that are overridden by Fieldmanager
	 * fields.
	 *
	 * @var bool
	 */
	public $remove_default_meta_boxes = false;

	/**
	 * The path to the field template.
	 *
	 * @var string Template
	 */
	public $template = null;

	/**
	 * If $remove_default_meta_boxes is true, this array will be populated with
	 * the list of default meta boxes to remove.
	 *
	 * @var array
	 */
	public $meta_boxes_to_remove = array();

	/**
	 * The default value for the field, if unset.
	 *
	 * @var mixed Default value
	 */
	public $default_value = null;

	/**
	 * Function that parses an index value and returns an optionally modified value.
	 *
	 * @var callable
	 */
	public $index_filter = null;

	/**
	 * Input type, mainly to support HTML5 input types.
	 *
	 * @var string
	 */
	public $input_type = 'text';

	/**
	 * Custom escaping for labels, descriptions, etc. Associative array of
	 * $field => $callable arguments, for example:
	 *
	 *     'escape' => array( 'label' => 'wp_kses_post' )
	 *
	 * @var array
	 */
	public $escape = array();

	/**
	 * If $this->limit > 1, which element in sequence are we currently rendering?
	 *
	 * @var int
	 */
	protected $seq = 0;

	/**
	 * If $is_proto is true, we're rendering the prototype element for a field
	 * that can have infinite instances.
	 *
	 * @var bool
	 */
	protected $is_proto = false;

	/**
	 * Parent element, if applicable. Would be a Fieldmanager_Group unless
	 * third-party plugins support this.
	 *
	 * @var Fieldmanager_Field
	 */
	protected $parent = null;

	/**
	 * Render this element in a tab?
	 *
	 * @todo Add extra wrapper info rather than this specific.
	 *
	 * @var bool
	 */
	protected $is_tab = false;

	/**
	 * Have we added this field as a meta box yet?
	 *
	 * @var bool
	 */
	private $meta_box_actions_added = false;

	/**
	 * Whether or not this field is present on the attachment edit screen.
	 *
	 * @var bool
	 */
	public $is_attachment = false;

	/**
	 * The global sequence of elements.
	 *
	 * @var int Global Sequence
	 */
	private static $global_seq = 0;

	/**
	 * Generate HTML for the form element itself. Generally should be just one
	 * tag, no wrappers.
	 *
	 * @param mixed $value The value of the element.
	 * @return string HTML for the element.
	 */
	public function form_element( $value ) {
		if ( ! $this->template ) {
			$tpl_slug       = strtolower( str_replace( 'Fieldmanager_', '', get_class( $this ) ) );
			$this->template = fieldmanager_get_template( $tpl_slug );
		}
		ob_start();
		include $this->template;
		return ob_get_clean();
	}

	/**
	 * Superclass constructor, just populates options and sanity-checks common elements.
	 * It might also die, but only helpfully, to catch errors in development.
	 *
	 * @param string $label   Title of form field.
	 * @param array  $options With keys matching vars of the field in use.
	 */
	public function __construct( $label = '', $options = array() ) {
		$this->set_options( $label, $options );

		// A post can only have one parent, so if this saves to post_parent and
		// it's repeatable, we're doing it wrong.
		if ( $this->datasource && ! empty( $this->datasource->save_to_post_parent ) && $this->is_repeatable() ) {
			_doing_it_wrong( 'Fieldmanager_Datasource_Post::$save_to_post_parent', esc_html__( 'A post can only have one parent, therefore you cannot store to post_parent in repeatable fields.', 'fieldmanager' ), '1.0.0' );
			$this->datasource->save_to_post_parent      = false;
			$this->datasource->only_save_to_post_parent = false;
		}

		// Only enqueue base assets once, and only when we have a field.
		if ( ! self::$enqueued_base_assets ) {
			fm_add_script( 'fieldmanager_script', 'js/fieldmanager.js', array( 'fm_loader', 'jquery', 'jquery-ui-sortable' ), FM_VERSION, false, 'fm' );
			fm_add_style( 'fieldmanager_style', 'css/fieldmanager.css', array(), FM_VERSION );
			self::$enqueued_base_assets = true;
		}
	}

	/**
	 * Build options into properties and throw errors if developers add an unsupported opt.
	 *
	 * @throws FM_Developer_Exception If an option is set but not defined in this class or the child class.
	 * @throws FM_Developer_Exception If an option is set but not public.
	 *
	 * @param string $label   Title of form field.
	 * @param array  $options With keys matching vars of the field in use.
	 */
	public function set_options( $label, $options ) {
		if ( is_array( $label ) ) {
			$options = $label;
		} else {
			$options['label'] = $label;
		}

		// Get all the public properties for this object.
		$properties = call_user_func( 'get_object_vars', $this );

		foreach ( $options as $key => $value ) {
			if ( array_key_exists( $key, $properties ) ) {
				$this->$key = $value;
			} elseif ( self::$debug ) {
				$message = sprintf(
					/* translators: 1: option key, 2: field class, 3: field name */
					__( 'You attempted to set a property "%1$s" that is nonexistant or invalid for an instance of "%2$s" named "%3$s".', 'fieldmanager' ),
					$key,
					get_class( $this ),
					! empty( $options['name'] ) ? $options['name'] : 'NULL'
				);
				throw new FM_Developer_Exception( esc_html( $message ) );
			}
		}

		// If this is a single field with a limit of 1, serialize_data has no impact.
		if ( ! $this->serialize_data && ! $this->is_group() && 1 == $this->limit ) {
			$this->serialize_data = true;
		}

		// Cannot use serialize_data => false with index => true.
		if ( ! $this->serialize_data && $this->index ) {
			throw new FM_Developer_Exception( esc_html__( 'You cannot use `"serialize_data" => false` with `"index" => true`', 'fieldmanager' ) );
		}
	}

	/**
	 * Generates all markup needed for all form elements in this field.
	 * Could be called directly by a plugin or theme.
	 *
	 * @since 1.3.0 Added the 'fm-display-if' class for fields using display-if.
	 *
	 * @param mixed|mixed[]|null $values The current value or values for this
	 *                                   element, or an associative array of
	 *                                   the values of this element's children.
	 *                                   Can be null if no value exists.
	 * @return string HTML for all form elements.
	 */
	public function element_markup( $values = array() ) {
		$values = $this->preload_alter_values( $values );
		if ( 1 != $this->limit ) {
			// count() generates a warning when passed non-countable values in PHP 7.2.
			if ( is_scalar( $values ) ) {
				$count_values = 1;
			} elseif ( ! is_array( $values ) && ! ( $values instanceof \Countable ) ) {
				$count_values = 0;
			} else {
				$count_values = count( $values );
			}

			$max = max( $this->minimum_count, $count_values + $this->extra_elements );

			// Ensure that we don't display more fields than we can save.
			if ( $this->limit > 1 && $max > $this->limit ) {
				$max = $this->limit;
			}
		} else {
			$max = 1;
		}

		$classes          = array( 'fm-wrapper', 'fm-' . $this->name . '-wrapper' );
		$fm_wrapper_attrs = array();
		if ( $this->sortable ) {
			$classes[] = 'fmjs-sortable';
		}
		$classes = array_merge( $classes, $this->get_extra_element_classes() );

		$out = '';

		/*
		 * If this element is part of tabbed output, there needs to be a wrapper
		 * to contain the tab content.
		 */
		if ( $this->is_tab ) {
			$out .= sprintf(
				'<div id="%s-tab" class="wp-tabs-panel"%s>',
				esc_attr( $this->get_element_id() ),
				( $this->parent->child_count > 0 ) ? ' style="display: none"' : ''
			);
		}

		// Find the array position of the "counter" (e.g. in element[0], [0] is the counter, thus the position is 1).
		$html_array_position = 0; // default is no counter; i.e. if $this->limit = 0.
		if ( 1 != $this->limit ) {
			$html_array_position = 1; // base situation is formname[0], so the counter is in position 1.
			if ( $this->parent ) {
				$parent = $this->parent;
				while ( $parent ) {
					$html_array_position++; // one more for having a parent (e.g. parent[this][0]).
					if ( 1 != $parent->limit ) { // and another for the parent having multiple (e.g. parent[0][this][0]).
						$html_array_position++;
					}
					$parent = $parent->parent; // parent's parent; root element has null parent which breaks while loop.
				}
			}
		}

		// Checks to see if element has display_if data values, and inserts the data attributes if it does.
		if ( isset( $this->display_if ) && ! empty( $this->display_if ) ) {
			$classes[] = 'fm-display-if';

			// For backwards compatibility.
			$classes[] = 'display-if';

			$fm_wrapper_attrs['data-display-src']   = $this->display_if['src'];
			$fm_wrapper_attrs['data-display-value'] = $this->display_if['value'];
		}
		$fm_wrapper_attr_string = '';
		foreach ( $fm_wrapper_attrs as $attr => $val ) {
			$fm_wrapper_attr_string .= sprintf( '%s="%s" ', sanitize_key( $attr ), esc_attr( $val ) );
		}
		$out .= sprintf(
			'<div class="%s" data-fm-array-position="%d" %s>',
			esc_attr( implode( ' ', $classes ) ),
			absint( $html_array_position ),
			$fm_wrapper_attr_string
		);

		// For lists of items where $one_label_per_item = False, the label should go before the elements.
		if ( ! empty( $this->label ) && ! $this->one_label_per_item ) {
			$out .= $this->get_element_label( array( 'fm-label-for-list' ) );
		}

		/**
		 * Filters field markup before adding markup for its form elements.
		 *
		 * @since 0.1.0
		 * @since 1.0.0 The `$values` parameter was added.
		 *
		 * @param string             $out    Field markup.
		 * @param Fieldmanager_Field $this   Field instance.
		 * @param mixed|mixed[]|null $values Current element value or values, if any.
		 */
		$out = apply_filters( 'fm_element_markup_start', $out, $this, $values );

		/**
		 * Filters a specific field's markup before adding markup for its form elements.
		 *
		 * The dynamic portion of the hook name, `$this->name`, refers to the field's `$name` property.
		 *
		 * @since 1.2.0
		 *
		 * @param string             $out    Field markup.
		 * @param Fieldmanager_Field $this   Field instance.
		 * @param mixed|mixed[]|null $values Current element value or values, if any.
		 */
		$out = apply_filters( "fm_element_markup_start_{$this->name}", $out, $this, $values );

		if ( ( 0 == $this->limit || ( $this->limit > 1 && $this->limit > $this->minimum_count ) ) && 'top' == $this->add_more_position ) {
			$out .= $this->add_another();
		}

		if ( 1 != $this->limit ) {
			$out .= $this->single_element_markup( null, true );
		}
		for ( $i = 0; $i < $max; $i++ ) {
			$this->seq = $i;
			if ( 1 == $this->limit ) {
				$value = $values;
			} else {
				$value = isset( $values[ $i ] ) ? $values[ $i ] : null;
			}
			if (1 === $this->limit || !empty($value)) {
				$out .= $this->single_element_markup($value);
			}
		}
		if ( ( 0 == $this->limit || ( $this->limit > 1 && $this->limit > $this->minimum_count ) ) && 'bottom' == $this->add_more_position ) {
			$out .= $this->add_another();
		}

		/**
		 * Filters field markup after adding markup for its form elements.
		 *
		 * @since 0.1.0
		 * @since 1.0.0 The `$values` parameter was added.
		 *
		 * @param string             $out    Field markup.
		 * @param Fieldmanager_Field $this   Field instance.
		 * @param mixed|mixed[]|null $values Current element value or values, if any.
		 */
		$out = apply_filters( 'fm_element_markup_end', $out, $this, $values );

		/**
		 * Filters a specific field's markup after adding markup for its form elements.
		 *
		 * The dynamic portion of the hook name, `$this->name`, refers to the field's `$name` property.
		 *
		 * @since 1.2.0
		 *
		 * @param string             $out    Field markup.
		 * @param Fieldmanager_Field $this   Field instance.
		 * @param mixed|mixed[]|null $values Current element value or values, if any.
		 */
		$out = apply_filters( "fm_element_markup_end_{$this->name}", $out, $this, $values );

		$out .= '</div>';

		// Close the tab wrapper if one exists.
		if ( $this->is_tab ) {
			$out .= '</div>';
		}

		return $out;
	}

	/**
	 * Generate wrappers and labels for one form element. Is called by
	 * `element_markup()`, calls `form_element()`.
	 *
	 * @see Fieldmanager_Field::element_markup()
	 * @see Fieldmanager_Field::form_element()
	 *
	 * @param mixed|mixed[]|null $value    Single element value, if any.
	 * @param bool               $is_proto True to generate a prototype element
	 *                                     for Javascript.
	 * @return string HTML for a single form element.
	 */
	public function single_element_markup( $value = null, $is_proto = false ) {
		if ( $is_proto ) {
			$this->is_proto = true;
		}
		$out     = '';
		$classes = array( 'fm-item', 'fm-' . $this->name );

		self::$global_seq++;

		// Drop the fm-group class to hide inner box display if no label is set.
		if ( ! ( $this->is_group() && ( ! isset( $this->label ) || empty( $this->label ) ) ) ) {
			$classes[] = 'fm-' . $this->field_class;
		}

		// Check if the required attribute is set. If so, add the class.
		if ( $this->required ) {
			$classes[] = 'form-required';
		}

		if ( ! $this->is_group() && ! $this->is_tab ) {
			$classes[] = 'fm-field';
		}

		if ( ! $this->is_group() && $this->sortable ) {
			$classes[] = 'fm-sortable-field';
			if ( ( ! $this->one_label_per_item || empty( $this->label ) ) && empty( $this->description ) ) {
				$classes[] = 'fm-no-labels';
			}
		}

		if ( $is_proto ) {
			$classes[] = 'fmjs-proto';
		}

		if ( $this->is_group() && 'vertical' === $this->tabbed ) {
			$classes[] = 'fm-tabbed-vertical';
		}

		$classes = apply_filters( 'fm_element_classes', $classes, $this->name, $this );

		$out .= sprintf( '<div class="%s">', esc_attr( implode( ' ', $classes ) ) );

		$label              = $this->get_element_label();
		$render_label_after = false;

		/*
		 * Hide the label if it is empty or if this is a tab since it would duplicate
		 * the title from the tab label.
		 */
		if ( ! empty( $this->label ) && ! $this->is_tab && $this->one_label_per_item ) {
			if ( 1 != $this->limit ) {
				$out .= $this->wrap_with_multi_tools( $label, array( 'fmjs-removable-label' ) );
			} elseif ( ! $this->label_after_element ) {
				$out .= $label;
			} else {
				$render_label_after = true;
			}
		}

		if ( ! empty( $this->description ) && ! $this->description_after_element && ! $this->is_group() ) {
			$out .= sprintf( '<div class="fm-item-description">%s</div>', $this->escape( 'description' ) );
		}

		if ( null === $value && null !== $this->default_value ) {
			$value = $this->default_value;
		}

		$form_element = $this->form_element( $value );

		if ( 1 != $this->limit && ( ! $this->one_label_per_item || empty( $this->label ) ) ) {
			$out .= $this->wrap_with_multi_tools( $form_element );
		} else {
			$out .= $form_element;
		}

		if ( $render_label_after ) {
			$out .= $label;
		}

		if ( ! empty( $this->description ) && $this->description_after_element && ! $this->is_group() ) {
			$out .= sprintf( '<div class="fm-item-description">%s</div>', $this->escape( 'description' ) );
		}

		$out .= '</div>';

		if ( $is_proto ) {
			$this->is_proto = false;
		}
		return $out;
	}

	/**
	 * Alter values before rendering.
	 *
	 * @param mixed|mixed[]|null $values The current value or values for this element, if any.
	 * @return mixed|mixed[]|null The altered value.
	 */
	public function preload_alter_values( $values ) {
		return apply_filters( 'fm_preload_alter_values', $values, $this );
	}

	/**
	 * Wrap a chunk of HTML with "remove" and "move" buttons if applicable.
	 *
	 * @param  string $html    HTML to wrap.
	 * @param  array  $classes An array of classes.
	 * @return string Wrapped HTML.
	 */
	public function wrap_with_multi_tools( $html, $classes = array() ) {
		$classes[] = 'fmjs-removable';
		$out       = sprintf( '<div class="%s">', implode( ' ', $classes ) );
		$handle = '';
		if ( $this->sortable ) {
			if ( ( $this->one_label_per_item || ! empty( $this->label ) ) && ! in_array( 'fmjs-removable-label', $classes, true ) && empty( $this->description ) ) {
				$classes[] = 'fmjs-removable-sort';
			}
			$handle = $this->get_sort_handle();
		}
		$out .= $handle;
		$out .= '<div class="fmjs-removable-element">';
		$out .= $html;
		$out .= '</div>';

		if ( 0 == $this->limit || $this->limit > $this->minimum_count ) {
			$out .= $this->get_remove_handle();
		}

		$out .= '</div>';
		return $out;
	}

	/**
	 * Get HTML form name (e.g. questions[answer]).
	 *
	 * @param string $multiple Multiple fields.
	 * @return string Form name.
	 */
	public function get_form_name( $multiple = '' ) {
		$tree = $this->get_form_tree();
		$name = '';
		foreach ( $tree as $level => $branch ) {
			if ( 0 == $level ) {
				$name .= $branch->name;
			} else {
				$name .= '[' . $branch->name . ']';
			}
			if ( 1 != $branch->limit ) {
				$name .= '[' . $branch->get_seq() . ']';
			}
		}
		return $name . $multiple;
	}

	/**
	 * Recursively build path to this element, e.g. [grandparent, parent, this].
	 *
	 * @return array $tree The form tree of parents.
	 */
	public function get_form_tree() {
		$tree = array();
		if ( $this->parent ) {
			$tree = $this->parent->get_form_tree();
		}
		$tree[] = $this;
		return $tree;
	}

	/**
	 * Get the ID for the form element itself, uses $this->seq (e.g. which position is this element in).
	 * Relying on the element's ID for anything isn't a great idea since it can be rewritten in JS.
	 *
	 * @return string ID for use in a form element.
	 */
	public function get_element_id() {
		$el       = $this;
		$id_slugs = array();
		while ( $el ) {
			$slug = $el->is_proto ? 'proto' : $el->seq;
			array_unshift( $id_slugs, $el->name . '-' . $slug );
			$el = $el->parent;
		}
		return 'fm-' . implode( '-', $id_slugs );
	}

	/**
	 * Get the storage key for the form element.
	 *
	 * @return string
	 */
	public function get_element_key() {
		$el  = $this;
		$key = $el->name;
		while ( $el = $el->parent ) {
			if ( $el->add_to_prefix ) {
				$key = "{$el->name}_{$key}";
			}
		}
		return $key;
	}

	/**
	 * Is this element repeatable or does it have a repeatable ancestor?
	 *
	 * @return bool True if yes, false if no.
	 */
	public function is_repeatable() {
		if ( 1 != $this->limit ) {
			return true;
		} elseif ( $this->parent ) {
			return $this->parent->is_repeatable();
		}
		return false;
	}

	/**
	 * Is the current field a group?
	 *
	 * @return bool True if yes, false if no.
	 */
	public function is_group() {
		return $this instanceof \Fieldmanager_Group;
	}

	/**
	 * Presaves all elements in what could be a set of them, dispatches to $this->presave().
	 *
	 * @throws FM_Exception General FM exception.
	 *
	 * @param  mixed $values         The new values.
	 * @param  mixed $current_values The current values.
	 * @return mixed Sanitized values.
	 */
	public function presave_all( $values, $current_values ) {
		if ( 1 == $this->limit && empty( $this->multiple ) ) {
			$values = $this->presave_alter_values( array( $values ), array( $current_values ) );
			if ( ! empty( $values ) ) {
				$value = $this->presave( $values[0], $current_values );
			} else {
				$value = $values;
			}
			if ( ! empty( $this->index ) ) {
				$this->save_index( array( $value ), array( $current_values ) );
			}
			return $value;
		}

		// If $this->limit != 1, and $values is not an array, that'd just be wrong, and possibly an attack, so...
		if ( 1 != $this->limit && ! is_array( $values ) ) {

			// EXCEPT maybe this is a request to remove indices.
			if ( ! empty( $this->index ) && null === $values && ! empty( $current_values ) && is_array( $current_values ) ) {
				$this->save_index( null, $current_values );
				return;
			}

			// OR doing cron, where we should just do nothing if there are no values to process.
			// OR we've now accumulated some cases where a null value instead of an empty array is an acceptable case to
			// just bail out instead of throwing an error. If it WAS an attack, bailing should prevent damage.
			if ( null === $values || ( defined( 'DOING_CRON' ) && DOING_CRON && empty( $values ) ) ) {
				return;
			}

			/* translators: %d: field limit */
			$this->_unauthorized_access( sprintf( __( '$values should be an array because $limit is %d', 'fieldmanager' ), $this->limit ) );
		}

		if ( empty( $values ) ) {
			$values = array();
		}

		// Remove the proto.
		if ( isset( $values['proto'] ) ) {
			unset( $values['proto'] );
		}

		// If $this->limit is not 0 or 1, and $values has more than $limit, that could also be an attack...
		if ( $this->limit > 1 && count( $values ) > $this->limit ) {
			$this->_unauthorized_access(
				/* translators: 1: value count, 2: field limit */
				sprintf( __( 'submitted %1$d values against a limit of %2$d', 'fieldmanager' ), count( $values ), $this->limit )
			);
		}

		// Check for non-numeric keys.
		$keys = array_keys( $values );
		foreach ( $keys as $key ) {
			if ( ! is_numeric( $key ) ) {
				throw new FM_Exception( esc_html__( 'Use of a non-numeric key suggests that something is wrong with this group.', 'fieldmanager' ) );
			}
		}

		// Condense the array to account for middle items removed.
		$values = array_values( $values );

		$values = $this->presave_alter_values( $values, $current_values );

		// If this update results in fewer children, trigger presave on empty children to make up the difference.
		if ( ! empty( $current_values ) && is_array( $current_values ) ) {
			foreach ( array_diff( array_keys( $current_values ), array_keys( $values ) ) as $i ) {
				$values[ $i ] = null;
			}
		}

		foreach ( $values as $i => $value ) {
			$values[ $i ] = $this->presave( $value, empty( $current_values[ $i ] ) ? array() : $current_values[ $i ] );
		}

		if ( ! $this->save_empty ) {
			// Remove empty values.
			$values = array_filter(
				$values,
				function( $value ) {
					if ( is_array( $value ) ) {
						return ! empty( $value );
					} else {
						return strlen( $value );
					}
				}
			);
			// reindex the array after removing empty values.
			$values = array_values( $values );
		}

		if ( ! empty( $this->index ) ) {
			$this->save_index( $values, $current_values );
		}

		return $values;
	}

	/**
	 * Optionally save fields to a separate postmeta index for easy lookup with WP_Query
	 * Handles internal arrays (e.g. for fieldmanager-options).
	 * Is called multiple times for multi-fields (e.g. limit => 0).
	 *
	 * @todo make this a context method.
	 *
	 * @param  array $values The new values.
	 * @param  array $current_values The current values.
	 */
	protected function save_index( $values, $current_values ) {
		if ( 'post' != $this->data_type || empty( $this->data_id ) ) {
			return;
		}
		// Must delete current values specifically, then add new ones, to support a scenario where the
		// same field in repeating groups with limit = 1 is going to create more than one entry here, and
		// if we called update_post_meta() we would overwrite the index with each new group.
		if ( ! empty( $current_values ) && is_array( $current_values ) ) {
			foreach ( $current_values as $old_value ) {
				if ( ! is_array( $old_value ) ) {
					$old_value = array( $old_value );
				}
				foreach ( $old_value as $value ) {
					$value = $this->process_index_value( $value );
					if ( empty( $value ) ) {
						// false or null should be saved as 0 to prevent duplicates.
						$value = 0;
					}
					delete_post_meta( $this->data_id, $this->index, $value );
				}
			}
		}
		// add new values.
		if ( ! empty( $values ) && is_array( $values ) ) {
			foreach ( $values as $new_value ) {
				if ( ! is_array( $new_value ) ) {
					$new_value = array( $new_value );
				}
				foreach ( $new_value as $value ) {
					$value = $this->process_index_value( $value );
					if ( isset( $value ) ) {
						if ( empty( $value ) ) {
							// false or null should be saved as 0 to prevent duplicates.
							$value = 0;
						}
						add_post_meta( $this->data_id, $this->index, $value );
					}
				}
			}
		}
	}

	/**
	 * Hook to alter handling of an individual index value, which may make sense
	 * to change per field type.
	 *
	 * @param mixed $value The current value.
	 * @return mixed The processed value.
	 */
	protected function process_index_value( $value ) {
		if ( is_callable( $this->index_filter ) ) {
			$value = call_user_func( $this->index_filter, $value );
		}

		return apply_filters( 'fm_process_index_value', $value, $this );
	}

	/**
	 * Hook to alter or respond to all the values of a particular element.
	 *
	 * @param  array $values         The new values.
	 * @param  array $current_values The current values.
	 * @return array The filtered values.
	 */
	protected function presave_alter_values( $values, $current_values = array() ) {
		/**
		 * Filters the new field value prior to saving.
		 *
		 * @param mixed              $values         New field value.
		 * @param Fieldmanager_Field $this           Field object.
		 * @param mixed              $current_values Current field value.
		 */
		return apply_filters( 'fm_presave_alter_values', $values, $this, $current_values );
	}

	/**
	 * Presave function, which handles sanitization and validation.
	 *
	 * @param  mixed $value         If a single field expects to manage an array,
	 *                              it must override presave().
	 * @param  array $current_value The current values.
	 * @return array The sanitized values.
	 */
	public function presave( $value, $current_value = array() ) {
		// It's possible that some elements (Grid is one) would be arrays at
		// this point, but those elements must override this function. Let's
		// make sure we're dealing with one value here.
		if ( is_array( $value ) ) {
			$this->_unauthorized_access( __( 'presave() in the base class should not get arrays, but did.', 'fieldmanager' ) );
		}
		foreach ( $this->validate as $func ) {
			if ( ! call_user_func( $func, $value ) ) {
				$this->_failed_validation(
					sprintf(
						/* translators: 1: field value, 2: field label */
						__( 'Input "%1$s" is not valid for field "%2$s" ', 'fieldmanager' ),
						(string) $value,
						$this->label
					)
				);
			}
		}
		return call_user_func( $this->sanitize, $value );
	}

	/**
	 * Generates an HTML attribute string based on the value of $this->attributes.
	 *
	 * @see Fieldmanager_Field::$attributes
	 * @return string attributes ready to insert into an HTML tag.
	 */
	public function get_element_attributes() {
		$attr_str = array();
		foreach ( $this->attributes as $attr => $val ) {
			if ( true === $val ) {
				$attr_str[] = sanitize_key( $attr );
			} else {
				$attr_str[] = sprintf( '%s="%s"', sanitize_key( $attr ), esc_attr( $val ) );
			}
		}
		return implode( ' ', $attr_str );
	}

	/**
	 * Get an HTML label for this element.
	 *
	 * @param  array $classes Extra CSS classes.
	 * @return string HTML label.
	 */
	public function get_element_label( $classes = array() ) {
		$classes[] = 'fm-label';
		$classes[] = 'fm-label-' . $this->name;
		if ( $this->inline_label ) {
			$this->label_element = 'span';
			$classes[]           = 'fm-label-inline';
		}
		if ( $this->label_after_element ) {
			$classes[] = 'fm-label-after';
		}
		return sprintf(
			'<%s class="%s"><label for="%s">%s</label></%s>',
			sanitize_key( $this->label_element ),
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $this->get_element_id( $this->get_seq() ) ),
			$this->escape( 'label' ),
			sanitize_key( $this->label_element )
		);
	}

	/**
	 * Generates HTML for the "Add Another" button.
	 *
	 * @return string Button HTML.
	 */
	public function add_another() {
		$classes = array( 'fm-add-another', 'fm-' . $this->name . '-add-another', 'button-secondary' );
		if ( empty( $this->add_more_label ) ) {
			$this->add_more_label = $this->is_group() ? __( 'Add group', 'fieldmanager' ) : __( 'Add field', 'fieldmanager' );
		}

		$out  = '<div class="fm-add-another-wrapper">';
		$out .= sprintf(
			'<input type="button" class="%s" value="%s" name="%s" data-related-element="%s" data-add-more-position="%s" data-limit="%d" />',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $this->add_more_label ),
			esc_attr( 'fm_add_another_' . $this->name ),
			esc_attr( $this->name ),
			esc_attr( $this->add_more_position ),
			intval( $this->limit )
		);
		$out .= '</div>';
		return $out;
	}

	/**
	 * Return HTML for the sort handle (multi-tools); a separate function to override.
	 *
	 * @return string
	 */
	public function get_sort_handle() {
		return sprintf( '<div class="fmjs-drag fmjs-drag-icon"><span class="screen-reader-text">%s</span></div>', esc_html__( 'Move', 'fieldmanager' ) );
	}

	/**
	 * Return HTML for the remove handle (multi-tools); a separate function to override.
	 *
	 * @return string
	 */
	public function get_remove_handle() {
		return sprintf( '<a href="#" class="fmjs-remove" title="%1$s"><span class="screen-reader-text">%1$s</span></a>', esc_attr__( 'Remove', 'fieldmanager' ) );
	}

	/**
	 * Return HTML for the collapse handle (multi-tools); a separate function to override.
	 *
	 * @return string
	 */
	public function get_collapse_handle() {
		return '<span class="toggle-indicator" aria-hidden="true"></span>';
	}

	/**
	 * Return extra element classes; overriden by some fields.
	 *
	 * @return array
	 */
	public function get_extra_element_classes() {
		return array();
	}

	/**
	 * Add a form on user pages.
	 *
	 * @param string $title The form title.
	 */
	public function add_user_form( $title = '' ) {
		$this->require_base();
		return new Fieldmanager_Context_User( $title, $this );
	}

	/**
	 * Add a form on a frontend page.
	 *
	 * @see Fieldmanager_Context_Form
	 *
	 * @param string $uniqid A unique identifier for this form.
	 */
	public function add_page_form( $uniqid ) {
		_deprecated_function( __METHOD__, '1.2.0' );

		$this->require_base();
		return new Fieldmanager_Context_Page( $uniqid, $this );
	}

	/**
	 * Add a form on a term add/edit page
	 *
	 * @deprecated 1.0.0-beta.3 Replaced by {@see Fieldmanager_Field::add_term_meta_box()}.
	 *
	 * @see Fieldmanager_Context_Term
	 *
	 * @param string       $title        The title of the form.
	 * @param string|array $taxonomies   The taxonomies on which to display this form.
	 * @param bool         $show_on_add  Whether or not to show the fields on the add term form.
	 * @param bool         $show_on_edit Whether or not to show the fields on the edit term form.
	 * @param int          $parent       Only show this field on child terms of this parent term ID.
	 */
	public function add_term_form( $title, $taxonomies, $show_on_add = true, $show_on_edit = true, $parent = '' ) {
		$this->require_base();
		return new Fieldmanager_Context_Term(
			array(
				'title'        => $title,
				'taxonomies'   => $taxonomies,
				'show_on_add'  => $show_on_add,
				'show_on_edit' => $show_on_edit,
				'parent'       => $parent,
				// Use the deprecated FM Term Meta instead of core's term meta.
				'use_fm_meta'  => true,
				'field'        => $this,
			)
		);
	}

	/**
	 * Add fields to the term add/edit page
	 *
	 * @see Fieldmanager_Context_Term
	 *
	 * @param string       $title        The title of the form.
	 * @param string|array $taxonomies   The taxonomies on which to display this form.
	 * @param bool         $show_on_add  Whether or not to show the fields on the add term form.
	 * @param bool         $show_on_edit Whether or not to show the fields on the edit term form.
	 * @param int          $parent       Only show this field on child terms of this parent term ID.
	 */
	public function add_term_meta_box( $title, $taxonomies, $show_on_add = true, $show_on_edit = true, $parent = '' ) {
		// Bail if term meta table is not installed.
		if ( get_option( 'db_version' ) < 34370 ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'This method requires WordPress 4.4 or above', 'fieldmanager' ), 'Fieldmanager-1.0.0-beta.3' );
			return false;
		}

		$this->require_base();
		return new Fieldmanager_Context_Term(
			array(
				'title'        => $title,
				'taxonomies'   => $taxonomies,
				'show_on_add'  => $show_on_add,
				'show_on_edit' => $show_on_edit,
				'parent'       => $parent,
				'use_fm_meta'  => false,
				'field'        => $this,
			)
		);
	}

	/**
	 * Add this field as a metabox to a post type.
	 *
	 * @see Fieldmanager_Context_Post
	 *
	 * @param string       $title      The title of the form.
	 * @param string|array $post_types The post type(s).
	 * @param string       $context    The context for the meta box.
	 * @param string       $priority   The priority of the meta box.
	 */
	public function add_meta_box( $title, $post_types, $context = 'normal', $priority = 'default' ) {
		$this->require_base();
		// Check if any default meta boxes need to be removed for this field.
		$this->add_meta_boxes_to_remove( $this->meta_boxes_to_remove );
		if ( in_array( 'attachment', (array) $post_types ) ) {
			$this->is_attachment = true;
		}
		return new Fieldmanager_Context_Post( $title, $post_types, $context, $priority, $this );
	}

	/**
	 * Add this field to a post type's quick edit box.
	 *
	 * @see Fieldmanager_Context_Quickedit
	 *
	 * @param string       $title                   The title of the form.
	 * @param string|array $post_types              The post type(s).
	 * @param callable     $column_display_callback The display callback.
	 * @param string       $column_title            The column title.
	 */
	public function add_quickedit_box( $title, $post_types, $column_display_callback, $column_title = '' ) {
		$this->require_base();
		return new Fieldmanager_Context_QuickEdit( $title, $post_types, $column_display_callback, $column_title, $this );
	}

	/**
	 * Add this group to an nav menu.
	 */
	public function add_nav_menu_fields() {
		$this->require_base();
		return new Fieldmanager_Context_MenuItem( $this );
	}

	/**
	 * Add this group to an options page.
	 *
	 * @param string $parent_slug The parent slug for the menu.
	 * @param string $page_title  The page title.
	 * @param string $menu_title  The menu title.
	 * @param string $capability  The page capability access.
	 * @param string $menu_slug   The menu slug.
	 */
	public function add_submenu_page( $parent_slug, $page_title, $menu_title = null, $capability = 'manage_options', $menu_slug = null ) {
		$this->require_base();
		return new Fieldmanager_Context_Submenu( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $this );
	}

	/**
	 * Activate this group in an already-added submenu page.
	 */
	public function activate_submenu_page() {
		$this->require_base();
		$submenus       = _fieldmanager_registry( 'submenus' );
		$s              = $submenus[ $this->name ];
		$active_submenu = new Fieldmanager_Context_Submenu( $s[0], $s[1], $s[2], $s[3], $s[4], $this, true );
		_fieldmanager_registry( 'active_submenu', $active_submenu );
	}

	/**
	 * Check if we can require the base.
	 *
	 * @throws FM_Developer_Exception Cannot use in subgroup.
	 */
	private function require_base() {
		if ( ! empty( $this->parent ) ) {
			throw new FM_Developer_Exception( esc_html__( 'You cannot use this method on a subgroup', 'fieldmanager' ) );
		}
	}

	/**
	 * Die violently. If self::$debug is true, throw an exception.
	 *
	 * @throws FM_Exception Unauthorized debug message.
	 *
	 * @param string $debug_message The debug message.
	 */
	public function _unauthorized_access( $debug_message = '' ) {
		if ( self::$debug ) {
			throw new FM_Exception( esc_html( $debug_message ) );
		} else {
			wp_die( esc_html__( "Sorry, you're not supposed to do that...", 'fieldmanager' ) );
		}
	}

	/**
	 * Fail validation. If self::$debug is true, throw an exception.
	 *
	 * @throws FM_Validation_Exception Failed Validation.
	 *
	 * @param string $debug_message The debug message.
	 */
	protected function _failed_validation( $debug_message = '' ) {
		if ( self::$debug ) {
			throw new FM_Validation_Exception( $debug_message );
		} else {
			wp_die(
				esc_html(
					$debug_message . "\n\n" .
					__( "You may be able to use your browser's back button to resolve this error.", 'fieldmanager' )
				)
			);
		}
	}

	/**
	 * Die violently. If self::$debug is true, throw an exception.
	 *
	 * @throws FM_Exception Invalid definition.
	 *
	 * @param string $debug_message The debug message.
	 */
	public function _invalid_definition( $debug_message = '' ) {
		if ( self::$debug ) {
			throw new FM_Exception( esc_html( $debug_message ) );
		} else {
			wp_die( esc_html__( "Sorry, you've created an invalid field definition. Please check your code and try again.", 'fieldmanager' ) );
		}
	}

	/**
	 * In a multiple element set, return the index of the current element we're rendering.
	 *
	 * @return int The Proto or sequence.
	 */
	protected function get_seq() {
		return $this->has_proto() ? 'proto' : $this->seq;
	}

	/**
	 * Are we in the middle of generating a prototype element for repeatable fields?
	 *
	 * @return bool
	 */
	protected function has_proto() {
		if ( $this->is_proto ) {
			return true;
		}
		if ( $this->parent ) {
			return $this->parent->has_proto();
		}
		return false;
	}

	/**
	 * Helper function to add to the list of meta boxes to remove. This will be
	 * defined in child classes that require this functionality.
	 *
	 * @param array $meta_boxes_to_remove Current list of meta boxes to remove.
	 */
	protected function add_meta_boxes_to_remove( &$meta_boxes_to_remove ) {}

	/**
	 * Escape a field based on the function in the escape argument.
	 *
	 * @param  string $field   The field to escape.
	 * @param  string $default The default function to use to escape the field.
	 *                         Optional. Defaults to `esc_html()`.
	 * @return string The escaped field.
	 */
	public function escape( $field, $default = 'esc_html' ) {
		if ( isset( $this->escape[ $field ] ) && is_callable( $this->escape[ $field ] ) ) {
			return call_user_func( $this->escape[ $field ], $this->$field );
		} else {
			return call_user_func( $default, $this->$field );
		}
	}
}
