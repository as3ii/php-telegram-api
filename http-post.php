<?php

echo "<br />Libreria http-post creata da @as3ii";

function http_post($url,$args) {
	// Create the context for the request
	$context = stream_context_create(array(
		'http' => array(
		// http://www.php.net/manual/en/context.http.php
		'method' => 'POST',
		'header' => 'Content-Type: application/json',
		'content' => $args
		)));

	// Send the request
	$response = file_get_contents($url, false, $context);
	return($response);
}

?>

