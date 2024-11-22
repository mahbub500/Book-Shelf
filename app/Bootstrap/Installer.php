<?php
namespace BookShelf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use BOOKSHELF\Model\Database;

class Installer {

    /**
     * Run installation routines.
     */
    public static function install() {
        $installer = new self();

        if ( ! $installer->is_database_up_to_date() ) {
            // $installer->create_tables();
            $installer->update_db_version();
        }
    }

    /**
     * Check if the database is up to date.
     *
     * @return bool
     */
    protected function is_database_up_to_date() {
        $installed_ver = get_option( 'book_shelf_db_version' );
        return version_compare( $installed_ver, BOOKSHELF_VERSION, '=' );
    }

    /**
     * Create database tables from configuration file.
     */
    protected function create_tables() {
        
        global $book_shelfs;

        if( empty( $book_shelfs ) ) {
            require_once BOOKSHELF_PLUGIN_DIR . 'app/Config/tables.php';
        }

        foreach ( $book_shelfs as $table_name => $table_data ) {

            $db = new Database( $table_name );
            
            $columns = $table_data['columns'];
            $options = ! empty( $table_data['options'] ) ? $table_data['options'] : [];

            // Create the table
            $db->create_table( $columns, $options );
        }
    }

    /**
     * Update or add the database version to the options table.
     */
    protected function update_db_version() {
        update_option( 'book_shelf_db_version', BOOKSHELF_VERSION );
    }
}