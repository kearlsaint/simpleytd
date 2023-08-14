<?php

if(isset($_GET['url'])) {
	$url = $_GET['url'];
	header('Location: '.$_GET['url']);die;
	$fp = fopen($url, 'rb');
	foreach (get_headers($url) as $header) {
		if(stripos($header, 'attachment') > -1) {
			header('Content-Disposition: attachment; filename="'.$_GET['filename'].'.'.$_GET['filetype'].'"');
		} else {
			header($header);
		}
	}
	fpassthru($fp);
	exit;
}

?>
