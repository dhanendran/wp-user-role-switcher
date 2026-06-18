=== User Role Switcher ===

Contributors: dhanendran
Tags: user roles, role switcher, switch user, user management, testing
Requires at least: 3.7
Tested up to: 7.0
Stable tag: 0.2.4
Requires PHP: 7.4
License: GPL v3 or later
License URI: <a href="http://www.gnu.org/licenses/gpl-3.0.html">http://www.gnu.org/licenses/gpl-3.0.html</a>

Instant switching between user roles in WordPress.

== Description ==

This plugin allows you to quickly swap between user roles in WordPress at the click of a button. You’ll be instantly switched to the new user role. This is handy for test environments where you regularly log out and in between different accounts, or for administrators who need to switch between multiple accounts to test the feature in different user roles.

=== Features ===

 * Switch Role To: Instantly switch to any user role from the admin bar at top.
 * Switch back: Instantly switch back to your originating role.
 * Compatible with WordPress, WordPress Multisite, WooCommerce.

=== Security ===

 * Only users with the ability to edit other users can switch user roles. By default this is only Administrators on single site installations, and Super Admins on Multisite installations.

=== Usage ===

 1. Once plugin is activated, you will see *Switch Role To* in the top admin bar.
 2. Clicking this will bring the list of user roles available in the system.
 3. Click on any user role you want to test as.
 4. You can switch back to your originating user role via the *Switch back* link on the top admin bar.

== Changelog ==

= 0.2.4 =

* [Security] Role switching now requires the `promote_users` capability (administrators / super admins) instead of `manage_options`, preventing a lower-trust custom role granted `manage_options` from self-assigning a higher role such as administrator.

= 0.2.3 =

* [Security] Added a capability check to the role-switch handler so only administrators (or an already-switched user switching back) can switch roles.
* [Security] The "Switch Back" action now verifies its nonce before changing any roles.
* [Security] Escaped role-switch URLs and labels in the admin bar and floating button output.
* [Security] Hardened the post-switch redirect to a safe, local URL (no longer trusts the host header).
* [Fix] Replaced the deprecated `FILTER_SANITIZE_STRING` with core sanitization for PHP 8.1+ compatibility.

= 0.2.0 =

* [Improvements] Added floating action button when role is switched.

= 0.1.0 =

* Initial release
