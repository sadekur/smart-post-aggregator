<?php
namespace SmartPostAggregantor\Helpers\Field;

use SmartPostAggregantor\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Radio Field Class
 */
class Radio extends Multicheck {
	protected $option_type = 'radio';
}
