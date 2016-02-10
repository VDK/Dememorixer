<?php
include_once('beeldbanken.php');
$regex = '`(images\.memorix|afbeeldingen\.gahetna)\.nl/([a-z\-_]{3,6})/thumb/(image(bank)?-)?([0-9]{2,3}x[0-9]{2,3}(crop)?|detailresult|gallery_thumb|mediabank-(detail|horizontal))/(.*?)\.jpg`';
$imagelink = preg_replace("`[\.:]`", "", $_SERVER['REMOTE_ADDR']).".jpg";

function generateImage($imagelink, $institution, $id){
	$json_link = 'http://images.memorix.nl/'.$institution.'/topviewjson/memorix/'.$id;
	$test = get_headers($json_link, 1);
 if ($test[0] == 'HTTP/1.1 200 OK'){
		$string = file_get_contents ($json_link);
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
	imagejpeg($image, $imagelink );
	imagedestroy($image);
	unset($image);
	return array("succes" =>true, "xy" =>$layer['width']."x".$layer['height']);
	}
	else{
		return array("succes" =>false,"xy" =>"404");
	}
}
if (isset($_POST['input'])){	

	$succes = false;
	ini_set('memory_limit', '-1');
	ini_set('max_execution_time', 3000);
	$input = trim($_POST['input']);
	if (isset( $_POST['naam_afb']) && $_POST['naam_afb'] != ""){
		$imagelink = $_POST['naam_afb'];
	}
	if (preg_match($regex, $input, $matches)){
	 $return = generateImage($imagelink, $matches[2],  $matches[count($matches)-1]);
		$xy =  $return["xy"];
		$succes = $return['succes'];
	}
	elseif(!$succes && filter_var($input, FILTER_VALIDATE_URL)){ //input is a URL
		foreach ($beeldbanken as $beeldbank) {
			if(preg_match('`https?:\/\/(www\.)?'.$beeldbank['url'].'\/detail\/[a-z0-9\-]{36}\/media\/([a-z0-9\-]{36})`', $input, $matches)){
				$return = generateImage($imagelink, $beeldbank['tla'],  $matches[count($matches)-1]);
				$xy =  $return["xy"];
				$succes = $return['succes'];
			}
		}
		if (!$succes){
			$content = file_get_contents(trim($_POST['input']));
			if (preg_match($regex, $content, $matches)){

			 $return = generateImage($imagelink, $matches[2],  $matches[count($matches)-1]);
			 $xy = $return["xy"];
				$succes = $return["succes"];
			}
			elseif(preg_match('`files\.archieven\.nl\/php\/get_thumb\.php\?adt_id=([0-9]{2,4})&(amp;)?toegang=([A-Z0-9]{2,3})&(amp;)?id=[0-9]{9}&(amp;)?file=([0-9A-Z]{2,3})(%5C|-)([0-9]{2,7})\.jpg`', $content, $matches)){
				$imagelink = "http://files.archieven.nl/".$matches[1]."/f/".$matches[3]."/".$matches[6]."/".$matches[8].".jpg";
				//http://files.archieven.nl/69/f/THA/27/986.jpg
				$xy = "";
				$succes = true;
			}
		}
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
	document.getElementById("wachten").style.display = "block";
 document.getElementById("formulier").style.visibility = "hidden";
 document.getElementById("previous").style.visibility = "hidden";
 document.getElementById("form_1046651").submit();
}
</script>
</head>
<body id="main_body" >
	
	<img id="top" src="img/top.png" alt="">
	<div id="form_container">
	
		<h1><a>Dememorixer </a></h1>
		<form id="form_1046651" class="appnitro"  method="post" target="_self">
					<div class="form_description">
								<h2>Dememorixer</h2>
			<p>Download de volle resolutie van een afbeelding die in een Memorix Maior viewer is geplaatst</p>
		</div>				
		<div id="previous">
		<?php 
		if (isset($succes)){
			if ($succes){
			echo "<img src='".$imagelink."'  width='500'><br/>	";
			echo "<p><a href='".$imagelink."' download>Download afbeelding</a> (".$xy.")</p>";
		}
		elseif (isset($xy) && $xy == "404"){
			echo "<p>Er is geen hogere resoultie te downloaden, je moet het helaas doen met de thumbnail</p>";
		}
		else{
			echo "<p>Er is iets misgegaan</p>";
		}
	}?>
</div>
		<ul id="wachten" style="display:none;">
			<li id="li_1" >
		<label class="description" for="element_1">WACHTEN</label><p id="wachten"><img   src="img/loader.gif" width="32px"  height="32px" /></p>
			</ul>
			<ul id="formulier">
			
					<li id="li_1" >
		<label class="description" for="element_1">URL naar thumbnail / "Insluiten" informatie / Permalink naar pagina</label>
		<div>
<input id="input" name="input" class="element text medium" type="text" value=""/> 		
		</div> 
		<p class="guidelines" id="guide_1"><small>ziet er uit als https://images.memorix.nl/abc/thumb/250x250/51d542ba-fe9f-8cab-3434-41a91438e94e.jpg</small></p>
		</li>
				<li id="li_1" style="display:none;"> <!-- server raakt vol als iedereen voor elke foto een nieuwe bestandsnaam aanmaakt. -->
		<label class="description" for="element_1">Naam afbeelding</label>
		<div>
<input id="naam_afb" name="naam_afb" class="element text medium" type="text" value=""/> 		
		</div> 
	
		</li>
					<li class="buttons">
			    <input type="hidden" name="form_id" value="1046651" />
			    
				<button onclick="myFunction()">Click me</button>
		</li>
			</ul>
		</form>	
		<div id="footer">
			Created by <a href="http://www.veradekok.nl">Vera de Kok</a><br/>
			See <a href="https://github.com/VDK/Dememorixer" target="_blank">code on GitHub</a><br/>
			Form generated with <a href="http://www.phpform.org">pForm</a>
		</div>
	</div>
	<img id="bottom" src="img/bottom.png" alt="">
	</body>
</html>
