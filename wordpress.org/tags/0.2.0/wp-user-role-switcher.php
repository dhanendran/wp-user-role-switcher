<?php
/**
 * @author            Dhanendran Rajagopal
 * @link              https://dhanendranrajagopal.me
 * @since             0.1.0
 * @package           wp-user-role-switcher
 *
 * @wordpress-plugin
 * Plugin Name:       WP User Role Switcher
 * Plugin URI:        https://github.com/dhanendran/wp-user-role-switcher
 * Description:       This plugin allows you to quickly swap between user roles in WordPress at the click of a button. You’ll be instantly switched to the new user role. This is handy for test environments where you regularly log out and in between different accounts, or for administrators who need to switch between multiple accounts to test the feature in different user roles.
 * Version:           0.1.0
 * Author:            Dhanendran Rajagopal
 * Author URI:        https://dhanendranrajagopal.me
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       d9urs
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Initialize `WP_User_Role_Switcher` class.
 */
add_action( 'init', function() {
	$wp_user_role_switcher = new WP_User_Role_Switcher();
	$wp_user_role_switcher->init();

	$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
	$role   = filter_input( INPUT_GET, 'role', FILTER_SANITIZE_STRING );
	$nonce  = filter_input( INPUT_GET, 'nonce', FILTER_SANITIZE_STRING );

	if ( 'role_switcher' === $action || 'role_switch_back' === $action ) {
		$wp_user_role_switcher->switch_role( $action, $role, $nonce );
	}
} );

/**
 * Class WP_User_Role_Switcher
 */
class WP_User_Role_Switcher {
	/**
	 * Adds the required hooks into WP Core.
	 */
	public function init() {
		if ( current_user_can( 'manage_options' ) || get_user_meta( get_current_user_id(), '_d9urs_role_switched', true ) ) {
			add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 100 );
			wp_enqueue_style( 'd9urs-styles', plugin_dir_url( __FILE__ ) . 'style.css', array(), '0.1' );
		}

