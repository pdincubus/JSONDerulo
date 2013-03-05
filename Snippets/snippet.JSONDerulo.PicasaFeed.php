<?php
/*
* @author Phil Steer
* @package JSONDerulo
* @site GitHub source: https://github.com/pdincubus/JSONDerulo
* @site MODX Exta: http://modx.com/extras/package/jsonderulo
* Fetches Picasa feed in JSON format and allows templating via chunk
*/

$feedUrl = 'https://picasaweb.google.com/data/feed/base/user/{userid}/albumid/{albumid}?kind=photo&alt=json';

$ch = null;

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'content'));
$feeds = explode(',', $modx->getOption('users', $scriptProperties, '3'));
$albumId = $modx->getOption('albumId', $scriptProperties, '');
$albumName = $modx->getOption('albumName', $scriptProperties, '');
$cacheTime = $modx->getOption('cacheTime', $scriptProperties, 43200);

$rawFeedData = array();

foreach ($feeds as $userId) {
	$cacheId = 'picasafeed-'.$userId;

	if (($json = $modx->cacheManager->get($cacheId)) === null) {
		if ($ch === null) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		}

		curl_setopt_array($ch, array(
		  CURLOPT_URL => str_replace(array('{userid}', '{albumid}'), array($userId, $albumId), $feedUrl),
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

	$feeditems = $feed->feed;

	foreach ($feeditems->entry as $photo) {
		$counter++;

		if($counter>$limit){
		      break;
		}

		foreach ($excludeEmpty as $k) {
			if ($photo->$k == '') {
				continue 2;
			}
		}

		$rawFeedData[] = array(
			'link' => $photo->link[1]->href,
			'albumid' => $albumId,
			'created' => strtotime($photo->published->{'$t'}),
			'picture' => $photo->content->src,
			'title' => $photo->{'media$group'}->{'media$title'}->{'$t'},
			'userid' => $userId,
			'albumname' => $albumName,
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