<?php
namespace BookShelf\Bootstrap\Activator;

defined( 'ABSPATH' ) || exit;

class Thumbnail {

    /**
     * Register thumbnails
     */
    public function register() {

    	// Add theme support for Post Thumbnails
    	add_theme_support( 'post-thumbnails' );

        // add_image_size( 'book-gallery-image', 800, 800 );
        add_image_size( 'book-gallery-thumbnail', 200, 200, true );
    }
}