		if ( get_user_meta( get_current_user_id(), '_d9urs_role_switched', true ) ) {
			add_action( 'wp_footer', array( $this, 'add_floating_button' ) );
			add_action( 'admin_footer', array( $this, 'add_floating_button' ) );
		}

		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		if ( isset( $_GET['_urs_action'] ) ) {
			switch ( $_GET['_urs_action'] ) {
				case 'switch_back':
					add_action( 'admin_notices', array( $this, 'admin_switch_back_notice' ) );
					break;
				case 'error':
					add_action( 'admin_notices', array( $this, 'admin_error_notice' ) );
					break;
				case 'switched':
					add_action( 'admin_notices', array( $this, 'admin_success_notice' ) );
					break;
			}
		}
	}

	/**
	 * Adding `Switch To` menu item to admin bar.
	 * @param $admin_bar
	 */
	public function add_admin_bar_menu( $admin_bar ) {

		$admin_bar->add_menu( array(
			'id'     => 'd9-role-switcher',
			'parent' => 'top-secondary',
			'title'  => 'Switch Role To',
			'href'   => '#',
			'meta'   => array(
				'title' => __( 'Switch To', 'd9urs' ),
			),
		));

		$roles = $this->get_switchable_roles();

		foreach ( $roles as $role => $url ) {
			$admin_bar->add_menu( array(
				'id'     => sprintf( 'role_%s', $role ),
				'parent' => 'd9-role-switcher',
				'title'  => __( ucfirst( $role ), 'd9urs' ),
				'href'   => $url,
				'meta'   => array(
					'title'  => __( ucfirst( $role ), 'd9urs' ),
				),
			));
		}

		$admin_bar->add_menu( array(
			'id'     => 'd9-role-switcher-back',
			'parent' => 'd9-role-switcher',
			'title'  => 'Switch Back',
			'href'   => $this->get_switch_back_link(),
			'meta'   => array(
				'title' => __( 'Switch Back', 'd9urs' ),
				'class' => 'd9-switch-back'
			),
		));
	}

	/**
	 * Get switchable roles.
	 *
	 * @return array
	 */
	private function get_switchable_roles() {
		$all_roles = array_keys( get_editable_roles() );
		$curr_user = wp_get_current_user();
		$all_roles     = array_diff( $all_roles, $curr_user->roles );

		$orig_roles = get_user_meta( get_current_user_id(), '_d9urs_original_user_role', true );
		if ( ! empty( $orig_roles ) ) {
			$all_roles = array_values( array_diff( $all_roles, $orig_roles ) );
		}

		$roles = [];
		foreach ( $all_roles as $role ) {
			$roles[ $role ] = wp_nonce_url(
						add_query_arg( array(
							'action' => 'role_switcher',
							'role'   => $role,
						) ),
						sprintf( 'd9SwitchAs%s', $role ),
						'nonce'
					);
		}

		return $roles;
	}

	/**
	 * Get the `Switch Back` link.
	 *
	 * @return string
	 */
	private function get_switch_back_link() {
		return wp_nonce_url(
				add_query_arg( array(
					'action' => 'role_switch_back',
				) ),
				sprintf( 'd9SwitchBack' ),
				'nonce'
			);
	}

	/**
	 * Adds floating action button.
	 *
	 * @return void
	 */
	public function add_floating_button() {
		?>
		<div class="fab-container">
			<div class="fab fab-icon-holder">
				<i class="fab-urs-icon"></i>
			</div>
			<ul class="fab-options">
				<?php $i = 1; foreach( $this->get_switchable_roles() as $role => $url ) : ?>
				<li>
					<a class="fab-link" href="<?php echo $url; ?>">
						<span class="fab-label"><?php echo ucfirst( $role ); ?></span>
						<div class="fab-icon-holder">
							<i><?php echo $i++; ?></i>
						</div>
					</a>
				</li>
				<?php endforeach; ?>
				<li>
					<a class="fab-link" href="<?php echo $this->get_switch_back_link(); ?>">
						<span class="fab-label">Switch Back</span>
						<div class="fab-icon-holder">
							<i class="dashicons-before dashicons-undo"></i>
						</div>
					</a>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Switch user role.
	 */
	public function switch_role( $action, $role, $nonce ) {
		if ( 'role_switcher' !== $action && 'role_switch_back' !== $action ) {
			return;
		}

		$curr_user  = wp_get_current_user();
		$curr_roles = $curr_user->roles;
		if ( 'role_switch_back' === $action ) {
			// Remove all current roles from user.
			foreach ( $curr_roles as $curr_role ) {
				$curr_user->remove_role( $curr_role );
			}

			$orig_roles = get_user_meta( get_current_user_id(), '_d9urs_original_user_role', true );
			foreach ( $orig_roles as $orig_role ) {
				$curr_user->add_role( $orig_role );
			}

			update_user_meta( get_current_user_id(), '_d9urs_role_switched', false );

			$this->redirect_user( 'switch_back' );
			return;
		}

		$all_roles = array_keys( get_editable_roles() );

		if ( ! wp_verify_nonce( $nonce, sprintf( 'd9SwitchAs%s', $role ) ) || ! in_array( $role, $all_roles ) ) {
			$this->redirect_user( 'error' );
			return;
		}

		// Backup original user role before switching it to another.
		$orig_roles = get_user_meta( get_current_user_id(), '_d9urs_original_user_role', true );
		if ( empty( $orig_roles ) ) {
			update_user_meta( get_current_user_id(), '_d9urs_original_user_role', $curr_roles );
		}
		update_user_meta( get_current_user_id(), '_d9urs_role_switched', true );

		// Remove all current roles from user.
		foreach ( $curr_roles as $curr_role ) {
			$curr_user->remove_role( $curr_role );
		}

		// Add new role to user.
		$curr_user->add_role( $role );

		$this->redirect_user( 'switched' );
	}

	/**
	 * Redirect User back to the same page.
	 *
	 * @param string $msg
	 */
	private function redirect_user( $msg ) {
		$url = ( ! empty( $_SERVER['REQUEST_SCHEME'] ) ) ? $_SERVER['REQUEST_SCHEME'] : 'http';
		$url = $url . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$url = remove_query_arg( array( 'action', 'role', 'nonce' ), $url );
		$url = add_query_arg( '_urs_action', $msg, $url );

		if ( wp_redirect( $url ) ) {
			die;
		}
	}

	/**
	 * Adding admin notice.
	 */
	public function admin_error_notice() {
		if ( is_admin() ) {
			echo '<div class="d9urs notice notice-warning is-dismissible">
             <p>There are some issues while switching the user role. Please try again.</p>
         	</div>';
		}
	}

	/**
	 * Adding admin notice.
	 */
	public function admin_success_notice() {
		if ( is_admin() ) {
			echo '<div class="d9urs notice notice-success is-dismissible">
             <p>Your role has been changed. Please click `Switch Back` from the `Switch Role To` menu in the top admin bar to switch back to your original role.</p>
         	</div>';
		}
	}

	/**
	 * Adding admin notice.
	 */
	public function admin_switch_back_notice() {
		if ( is_admin() ) {
			echo '<div class="d9urs notice notice-success is-dismissible">
             <p>Role has been switched back to your original role.</p>
         	</div>';
		}
	}
}
