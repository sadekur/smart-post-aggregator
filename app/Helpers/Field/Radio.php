<?php
namespace SmartPostAggregator\Helpers\Field;

use SmartPostAggregator\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Radio Field Class
 */
class Radio extends Multicheck {
	protected $option_type = 'radio';
}
