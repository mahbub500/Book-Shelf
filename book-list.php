<?php
/**
 * Plugin Name: BookList
 * Description: A plugin for managing books with custom post type and admin inputs.
 * Version: 1.0.0
 * Author: Mahbub
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class BookList_Common {
    public function __construct() {
        add_action('init', [$this, 'register_post_type']);
    }

    public function register_post_type() {
        register_post_type('book', [
            'labels' => [
                'name' => __('Books', 'book-list'),
                'singular_name' => __('Book', 'book-list'),
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'custom-fields'],
        ]);
    }
}

class BookList_Admin {
    public function __construct() {
        if (is_admin()) {
            add_action('admin_menu', [$this, 'register_admin_pages']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
            add_action('save_post', [$this, 'save_book_meta'], 10, 3);
            add_action('admin_post_delete_book', [$this, 'delete_book']);
        }
    }

    // Register two menus: Add Book and Show Books
    public function register_admin_pages() {
        add_menu_page(
            __('Book  List', 'book-list'),
            __('Book List', 'book-list'),
            'manage_options',
            'book-list',
            [$this, 'render_book_list_page'],
            'dashicons-book-alt'
        );

        add_submenu_page(
            'book-list', 
            __('Add Book', 'book-list'), 
            __('Add Book', 'book-list'), 
            'manage_options', 
            'book-list-show', 
            [$this, 'render_add_book_page']
        );
    }

    // Render Add Book Page
    public function render_add_book_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Add New Book', 'book-list'); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field('book_list_nonce_action', 'book_list_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="book_name"><?php _e('Book Name', 'book-list'); ?></label></th>
                        <td><input type="text" id="book_name" name="book_name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="author_name"><?php _e('Author Name', 'book-list'); ?></label></th>
                        <td><input type="text" id="author_name" name="author_name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="isbn_number"><?php _e('ISBN Number', 'book-list'); ?></label></th>
                        <td><input type="text" id="isbn_number" name="isbn_number" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="price"><?php _e('Price', 'book-list'); ?></label></th>
                        <td><input type="number" id="price" name="price" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="publisher"><?php _e('Publisher', 'book-list'); ?></label></th>
                        <td><input type="text" id="publisher" name="publisher" class="regular-text" required></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit_book" class="button-primary" value="<?php _e('Add Book', 'book-list'); ?>">
                </p>
            </form>
        </div>
        <?php
    }

    // Save Book Meta Data
    public function save_book_meta($post_id, $post, $update) {
        if (!isset($_POST['book_list_nonce']) || !wp_verify_nonce($_POST['book_list_nonce'], 'book_list_nonce_action')) {
            return;
        }

        if ($post->post_type === 'book') {
            if (isset($_POST['book_name'])) {
                update_post_meta($post_id, 'book_name', sanitize_text_field($_POST['book_name']));
            }
            if (isset($_POST['author_name'])) {
                update_post_meta($post_id, 'author_name', sanitize_text_field($_POST['author_name']));
            }
            if (isset($_POST['isbn_number'])) {
                update_post_meta($post_id, 'isbn_number', sanitize_text_field($_POST['isbn_number']));
            }
            if (isset($_POST['price'])) {
                update_post_meta($post_id, 'price', sanitize_text_field($_POST['price']));
            }
            if (isset($_POST['publisher'])) {
                update_post_meta($post_id, 'publisher', sanitize_text_field($_POST['publisher']));
            }
        }
    }

    // Render Book List Page
    public function render_book_list_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Book List', 'book-list'); ?></h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Book Name', 'book-list'); ?></th>
                        <th><?php _e('Author Name', 'book-list'); ?></th>
                        <th><?php _e('ISBN Number', 'book-list'); ?></th>
                        <th><?php _e('Price', 'book-list'); ?></th>
                        <th><?php _e('Publisher', 'book-list'); ?></th>
                        <th><?php _e('Actions', 'book-list'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $args = [
                        'post_type' => 'book',
                        'post_status' => 'publish',
                        'numberposts' => -1
                    ];
                    $books = get_posts($args);
                    if ($books) {
                        foreach ($books as $book) {
                            $meta = get_post_meta($book->ID);
                            echo '<tr>';
                            echo '<td>' . esc_html($book->post_title) . '</td>';
                            echo '<td>' . esc_html($meta['author_name'][0] ?? '') . '</td>';
                            echo '<td>' . esc_html($meta['isbn_number'][0] ?? '') . '</td>';
                            echo '<td>' . esc_html($meta['price'][0] ?? '') . '</td>';
                            echo '<td>' . esc_html($meta['publisher'][0] ?? '') . '</td>';
                            echo '<td><a href="' . esc_url(get_edit_post_link($book->ID)) . '">' . __('Edit', 'book-list') . '</a> | ';
                            echo '<a href="' . esc_url(admin_url('admin-post.php?action=delete_book&book_id=' . $book->ID)) . '" onclick="return confirm(\'' . __('Are you sure you want to delete this book?', 'book-list') . '\')">' . __('Delete', 'book-list') . '</a></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6">' . __('No books found.', 'book-list') . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // Delete Book
    public function delete_book() {
        if (!isset($_GET['book_id']) || !current_user_can('manage_options')) {
            return;
        }

        $book_id = absint($_GET['book_id']);
        if (get_post_type($book_id) === 'book') {
            wp_delete_post($book_id, true);
        }

        wp_redirect(admin_url('admin.php?page=book-list-show'));
        exit;
    }

    // Enqueue Admin Scripts
    public function enqueue_admin_scripts() {
        wp_enqueue_style('book-list-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    }
}

// Frontend class remains the same
class BookList_Front {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_scripts']);
    }

    public function enqueue_front_scripts() {
        wp_enqueue_style('book-list-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    }
}

// Initialize classes
new BookList_Common();
if (is_admin()) {
    new BookList_Admin();
} else {
    new BookList_Front();
}
