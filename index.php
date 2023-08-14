<?php
set_time_limit(300);
@$token = md5(urlencode($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['SCRIPT_NAME']));

if(isset($_POST['token'])) {
	/*if($_POST['token'] != $token) {
		header("Location: index.php");
		die('Fucking with me, eh?');
	}*/
	if(isset($_POST['multi'])) {
		// separate into array
		$links = explode("\n", urldecode($_POST['multi']));
		// remove empty shits
		$links = array_filter($links, '_multi_check'); // "If no callback is supplied, all entries of input equal to FALSE will be removed."
		// reorganize index
		$links = array_map('trim', $links);
		if(count($links) <= 0) {
			// no urls? wtf?
			header("Location: index.php");
			die("No Youtube urls detected!");
		}
		// get data for each link
		$data = array();
		$count = 0;
		foreach($links as $link) {
			$data[] = getData($link);
			$count++;
			if($count>=3) break;
		}
	} else if(isset($_POST['fragment']) && isset($_POST['link'])) {
		$data = getData(trim($_POST['link']));
		die(json_encode($data));
	}
}

function _multi_check($str) {
	return (strpos($str, 'youtube.com/watch?v=')!==false
			 || strpos($str, 'youtube.com')!==false
	);
}
function getData($link) {
	$html = file_get_contents('http://www.save-video.com/download.php?url=' . urlencode($link));
	$title = stristr($html, '</title>', true);
	$title = stristr($title, '<title> Save-Video.com ');
	$title = substr($title, 34);
	$title = str_ireplace(' Video in HD Quality and convert to Mp3','', $title);
	$html = stristr($html, '<div class="sv-download-links">');
	$html = stristr($html, '</ul>', true);
	$results = array();
	
	// get mp3
	{
		$url = urlencode($link);
		$conn = curl_init(); // initialize new cUrl connection
		curl_setopt( $conn, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36"); // user agent
		curl_setopt( $conn, CURLOPT_TIMEOUT, 15); // time-out
		curl_setopt( $conn, CURLOPT_RETURNTRANSFER, TRUE); // wait for transfer
		curl_setopt( $conn, CURLOPT_HEADER, TRUE); // send request header
		curl_setopt( $conn, CURLOPT_FOLLOWLOCATION, TRUE); // follow redirects
		curl_setopt( $conn, CURLOPT_POST, TRUE); // post
		curl_setopt( $conn, CURLOPT_POSTFIELDS, "url=".$url."&format=mp3&quality=1"); // data to post
		curl_setopt( $conn, CURLOPT_URL, "http://convert2mp3.net/en/index.php?p=convert");
		curl_setopt( $conn, CURLOPT_REFERER, "http://convert2mp3.net/en/index.php");
		
		$data = curl_exec($conn); // execute
		$next = between($data, 'id="convertFrame" src="', '" style="width: 100%; height: 200px;');
		
		curl_setopt( $conn, CURLOPT_POST, FALSE); // post
		curl_setopt( $conn, CURLOPT_URL, $next);
		$data = curl_exec($conn); // execute
		$next = between($data, '<br /><br /><a href="', '" target="_parent">');

		curl_setopt( $conn, CURLOPT_URL, $next);
		$data = curl_exec($conn); // execute
		$next = between($data, '<form class="form-horizontal" action="index.php?p=complete', '" method="post">');

		curl_setopt( $conn, CURLOPT_URL, 'http://convert2mp3.net/en/index.php?p=complete'.$next);
		$data = curl_exec($conn); // execute
		
		$download_link = between($data, '<a class="btn btn-success btn-large" href="', '"><i class="icon-download">');
		$download_name = between($data, '" data-filename="', '" class="dropbox-saver">');
		
		if($download_link=='') {
			// do nothing
		} else {
			//$results[] = array('filetype' => 'mp3', 'type' => 'MP3 (192kbps)', 'url' => 'download.php?url='.urlencode($download_link));
			$results[] = array('filetype' => 'mp3', 'type' => 'MP3 (192kbps)', 'url' => $download_link);
		}
		
	}
	
	while($html = substr(stristr($html, 'href="'), 6)) {
		$tmp2 = stristr($html, '</a></li>', true);
		$tmp3 = stristr($tmp2, '">', true);
		$tmp2 = substr(stristr($tmp2, '">'), 2);
		$tmp3 = str_ireplace('generate.php?url=', '', $tmp3);
		$tmp3 = urldecode($tmp3);
		if(stripos($tmp2, 'mp3')===false) {
			if(stripos($tmp2, 'mp4') > -1) $ft = 'mp4';
			if(stripos($tmp2, 'web') > -1) $ft = 'webm';
			if(stripos($tmp2, 'flv') > -1) $ft = 'flv';
			if(stripos($tmp2, '3gp') > -1) $ft = '3gp';
			
			if(stripos($tmp3, 'downloadfile') > -1) {
				$htmlx = file_get_contents('http://www.save-video.com/'.$tmp3);
				$htmlx = between($htmlx, 'href="GetFile.php/', '">Click here');
				//$results[] = array('filetype' => $ft, 'type' => $tmp2, 'url' => 'download.php?url='.urlencode('http://www.save-video.com/GetFile.php/'.$htmlx));
				$results[] = array('filetype' => $ft, 'type' => $tmp2, 'url' => 'http://www.save-video.com/GetFile.php/'.$htmlx);
			} else {
				//$results[] = array('filetype' => $ft, 'type' => $tmp2, 'url' => 'download.php?url='.urlencode($tmp3));
				$results[] = array('filetype' => $ft, 'type' => $tmp2, 'url' => $tmp3);
			}
			
		}
	}
	return array('title' => $title, 'link' => $link, 'data' => $results);
}

function between( $string=null, $first=null, $last=null ){
	if( $string == null || $string == '' || $first == null || $first == '' || $last == null || $last == '' ) return '';
	return stristr(substr(stristr( $string , $first ), strlen($first)) , $last ,true);
}

for($i=0; $i<10; $i++) echo "\n";?>
<!--		HEY FUCKER,
<?php for($i=0; $i<15; $i++) echo "\n";?>
                                       .....'',;;::cccllllllllllllcccc:::;;,,,''...'',,'..
                            ..';cldkO00KXNNNNXXXKK000OOkkkkkxxxxxddoooddddddxxxxkkkkOO0XXKx:.
                      .':ok0KXXXNXK0kxolc:;;,,,,,,,,,,,;;,,,''''''',,''..              .'lOXKd'
                 .,lx00Oxl:,'............''''''...................    ...,;;'.             .oKXd.
              .ckKKkc'...'',:::;,'.........'',;;::::;,'..........'',;;;,'.. .';;'.           'kNKc.
           .:kXXk:.    ..       ..................          .............,:c:'...;:'.         .dNNx.
          :0NKd,          .....''',,,,''..               ',...........',,,'',,::,...,,.        .dNNx.
         .xXd.         .:;'..         ..,'             .;,.               ...,,'';;'. ...       .oNNo
         .0K.         .;.              ;'              ';                      .'...'.           .oXX:
        .oNO.         .                 ,.              .     ..',::ccc:;,..     ..                lXX:
       .dNX:               ......       ;.                'cxOKK0OXWWWWWWWNX0kc.                    :KXd.
     .l0N0;             ;d0KKKKKXK0ko:...              .l0X0xc,...lXWWWWWWWWKO0Kx'                   ,ONKo.
   .lKNKl...'......'. .dXWN0kkk0NWWWWWN0o.            :KN0;.  .,cokXWWNNNNWNKkxONK: .,:c:.      .';;;;:lk0XXx;
  :KN0l';ll:'.         .,:lodxxkO00KXNWWWX000k.       oXNx;:okKX0kdl:::;'',;coxkkd, ...'. ...'''.......',:lxKO:.
 oNNk,;c,'',.                      ...;xNNOc,.         ,d0X0xc,.     .dOd,           ..;dOKXK00000Ox:.   ..''dKO,
'KW0,:,.,:..,oxkkkdl;'.                'KK'              ..           .dXX0o:'....,:oOXNN0d;.'. ..,lOKd.   .. ;KXl.
;XNd,;  ;. l00kxoooxKXKx:..ld:         ;KK'                             .:dkO000000Okxl;.   c0;      :KK;   .  ;XXc
'XXdc.  :. ..    '' 'kNNNKKKk,      .,dKNO.                                   ....       .'c0NO'      :X0.  ,.  xN0.
.kNOc'  ,.      .00. ..''...      .l0X0d;.             'dOkxo;...                    .;okKXK0KNXx;.   .0X:  ,.  lNX'
 ,KKdl  .c,    .dNK,            .;xXWKc.                .;:coOXO,,'.......       .,lx0XXOo;...oNWNXKk:.'KX;  '   dNX.
  :XXkc'....  .dNWXl        .';l0NXNKl.          ,lxkkkxo' .cK0.          ..;lx0XNX0xc.     ,0Nx'.','.kXo  .,  ,KNx.
   cXXd,,;:, .oXWNNKo'    .'..  .'.'dKk;        .cooollox;.xXXl     ..,cdOKXXX00NXc.      'oKWK'     ;k:  .l. ,0Nk.
    cXNx.  . ,KWX0NNNXOl'.           .o0Ooldk;            .:c;.':lxOKKK0xo:,.. ;XX:   .,lOXWWXd.      . .':,.lKXd.
     lXNo    cXWWWXooNWNXKko;'..       .lk0x;       ...,:ldk0KXNNOo:,..       ,OWNOxO0KXXNWNO,        ....'l0Xk,
     .dNK.   oNWWNo.cXK;;oOXNNXK0kxdolllllooooddxk00KKKK0kdoc:c0No        .'ckXWWWNXkc,;kNKl.          .,kXXk,
      'KXc  .dNWWX;.xNk.  .kNO::lodxkOXWN0OkxdlcxNKl,..        oN0'..,:ox0XNWWNNWXo.  ,ONO'           .o0Xk;
      .ONo    oNWWN0xXWK, .oNKc       .ONx.      ;X0.          .:XNKKNNWWWWNKkl;kNk. .cKXo.           .ON0;
      .xNd   cNWWWWWWWWKOkKNXxl:,'...;0Xo'.....'lXK;...',:lxk0KNWWWWNNKOd:..   lXKclON0:            .xNk.
      .dXd   ;XWWWWWWWWWWWWWWWWWWNNNNNWWNNNNNNNNNWWNNNNNNWWWWWNXKNNk;..        .dNWWXd.             cXO.
      .xXo   .ONWNWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWNNK0ko:'..OXo          'l0NXx,              :KK,
      .OXc    :XNk0NWXKNWWWWWWWWWWWWWWWWWWWWWNNNX00NNx:'..       lXKc.     'lONN0l.              .oXK:
      .KX;    .dNKoON0;lXNkcld0NXo::cd0NNO:;,,'.. .0Xc            lXXo..'l0NNKd,.              .c0Nk,
      :XK.     .xNX0NKc.cXXl  ;KXl    .dN0.       .0No            .xNXOKNXOo,.               .l0Xk;.
     .dXk.      .lKWN0d::OWK;  lXXc    .OX:       .ONx.     . .,cdk0XNXOd;.   .'''....;c:'..;xKXx,
     .0No         .:dOKNNNWNKOxkXWXo:,,;ONk;,,,,,;c0NXOxxkO0XXNXKOdc,.  ..;::,...;lol;..:xKXOl.
     ,XX:             ..';cldxkOO0KKKXXXXXXXXXXKKKKK00Okxdol:;'..   .';::,..':llc,..'lkKXkc.
     :NX'    .     ''            ..................             .,;:;,',;ccc;'..'lkKX0d;.
     lNK.   .;      ,lc,.         ................        ..,,;;;;;;:::,....,lkKX0d:.
    .oN0.    .'.      .;ccc;,'....              ....'',;;;;;;;;;;'..   .;oOXX0d:.
    .dN0.      .;;,..       ....                ..''''''''....     .:dOKKko;.
     lNK'         ..,;::;;,'.........................           .;d0X0kc'.
     .xXO'                                                 .;oOK0x:.
      .cKKo.                                    .,:oxkkkxk0K0xc'.
        .oKKkc,.                         .';cok0XNNNX0Oxoc,.
          .;d0XX0kdlc:;,,,',,,;;:clodkO0KK0Okdl:,'..
              .,coxO0KXXXXXXXKK0OOxdoc:,..
                        ...
												

<?php for($i=0; $i<300; $i++) echo "\n				GO FUCK YOURSELF\n\n\n\n\n\n\n";?>

-->
<!doctype html>
<html lang="en-us">
<head>
<title>SimpleYTD</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="main.css" type="text/css" rel="stylesheet">
<noscript><style>[jsonly] { display: none }</style></noscript>
</head>
<body>
	<header>
		<h1>Simple YTD</h1>
		<h5>Simple, fast &amp; ad-free</h5>
		<br><br>
	</header>
	<section>
		<?php include_once "nojs.php"; ?>
		<?php include_once "sect.php"; ?>
	</section>
	<hr>
	<footer style="text-align: right">
		<h5><a href="http://kearlsaint.github.io/">http://kearlsaint.github.io</a></h5>
		<h6>...</h6>
		<h6>BugFixes &plus; Added MP3 Support | Version 1.02 (02APR2016)</h6>
		<h6>Added viewport tag | Version 1.01 (26SEP2015)</h6>
		<h6>Initial launch | Version 1.00 (25SEP2015)</h6>
		<br><br><br>
	</footer>
	<div style="float: left">
		Thankful? Hit follow :)<br>
		<a href="https://twitter.com/kearlsaint" class="twitter-follow-button" data-show-count="false">Follow @kearlsaint</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
	</div>
<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', 'UA-57422211-2', 'auto');
	ga('send', 'pageview');

</script>
</body>
</html>