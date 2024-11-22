<?php
namespace BookShelf\Bootstrap;

defined( 'ABSPATH' ) || exit;

// use BOOKSHELF\Trait\Hook;

class Activator {

	// use Hook;

	/**
	 * Static method for plugin activation tasks.
	 */
	public static function activate() {
	    $activator = new self();

	    // $activator->set_cron();
	    // $activator->register_roles();
	    // $activator->register_post_types();
	    // $activator->register_taxonomies();
	    // $activator->register_thumbnails();
	    
	    // Set a flag that indicates the plugin has been activated
	    update_option( 'book_shelf_activated', true );
	}

}