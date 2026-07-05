<?php
namespace SmartPostAggregator\Helpers\Field;

use SmartPostAggregator\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Email Field Class
 */
class Email extends Text {

	public function __construct( $config = array() ) {
		parent::__construct( $config );
		$this->set_type( 'email' );
	}
}
