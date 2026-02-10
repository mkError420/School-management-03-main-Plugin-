<?php
/**
 * Authentication class for student and parent login.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * Authentication class
 */
class Auth {

	/**
	 * Initialize authentication.
	 */
	public function init() {
		// Handle login form submission.
		if ( isset( $_POST['sms_login_submit'] ) ) {
			$this->handle_login();
		}

		// Handle logout.
		if ( isset( $_GET['sms_action'] ) && 'logout' === sanitize_text_field( $_GET['sms_action'] ) ) {
			$this->handle_logout();
		}
	}

	/**
	 * Handle login form submission.
	 */
	private function handle_login() {
		if ( ! isset( $_POST['sms_login_nonce'] ) || ! wp_verify_nonce( $_POST['sms_login_nonce'], 'sms_login_form' ) ) {
			wp_die( esc_html__( 'Security check failed', 'school-management-system' ) );
		}

		$email = sanitize_email( $_POST['sms_email'] ?? '' );
		$password = sanitize_text_field( $_POST['sms_password'] ?? '' );

		if ( empty( $email ) || empty( $password ) ) {
			wp_die( esc_html__( 'Email and password are required', 'school-management-system' ) );
		}

		// Get user by email.
		$user = get_user_by( 'email', $email );

		if ( ! $user ) {
			wp_die( esc_html__( 'Invalid email or password', 'school-management-system' ) );
		}

		// Verify password.
		if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
			wp_die( esc_html__( 'Invalid email or password', 'school-management-system' ) );
		}

		// Check if user has student or parent role.
		if ( ! in_array( 'sms_student', $user->roles, true ) && ! in_array( 'sms_parent', $user->roles, true ) ) {
			wp_die( esc_html__( 'Invalid user role', 'school-management-system' ) );
		}

		// Login user.
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );
		do_action( 'wp_login', $user->user_login, $user );

		// Redirect to portal.
		if ( in_array( 'sms_student', $user->roles, true ) ) {
			wp_safe_remote_post( home_url( '/?sms_portal=student' ) );
		} else {
			wp_safe_remote_post( home_url( '/?sms_portal=parent' ) );
		}
	}

	/**
	 * Handle logout.
	 */
	private function handle_logout() {
		wp_logout();
		wp_safe_remote_post( home_url( '/' ) );
	}

	/**
	 * Check if user is student.
	 *
	 * @param int $user_id User ID.
	 * @return bool True if user is student, false otherwise.
	 */
	public static function is_student( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		$user = get_userdata( $user_id );
		return in_array( 'sms_student', $user->roles, true );
	}

	/**
	 * Check if user is teacher.
	 *
	 * @param int $user_id User ID.
	 * @return bool True if user is teacher, false otherwise.
	 */
	public static function is_teacher( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		$user = get_userdata( $user_id );
		return in_array( 'sms_teacher', $user->roles, true );
	}

	/**
	 * Check if user is parent.
	 *
	 * @param int $user_id User ID.
	 * @return bool True if user is parent, false otherwise.
	 */
	public static function is_parent( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		$user = get_userdata( $user_id );
		return in_array( 'sms_parent', $user->roles, true );
	}
}
