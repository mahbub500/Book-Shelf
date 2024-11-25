<?php
namespace BookShelf\Controller\Public;

defined( 'ABSPATH' ) || exit;

use BookShelf\Helper\Utility;
use BookShelf\Trait\Hook;
use BookShelf\Trait\Asset;
use BookShelf\Trait\Cleaner;

class Shortcode {

	use Hook;
	use Asset;
    use Cleaner;

    /**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		
		$this->shortcode( 'bookshelf-dashboard', [ $this, 'dashboard' ] );
	}




	public function dashboard( $atts ) {

        return sprintf( '<div id="bookshelf-dashboard" class="">Hello</div>' );
		
	}
}