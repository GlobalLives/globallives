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