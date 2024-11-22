<?php
namespace BookShelf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use BOOKSHELF\Model\Database;

class Uninstaller {

    /**
     * Run uninstallation routines.
     */
    public static function uninstall() {
        $uninstaller = new self();
        $uninstaller->delete_flags();
    }

    /**
     * Remeves flags
     */
    protected function delete_flags() {
        $deletable_options = [ 'book_shelf_activated', 'book_shelf_db_version' ];
        
        foreach ( $deletable_options as $option ) {
            delete_option( $option );
        }
    }
}