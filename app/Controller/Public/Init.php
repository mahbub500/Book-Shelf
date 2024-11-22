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
		$this->filter( 'admin_body_class', [ $this, 'add_body_class' ] );
		$this->action( 'wp_head', [ $this, 'head' ] );
	}

	public function head(){
		Utility::pri( 'Test' )
	}

	/**
     * Adds custom body class to the admin area.
     *
     * @param string $classes Existing body classes.
     * @return string Modified body classes with 'bookself' class added if conditions met.
     */
    public function add_body_class( $classes ) {
		global $current_screen;

    	if ( strpos( $current_screen->base, 'bookself' ) !== false || $current_screen->base == 'post' ) {
    		$classes .= ' bookself ';
    	}

		return $classes;
	}

}