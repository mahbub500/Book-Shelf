<?php
namespace BookShelf\Controller\Admin;

defined( 'ABSPATH' ) || exit;

use BookShelf\Trait\Hook;
use BookShelf\Helper\Utility;


class Admin {

	use Hook;


	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {

		 add_action('init', [$this, 'register_post_type']);
         add_action('init', [$this, 'book_list_create_tables']);
		 add_action('admin_menu', [$this, 'register_admin_pages']);
		 add_action('save_post', [$this, 'save_book_meta'], 10, 3);
		 add_action('admin_post_add_author', [$this, 'add_author']);
		 add_action('admin_post_add_publisher', [$this, 'add_publisher']);
		 add_action('before_delete_post', [$this, 'delete_book_meta']);
		 add_action('admin_post_delete_book', [$this, 'delete_book']);

		 add_action('wp_ajax_update_book', [$this, 'handle_update_book']);		
	}



    function book_list_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Table for Authors
    $table_name_authors = $wpdb->prefix . 'authors';
    if (!table_exists($table_name_authors)) {
        $sql_authors = "CREATE TABLE $table_name_authors (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate ENGINE=InnoDB;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_authors);
    }

    // Table for Publishers
    $table_name_publishers = $wpdb->prefix . 'publishers';
    if (!table_exists($table_name_publishers)) {
        $sql_publishers = "CREATE TABLE $table_name_publishers (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate ENGINE=InnoDB;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_publishers);
    }

    // Table for Book Metadata
    $table_name_meta = $wpdb->prefix . 'book_meta';
    if (!table_exists($table_name_meta)) {
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
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_meta);
    }

    // Flush rewrite rules
    flush_rewrite_rules();

        
    }


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
                'slug' => 'books',  
                'with_front' => false, 
            ],
            'has_archive' => true, 
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
                        echo '<button class="button button-primary edit-book" data-book-id="' . esc_attr($book->ID) . '" data-author-id="' . esc_attr($book_meta->author_id) . '" data-publisher-id="' . esc_attr($book_meta->publisher_id) . '" data-isbn="' . esc_attr($book_meta->isbn_number) . '" data-price="' . esc_attr($book_meta->price) . '">' . __('Edit', 'book-list') . '</button>';
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

	    <!-- Modal HTML Structure -->
	<div id="editBookModal" class="modal">
	    <div id="modalContent">
	        <span class="close">&times;</span>
	        <h2>Edit Book Details</h2>
	        <form id="edit-book-form" method="post" action="">
	            <input type="hidden" id="modal_book_id" name="book_id">
	            <p><label for="modal_author_id">Author:</label>
	            <select id="modal_author_id" name="author_id">
	                <!-- Author options will be populated dynamically with jQuery -->
	            </select></p>

	            <p><label for="modal_publisher_id">Publisher:</label>
	            <select id="modal_publisher_id" name="publisher_id">
	                <!-- Publisher options will be populated dynamically with jQuery -->
	            </select></p>

	            <p><label for="modal_isbn_number">ISBN Number:</label>
	            <input type="text" id="modal_isbn_number" name="isbn_number"></p>

	            <p><label for="modal_price">Price:</label>
	            <input type="number" id="modal_price" name="price"></p>

	            <button type="submit" class="button-primary">Update Book</button>
	        </form>
	    </div>
	</div>
	<?php
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
                    <tr>
                        <th><label for="price"><?php _e('My Libery', 'book-list'); ?></label></th>
                        <td><input type="checkbox" id="my_libery" name="my_libery" checked class="regular-text" required></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit_book" class="button-primary" value="<?php _e('Add Book', 'book-list'); ?>">
                </p>
            </form>
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

    // Handle the AJAX request to update book details
        function handle_update_book() {
            if (!isset($_POST['form_data'])) {
                wp_send_json_error(['message' => 'Invalid data']);
            }

            parse_str($_POST['form_data'], $formData);

            // Sanitize and get form data
            $book_id = intval($formData['book_id']);
            $author_id = intval($formData['author_id']);
            $publisher_id = intval($formData['publisher_id']);
            $isbn_number = sanitize_text_field($formData['isbn_number']);
            $price = floatval($formData['price']);

            global $wpdb;

            // Update the book metadata in the database
            $update_data = [
                'author_id' => $author_id,
                'publisher_id' => $publisher_id,
                'isbn_number' => $isbn_number,
                'price' => $price
            ];

            $where = ['book_id' => $book_id];

            $updated = $wpdb->update($wpdb->prefix . 'book_meta', $update_data, $where);

            if ($updated !== false) {
                wp_send_json_success();
            } else {
                wp_send_json_error(['message' => 'Failed to update the book.']);
            }

            wp_die();  
        }	

}