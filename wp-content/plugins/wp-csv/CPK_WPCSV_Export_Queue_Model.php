<?php

if ( !class_exists( 'CPK_WPCSV_Export_Queue_Model' ) ) {

class CPK_WPCSV_Export_Queue_Model {

	private $db;
	private $table_name;

	const TABLE_SUFFIX = 'cpk_wpcsv_export_queue';

	public function __construct( ) {
		global $wpdb;
		$this->db = $wpdb;

		$this->table_name = $this->db->prefix . self::TABLE_SUFFIX;
		if ( !$this->table_exists( $this->table_name ) ) {
			$this->create_table( $this->table_name );
		}
	}

	public function update_settings( $settings ) {
		$this->settings = $settings;
	}

	private function table_exists( $name ) {
		$sql = "SHOW TABLES LIKE '{$this->table_name}'";
		return (boolean)$this->db->get_results( $sql );
	}

	public function create_table( $name = NULL ) {

		if ( !isset( $name ) ) $name = $this->table_name;

		$sql =	"
			CREATE TABLE IF NOT EXISTS `{$name}` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`export_id` varchar(30) NOT NULL,
			`post_id` int(11) NOT NULL,
			`done` boolean NOT NULL DEFAULT 0,
			`msg` varchar(255) NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;
			";

		$this->db->query( $sql );
	}

	public function empty_table( $export_id = NULL ) {
		if ( empty( $export_id ) ) {
			$sql = "TRUNCATE TABLE `{$this->table_name}`";
		} else {
			$sql = "DELETE FROM `{$this->table_name}` WHERE export_id = '{$export_id}'";
		}
		$this->db->query( $sql );
	}	
	
	public function drop_table( ) {
		$sql =	"DROP TABLE `{$this->table_name}`";
		$this->db->query( $sql );
	}

	private function wrap_post_ids( &$element, $key, $export_id = NULL ) {
		$element = "('{$element}', '{$export_id}' )";
	}

	public function add_post_ids( Array $post_ids, $export_id ) {
		if ( is_array( $post_ids ) && !empty( $post_ids ) ) {
			array_walk( $post_ids, Array( $this, 'wrap_post_ids' ), $export_id );
			$post_id_sql = implode( ',', $post_ids );
			$sql = "INSERT INTO {$this->table_name} ( `post_id`, `export_id` ) VALUES {$post_id_sql}";
			$this->db->query( $sql );
		}
	}

	public function get_post_id_list( $limit = 100, $export_id ) {
		$sql =	"SELECT post_id FROM {$this->table_name} WHERE done = '0' AND export_id = '{$export_id}' LIMIT {$limit}";
		return $this->db->get_col( $sql );
	}

	public function get_count( $export_id ) {
		$sql =	"SELECT COUNT(*) FROM `{$this->table_name}` WHERE export_id = '{$export_id}'";
		return $this->db->get_var( $sql );
	}

	public function mark_done( $post_ids ) {
		if ( is_array( $post_ids ) && !empty( $post_ids ) ) {
			$post_id_list = implode( ',', $post_ids );
			$sql =	"UPDATE {$this->table_name} SET done = 1 WHERE post_id IN ( {$post_id_list} )";
			$this->db->query( $sql );
		}
	}
	
} # End class CPK_WPCSV_Export_Queue_Model

} # End if
