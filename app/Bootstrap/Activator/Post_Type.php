<?php
namespace BookShelf\Bootstrap\Activator;

defined( 'ABSPATH' ) || exit;

class Post_Type {

	public function register() {
		$labels = [
			'name'               => _x( 'Books', 'post type general name', 'book-shelf' ),
			'singular_name'      => _x( 'Book', 'post type singular name', 'book-shelf' ),
			'menu_name'          => _x( 'Books [temp]', 'admin menu', 'book-shelf' ),
			'name_admin_bar'     => _x( 'Book', 'add new on admin bar', 'book-shelf' ),
			'add_new'            => _x( 'Add New', 'Book', 'book-shelf' ),
			'add_new_item'       => __( 'Add New Book', 'book-shelf' ),
			'new_item'           => __( 'New Book', 'book-shelf' ),
			'edit_item'          => __( 'Edit Book', 'book-shelf' ),
			'view_item'          => __( 'View Book', 'book-shelf' ),
			'all_items'          => __( 'Book [temp]', 'book-shelf' ),
			'search_items'       => __( 'Search Books', 'book-shelf' ),
			'parent_item_colon'  => __( 'Parent Books:', 'book-shelf' ),
			'not_found'          => __( 'No Books found.', 'book-shelf' ),
			'not_found_in_trash' => __( 'No Books found in Trash.', 'book-shelf' )
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			// 'show_in_menu'       => 'store',
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'books' ],
			'capability_type'    => 'page',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ],
			'show_in_rest'       => true,
		];

		register_post_type( 'book', $args );
	}

}