<?php
namespace SmartPostAggregator\Helpers\Field;

use SmartPostAggregator\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Color Field Class
 */
class Color extends Text {

	public function __construct( $config = array() ) {
		parent::__construct( $config );
		$this->set_type( 'color' );
	}
}
