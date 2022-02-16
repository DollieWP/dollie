<?php

/**
 * @group WPThumbImageSizesTestCase
 */
class WPThumbImageSizesTestCase extends WP_Thumb_UnitTestCase {

	function setUp() {

		add_filter( 'wp_image_editors', array( $this, 'set_wp_image_editor') );

		$this->file = tempnam( get_temp_dir(), '' ) . '.png';

		copy( dirname( __FILE__ ) . '/images/transparent.png', $this->file );

		$this->attachment = wp_insert_attachment( array( 'post_title' => 'test attachment', 'post_content' => 'test content', 'post_type' => 'attachment', 'post_status' => 'publish', 'post_mime_type' => 'image/png' ), $this->file );

		wp_update_attachment_metadata( $this->attachment, wp_generate_attachment_metadata( $this->attachment, $this->file ) );

		update_option( 'thumbnail_size_w', 100 );
		update_option( 'thumbnail_size_h', 100 );
		update_option( 'thumbnail_crop', true );

		update_option( 'medium_size_w', 200 );
		update_option( 'medium_size_h', 200 );

		update_option( 'large_size_w', 300 );
		update_option( 'large_size_h', 300 );
	}

	function tearDown() {

		wp_delete_post( $this->attachment, true );
	}

	function set_wp_image_editor() {
		return array( 'WP_Thumb_Image_Editor_GD' );
	}
	/**
	 * @group testThumbnailResize
	 */
	function testThumbnailResize() {

		$image = wp_get_attachment_image_src( $this->attachment, 'thumbnail' );
		return;
		$this->assertEquals( 100, $image[1] );
		$this->assertEquals( 100, $image[2] );

		$this->assertFalse( (bool) strpos( $image[0], '/cache/' ) );
	}

	/**
	 * @group testMediumResize
	 */
	function testMediumResize() {

		$image = wp_get_attachment_image_src( $this->attachment, 'medium' );

		$this->assertEquals( 200, $image[1] );
		$this->assertEquals( 161, $image[2] );

		$this->assertFalse( (bool) strpos( $image[0], '/cache/' ) );

	}

	function testLargeResize() {

		$image = wp_get_attachment_image_src( $this->attachment, 'large' );

		$this->assertEquals( 300, $image[1] );
		$this->assertEquals( 242, $image[2] );

		$this->assertFalse( (bool) strpos( $image[0], '/cache/' ) );

	}

	function testCustomImageResize() {

		// add custom sizes
		add_image_size( 'testSize', 400, 400, true );

		$image = wp_get_attachment_image_src( $this->attachment, 'testSize' );

		$this->assertEquals( 400, $image[1] );
		$this->assertEquals( 323, $image[2] );

		$this->assertFalse( (bool) strpos( $image[0], '/cache/' ) );
	}

	function testResizedByArray() {
		$image = wp_get_attachment_image_src( $this->attachment, array( 300, 300 ) );

		$this->assertEquals( 300, $image[1] );
		$this->assertEquals( 242, $image[2] );

		$this->assertTrue( (bool) strpos( $image[0], '/cache/' ) );
	}
}