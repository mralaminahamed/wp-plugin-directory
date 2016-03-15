<?php
namespace WordPressdotorg\Plugin_Directory\Admin\Metabox;
use WordPressdotorg\Plugin_Directory\Admin\Committers_List_Table;

/**
 * The Plugin Committers admin metabox.
 *
 * @package WordPressdotorg\Plugin_Directory\Admin\Metabox
 */
class Committers {

	/**
	 * Filters the postbox classes for custom comment meta boxes.
	 *
	 * @param array $classes An array of postbox classes.
	 * @return array
	 */
	public static function postbox_classes( $classes ) {
		$classes[] = 'committers-meta-box';

		return array_filter( $classes );
	}

	public static function display() {
		$list = new Committers_List_Table();
		$list->prepare_items();
		$list->display();
	}

	public static function add_committer() {
		check_ajax_referer( 'add-committer' );

		$login   = isset( $_POST['add_committer'] ) ? sanitize_user( $_POST['add_committer'] ) : '';
		$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;

		if ( ! $committer = get_user_by( 'login', $login ) ) {
			wp_die( time() );
		}

		if ( ! current_user_can( 'manage_committers', $post_id ) ) {
		//	wp_die( -1 );
		}
		global $post, $wpdb;

		$response = new \WP_Ajax_Response();
		$post     = get_post( $post_id );

		$result = $wpdb->insert( PLUGINS_TABLE_PREFIX . 'svn_access', array(
			'path'   => "/{$post->post_name}",
			'user'   => $login,
			'access' => 'rw',
		) );

		if ( ! $result ) {
			$message = __( 'An error has occurred. Please reload the page and try again.' );
			if ( is_wp_error( $result ) && $result->get_error_message() ) {
				$message = $result->get_error_message();
			}

			$response->add( array(
				'what' => 'committer',
				'data' => new \WP_Error( 'error', $message ),
			) );
			$response->send();
		}

		$wp_list_table = new Committers_List_Table();

		$response->add( array(
			'what'     => 'committer',
			'id'       => $committer->ID,
			'data'     => $wp_list_table->single_row( $committer ),
			'position' => -1,
		) );
		$response->send();
	}

	public static function remove_committer() {
		$id      = isset( $_POST['id'] )      ? (int) $_POST['id']      : 0;
		$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;

		check_ajax_referer( "remove-committer-$id" );

		if ( ! $committer = get_user_by( 'id', $id ) ) {
			wp_die( time() );
		}

		if ( ! current_user_can( 'manage_committers', $post_id ) ) {
		//	wp_die( -1 );
		}

		$plugin_slug = get_post( $post_id )->post_name;

		$result = $GLOBALS['wpdb']->delete( PLUGINS_TABLE_PREFIX . 'svn_access', array(
			'path'   => "/{$plugin_slug}",
			'user'   => $committer->user_login,
		) );

		wp_die( $result );
	}
}
