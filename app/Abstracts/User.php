<?php
namespace SmartPostAggregantor\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract User Class
 */
abstract class User {

	protected $id = 0;

	protected $email = '';

	protected $name = '';

	protected $role = '';

	protected $password;

	public function __construct( $id = null ) {
		if ( $id && $user = get_userdata( $id ) ) {
			$this->id    = $id;
			$this->email = $user->user_email;
			$this->name  = $user->display_name;
			$this->role  = $user->roles[0];
		}
	}

	/**
	 * Get user ID.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get user email.
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Get user name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get user role.
	 *
	 * @return string
	 */
	public function get_role() {
		return $this->role;
	}

	/**
	 * Set user email.
	 *
	 * @param string $email
	 */
	public function set_email( $email ) {
		$this->email = $email;
	}

	/**
	 * Set user name.
	 *
	 * @param string $name
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * Set user role.
	 *
	 * @param string $role
	 */
	public function set_role( $role ) {
		$this->role = $role;
	}

	/**
	 * Save user data.
	 *
	 * @return bool
	 */
	public function save() {
		if ( $this->id ) {
			// Update existing user
			$userdata = array(
				'ID'           => $this->id,
				'user_email'   => $this->email,
				'display_name' => $this->name,
				'role'         => $this->role,
			);

			$user_id = wp_update_user( $userdata );
		} else {
			// Create new user
			$userdata = array(
				'user_email'   => $this->email,
				'user_login'   => $this->email,
				'display_name' => $this->name,
				'role'         => $this->role,
				'user_pass'    => $this->password,
			);

			$user_id = wp_insert_user( $userdata );

			if ( ! is_wp_error( $user_id ) ) {
				$this->id = $user_id;
			}
		}

		return ! is_wp_error( $user_id );
	}

	/**
	 * Delete user.
	 *
	 * @return bool
	 */
	public function delete() {
		require_once ABSPATH . 'wp-admin/includes/user.php';
		return wp_delete_user( $this->id );
	}

	/**
	 * Add user meta data.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return bool
	 */
	public function add_meta( $key, $value ) {
		return add_user_meta( $this->id, $key, $value );
	}

	/**
	 * Get user meta data.
	 *
	 * @param string $key
	 * @param bool   $single
	 * @return mixed
	 */
	public function get_meta( $key, $single = true ) {
		return get_user_meta( $this->id, $key, $single );
	}

	/**
	 * Update user meta data.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return bool
	 */
	public function update_meta( $key, $value ) {
		return update_user_meta( $this->id, $key, $value );
	}

	/**
	 * Delete user meta data.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function delete_meta( $key ) {
		return delete_user_meta( $this->id, $key );
	}

	/**
	 * Create a new user.
	 *
	 * @param array $args
	 * @return bool
	 */
	public function create( $args ) {
		if ( empty( $args['email'] ) || empty( $args['name'] ) ) {
			return false;
		}

		$this->email    = $args['email'];
		$this->name     = $args['name'];
		$this->role     = isset( $args['role'] ) ? $args['role'] : $this->role;
		$this->password = isset( $args['password'] ) ? $args['password'] : wp_generate_password();

		return $this->save();
	}
}
