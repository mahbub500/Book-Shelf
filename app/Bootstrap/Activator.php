<?php
namespace BookShelf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use BookShelf\Trait\Hook;

class Activator {

	use Hook;

	/**
	 * Static method for plugin activation tasks.
	 */
	public static function activate() {
	    $activator = new self();

	    // $activator->set_cron();
	    // $activator->register_roles();
	    $activator->register_post_types();
	    $activator->register_taxonomies();
	    $activator->register_thumbnails();
	    
	    // Set a flag that indicates the plugin has been activated
	    update_option( 'book_shelf_activated', true );
	}

	public function set_cron() {
		// code...
	}

	public function register_roles() {
		$this->action( 'init', [ new Activator\User_Role, 'register' ] );
	}

	public function register_post_types() {
		$this->action( 'init', [ new Activator\Post_Type, 'register' ] );
	}

	public function register_taxonomies() {
		$this->action( 'init', [ new Activator\Taxonomy, 'register' ] );
	}

	public function register_thumbnails() {
		$this->action( 'after_setup_theme', [ new Activator\Thumbnail, 'register' ] );
	}

}