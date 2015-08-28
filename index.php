<?php
$error = false;
if (isset($_POST['input'])){
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 300);
	$input = $_POST['input'];
	$match = preg_match('`(images\.memorix\.nl/([a-z]{3})/thumb/([0-9]{2,3}x[0-9]{2,3}|detailresult)/)(.*?)\.jpg`', $input, $matches);
	if ($match){
		$institution = $matches[2];
		$id = $matches[4];
		$string = file_get_contents ('http://images.memorix.nl/'.$institution.'/topviewjson/memorix/'.$id);
		$vars = json_decode($string, true);
		$tilewidth = $vars['topviews'][0]['tileWidth'];
		$tileheight = $vars['topviews'][0]['tileHeight'];
		$layers =$vars['topviews'][0]['layers'];
		$layer = $layers[count($layers)-1];


	$image = imagecreatetruecolor($layer['width'], $layer['height']);

	for ($row=0; $row < $layer['rows']; $row++) { 
		for ($col=0; $col < $layer['cols']; $col++) { 
		 $url = 'http://images.memorix.nl/'.$institution.'/getpic/'.$id.'/';
			$url .= $layer['starttile']+$row*$layer['cols']+$col.'.jpg';
			$tile = imagecreatefromjpeg($url);
			imagecopy($image, $tile, $col * $tilewidth, $row * $tileheight, 0, 0, $tilewidth, $tileheight);
		}
	}

	$imagelink = str_replace(".", "", $_SERVER['REMOTE_ADDR']).".jpg";
	imagejpeg($image, $imagelink );

	imagedestroy($image);
	unset($image);
	}
	else{
		$error = true;
	}
}
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Dememorixer</title>
<link rel="stylesheet" type="text/css" href="view.css" media="all">
<script type="text/javascript" src="view.js"></script>
<script type="text/javascript">
function myFunction() {
	document.getElementById("wachten").style.visibility = "visible";
 document.getElementById("formulier").style.visibility = "hidden";
 document.getElementById("form_1046651").submit();
}
</script>
</head>
<body id="main_body" >
	
	<img id="top" src="top.png" alt="">
	<div id="form_container">
	
		<h1><a>Dememorixer </a></h1>
		<form id="form_1046651" class="appnitro"  method="post" target="_self">
					<div class="form_description">
								<h2>Dememorixer</h2>
			<p>Download de volle resolutie van een afbeelding die in een Memorix Maior viewer is geplaatst</p>
		</div>					
		<?php if (isset($imagelink)){
			echo "<img src='".$imagelink."'  width='500'><br/>	";
			echo "<a href='".$imagelink."' download>Download afbeelding</a>";
		}
		elseif ($error){
			echo "<p>Er is iets misgegaan</p>";
		}?>
		<ul id="wachten" style="visibility:hidden;">
			<li id="li_1" >
		<label class="description" for="element_1">WACHTEN</label>
			</ul>
			<ul id="formulier">
			
					<li id="li_1" >
		<label class="description" for="element_1">URL naar thumbnail / Insluiten informatie</label>
		<div>
<input id="input" name="input" class="element text medium" type="text" value=""/> 
		
		</div> 
		<p class="guidelines" id="guide_1"><small>ziet er uit als https://images.memorix.nl/abc/thumb/250x250/51d542ba-fe9f-8cab-3434-41a91438e94e.jpg</small></p>
		</li>
			
					<li class="buttons">
			    <input type="hidden" name="form_id" value="1046651" />
			    
				<button onclick="myFunction()">Click me</button>
		</li>
			</ul>
		</form>	
		<div id="footer">
			Created by <a href="http://www.veradekok.nl">Vera de Kok</a><br/>
			Form generated with <a href="http://www.phpform.org">pForm</a>
		</div>
	</div>
	<img id="bottom" src="bottom.png" alt="">
	</body>
</html>
