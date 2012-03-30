<?php
$cacheTime = 43200; // 12 hours
$feedUrl = 'http://api.flickr.com/services/rest/?method=flickr.photos.search&format=json&nojsoncallback=1&api_key={apikey}&user_id={userid}&per_page={limit}&extras=url_m,date_upload';

$ch = null;

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'url_m'));
$feeds = explode(',', $modx->getOption('users', $scriptProperties, '22342246@N03'));
$apiKey = $modx->getOption('apiKey', $scriptProperties, '');
$userName = $modx->getOption('userName', $scriptProperties, '');

$rawFeedData = array();

foreach ($feeds as $userId) {
	$cacheId = 'flickrfeed-'.$userId;
  
	if (($json = $modx->cacheManager->get($cacheId)) === null) {
		if ($ch === null) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		}
		
		curl_setopt_array($ch, array(
		  CURLOPT_URL => str_replace(array('{apikey}', '{userid}', '{limit}'), array($apiKey, $userId, $limit), $feedUrl),
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
	
	foreach ($feed->photos->photo as $photo) {
		foreach ($excludeEmpty as $k) {
			if ($photo->$k == '') {
				continue 2;
			}
		}
	
		$rawFeedData[] = array(
			'id' => $photo->id,
			'created' => $photo->dateupload,
			'picture' => $photo->url_m,
			'title' => $photo->title,
			'username' => $userName,
		);
	}
}

if ($ch !== null) {
	curl_close($ch);
}

$output = '';
foreach ($rawFeedData as $photo) {
	$output .= $modx->getChunk($tpl, $photo);
}

return $output;