<?php
/**
 * Test the Registration class.
 *
 * @since 2.4.4
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard\Tests;

use CT_DB_Registration;

/**
 * Test class for Registration.
 *
 * @since 2.4.4
 */
class Test_CT_DB_Registration extends \WP_UnitTestCase {

	/**
	 * Instance under test.
	 *
	 * @since 2.4.4
	 */
	protected $instance;

	/**
	 * Setup the tests.
	 *
	 * @since 2.4.4
	 */
	protected function setUp() {
		$this->instance = new CT_DB_Registration();

		parent::setUp();
	}

	/**
	 * Test fetching a user.
	 *
	 * @since 2.4.4
	 *
	 * @covers CT_DB_Registration::get_user()
	 */
	public function test_get_user() {
		$user = $this->factory->user->create(
			array(
				'user_login' => 'test',
				'user_email' => 'test@test.com',
			)
		);

		// Test an invalid user does not return a result.
		$this->assertFalse( $this->instance->get_user( 'doesnotexist' ) );

		// Test fetching a valid user by username.
		$test_login_user = $this->instance->get_user( 'test' );
		$this->assertEquals( 'test@test.com', $test_login_user->user_email );

		// Test fetching a valid user by email address.
		$test_email_user = $this->instance->get_user( 'test@test.com' );
		$this->assertEquals( 'test@test.com', $test_email_user->user_email );
	}

	/**
	 * Test the redirect login action.
	 *
	 * @since 2.4.4
	 *
	 * @covers CT_DB_Registration::redirect_login_action()
	 */
	public function test_redirect_login_action() {
		$_GET['action'] = 'login';

		$this->assertTrue( $this->instance->redirect_login_action() );

		$_GET['action'] = 'logout';
		$this->assertFalse( $this->instance->redirect_login_action() );

		$_GET['action'] = 'lostpassword';
		$this->assertFalse( $this->instance->redirect_login_action() );

		$_GET['action'] = 'rp';
		$this->assertFalse( $this->instance->redirect_login_action() );

		$_GET['action'] = 'resetpass';
		$this->assertFalse( $this->instance->redirect_login_action() );

		$_GET['action'] = 'postpass';
		$this->assertFalse( $this->instance->redirect_login_action() );
	}
}
