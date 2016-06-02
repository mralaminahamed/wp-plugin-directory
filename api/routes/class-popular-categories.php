<?php
namespace WordPressdotorg\Plugin_Directory\API\Routes;
use WordPressdotorg\Plugin_Directory\API\Base;
use WP_REST_Server;

/**
 * An API Endpoint to expose a the plugin categories data.
 *
 * @package WordPressdotorg_Plugin_Directory
 */
class Popular_Categories extends Base {

	function __construct() {
		register_rest_route( 'plugins/v1', '/popular-categories/?', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array( $this, 'popular_categories' ),
		) );
	}

	/**
	 * Endpoint to retrieve the popular categories for the plugin directory.
	 *
	 * @param \WP_REST_Request $request The Rest API Request.
	 * @return array A formatted array of all plugin categories on the site.
	 */
	function popular_categories( $request ) {
		$terms = get_terms( 'plugin_category', array( 'hide_empty' => false, 'orderby' => 'count', 'order' => 'DESC' ) );

		$response = array();
		foreach ( $terms as $term ) {
			$response[ $term->slug ] = array(
				'name'  => html_entity_decode( $term->name ),
				'slug'  => $term->slug,
				'count' => $term->count,
			);
		}

		return $response;
	}

}

