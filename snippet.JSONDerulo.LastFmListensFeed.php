<?php
/*
* @author Phil Steer
* @package JSONDerulo
* @site https://github.com/pdincubus/JSONDerulo
* Fetches LastFM latest listens feed in JSON format and allows templating via chunk
*/

$cacheTime = 43200; // 12 hours
$feedUrl = 'http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user={username}&api_key={apikey}&format=json&limit={limit}';

$ch = null;

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'name'));
$feeds = explode(',', $modx->getOption('users', $scriptProperties, ''));
$apiKey = $modx->getOption('apiKey', $scriptProperties, '');

$rawFeedData = array();

foreach ($feeds as $username) {
	$cacheId = 'lastfmlistensfeed-'.$username;
  
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
  
  $feedtracks = $feed->recenttracks;

	foreach ($feedtracks->track as $item) {
		foreach ($excludeEmpty as $k) {
			if ($item->$k == '') {
				continue 2;
			}
		}

		$rawFeedData[] = array(
			'track' => $item->name,
			'artist' => $item->artist->name,
			'link' => $item->url,
		 	'picture' => $item->image[3]->{'#text'},
			'date' => $item->date->uts,
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