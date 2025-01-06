<?php 


if ( ! function_exists( 'table_exists' ) ) {
	function table_exists($table_name) {
		global $wpdb;
		$query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
		return $wpdb->get_var($query) === $table_name;
	}
}

