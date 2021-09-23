<?php
/**
 * Class file for Fieldmanager_NumberField
 *
 * @package Fieldmanager
 */

/**
 * NumberField Field.
 */
class Fieldmanager_NumberField extends Fieldmanager_Field {
	/**
	 * NumberField content string.
	 *
	 * @var string
	 */
	public $field_class = 'text';

	/**
	 * Setup NumberField Template and Type.
	 *
	 * @param string $label   Field label.
	 * @param array  $options The field options.
	 */
	public function __construct( $label = '', $options = array() ) {
		$this->input_type     = 'number';
		parent::__construct( $label, $options );
	}
}
