<?php
namespace BookShelf\Controller\Public;

defined( 'ABSPATH' ) || exit;

use BookShelf\Trait\Hook;
use BookShelf\Helper\Utility;


class Init {

	use Hook;


	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'wp_head', [ $this, 'head' ] );
	}

	public function head(){

		// Utility::pri( BOOKSHELF_PLUGIN_URL );

		// echo  '<p class="outline-2">Test</p>' ;
	}


}