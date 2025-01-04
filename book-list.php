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

// Define the table creation function
function book_list_create_tables() {
    global $wpdb;

    // Charset Collate for the tables
    $charset_collate = $wpdb->get_charset_collate();

    // Table for Authors
    $table_name_authors = $wpdb->prefix . 'authors';
    $sql_authors = "CREATE TABLE $table_name_authors (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate ENGINE=InnoDB;";

    // Table for Publishers
    $table_name_publishers = $wpdb->prefix . 'publishers';
    $sql_publishers = "CREATE TABLE $table_name_publishers (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate ENGINE=InnoDB;";

    // Table for Book Metadata
    $table_name_meta = $wpdb->prefix . 'book_meta';
    $sql_meta = "CREATE TABLE $table_name_meta (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        book_id BIGINT(20) UNSIGNED NOT NULL,
        author_id BIGINT(20) UNSIGNED NOT NULL,
        publisher_id BIGINT(20) UNSIGNED NOT NULL,
        isbn_number VARCHAR(20) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (book_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE,
        FOREIGN KEY (author_id) REFERENCES {$wpdb->prefix}authors(id) ON DELETE CASCADE,
        FOREIGN KEY (publisher_id) REFERENCES {$wpdb->prefix}publishers(id) ON DELETE CASCADE
    ) $charset_collate ENGINE=InnoDB;";

    // Execute table creation
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_authors);
    dbDelta($sql_publishers);
    dbDelta($sql_meta);  

    flush_rewrite_rules();
}


// Hook to create tables upon plugin activation
register_activation_hook(__FILE__, 'book_list_create_tables');

// Admin-related class for managing the plugin
class BookList_Admin {

