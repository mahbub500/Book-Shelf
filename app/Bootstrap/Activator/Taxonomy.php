<?php
namespace EasyCommerce\Bootstrap\Activator;

defined( 'ABSPATH' ) || exit;

class Taxonomy {

	public function register() {
		$category_labels = [
			'name'              => _x( 'Categories', 'taxonomy general name', 'easycommerce' ),
			'singular_name'     => _x( 'Category', 'taxonomy singular name', 'easycommerce' ),
			'search_items'      => __( 'Search Categories', 'easycommerce' ),
			'all_items'         => __( 'All Categories', 'easycommerce' ),
			'parent_item'       => __( 'Parent Category', 'easycommerce' ),
			'parent_item_colon' => __( 'Parent Category:', 'easycommerce' ),
			'edit_item'         => __( 'Edit Category', 'easycommerce' ),
			'update_item'       => __( 'Update Category', 'easycommerce' ),
			'add_new_item'      => __( 'Add New Category', 'easycommerce' ),
			'new_item_name'     => __( 'New Category Name', 'easycommerce' ),
			'menu_name'         => __( 'Categories', 'easycommerce' ),
		];

		$category_args = [
			'hierarchical'      => true,
			'labels'            => $category_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'product-cat' ],
			'show_in_rest'      => true,
		];

		register_taxonomy( 'product_cat', [ 'product' ], $category_args );
		
		$brand_labels = [
			'name'              => _x( 'Brands', 'taxonomy general name', 'easycommerce' ),
			'singular_name'     => _x( 'Brand', 'taxonomy singular name', 'easycommerce' ),
			'search_items'      => __( 'Search Brands', 'easycommerce' ),
			'all_items'         => __( 'All Brands', 'easycommerce' ),
			'parent_item'       => __( 'Parent Brand', 'easycommerce' ),
			'parent_item_colon' => __( 'Parent Brand:', 'easycommerce' ),
			'edit_item'         => __( 'Edit Brand', 'easycommerce' ),
			'update_item'       => __( 'Update Brand', 'easycommerce' ),
			'add_new_item'      => __( 'Add New Brand', 'easycommerce' ),
			'new_item_name'     => __( 'New Brand Name', 'easycommerce' ),
			'menu_name'         => __( 'Brands', 'easycommerce' ),
		];

		$brand_args = [
			'hierarchical'      => true,
			'labels'            => $brand_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'product-brand' ],
			'show_in_rest'      => true,
		];

		register_taxonomy( 'product_brand', [ 'product' ], $brand_args );
	}
}