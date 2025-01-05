<?php

class PersonalBookManager {
    public function __construct() {
        add_action('init', [$this, 'register_book_post_type']);
        add_action('add_meta_boxes', [$this, 'add_book_meta_boxes']);
        add_action('save_post', [$this, 'save_book_meta'], 10, 2);
        add_action('wp_ajax_edit_book', [$this, 'handle_edit_book']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function register_book_post_type() {
        $labels = [
            'name'               => 'Books',
            'singular_name'      => 'Book',
            'add_new'            => 'Add New Book',
            'add_new_item'       => 'Add New Book',
            'edit_item'          => 'Edit Book',
            'new_item'           => 'New Book',
            'view_item'          => 'View Book',
            'search_items'       => 'Search Books',
            'not_found'          => 'No books found',
            'not_found_in_trash' => 'No books found in Trash',
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => true,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-book',
            'supports'           => ['title', 'editor', 'thumbnail'],
        ];

        register_post_type('book', $args);
    }

    public function add_book_meta_boxes() {
        add_meta_box(
            'book_details',
            'Book Details',
            [$this, 'render_book_meta_box'],
            'book',
            'normal',
            'high'
        );
    }

    public function render_book_meta_box($post) {
        $author = get_post_meta($post->ID, 'book_author', true);
        $price = get_post_meta($post->ID, 'book_price', true);
        wp_nonce_field('save_book_meta', 'book_meta_nonce');
        ?>
        <p>
            <label for="book_author">Author</label><br>
            <input type="text" name="book_author" id="book_author" value="<?php echo esc_attr($author); ?>" class="widefat">
        </p>
        <p>
            <label for="book_price">Price</label><br>
            <input type="number" name="book_price" id="book_price" value="<?php echo esc_attr($price); ?>" class="widefat">
        </p>
        <?php
    }

    public function save_book_meta($post_id, $post) {
        if (!isset($_POST['book_meta_nonce']) || !wp_verify_nonce($_POST['book_meta_nonce'], 'save_book_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if ($post->post_type !== 'book') {
            return;
        }

        if (isset($_POST['book_author'])) {
            update_post_meta($post_id, 'book_author', sanitize_text_field($_POST['book_author']));
        }

        if (isset($_POST['book_price'])) {
            update_post_meta($post_id, 'book_price', sanitize_text_field($_POST['book_price']));
        }
    }

    public function handle_edit_book() {
        if (!isset($_POST['book_id'], $_POST['book_author'], $_POST['book_price'])) {
            wp_send_json_error(['message' => 'Invalid request']);
        }

        $book_id = intval($_POST['book_id']);
        $author = sanitize_text_field($_POST['book_author']);
        $price = sanitize_text_field($_POST['book_price']);

        update_post_meta($book_id, 'book_author', $author);
        update_post_meta($book_id, 'book_price', $price);

        wp_send_json_success(['message' => 'Book updated successfully']);
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_script(
            'personal-book-manager',
            plugin_dir_url(__FILE__) . 'js/admin.js',
            ['jquery'],
            '1.0',
            true
        );

        wp_localize_script('personal-book-manager', 'BookManagerAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('book_manager_nonce'),
        ]);
    }
}

// new PersonalBookManager();
