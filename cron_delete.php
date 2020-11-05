<?php
	$root            = $_SERVER['DOCUMENT_ROOT'];
	$files_process   = glob($root.'/qrcode/download/ebs/process/{,.}*', GLOB_BRACE); // define folder locations
	$files_unprocess = glob($root.'/qrcode/download/ebs/unprocess/{,.}*', GLOB_BRACE); // define folder locations
	$now             = time();

	// delete processed file
	foreach ($files_process as $file) {
		if (is_file($file)) {
			if ($now - filemtime($file) >= 60 * 60 * 24 * 30) { // delete processed file older than 30 days
				unlink($file);
			}
		}
	}

	// delete unprocessed file
	foreach ($files_unprocess as $file) {
		if (is_file($file)) {
			if ($now - filemtime($file) >= 60 * 60 * 24 * 2) { // delete unprocessed file older than 2 days
				unlink($file);
			}
		}
	}
?>