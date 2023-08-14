<?php
if(!isset($_POST['multi']) && !isset($_POST['fragment']) && !isset($_POST['token'])) {
	header("Location: index.php");
	die('An error occured!'); // for security, google why
} else {
	if($_POST['token'] != md5(urlencode($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['SCRIPT_NAME']))) {
		header("Location: index.php");
		die('Fucking with me, eh?');
	}
	if(isset($_POST['multi'])) {
		// separate into array
		$links = explode("\n", urldecode($_POST['multi']));
		// remove empty shits
		$links = array_filter($links, _multi_check); // "If no callback is supplied, all entries of input equal to FALSE will be removed."
		// reorganize index
		$links = array_map($links);
		// get data for each link
		$data = array(count($links));
		foreach($links as $link) {
			$data[] = getData($$link);
		}
	} else if(isset($_POST['fragment'])) {
		getData($link);
		
	}
}
function _multi_check($str) {
	return (strpos($str, 'https://www.youtube.com/watch?v=') || strpos($str, 'http://www.youtube.com/watch?v=') || strpos($str, 'https://m.youtube.com/watch?v=') || strpos($str, 'http://m.youtube.com/watch?v='));
}
function getData($link) {
	$html = file_get_contents('www.save-video.com/download.php?url=' . urlencode($link));
	$html = stristr($html, '<div class="sv-download-links">');
	$html = stristr($html, '</ul>', true);
	die($html);
}
?>