<?php
/*
* @author Phil Steer
* @package JSONDerulo
* @site GitHub source: https://github.com/pdincubus/JSONDerulo
* @site MODX Exta: http://modx.com/extras/package/jsonderulo23
* Fetches YouTube user uploads feed in JSON format and allows templating via chunk
*/

$feedUrl = 'http://gdata.youtube.com/feeds/api/users/{username}/uploads?max-results={limit}&start-index={offset}&alt=json';

$ch = null;

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$startIndex = $modx->getOption('startIndex', $scriptProperties, 1);
$videoParams = $modx->getOption('videoParams', $scriptProperties, '?fs=1');
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'link'));
$feeds = explode(',', $modx->getOption('users', $scriptProperties, ''));
$cacheTime = $modx->getOption('cacheTime', $scriptProperties, 43200);

$rawFeedData = array();

foreach ($feeds as $username) {
	$cacheId = 'youtubeuploadsfeed-'.$username;
  
	if (($json = $modx->cacheManager->get($cacheId)) === null) {
		if ($ch === null) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		}

		curl_setopt_array($ch, array(
		  CURLOPT_URL => str_replace(array('{username}', '{limit}', '{offset}'), array($username, $limit, $startIndex), $feedUrl),
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
    
    $feeditems = $feed->feed;

	foreach ($feeditems->entry as $video) {

		foreach ($excludeEmpty as $k) {
			if ($video->$k == '') {
				continue 2;
			}
		}	  

	  	$videoId = substr($video->id->{'$t'},42);

		$rawFeedData[] = array(
		  	'published' => strtotime($video->published->{'$t'}),
		  	'picture' => $video->{'media$group'}->{'media$thumbnail'}[0]->url,
		  	'title' => $video->title->{'$t'},
		  	'ytlink' => $video->link[0]->href,
		  	'embedlink' => 'https://www.youtube.com/v/' .$videoId. $videoParams,
		    'author' => $video->author[0]->name->{'$t'},
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
