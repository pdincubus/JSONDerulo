<?php
/*
* @author Phil Steer
* @package JSONDerulo
* @site https://github.com/pdincubus/JSONDerulo
* Fetches ZooTool feed in JSON format and allows templating via chunk
*/

$cacheTime = 43200; // 12 hours
$feedUrl = 'http://zootool.com/api/users/items/?username={username}&apikey={apikey}&limit={limit}';

$ch = null;

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'image'));
$feeds = explode(',', $modx->getOption('users', $scriptProperties, ''));
$apiKey = $modx->getOption('apiKey', $scriptProperties, '');

$rawFeedData = array();

foreach ($feeds as $username) {
	$cacheId = 'zootoolfeed-'.$username;
  
	if (($json = $modx->cacheManager->get($cacheId)) === null) {
		if ($ch === null) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		}

		curl_setopt_array($ch, array(
		  CURLOPT_URL => str_replace(array('{apikey}', '{username}', '{limit}'), array($apiKey, $username, $limit), $feedUrl),
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

	foreach ($feed as $image) {
		foreach ($excludeEmpty as $k) {
			if ($image->$k == '') {
				continue 2;
			}
		}

		$rawFeedData[] = array(
			'date' => $image->added,
			'picture' => $image->image,
			'title' => $image->title,
			'username' => $username,
		  	'referrer' => $image->referer,
		  	'permalink' => $image->permalink,
		);

	}
}

if ($ch !== null) {
	curl_close($ch);
}

$output = '';
foreach ($rawFeedData as $image) {
	$output .= $modx->getChunk($tpl, $image);
}

return $output;