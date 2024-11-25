<?php
namespace BookShelf\Controller\Public;

defined( 'ABSPATH' ) || exit;

use BookShelf\Trait\Hook;
use BookShelf\Helper\Utility;


class Common {

	use Hook;


	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		// $this->shortcode( 'book-list', [ $this, 'dashboard' ] );
	}

	public function dashboard( $atts ) {
    return ' Test';
}

	

}