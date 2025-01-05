<?php
namespace BookShelf\Controller\Common;
use BookShelf\Trait\Hook;
use BookShelf\Helper\Utility;

defined('ABSPATH') || exit;

class Assets {
    use Hook;


    /**
     * Constructor to add hooks for enqueuing scripts and styles.
     */
    public function __construct() {
        $this->action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        $this->action('wp_enqueue_scripts', [$this, 'public_enqueue_admin_assets']);
    }

    /**
     * Enqueue admin JS and CSS files.
     */
    public function enqueue_admin_assets($hook) {

        if ( is_admin() ) {
            // Path to your CSS and JS files
            $css_file = BOOKSHELF_ASSETS_URL . 'Admin/css/admin.css';
            $js_file = BOOKSHELF_ASSETS_URL . 'Admin/js/admin.js';  

            // Enqueue the CSS file
            wp_enqueue_style('bookshelf-admin-css', $css_file, [], '1.0.0');

            // Enqueue the JS file
            wp_enqueue_script('bookshelf-admin-js', $js_file, ['jquery'], '1.0.0', true);

            // Optionally, pass data to the JS file
            wp_localize_script('bookshelf-admin-js', 'bookshelfData', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('bookshelf_admin_nonce'),
            ]);
        }
        
    }

    /**
     * Enqueue Public JS and CSS files.
     */

    public function public_enqueue_admin_assets(){
        if ( ! is_admin() ) {
           // Path to your CSS and JS files
            $css_file = BOOKSHELF_ASSETS_URL . 'Public/css/public.css';
            $js_file = BOOKSHELF_ASSETS_URL . 'Public/js/public.js';  

            // Enqueue the CSS file
            wp_enqueue_style('bookshelf-public-css', $css_file, [], '1.0.0');

            // Enqueue the JS file
            wp_enqueue_script('bookshelf-public-js', $js_file, ['jquery'], '1.0.0', true);

            // Optionally, pass data to the JS file
            wp_localize_script('bookshelf-public-js', 'bookshelfPublicData', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('bookshelf_public_nonce'),
            ]);
        }        
    }
}

