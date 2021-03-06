<?php
/**
 * Tests for the Purger class.
 *
 * @package Pantheon_Advanced_Page_Cache
 */

/**
 * Tests for the Purger class.
 */
class Test_Purger extends Pantheon_Advanced_Page_Cache_Testcase {

	/**
	 * Verify publishing a new post purges the homepage and associated archive pages.
	 */
	public function test_publish_post() {
		$this->post_id5 = $this->factory->post->create( array(
			'post_status'   => 'publish',
			'post_author'   => $this->user_id1,
			'post_date'     => '2016-10-21 12:00',
			'post_date_gmt' => '2016-10-21 12:00',
			'post_name'     => 'fifth-post',
		) );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->post_id5,
			'user-' . $this->user_id1,
			'term-' . $this->category_id1,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/author/first-user/',
			'/category/uncategorized/',
			'/wp-json/wp/v2/users',
			'/wp-json/wp/v2/users/' . $this->user_id1,
			'/wp-json/wp/v2/categories',
			'/wp-json/wp/v2/categories/' . $this->category_id1,
		) );
	}

	/**
	 * Verify updating an existing post clears the expected keys.
	 */
	public function test_update_post() {
		wp_update_post( array(
			'ID'           => $this->post_id1,
			'post_content' => 'Test content',
		) );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->post_id1,
			'user-' . $this->user_id1,
			'term-' . $this->category_id1,
			'term-' . $this->tag_id2,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/2016/',
			'/2016/10/',
			'/2016/10/14/',
			'/2016/10/14/first-post/',
			'/author/first-user/',
			'/category/uncategorized/',
			'/tag/second-tag/',
			'/wp-json/wp/v2/categories',
			'/wp-json/wp/v2/categories/' . $this->category_id1,
			'/wp-json/wp/v2/tags',
			'/wp-json/wp/v2/tags/' . $this->tag_id2,
			'/wp-json/wp/v2/users',
			'/wp-json/wp/v2/users/' . $this->user_id1,
			'/wp-json/wp/v2/posts',
			'/wp-json/wp/v2/posts/' . $this->post_id1,
		) );
	}

	/**
	 * Verify updating a draft doesn't clear any keys
	 */
	public function test_update_post_draft() {
		wp_update_post( array(
			'ID'           => $this->post_id4,
			'post_content' => 'Test content',
		) );
		$this->assertClearedKeys( array(
			'post-' . $this->post_id4,
			'term-' . $this->category_id1,
		) );
		$this->assertPurgedURIs( array(
			'/category/uncategorized/',
			'/wp-json/wp/v2/categories',
			'/wp-json/wp/v2/categories/' . $this->category_id1,
		) );
	}

	/**
	 * Verify unpublishing a post clears the expected keys
	 */
	public function test_unpublish_post() {
		wp_update_post( array(
			'ID'           => $this->post_id1,
			'post_status'  => 'draft',
		) );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->post_id1,
			'user-' . $this->user_id1,
			'term-' . $this->category_id1,
			'term-' . $this->tag_id2,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/2016/',
			'/2016/10/',
			'/2016/10/14/',
			'/2016/10/14/first-post/',
			'/author/first-user/',
			'/category/uncategorized/',
			'/tag/second-tag/',
			'/wp-json/wp/v2/categories',
			'/wp-json/wp/v2/categories/' . $this->category_id1,
			'/wp-json/wp/v2/tags',
			'/wp-json/wp/v2/tags/' . $this->tag_id2,
			'/wp-json/wp/v2/users',
			'/wp-json/wp/v2/users/' . $this->user_id1,
			'/wp-json/wp/v2/posts',
			'/wp-json/wp/v2/posts/' . $this->post_id1,
		) );
	}

	/**
	 * Verify trashing a post clears the expected keys.
	 */
	public function test_trash_post() {
		wp_trash_post( $this->post_id1 );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->post_id1,
			'user-' . $this->user_id1,
			'term-' . $this->category_id1,
			'term-' . $this->tag_id2,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/2016/',
			'/2016/10/',
			'/2016/10/14/',
			'/2016/10/14/first-post/',
			'/author/first-user/',
			'/category/uncategorized/',
			'/tag/second-tag/',
			'/wp-json/wp/v2/categories',
			'/wp-json/wp/v2/categories/' . $this->category_id1,
			'/wp-json/wp/v2/tags',
			'/wp-json/wp/v2/tags/' . $this->tag_id2,
			'/wp-json/wp/v2/users',
			'/wp-json/wp/v2/users/' . $this->user_id1,
			'/wp-json/wp/v2/posts',
			'/wp-json/wp/v2/posts/' . $this->post_id1,
		) );
	}

	/**
	 * Verify deleting a post clears the expected keys.
	 */
	public function test_delete_post() {
		wp_delete_post( $this->post_id1, true );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->post_id1,
			'user-' . $this->user_id1,
			'term-' . $this->category_id1,
			'term-' . $this->tag_id2,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/2016/',
			'/2016/10/',
			'/2016/10/14/',
			'/2016/10/14/first-post/',
			'/author/first-user/',
			'/category/uncategorized/',
			'/tag/second-tag/',
			'/wp-json/wp/v2/categories',
			'/wp-json/wp/v2/categories/' . $this->category_id1,
			'/wp-json/wp/v2/tags',
			'/wp-json/wp/v2/tags/' . $this->tag_id2,
			'/wp-json/wp/v2/users',
			'/wp-json/wp/v2/users/' . $this->user_id1,
			'/wp-json/wp/v2/posts',
			'/wp-json/wp/v2/posts/' . $this->post_id1,
		) );
	}

	/**
	 * Verify creating a new page purges the homepage.
	 */
	public function test_publish_page() {
		$this->page_id2 = $this->factory->post->create( array(
			'post_status'   => 'publish',
			'post_author'   => $this->user_id1,
			'post_date'     => '2016-10-21 12:00',
			'post_date_gmt' => '2016-10-21 12:00',
			'post_name'     => 'second-page',
		) );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->page_id2,
			'user-' . $this->user_id1,
			'term-' . $this->category_id1,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/author/first-user/',
			'/category/uncategorized/',
			'/wp-json/wp/v2/categories',
			'/wp-json/wp/v2/categories/' . $this->category_id1,
			'/wp-json/wp/v2/users',
			'/wp-json/wp/v2/users/' . $this->user_id1,
		) );
	}

	/**
	 * Verify updating a page clears the expected keys.
	 */
	public function test_update_page() {
		wp_update_post( array(
			'ID'           => $this->page_id1,
			'post_content' => 'Test content',
		) );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->page_id1,
			'user-' . $this->user_id1,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/author/first-user/',
			'/first-page/',
			'/wp-json/wp/v2/pages',
			'/wp-json/wp/v2/pages/' . $this->page_id1,
			'/wp-json/wp/v2/users',
			'/wp-json/wp/v2/users/' . $this->user_id1,
		) );
	}

	/**
	 * Verify unpublishing a page clears the expected keys.
	 */
	public function test_unpublish_page() {
		wp_update_post( array(
			'ID'           => $this->page_id1,
			'post_status'  => 'draft',
		) );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->page_id1,
			'user-' . $this->user_id1,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/author/first-user/',
			'/first-page/',
			'/wp-json/wp/v2/pages',
			'/wp-json/wp/v2/pages/' . $this->page_id1,
			'/wp-json/wp/v2/users',
			'/wp-json/wp/v2/users/' . $this->user_id1,
		) );
	}

	/**
	 * Verify trashing a page clears the expected keys.
	 */
	public function test_trash_page() {
		wp_trash_post( $this->page_id1 );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->page_id1,
			'user-' . $this->user_id1,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/author/first-user/',
			'/first-page/',
			'/wp-json/wp/v2/pages',
			'/wp-json/wp/v2/pages/' . $this->page_id1,
			'/wp-json/wp/v2/users',
			'/wp-json/wp/v2/users/' . $this->user_id1,
		) );
	}

	/**
	 * Verify deleting a page clears the expected keys.
	 */
	public function test_delete_page() {
		wp_delete_post( $this->page_id1, true );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->page_id1,
			'user-' . $this->user_id1,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/author/first-user/',
			'/first-page/',
			'/wp-json/wp/v2/pages',
			'/wp-json/wp/v2/pages/' . $this->page_id1,
			'/wp-json/wp/v2/users',
			'/wp-json/wp/v2/users/' . $this->user_id1,
		) );
	}

	/**
	 * Verify calling clean_post_cache() on a page clears expected keys.
	 */
	public function test_clean_post_cache_page() {
		clean_post_cache( $this->page_id1 );
		$this->assertClearedKeys( array(
			'post-' . $this->page_id1,
		) );
		$this->assertPurgedURIs( array(
			'/first-page/',
			'/wp-json/wp/v2/pages',
			'/wp-json/wp/v2/pages/' . $this->page_id1,
		) );
	}

	/**
	 * Verify publishing a new product clears expected keys.
	 */
	public function test_publish_product() {
		$this->product_id3 = $this->factory->post->create( array(
			'post_status'   => 'publish',
			'post_type'     => 'product',
			'post_author'   => $this->user_id2,
			'post_date'     => '2016-10-21 11:00',
			'post_date_gmt' => '2016-10-21 11:00',
			'post_name'     => 'third-product',
		) );
		wp_set_object_terms( $this->product_id3, array( $this->product_category_id1 ), 'product_category' );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->product_id3,
			'term-' . $this->product_category_id1,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/product-category/first-product-category/',
		) );
	}

	/**
	 * Verify updating a product clears the expected keys.
	 */
	public function test_update_product() {
		wp_update_post( array(
			'ID'           => $this->product_id2,
			'post_content' => 'Test content',
		) );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->product_id2,
			'term-' . $this->product_category_id1,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/products/',
			'/product/second-product/',
			'/product-category/first-product-category/',
		) );
	}

	/**
	 * Verify trashing a product clears the expected keys.
	 */
	public function test_trash_product() {
		wp_trash_post( $this->product_id2 );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->product_id2,
			'term-' . $this->product_category_id1,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/products/',
			'/product/second-product/',
			'/product-category/first-product-category/',
		) );
	}

	/**
	 * Verify deleting a product clears the expected keys.
	 */
	public function test_delete_product() {
		wp_delete_post( $this->product_id2, true );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $this->product_id2,
			'term-' . $this->product_category_id1,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/products/',
			'/product/second-product/',
			'/product-category/first-product-category/',
		) );
	}

	/**
	 * Verify calling clean_post_cache() on a product clears expected keys.
	 */
	public function test_clean_post_cache_product() {
		clean_post_cache( $this->product_id1 );
		$this->assertClearedKeys( array(
			'post-' . $this->product_id1,
		) );
		$this->assertPurgedURIs( array(
			'/product-category/second-product-category/',
			'/product/first-product/',
			'/products/',
		) );
	}

	/**
	 * Verify deleting an attachment clears expected keys.
	 */
	public function test_delete_attachment() {
		$attachment_id = $this->factory->post->create( array(
			'post_mime_type' => 'image/jpeg',
			'post_type'      => 'attachment',
			'post_author'    => $this->user_id1,
		) );
		// Ignore the original insert event.
		$this->cleared_keys = array();
		wp_delete_attachment( $attachment_id );
		$this->assertClearedKeys( array(
			'home',
			'front',
			'post-' . $attachment_id,
			'user-' . $this->user_id1,
		) );
		$this->assertPurgedURIs( array(
			'/',
			'/author/first-user/',
			'/wp-json/wp/v2/users',
			'/wp-json/wp/v2/users/' . $this->user_id1,
		) );
	}

	/**
	 * Verify creating a new term clears expected keys.
	 */
	public function test_create_term() {
		$this->tag_id3 = $this->factory->tag->create( array( 'slug' => 'third-tag' ) );
		$this->assertClearedKeys( array(
			'term-' . $this->tag_id3,
			'post-term-' . $this->tag_id3,
		) );
		// Hasn't appeared on any views yet.
		$this->assertPurgedURIs( array() );
	}

	/**
	 * Verify updating an existing term clears expected keys.
	 */
	public function test_update_term() {
		wp_update_term( $this->tag_id2, 'post_tag', array(
			'description' => 'Test description',
		) );
		$this->assertClearedKeys( array(
			'term-' . $this->tag_id2,
			'post-term-' . $this->tag_id2,
		) );
		$this->assertPurgedURIs( array(
			'/2016/10/14/first-post/',
			'/tag/second-tag/',
			'/wp-json/wp/v2/tags',
			'/wp-json/wp/v2/tags/' . $this->tag_id2,
		) );
	}

	/**
	 * Verify deleting an existing term clears expected keys.
	 */
	public function test_delete_term() {
		wp_delete_term( $this->tag_id2, 'post_tag' );
		$this->assertClearedKeys( array(
			'term-' . $this->tag_id2,
			'post-term-' . $this->tag_id2,
		) );
		$this->assertPurgedURIs( array(
			'/2016/10/14/first-post/',
			'/tag/second-tag/',
			'/wp-json/wp/v2/tags',
			'/wp-json/wp/v2/tags/' . $this->tag_id2,
		) );
	}

	/**
	 * Verify calling clean_term_cache() clears expected keys.
	 */
	public function test_clean_term_cache() {
		clean_term_cache( $this->tag_id1 );
		$this->assertClearedKeys( array(
			'term-' . $this->tag_id1,
		) );
		$this->assertPurgedURIs( array(
			'/tag/first-tag/',
			'/wp-json/wp/v2/tags',
			'/wp-json/wp/v2/tags/' . $this->tag_id1,
		) );
	}

	/**
	 * Verify calling clean_term_cache() on a category clears expected keys.
	 */
	public function test_clean_term_cache_category() {
		clean_term_cache( $this->category_id1 );
		$this->assertClearedKeys( array(
			'term-' . $this->category_id1,
		) );
		$this->assertPurgedURIs( array(
			'/category/uncategorized/',
			'/wp-json/wp/v2/categories',
			'/wp-json/wp/v2/categories/' . $this->category_id1,
		) );
	}

	/**
	 * Verify calling clean_term_cache() on a product category clears expected keys.
	 */
	public function test_clean_term_cache_product_category() {
		clean_term_cache( $this->product_category_id1 );
		$this->assertClearedKeys( array(
			'term-' . $this->product_category_id1,
		) );
		$this->assertPurgedURIs( array(
			'/product-category/first-product-category/',
		) );
	}

	/**
	 * Verify calling clean_user_cache() clears expected keys.
	 */
	public function test_clean_user_cache() {
		clean_user_cache( $this->user_id1 );
		$this->assertClearedKeys( array(
			'user-' . $this->user_id1,
		) );
		$this->assertPurgedURIs( array(
			'/author/first-user/',
			'/wp-json/wp/v2/users',
			'/wp-json/wp/v2/users/' . $this->user_id1,
		) );
	}

}
