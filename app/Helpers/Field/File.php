<?php
namespace SmartPostAggregator\Helpers\Field;

use SmartPostAggregator\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * File Field Class
 */
class File extends Text {

	public function __construct( $config = array() ) {
		parent::__construct( $config );
		$this->set_type( 'file' );
		$this->set_value();
	}
}