    // Constructor to initialize actions
    public function __construct() {
        if (is_admin()) {
            add_action('init', [$this, 'register_post_type']);
            add_action('admin_menu', [$this, 'register_admin_pages']);
            add_action('save_post', [$this, 'save_book_meta'], 10, 3);
            add_action('admin_post_add_author', [$this, 'add_author']);
            add_action('admin_post_add_publisher', [$this, 'add_publisher']);
            add_action('before_delete_post', [$this, 'delete_book_meta']);
            add_action('admin_post_delete_book', [$this, 'delete_book']);
            add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        }
    }

public function add_meta_boxes() {
    add_meta_box(
        'book_meta_box',            // ID of the meta box
        __('Book Metadata', 'book-list'),  // Title of the meta box
        [$this, 'render_meta_box'],  // Callback to render the content of the meta box
        'book',                      // Post type where it will appear
        'normal',                    // Position
        'high'                       // Priority
    );
}

public function render_meta_box($post) {
    global $wpdb;
    $book_meta = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}book_meta WHERE book_id = %d", $post->ID));
    $authors = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}authors");
    $publishers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}publishers");

    ?>
    <p><label for="author_id"><?php _e('Author', 'book-list'); ?></label></p>
    <select name="author_id" id="author_id" class="regular-text">
        <option value=""><?php _e('Select Author', 'book-list'); ?></option>
        <?php foreach ($authors as $author) {
            echo '<option value="' . esc_attr($author->id) . '" ' . selected($book_meta->author_id, $author->id, false) . '>' . esc_html($author->name) . '</option>';
        } ?>
    </select>

    <p><label for="publisher_id"><?php _e('Publisher', 'book-list'); ?></label></p>
    <select name="publisher_id" id="publisher_id" class="regular-text">
        <option value=""><?php _e('Select Publisher', 'book-list'); ?></option>
        <?php foreach ($publishers as $publisher) {
            echo '<option value="' . esc_attr($publisher->id) . '" ' . selected($book_meta->publisher_id, $publisher->id, false) . '>' . esc_html($publisher->name) . '</option>';
        } ?>
    </select>

    <p><label for="isbn_number"><?php _e('ISBN Number', 'book-list'); ?></label></p>
    <input type="text" name="isbn_number" id="isbn_number" value="<?php echo esc_attr($book_meta->isbn_number); ?>" class="regular-text">

    <p><label for="price"><?php _e('Price', 'book-list'); ?></label></p>
    <input type="text" name="price" id="price" value="<?php echo esc_attr($book_meta->price); ?>" class="regular-text">
    
    <?php
    wp_nonce_field('book_list_nonce_action', 'book_list_nonce');
}




    public function delete_book() {
    if (isset($_GET['book_id']) && is_numeric($_GET['book_id'])) {
        $book_id = intval($_GET['book_id']);
        
        // Ensure the user has the required permissions
        if (current_user_can('delete_post', $book_id)) {

            // Delete book metadata from the wp_book_meta table
            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'book_meta', ['book_id' => $book_id]);

            // Delete the book post
            wp_delete_post($book_id, true);  // `true` will forcefully delete the post

            // Redirect back to the book list page
            wp_redirect(admin_url('admin.php?page=book-list'));
            exit;
        }
    }
}

    // Register custom post type for 'book'
    public function register_post_type() {
        register_post_type('book', [
            'labels' => [
                'name' => __('Books', 'book-list'),
                'singular_name' => __('Book', 'book-list'),
            ],
            'public' => true,
            'show_ui' => false, 
            'show_in_menu' => false,
            'supports' => ['title'],
            'rewrite' => [
                'slug' => 'books',  // This defines the URL structure for the books
                'with_front' => false, // Optionally remove the "front" part from the URL if you have it
            ],
            'has_archive' => true, // Enable archive page for books (optional)
            'show_in_rest' => true, // Optional: Allow the post type to be available in the REST API
        ]);

    }

    // Register admin menus
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

    // Save book metadata when a book post is saved

    public function save_book_meta($post_id, $post, $update) {
    // Prevent saving on autosave or if it's not a 'book' post
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if ('book' !== $post->post_type) return;

    // Verify nonce (security check for form submission)
    if (!isset($_POST['book_list_nonce']) || !wp_verify_nonce($_POST['book_list_nonce'], 'book_list_nonce_action')) {
        return;
    }

    // Check if the necessary data is available in the form submission
    if (isset($_POST['author_id'], $_POST['publisher_id'], $_POST['isbn_number'], $_POST['price'])) {
        global $wpdb;

        // Prepare the metadata to save/update
        $data = [
            'book_id' => $post_id,
            'author_id' => absint($_POST['author_id']),
            'publisher_id' => absint($_POST['publisher_id']),
            'isbn_number' => sanitize_text_field($_POST['isbn_number']),
            'price' => sanitize_text_field($_POST['price']),
        ];

        // Check if metadata exists for this book, and update it if necessary
        $existing_meta = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}book_meta WHERE book_id = %d", $post_id));
        
        if ($existing_meta) {
            // Update the metadata if it exists
            $wpdb->update($wpdb->prefix . 'book_meta', $data, ['book_id' => $post_id]);
        } else {
            // Insert the new metadata if it doesn't exist
            $wpdb->insert($wpdb->prefix . 'book_meta', $data);
        }
    }
}



    // Delete book metadata when a book is deleted
    public function delete_book_meta($post_id) {
        if ('book' === get_post_type($post_id)) {
            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'book_meta', ['book_id' => $post_id]);
        }
    }

    public function process_add_book_form() {
    // Verify nonce
    if (!isset($_POST['book_list_nonce']) || !wp_verify_nonce($_POST['book_list_nonce'], 'book_list_nonce_action')) {
        return;
    }

    // Sanitize and validate input
    $book_name = sanitize_text_field($_POST['book_name']);
    $author_id = absint($_POST['author_id']);
    $isbn_number = sanitize_text_field($_POST['isbn_number']);
    $price = sanitize_text_field($_POST['price']);
    $publisher_id = absint($_POST['publisher_id']);

    // Create the book post
    $book_post = [
        'post_title' => $book_name,
        'post_type' => 'book',
        'post_status' => 'publish',
    ];

    // Insert the post and get its ID
    $book_id = wp_insert_post($book_post);

    // If the post was created successfully, insert the metadata
    if ($book_id) {
        global $wpdb;

        $data = [
            'book_id' => $book_id,
            'author_id' => $author_id,
            'publisher_id' => $publisher_id,
            'isbn_number' => $isbn_number,
            'price' => $price,
        ];

        // Insert or update the metadata
        $existing_meta = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}book_meta WHERE book_id = %d", $book_id));

        if ($existing_meta) {
            $wpdb->update($wpdb->prefix . 'book_meta', $data, ['book_id' => $book_id]);
        } else {
            $wpdb->insert($wpdb->prefix . 'book_meta', $data);
        }

        // Redirect after saving
        // wp_redirect(admin_url('admin.php?page=book-list'));
        exit;
    }
}


    // Render Add Book page
    public function render_add_book_page() {
    // Check if the form is submitted
    if (isset($_POST['submit_book'])) {
        $this->process_add_book_form();
    }
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
                        // Fetch metadata
                        $book_meta = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}book_meta WHERE book_id = %d", $book->ID));
                        $author_name = $book_meta ? $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}authors WHERE id = %d", $book_meta->author_id)) : __('Unknown', 'book-list');
                        $isbn_number = $book_meta ? $book_meta->isbn_number : __('Not Available', 'book-list');
                        $price = $book_meta ? $book_meta->price : __('Not Available', 'book-list');
                        $publisher_name = $book_meta ? $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}publishers WHERE id = %d", $book_meta->publisher_id)) : __('Unknown', 'book-list');

                        // Generate the 'View' URL
                        $view_url = get_permalink($book->ID);

                        echo '<tr>';
                        echo '<td>' . esc_html($book->post_title) . '</td>';
                        echo '<td>' . esc_html($author_name) . '</td>';
                        echo '<td>' . esc_html($isbn_number) . '</td>';
                        echo '<td>' . esc_html($price) . '</td>';
                        echo '<td>' . esc_html($publisher_name) . '</td>';
                        echo '<td>';
                        echo '<a href="' . esc_url($view_url) . '" target="_blank" class="button button-primary">' . __('View', 'book-list') . '</a> | ';
                    
                        echo '<a href="' . esc_url(get_edit_post_link($book->ID)) . '" class="button button-primary">' . __('Edit', 'book-list') . '</a> | ';

                        echo '<a href="' . esc_url(admin_url('admin-post.php?action=delete_book&book_id=' . $book->ID)) . '" onclick="return confirm(\'' . __('Are you sure you want to delete this book?', 'book-list') . '\')">' . __('Delete', 'book-list') . '</a>';
                        echo '</td>';
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

// Initialize the plugin
new BookList_Admin();

?>
