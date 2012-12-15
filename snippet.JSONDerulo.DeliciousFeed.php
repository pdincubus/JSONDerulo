<?php
/*
* @author Phil Steer
* @package JSONDerulo
* @site https://github.com/pdincubus/JSONDerulo
* Fetches Delicious feed in JSON format and allows templating via chunk
*/

$feedUrl = 'http://feeds.delicious.com/v2/json/{username}?count={limit}';

$ch = null;

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'd'));
$feeds = explode(',', $modx->getOption('users', $scriptProperties, ''));
$cacheTime =	$modx->getOption('cacheTime', $scriptProperties, 43200);

$rawFeedData = array();

foreach ($feeds as $username) {
	$cacheId = 'deliciousfeed-'.$username;
  
	if (($json = $modx->cacheManager->get($cacheId)) === null) {
		if ($ch === null) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		}

		curl_setopt_array($ch, array(
		  CURLOPT_URL => str_replace(array('{username}', '{limit}'), array($username, $limit), $feedUrl),
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


	foreach ($feed as $item) {
		foreach ($excludeEmpty as $k) {
			if ($item->$k == '') {
				continue 2;
			}
		}

		$rawFeedData[] = array(
			'title' => $item->d,
			'link' => $item->u,
			'date' => strtotime($item->dt),
		  	'description' => $item->n,
			'username' => $username,
		);

	}
 
}

if ($ch !== null) {
	curl_close($ch);
}

$output = '';
foreach ($rawFeedData as $item) {
	$output .= $modx->getChunk($tpl, $item);
}

return $output;