<?php
namespace EasyCommerce\Bootstrap\Activator;

defined( 'ABSPATH' ) || exit;

class Post_Type {

	public function register() {
		$labels = [
			'name'               => _x( 'Products', 'post type general name', 'easycommerce' ),
			'singular_name'      => _x( 'Product', 'post type singular name', 'easycommerce' ),
			'menu_name'          => _x( 'Products [temp]', 'admin menu', 'easycommerce' ),
			'name_admin_bar'     => _x( 'Product', 'add new on admin bar', 'easycommerce' ),
			'add_new'            => _x( 'Add New', 'product', 'easycommerce' ),
			'add_new_item'       => __( 'Add New Product', 'easycommerce' ),
			'new_item'           => __( 'New Product', 'easycommerce' ),
			'edit_item'          => __( 'Edit Product', 'easycommerce' ),
			'view_item'          => __( 'View Product', 'easycommerce' ),
			'all_items'          => __( 'Products [temp]', 'easycommerce' ),
			'search_items'       => __( 'Search Products', 'easycommerce' ),
			'parent_item_colon'  => __( 'Parent Products:', 'easycommerce' ),
			'not_found'          => __( 'No products found.', 'easycommerce' ),
			'not_found_in_trash' => __( 'No products found in Trash.', 'easycommerce' )
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			// 'show_in_menu'       => 'store',
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'products' ],
			'capability_type'    => 'page',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ],
			'show_in_rest'       => true,
		];

		register_post_type( easycommerce_product_post_type(), $args );
	}

}