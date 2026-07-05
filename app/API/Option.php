<?php
namespace SmartPostAggregantor\API;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregantor\Traits\Rest;

class Option {

	use Rest;


	/**
	 * Get the value of a specified option.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get( $request ) {
		$key = $request->get_param( 'key' );

		if ( empty( $key ) ) {
			return $this->response_error( __( 'Option key is required.', 'smart-post-aggregantor' ) );
		}

		$value = get_option( $key );

		// if ( empty( $value ) ) {
		// return $this->response_error( __( 'Option not found.', 'smart-post-aggregantor' ) );
		// }

		return $this->response_success( $value );
	}

	/**
	 * Update the value of a specified option.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function update( $request ) {
		$key   = $request->get_param( 'key' );
		$value = $request->get_param( 'value' );

		if ( empty( $key ) || empty( $value ) ) {
			return $this->response_error( __( 'Option key and value are required.', 'smart-post-aggregantor' ) );
		}

		$updated = update_option( $key, $value );

		if ( ! $updated ) {
			return $this->response_success( __( 'Option not updated.', 'smart-post-aggregantor' ) );
		}

		return $this->response_success( __( 'Option updated successfully.', 'smart-post-aggregantor' ) );
	}

	/**
	 * Delete the specified option.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete( $request ) {
		$key = $request->get_param( 'key' );

		if ( empty( $key ) ) {
			return $this->response_error( __( 'Option key is required.', 'smart-post-aggregantor' ) );
		}

		$deleted = delete_option( $key );

		if ( ! $deleted ) {
			return $this->response_error( __( 'Failed to delete option.', 'smart-post-aggregantor' ) );
		}

		return $this->response_success( __( 'Option deleted successfully.', 'smart-post-aggregantor' ) );
	}
}
