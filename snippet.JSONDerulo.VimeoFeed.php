<?php
$cacheTime = 43200; // 12 hours
$feedUrl = 'http://vimeo.com/api/v2/{username}/likes.json';
$ch = null;
$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, '2');
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'title'));
$feeds = explode(',', $modx->getOption('users', $scriptProperties, ''));
$rawFeedData = array();

foreach ($feeds as $username) {
	$cacheId = 'vimeofeed-'.$username;
  
	if (($json = $modx->cacheManager->get($cacheId)) === null) {
		if ($ch === null) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		}
		
		curl_setopt_array($ch, array(
		  CURLOPT_URL => str_replace(array('{username}'), array($username), $feedUrl),
		));
			  
		
	  	$json = curl_exec($ch);
		if (empty($json)) {
			continue;
		}
		
		$modx->cacheManager->set($cacheId, $json, $cacheTime);
	}
	
	$feed = json_decode($json);
  
	if ($feed === null) {
		continue;
	}
  
  	$counter = NULL;
    
  	foreach ($feed as $video) {
		$counter++;
		
		if($counter>$limit){
		      break; 
		}
		
	  	foreach ($excludeEmpty as $k) {
			if ($video->$k == '') {
				continue 2;
			}
		}
	
		$rawFeedData[] = array(
			'id' => $video->id,
		  	'url' => $video->url,
			'created' => strtotime($video->upload_date),
			'picture' => $video->thumbnail_large,
			'title' => $video->title,
			'username' => $userName,
		);
	}
}

if ($ch !== null) {
	curl_close($ch);
}

$output = '';
foreach ($rawFeedData as $video) {
	$output .= $modx->getChunk($tpl, $video);
}

return $output;