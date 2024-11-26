<?php
namespace BookShelf\Bootstrap\Activator;

defined( 'ABSPATH' ) || exit;

class Taxonomy {

	public function register() {
		$category_labels = [
			'name'              => _x( 'Categories', 'taxonomy general name', 'book-shelf' ),
			'singular_name'     => _x( 'Category', 'taxonomy singular name', 'book-shelf' ),
			'search_items'      => __( 'Search Categories', 'book-shelf' ),
			'all_items'         => __( 'All Categories', 'book-shelf' ),
			'parent_item'       => __( 'Parent Category', 'book-shelf' ),
			'parent_item_colon' => __( 'Parent Category:', 'book-shelf' ),
			'edit_item'         => __( 'Edit Category', 'book-shelf' ),
			'update_item'       => __( 'Update Category', 'book-shelf' ),
			'add_new_item'      => __( 'Add New Category', 'book-shelf' ),
			'new_item_name'     => __( 'New Category Name', 'book-shelf' ),
			'menu_name'         => __( 'Categories', 'book-shelf' ),
		];

		$category_args = [
			'hierarchical'      => true,
			'labels'            => $category_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'book-cat' ],
			'show_in_rest'      => true,
		];

		register_taxonomy( 'book_cat', [ 'book' ], $category_args );
	}
}