<?php

if ( !class_exists( 'CPK_WPCSV_Log_Model' ) ) {

class CPK_WPCSV_Log_Model {

	private $db;
	private $table_name = '';
	private $messages = Array( );

	const TABLE_SUFFIX = 'cpk_wpcsv_log';

	public function __construct( ) {
		global $wpdb;
		$this->db = $wpdb;

		$this->table_name = $this->db->prefix . self::TABLE_SUFFIX;
		if ( !$this->table_exists( $this->table_name ) ) {
			$this->create_table( $this->table_name );
		}
	}

	private function table_exists( $name ) {
		$sql = "SHOW TABLES LIKE '{$this->table_name}'";
		return (boolean)$this->db->get_results( $sql );
	}

	private function create_table( $name ) {
		$sql =	"
			CREATE TABLE IF NOT EXISTS `{$name}` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`category` varchar(255) NOT NULL,
			`msg` varchar(255) NOT NULL,
			`data` text NULL,
			`created` DATETIME NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;
			";

		$this->db->query( $sql );
	}

	public function empty_table( ) {
		$sql =	"TRUNCATE TABLE `{$this->table_name}`";
		$this->db->query( $sql );
	}	
	
	public function drop_table( ) {
		$sql =	"DROP TABLE `{$this->table_name}`";
		$this->db->query( $sql );
	}
	
	public function add_message( $message, $category = 'Report', $data = NULL  ) {
		$message = Array(
			'msg' => $message,
			'category' => $category,
			'data' => isset( $data ) ? print_r( $data, TRUE ) : ''
		);
		$this->messages[] = $message;
	}

	public function store_messages( ) {
		if ( empty( $this->messages ) ) return;
		if ( is_array( $this->messages ) && !empty( $this->messages ) ) {
			$date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

			if ( function_exists( 'mysqli_connect' ) ) {
				$host_parts = explode( ':', DB_HOST );
				if ( count( $host_parts ) == 2 ) {
					list( $db_host, $db_port ) = $host_parts;
				} else {
					$db_host = $host_parts[0];
					$db_port = 3306;
				}
				$link = mysqli_connect( $db_host, DB_USER, DB_PASSWORD, DB_NAME, (int)$db_port );
				mysqli_set_charset( $link, 'utf8mb4' );
				foreach( $this->messages as $message ) {
					extract( $message );
					$msg = mysqli_real_escape_string( $link, $msg );
					$data = mysqli_real_escape_string( $link, $data );
					$values[] = "( '{$msg}', '{$category}', '{$data}', '{$date}' )";	
				} # End foreach
			} else {
				foreach( $this->messages as $message ) {
					extract( $message );
					$msg = mysql_real_escape_string( $msg, $this->db->dbh );
					$data = mysql_real_escape_string( $data, $this->db->dbh );
					$values[] = "( '{$msg}', '{$category}', '{$data}', '{$date}' )";	
				} # End foreach
			}

		} # End if

		$values_sql = implode( ',', $values );

		$sql = "INSERT INTO {$this->table_name} ( `msg`, `category`, `data`, `created` ) VALUES {$values_sql}";
		
		$this->db->query( $sql );

		$this->messages = Array( );
	}

	public function get_message_list( $category = NULL, $limit = NULL ) {
		$conditions[] = ( isset( $category ) ) ? "WHERE category = '{$category}'" : NULL;
		$conditions[] = ( isset( $limit ) ) ? "LIMIT {$limit}" : NULL;

		$conditions_sql = ( !empty( $conditions ) ) ? implode( ' ', $conditions ) : NULL;

		$sql =	"SELECT msg, category, data FROM {$this->table_name} {$conditions_sql}";

		return $this->db->get_results( $sql );
	}

	public function get_count( ) {
		$sql =	"SELECT COUNT(*) FROM {$this->table_name}";
		return $this->db->get_var( $sql );
	}

} # End class CPK_WPCSV_Log_Model

} # End if
