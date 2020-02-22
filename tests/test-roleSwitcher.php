<?php
/**
 * Class RoleSwitcherTest
 *
 * @package Wp_User_Role_Switcher
 */

/**
 * Role Switcher test class.
 */
class RoleSwitcherTest extends WP_UnitTestCase {

	/**
	 * Store user Id
	 * @var int $user_id
	 */
	private $user_id;

	/**
	 * Store WP_User_Role_Switcher class object
	 * @var WP_User_Role_Switcher
	 */
	private $wp_user_role_switcher;


	public function setUp() {
		parent::setUp();

		$this->user_id = $this->factory->user->create([
			'role' => 'administrator',
		]);
		wp_set_current_user( $this->user_id );

		$this->wp_user_role_switcher = new WP_User_Role_Switcher();
		$this->wp_user_role_switcher->init();

		// Stop redirection after role switch action.
		add_action( 'wp_redirect', function() {
			return false;
		}, 999, 0 );
	}

	public function tearDown() {
		parent::tearDown();

		wp_delete_user( $this->user_id );
	}

	public function test_admin_bar_menu_hook() {
		global $wp_filter;

		$admin_bar_menu = $wp_filter['admin_bar_menu'];
		$this->assertTrue( $admin_bar_menu->has_filter('add_admin_bar_menu') );
	}

	public function test_admin_bar_menu() {
		/* Load the admin bar class code ready for instantiation */
		require_once( ABSPATH . WPINC . '/class-wp-admin-bar.php' );

		$admin_bar = new WP_Admin_Bar();
		do_action_ref_array( 'admin_bar_menu', array( &$admin_bar ) );
		$nodes = $admin_bar->get_nodes();

		$this->assertArrayHasKey( 'd9-role-switcher', $nodes );
		$this->assertArrayHasKey( 'role_editor', $nodes );
		$this->assertArrayHasKey( 'role_author', $nodes );
		$this->assertArrayHasKey( 'role_contributor', $nodes );
		$this->assertArrayHasKey( 'role_subscriber', $nodes );
		$this->assertArrayHasKey( 'd9-role-switcher-back', $nodes );
	}

	public function test_switch_editor() {
		$this->wp_user_role_switcher->switch_role( 'role_switcher', 'editor', wp_create_nonce( 'd9SwitchAseditor' ) );
		$this->assertContains( 'editor', wp_get_current_user()->roles );
	}

	public function test_switch_author() {
		$this->wp_user_role_switcher->switch_role( 'role_switcher', 'author', wp_create_nonce( 'd9SwitchAsauthor' ) );
		$this->assertContains( 'author', wp_get_current_user()->roles );
	}

	public function test_switch_contributor() {
		$this->wp_user_role_switcher->switch_role( 'role_switcher', 'contributor', wp_create_nonce( 'd9SwitchAscontributor' ) );
		$this->assertContains( 'contributor', wp_get_current_user()->roles );
	}

	public function test_switch_subscriber() {
		$this->wp_user_role_switcher->switch_role( 'role_switcher', 'subscriber', wp_create_nonce( 'd9SwitchAssubscriber' ) );
		$this->assertContains( 'subscriber', wp_get_current_user()->roles );
	}

	public function test_switch_switchback() {
		$this->wp_user_role_switcher->switch_role( 'role_switcher', 'editor', wp_create_nonce( 'd9SwitchAseditor' ) );
		$this->assertContains( 'editor', wp_get_current_user()->roles );

		$this->wp_user_role_switcher->switch_role( 'role_switch_back', '', wp_create_nonce( 'd9SwitchBack' ) );
		$this->assertContains( 'administrator', wp_get_current_user()->roles );
	}
}
