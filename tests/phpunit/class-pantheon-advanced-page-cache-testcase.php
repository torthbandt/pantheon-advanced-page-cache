<?php
/**
 * Common testcase abstractions.
 *
 * @package Pantheon_Advanced_Page_Cache
 */

use Pantheon_Advanced_Page_Cache\Emitter;

/**
 * Class from which all tests inherit.
 */
class Pantheon_Advanced_Page_Cache_Testcase extends WP_UnitTestCase {

	/**
	 * Surrogate keys cleared by the Purger (reported on an action).
	 *
	 * @var array
	 */
	protected $cleared_keys = array();

	/**
	 * Mapping of views to their surrogate keys.
	 *
	 * @var array
	 */
	protected $view_surrogate_keys = array();

	/**
	 * Sets up the testcase.
	 */
	public function setUp() {
		parent::setUp();

		$this->factory->product_category = new WP_UnitTest_Factory_For_Term( $this->factory, 'product_category' );

		$this->setup_permalink_structure();

		$this->user_id1 = $this->factory->user->create( array( 'user_role' => 'author', 'user_nicename' => 'first-user' ) );
		$this->user_id2 = $this->factory->user->create( array( 'user_role' => 'author', 'user_nicename' => 'second-user' ) );
		$this->user_id3 = $this->factory->user->create( array( 'user_role' => 'author', 'user_nicename' => 'third-user' ) );

		$this->tag_id1 = $this->factory->tag->create( array( 'slug' => 'first-tag' ) );
		$this->tag_id2 = $this->factory->tag->create( array( 'slug' => 'second-tag' ) );
		$this->category_id1 = 1; // This is the default 'uncategorized' category.
		$this->category_id2 = $this->factory->category->create( array(
			'slug' => 'second-category',
		) );

		$this->product_category_id1 = $this->factory->product_category->create( array(
			'slug' => 'first-product-category',
		) );
		$this->product_category_id2 = $this->factory->product_category->create( array(
			'slug' => 'second-product-category',
		) );
		$this->product_category_id3 = $this->factory->product_category->create( array(
			'slug' => 'third-product-category',
		) );

		$this->post_id1 = $this->factory->post->create( array(
			'post_status'   => 'publish',
			'post_author'   => $this->user_id1,
			'post_date'     => '2016-10-14 12:00',
			'post_date_gmt' => '2016-10-14 12:00',
			'post_name'     => 'first-post',
		) );
		wp_set_object_terms( $this->post_id1, array( $this->tag_id2 ), 'post_tag' );
		$this->post_id2 = $this->factory->post->create( array(
			'post_status'   => 'publish',
			'post_author'   => $this->user_id2,
			'post_date'     => '2016-10-14 11:00',
			'post_date_gmt' => '2016-10-14 11:00',
			'post_name'     => 'second-post',
		) );
		$this->post_id3 = $this->factory->post->create( array(
			'post_status'   => 'publish',
			'post_author'   => $this->user_id2,
			'post_date'     => '2016-10-15 11:00',
			'post_date_gmt' => '2016-10-15 11:00',
			'post_name'     => 'third-post',
		) );
		$this->post_id4 = $this->factory->post->create( array(
			'post_status'   => 'draft',
			'post_author'   => $this->user_id2,
			'post_date'     => '2016-10-15 11:00',
			'post_date_gmt' => '2016-10-15 11:00',
			'post_name'     => 'fourth-post',
		) );
		$this->page_id1 = $this->factory->post->create( array(
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_author'   => $this->user_id1,
			'post_name'     => 'first-page',
		) );
		$this->product_id1 = $this->factory->post->create( array(
			'post_status'   => 'publish',
			'post_type'     => 'product',
			'post_author'   => $this->user_id1,
			'post_date'     => '2016-10-14 12:00',
			'post_date_gmt' => '2016-10-14 12:00',
			'post_name'     => 'first-product',
		) );
		wp_set_object_terms( $this->product_id1, array( $this->product_category_id2 ), 'product_category' );
		$this->product_id2 = $this->factory->post->create( array(
			'post_status'   => 'publish',
			'post_type'     => 'product',
			'post_author'   => $this->user_id2,
			'post_date'     => '2016-10-14 11:00',
			'post_date_gmt' => '2016-10-14 11:00',
			'post_name'     => 'second-product',
		) );
		wp_set_object_terms( $this->product_id2, array( $this->product_category_id1 ), 'product_category' );

		$this->cleared_keys = array();
		$this->setup_view_surrogate_keys();

		add_action( 'pantheon_wp_clear_edge_keys', array( $this, 'action_pantheon_wp_clear_edge_keys' ) );
	}

	/**
	 * Hooks into the 'pantheon_wp_clear_edge_keys' to listen to cleared keys.
	 *
	 * @param array $keys Surrogate keys being cleared.
	 */
	public function action_pantheon_wp_clear_edge_keys( $keys ) {
		$this->cleared_keys = array_merge( $this->cleared_keys, $keys );
	}

	/**
	 * Sets up the permalink structure so our tests have pretty permalinks.
	 */
	private function setup_permalink_structure() {
		global $wp_rewrite;

		$structure = '%year%/%monthnum%/%day%/%postname%/';
		update_option( 'permalink_structure', $structure );

		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( $structure );

		create_initial_taxonomies();
		$this->register_custom_types();

		$wp_rewrite->flush_rules();
	}

