<?php
namespace WordPressdotorg\Plugin_Directory\Admin\Tools;

use WordPressdotorg\Plugin_Directory;
use WordPressdotorg\Plugin_Directory\Admin\Metabox\Author_Card;

/**
 * All functionality related to Author_Cards Tool.
 *
 * @package WordPressdotorg\Plugin_Directory\Admin\Tools
 */
class Author_Cards {

	/**
	 * Fetch the instance of the Author_Card class.
	 */
	public static function instance() {
		static $instance = null;

		return ! is_null( $instance ) ? $instance : $instance = new self();
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu',            array( $this, 'add_to_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue JS and CSS assets needed for any wp-admin screens.
	 *
	 * @param string $hook_suffix The hook suffix of the current screen.
	 */
	public function enqueue_assets( $hook_suffix ) {
		switch ( $hook_suffix ) {
			case 'tools_page_authorcards':
				wp_enqueue_style( 'plugin-admin-post-css', plugins_url( 'css/edit-form.css', Plugin_Directory\PLUGIN_FILE ), array( 'edit' ), 'aaaa' );
			break;
		}
	}
	
	public function add_to_menu() {
		add_submenu_page(
			'tools.php',
			__( 'Author Cards', 'wporg-plugins' ),
			__( 'Author Cards', 'wporg-plugins' ),
			'plugin_review',
			'authorcards',
			array( $this, 'show_form' )
		);
	}

	public function show_form() {
		if ( ! current_user_can( 'plugin_review' ) ) {
			return;
		}

		$usernames = ! empty( $_POST['users'] ) ? $_POST['users'] : '';

		echo '<div class="wrap author-cards">';
		echo '<h1>' . __( 'Author Cards', 'wporg-plugins' ) . '</h1>';

		echo '<p>' . __( 'This is a tool to display an author card for one or more specified users.', 'wporg-plugins' ) . '</p>';

		echo '<form method="post">';
		echo '<table class="form-table"><tbody><tr>';
		echo '<th scope="row"><label for="users">' . __( 'Users', 'wporg-plugins' ) . '</label></th><td>';
		echo '<input name="users" type="text" id="users" value="' . esc_attr( $usernames ) . '" class="regular-text">';
		echo '<p>' . __( 'Comma-separated list of user slugs, logins, and/or email addresses.', 'wporg-plugins' ) . '</p>';
		echo '</td></tr></tbody></table>';
		echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="' . __( 'Submit', 'wporg-plugins' ) . '"></p>';
		echo '</form>';

		if ( $usernames ) {
			echo '<h2>' . __( 'Results', 'wporg-plugins' ) . '</h2>';

			echo '<div class="main">';

			// Array to store usernames that have been processed to ensure no
			// duplicates are displayed
			$processed_usernames = array();

			$usernames = explode( ',', $usernames );

			// Iterate through usernames
			foreach ( $usernames as $username ) {
				$username = trim( $username );

				if ( false !== strpos( $username, '@' ) ) {
					$user = get_user_by( 'email', $username );
				} else {
					$user = get_user_by( 'slug', $username );

					if ( ! $user ) {
						$user = get_user_by( 'login', $username );
					}
				}

				// Output author card
				if ( $user ) {
					if ( ! in_array( $user->user_nicename, $processed_usernames ) ) {
						$processed_usernames[] = $user->user_nicename;
						Author_Card::display( $user->ID );
					}
				} else {
					if ( ! in_array( $username, $processed_usernames ) ) {
						$processed_usernames[] = $username;
						echo '<div class="profile"><p class="profile-personal">';
						echo '<img class="avatar" src="https://gravatar.com/avatar/?d=mystery"><span class="profile-details"><strong>';
						echo esc_html( $username );
						echo '</strong></span></p>';
						echo '<p><em>' . __( 'No user found with this slug, login, or email address.', 'wporg-plugins' ) . '</em></p>';
						echo '</div>';
					}
				}
			}
		}

		echo '</div>';
	}

}
