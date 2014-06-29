<?php

function truncate( $text, $limit = 75, $ellipsis = '&hellip;') {
	$words = preg_split("/[\n\r\t ]+/", $text, $limit + 1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_OFFSET_CAPTURE);
	if (count($words) > $limit) {
		end($words); //ignore last element since it contains the rest of the string
		$last_word = prev($words);
		$text =  substr($text, 0, $last_word[1] + strlen($last_word[0])) . $ellipsis;
	}
	return $text;
}

function create_zip( $files = array(), $destination = '', $overwrite = false ) {
	if (file_exists($destination) && !$overwrite) { return false; }

	$valid_files = array();

	if (is_array($files)) {
		foreach($files as $file) {
			if(file_exists($file)) {
				$valid_files[] = $file;
			}
		}
	}

	if(count($valid_files)) {

		$zip = new ZipArchive();
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
			return false;
		}

		foreach($valid_files as $file) {
			$zip->addFile($file,$file);
		}

		$zip->close();

		return file_exists($destination);
	}
	else
	{
		return false;
	}
}