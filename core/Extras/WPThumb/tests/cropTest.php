<?php

/**
 * @group WPThumbCropTestCase
 */
class WPThumbCropTestCase extends WP_Thumb_UnitTestCase {

	function testCropStandard() {
	
		$path = dirname( __FILE__ ) . '/images/google.png';
		list( $width, $height ) = getimagesize( $path );
		
		$this->assertNotNull( $width );
		$this->assertNotNull( $height );
				
		$image = new WP_Thumb( $path, "width=80&height=80&crop=1&resize=0&cache=0&return=path" );
		
		$file = $image->returnImage();
		
		$this->assertContains( '/cache/', $file );
		$this->assertContains( WP_CONTENT_DIR, $file );
		
		list( $new_width, $new_height ) = getimagesize( $file );
		
		$this->assertEquals( $new_width, 80, 'Width is not expected' );
		$this->assertEquals( $new_height, 80, 'Height is not expcted' );
	
	}
	
	function testCropStandardGif() {
	
		$path = dirname( __FILE__ ) . '/images/google.gif';
		list( $width, $height ) = getimagesize( $path );
		
		$this->assertNotNull( $width );
		$this->assertNotNull( $height );
				
		$image = new WP_Thumb( $path, "width=80&height=80&crop=1&resize=0&cache=0&return=path" );
		
		$file = $image->returnImage();
		
		$this->assertContains( '/cache/', $file );
		$this->assertContains( WP_CONTENT_DIR, $file );
		
		list( $new_width, $new_height ) = getimagesize( $file );
		
		$this->assertEquals( $new_width, 80, 'Width is not expected' );
		$this->assertEquals( $new_height, 80, 'Height is not expcted' );
	
	}
	
	function testCropLargerThanSourceImage() {
	
		$path = dirname( __FILE__ ) . '/images/google.png';
		list( $width, $height ) = getimagesize( $path );
		
		$this->assertNotNull( $width );
		$this->assertNotNull( $height );
				
		$image = new WP_Thumb( $path, "width=300&height=300&crop=1&resize=0&cache=0&return=path" );
		
		$file = $image->returnImage();
		
		$this->assertContains( '/cache/', $file );
		$this->assertContains( WP_CONTENT_DIR, $file );
		
		list( $new_width, $new_height ) = getimagesize( $file );
		
		$this->assertEquals( $new_width, $width, 'Width is not expected' );
		$this->assertEquals( $new_height, $height, 'Height is not expcted' );
	
	}
	
	function testCropLargerThanSingleDimention() {
	
		$path = dirname( __FILE__ ) . '/images/google.png';
		list( $width, $height ) = getimagesize( $path );
		
		$this->assertNotNull( $width );
		$this->assertNotNull( $height );
				
		$image = new WP_Thumb( $path, "width=200&height=300&crop=1&resize=0&cache=0&return=path" );
		
		$file = $image->returnImage();
		
		$this->assertContains( '/cache/', $file );
		$this->assertContains( WP_CONTENT_DIR, $file );
		
		list( $new_width, $new_height ) = getimagesize( $file );
		
		$this->assertEquals( $new_width, 200, 'Width is not expected' );
		$this->assertEquals( $new_height, $height, 'Height is not expcted' );
	
	}

}