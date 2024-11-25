<?php
namespace BookShelf\Controller\Common;

defined( 'ABSPATH' ) || exit;

use BookShelf\Trait\Hook;
use BookShelf\Helper\Utility;
use BookShelf\Trait\Asset as Asset_trait;


class Asset {

	use Hook;
	use Asset_trait;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {

		$this->action( 'wp_enqueue_scripts', [ $this, 'add_assets' ] );
		// $this->action( 'admin_enqueue_scripts', [ $this, 'add_assets' ] );
	}

	public function add_assets(){
		$this->enqueue_script(
				'book_shelf_tailwind',
				BOOKSHELF_PLUGIN_URL . 'build/tailwind.bundle.js'
			);
	}





	
}