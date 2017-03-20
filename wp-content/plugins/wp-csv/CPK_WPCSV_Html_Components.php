<?php

if ( !class_exists( 'CPK_WPCSV_Html_Components' ) ) {

class CPK_WPCSV_Html_Components {

	public function __construct( ) {
		
	}

	public function post_type_and_status_filters( $config ) {

		$fieldsets = Array( );

		if ( is_array( $config ) && !empty( $config ) ) {
			foreach( $config as $type => $statuses ) {
				$legend = ucfirst( $type );
				$html = "<fieldset><legend><input class='wpcsv-type' type='checkbox' /> {$legend}</legend>";
				
				if ( is_array( $statuses ) && !empty( $statuses ) ) {
					$count = 1;
					foreach( $statuses as $status => $enabled ) {
						$label = ucfirst( $status );
						$checked = ( $enabled ) ? ' checked' : '';
						$html .= "<div class='status'><input class='wpcsv-status' type='checkbox' name='type_status_exclude[{$type}][{$status}]'{$checked} /> <strong>{$label}</strong></div>";
					} # End foreach
				} # End if

				$html .= "</fieldset>";
				$fieldsets[] = $html;

			} # End foreach
		} # End if
		
		return implode( "\n", $fieldsets );
	}

} # End class CPK_WPCSV_Html_Components

} # End if
