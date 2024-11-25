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

    public function dashboard($atts) {
    // HTML content to render the dashboard
    return sprintf(
        '
        <nav class="flex justify-center space-x-4">
          <a href="/dashboard" class="font-bold px-3 py-2 text-slate-700 rounded-lg hover:bg-slate-100 hover:text-slate-900">Home</a>
          <a href="/team" class="font-bold px-3 py-2 text-slate-700 rounded-lg hover:bg-slate-100 hover:text-slate-900">Team</a>
          <a href="/projects" class="font-bold px-3 py-2 text-slate-700 rounded-lg hover:bg-slate-100 hover:text-slate-900">Projects</a>
          <a href="/reports" class="font-bold px-3 py-2 text-slate-700 rounded-lg hover:bg-slate-100 hover:text-slate-900">Reports</a>
        </nav>
        '
    );
}

}