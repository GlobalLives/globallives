<h2><?php _e( 'Import Results', 'wp-csv' ); ?></h2>

<?php

if ( is_array( $info_messages ) && !empty( $info_messages ) ) {
	$message_heading = __( 'Details', 'wp-csv' );
	$info_html = "<table class='widefat'><thead><tr><th>{$message_heading}</th></tr></thead><tbody>";

	foreach( $info_messages as $message ) {
		$info_html .= "<tr><td>{$message->msg}</td></tr>";
	} # End foreach
	$info_html .= "</tbody></table>";
}

if ( is_array( $error_messages ) && !empty( $error_messages ) ) {
	$message_heading = __( 'Errors', 'wp-csv' );
	$error_html = "<table class='widefat'><thead><tr><th>{$message_heading}</th></tr></thead><tbody>";

	foreach( $error_messages as $message ) {
		if ( empty( $message->data ) ) {
			$error_html .= "<tr><td>{$message->msg}</td></tr>";
		} else {
			$error_html .= "<tr><td>{$message->data}</td></tr>";
		}
	} # End foreach
	$error_html .= "</tbody></table>";
}

if ( is_array( $warning_messages ) && !empty( $warning_messages ) ) {
	$message_heading = __( 'Warnings', 'wp-csv' );
	$warning_html = "<table class='widefat'><thead><tr><th>{$message_heading}</th></tr></thead><tbody>";

	foreach( $warning_messages as $message ) {
		if ( empty( $message->data ) ) {
			$warning_html .= "<tr><td>{$message->msg}</td></tr>";
		} else {
			$warning_html .= "<tr><td>{$message->data}</td></tr>";
		}
	} # End foreach
	$warning_html .= "</tbody></table>";
}

echo $info_html . '<br />';

if ( !empty( $error_html ) ) echo $error_html . '<br />';
if ( !empty( $warning_html ) ) echo $warning_html . '<br />';

if ( empty( $error_html ) && empty( $warning_html ) ) {
	$success_message = __( 'Imported successfully, with no errors or warnings.', 'wp-csv' );
	echo "<p><strong>{$success_message}</strong></p>";
}

?>
</tbody>
</table>
