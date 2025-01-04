<?php
/**
 * Plugin Name: BookList
 * Description: A plugin for managing books with custom database tables for authors, publishers, and book metadata.
 * Version: 1.0.0
 * Author: Mahbub
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Create custom tables for authors, publishers, and book metadata on plugin activation
function book_list_create_tables() {
    global $wpdb;

    // Log message to confirm function is being triggered
    error_log('book_list_create_tables triggered');

    // Table for Authors
    $table_name = $wpdb->prefix . 'authors';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Table for Publishers
    $table_name_publishers = $wpdb->prefix . 'publishers';
    $sql_publishers = "CREATE TABLE $table_name_publishers (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Table for Book Meta Data
    $table_name_books_meta = $wpdb->prefix . 'book_meta';
    $sql_book_meta = "CREATE TABLE $table_name_books_meta (
        id INT(11) NOT NULL AUTO_INCREMENT,
        book_id INT(11) NOT NULL,
        author_id INT(11) NOT NULL,
        publisher_id INT(11) NOT NULL,
        isbn_number VARCHAR(20) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (book_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE,
        FOREIGN KEY (author_id) REFERENCES {$wpdb->prefix}authors(id) ON DELETE CASCADE,
        FOREIGN KEY (publisher_id) REFERENCES {$wpdb->prefix}publishers(id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    dbDelta($sql_publishers);
    dbDelta($sql_book_meta);

    // Log successful table creation
    error_log('Tables created successfully');
}

register_activation_hook(__FILE__, 'book_list_create_tables');

// Class to register the custom post type 'book'
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
            'show_ui' => false,
            'show_in_menu' => false,
        ]);
    }
}

// Admin-related class
class BookList_Admin {
    public function __construct() {
        if (is_admin()) {
            add_action('admin_menu', [$this, 'register_admin_pages']);
            add_action('save_post', [$this, 'save_book_meta'], 10, 3);
            add_action('admin_post_add_author', [$this, 'add_author']);
            add_action('admin_post_add_publisher', [$this, 'add_publisher']);
            add_action('before_delete_post', [$this, 'delete_book_meta']);
        }
    }

    // Register menus for managing books, authors, and publishers
    public function register_admin_pages() {
        // Main menu
        add_menu_page(
            __('Book List', 'book-list'),
            __('Book List', 'book-list'),
            'manage_options',
            'book-list',
            [$this, 'render_book_list_page'],
            'dashicons-book-alt'
        );

        // Submenus
        add_submenu_page(
            'book-list',
            __('Add Book', 'book-list'),
            __('Add Book', 'book-list'),
            'manage_options',
            'book-list-show',
            [$this, 'render_add_book_page']
        );

        add_submenu_page(
            'book-list',
            __('Manage Authors', 'book-list'),
            __('Manage Authors', 'book-list'),
            'manage_options',
            'book-list-authors',
            [$this, 'render_author_page']
        );

        add_submenu_page(
            'book-list',
            __('Manage Publishers', 'book-list'),
            __('Manage Publishers', 'book-list'),
            'manage_options',
            'book-list-publishers',
            [$this, 'render_publisher_page']
        );
    }

    // Save Book Meta Data
    public function save_book_meta($post_id, $post, $update) {
        if (!isset($_POST['book_list_nonce']) || !wp_verify_nonce($_POST['book_list_nonce'], 'book_list_nonce_action')) {
            return;
        }

        if ($post->post_type === 'book') {
            global $wpdb;

            // Check if metadata already exists
            $existing_meta = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}book_meta WHERE book_id = %d", $post_id));

            // Prepare data
            $data = [
                'book_id' => $post_id,
                'author_id' => absint($_POST['author_id']),
                'publisher_id' => absint($_POST['publisher_id']),
                'isbn_number' => sanitize_text_field($_POST['isbn_number']),
                'price' => sanitize_text_field($_POST['price']),
            ];

            if ($existing_meta) {
                // Update existing record
                $wpdb->update(
                    $wpdb->prefix . 'book_meta',
                    $data,
                    ['book_id' => $post_id]
                );
            } else {
                // Insert new record
                $wpdb->insert(
                    $wpdb->prefix . 'book_meta',
                    $data
                );
            }
        }
    }

    // Delete Book Metadata when the book is deleted
    public function delete_book_meta($post_id) {
        if ('book' === get_post_type($post_id)) {
            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'book_meta', ['book_id' => $post_id]);
        }
    }

    // Render Add Book Page with author and publisher dropdowns
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
                        <th><label for="author_id"><?php _e('Author', 'book-list'); ?></label></th>
                        <td>
                            <select id="author_id" name="author_id" class="regular-text" required>
                                <option value=""><?php _e('Select Author', 'book-list'); ?></option>
                                <?php
                                global $wpdb;
                                $authors = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}authors");
                                foreach ($authors as $author) {
                                    echo '<option value="' . esc_attr($author->id) . '">' . esc_html($author->name) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
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
                        <th><label for="publisher_id"><?php _e('Publisher', 'book-list'); ?></label></th>
                        <td>
                            <select id="publisher_id" name="publisher_id" class="regular-text" required>
                                <option value=""><?php _e('Select Publisher', 'book-list'); ?></option>
                                <?php
                                $publishers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}publishers");
                                foreach ($publishers as $publisher) {
                                    echo '<option value="' . esc_attr($publisher->id) . '">' . esc_html($publisher->name) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit_book" class="button-primary" value="<?php _e('Add Book', 'book-list'); ?>">
                </p>
            </form>
        </div>
        <?php
    }

    // Render Book List Page with author and publisher data
   // Render Book List Page with author and publisher data
public function render_book_list_page() {
    global $wpdb;
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
                        // Fetch metadata from the new book_meta table
                        $book_meta = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}book_meta WHERE book_id = %d", $book->ID));

                        // Check if metadata exists before accessing properties
                        $author_name = '';
                        $isbn_number = '';
                        $price = '';
                        $publisher_name = '';

                        if ($book_meta) {
                            // Fetch author name
                            $author_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}authors WHERE id = %d", $book_meta->author_id));

                            // Fetch publisher name
                            $publisher_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}publishers WHERE id = %d", $book_meta->publisher_id));

                            // Fetch ISBN and price from book_meta
                            $isbn_number = $book_meta->isbn_number;
                            $price = $book_meta->price;
                        }

                        echo '<tr>';
                        echo '<td>' . esc_html($book->post_title) . '</td>';
                        echo '<td>' . esc_html($author_name ? $author_name : __('Unknown', 'book-list')) . '</td>';
                        echo '<td>' . esc_html($isbn_number ? $isbn_number : __('Not Available', 'book-list')) . '</td>';
                        echo '<td>' . esc_html($price ? $price : __('Not Available', 'book-list')) . '</td>';
                        echo '<td>' . esc_html($publisher_name ? $publisher_name : __('Unknown', 'book-list')) . '</td>';
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


    // Add new author
    public function add_author() {
        if (!empty($_POST['author_name'])) {
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'authors',
                ['name' => sanitize_text_field($_POST['author_name'])]
            );
        }
        wp_redirect(admin_url('admin.php?page=book-list-authors'));
        exit;
    }

    // Add new publisher
    public function add_publisher() {
        if (!empty($_POST['publisher_name'])) {
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'publishers',
                ['name' => sanitize_text_field($_POST['publisher_name'])]
            );
        }
        wp_redirect(admin_url('admin.php?page=book-list-publishers'));
        exit;
    }

    // Render Author Management Page
    public function render_author_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Manage Authors', 'book-list'); ?></h1>
            <form method="post" action="admin-post.php">
                <input type="hidden" name="action" value="add_author">
                <input type="text" name="author_name" placeholder="<?php _e('Enter Author Name', 'book-list'); ?>" required />
                <input type="submit" value="<?php _e('Add Author', 'book-list'); ?>" class="button-primary" />
            </form>
            <h2><?php _e('Authors List', 'book-list'); ?></h2>
            <ul>
                <?php
                global $wpdb;
                $authors = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}authors");
                foreach ($authors as $author) {
                    echo '<li>' . esc_html($author->name) . '</li>';
                }
                ?>
            </ul>
        </div>
        <?php
    }

    // Render Publisher Management Page
    public function render_publisher_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Manage Publishers', 'book-list'); ?></h1>
            <form method="post" action="admin-post.php">
                <input type="hidden" name="action" value="add_publisher">
                <input type="text" name="publisher_name" placeholder="<?php _e('Enter Publisher Name', 'book-list'); ?>" required />
                <input type="submit" value="<?php _e('Add Publisher', 'book-list'); ?>" class="button-primary" />
            </form>
            <h2><?php _e('Publishers List', 'book-list'); ?></h2>
            <ul>
                <?php
                global $wpdb;
                $publishers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}publishers");
                foreach ($publishers as $publisher) {
                    echo '<li>' . esc_html($publisher->name) . '</li>';
                }
                ?>
            </ul>
        </div>
        <?php
    }
}

// Initialize the BookList plugin
new BookList_Common();
new BookList_Admin();

?>