	/**
	 * Sets up the REST API server object
	 */
	protected function setup_rest_api_server() {
		$this->server = $GLOBALS['wp_rest_server'] = new Spy_REST_Server;
		do_action( 'rest_api_init' );
	}

	/**
	 * Primes the mapping of views to their surrogate keys.
	 */
	protected function setup_view_surrogate_keys() {
		$this->view_surrogate_keys = array();
		// Primes the mapping of views to their surrogate keys.
		$views = array(
			home_url( '/' ), // Homepage.
			'/products/', // Product post type archive.
			'/2016/10/14/', // Day archive with posts.
			'/2015/10/15/', // Day archive without posts.
			'/2016/10/', // Month archive with posts.
			'/2015/10/', // Month archive without posts.
			'/2016/', // Year archive with posts.
			'/2015/', // Year archive without posts.
		);
		$rest_api_routes = array();
		$posts = get_posts( array(
			'post_type'      => 'any',
			'post_status'    => 'any',
			'posts_per_page' => -1,
		) );
		foreach ( $posts as $post ) {
			$views[] = get_permalink( $post->ID );
			$post_type_object = get_post_type_object( $post->post_type );
			if ( ! empty( $post_type_object->show_in_rest ) ) {
				$rest_api_routes[] = '/wp/v2/' . $post_type_object->rest_base;
				$rest_api_routes[] = '/wp/v2/' . $post_type_object->rest_base . '/' . $post->ID;
			}
		}
		$users = get_users( array(
			'fields'         => 'ids',
		) );
		foreach ( $users as $user_id ) {
			$views[] = get_author_posts_url( $user_id );
			$rest_api_routes[] = '/wp/v2/users';
			$rest_api_routes[] = '/wp/v2/users/' . $user_id;
		}
		$terms = get_terms( array( 'post_tag', 'category', 'product_category' ), array(
			'hide_empty'     => false,
		) );
		foreach ( $terms as $term ) {
			$views[] = get_term_link( $term );
			$taxonomy_object = get_taxonomy( $term->taxonomy );
			if ( ! empty( $taxonomy_object->show_in_rest ) ) {
				$rest_api_routes[] = '/wp/v2/' . $taxonomy_object->rest_base;
				$rest_api_routes[] = '/wp/v2/' . $taxonomy_object->rest_base . '/' . $term->term_id;
			}
		}
		$views = array_unique( $views );
		foreach ( $views as $view ) {
			$path = parse_url( $view, PHP_URL_PATH );
			if ( $query = parse_url( $view, PHP_URL_QUERY ) ) {
				$path .= '?' . $query;
			}
			$this->go_to( $view );
			$this->view_surrogate_keys[ $path ] = Emitter::get_main_query_surrogate_keys();
		}
		if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '<' ) ) {
			return;
		}
		$this->setup_rest_api_server();
		$rest_api_routes = array_unique( $rest_api_routes );
		foreach ( $rest_api_routes as $rest_api_route ) {
			$request = new WP_REST_Request( 'GET', $rest_api_route );
			$this->server->dispatch( $request );
			$this->view_surrogate_keys[ '/wp-json' . $rest_api_route ] = Emitter::get_rest_api_surrogate_keys();
		}
	}

	/**
	 * Register custom post types and taxonomies.
	 */
	private function register_custom_types() {
		register_post_type( 'product', array(
			'public'      => true,
			'has_archive' => 'products',
		) );
		register_taxonomy( 'product_category', array( 'product' ), array(
			'public'  => true,
			'rewrite' => array(
				'slug'    => 'product-category',
			),
		) );
	}

	/**
	 * Assert cleared keys to match the expected set.
	 *
	 * @param array $expected Surrogate keys expected to be cleared.
	 */
	protected function assertClearedKeys( $expected ) {
		$this->assertArrayValues( $expected, array_unique( $this->cleared_keys ) );
	}

	/**
	 * Assert URIs purged by cleared keys to match the expected set.
	 *
	 * @param array $expected URIs expected to be cleared based on cleared keys.
	 */
	protected function assertPurgedURIs( $expected ) {
		$actual = array();
		foreach ( $this->view_surrogate_keys as $view => $keys ) {
			if ( array_intersect( $keys, $this->cleared_keys ) ) {
				$actual[] = $view;
			}
		}
		// Drop /wp-json/ URLs when <WP 4.7.
		if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '<' ) ) {
			foreach ( $expected as $k => $v ) {
				if ( 0 === stripos( $v, '/wp-json/' ) ) {
					unset( $expected[ $k ] );
				}
			}
		}
		$this->assertArrayValues( $expected, $actual );
	}

	/**
	 * Assert expected array values to match actual array values.
	 *
	 * Improves upon assertEquals by ensuring arrays are in similar order.
	 *
	 * @param array $expected Expected array values.
	 * @param array $actual Actual array values.
	 */
	protected function assertArrayValues( $expected, $actual ) {
		sort( $expected );
		sort( $actual );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Tear down behaviors after the tests have completed.
	 */
	public function tearDown() {
		$this->cleared_keys = array();
		remove_action( 'pantheon_wp_clear_edge_keys', array( $this, 'action_pantheon_wp_clear_edge_keys' ) );
		_unregister_post_type( 'product' );
		_unregister_taxonomy( 'product_category' );
		parent::tearDown();
	}

}
