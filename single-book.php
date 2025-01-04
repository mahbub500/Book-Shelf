<?php
get_header();

if (have_posts()) :
    while (have_posts()) : the_post();
        global $wpdb;
        // Get the book metadata from wp_book_meta
        $book_meta = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}book_meta WHERE book_id = %d",
            get_the_ID()
        ));

        if ($book_meta) :
            // Get author and publisher names
            $author_name = $wpdb->get_var($wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}authors WHERE id = %d",
                $book_meta->author_id
            ));
            $publisher_name = $wpdb->get_var($wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}publishers WHERE id = %d",
                $book_meta->publisher_id
            ));

            ?>
            <div class="book-details">
                <h1><?php the_title(); ?></h1>
                <p><strong><?php _e('Author: ', 'book-list'); ?></strong> <?php echo esc_html($author_name); ?></p>
                <p><strong><?php _e('ISBN: ', 'book-list'); ?></strong> <?php echo esc_html($book_meta->isbn_number); ?></p>
                <p><strong><?php _e('Price: ', 'book-list'); ?></strong> <?php echo esc_html($book_meta->price); ?></p>
                <p><strong><?php _e('Publisher: ', 'book-list'); ?></strong> <?php echo esc_html($publisher_name); ?></p>
            </div>
            <?php
        else :
            echo '<p>' . __('No book metadata found.', 'book-list') . '</p>';
        endif;

    endwhile;
else :
    echo '<p>' . __('No book found.', 'book-list') . '</p>';
endif;

get_footer();
?>
