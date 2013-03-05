<?php
/*
* @author Phil Steer
* @package JSONDerulo
* @site GitHub source: https://github.com/pdincubus/JSONDerulo
* @site MODX Exta: http://modx.com/extras/package/jsonderulo
* Fetches Google+ public feed in JSON format and allows templating via chunk
*/

$feedUrl = 'https://www.googleapis.com/plus/v1/people/{userid}/activities/public?alt=json&pp=1&key={apikey}&maxResults={limit}';
//$userProfile = 'https://www.googleapis.com/plus/v1/people/{userid}?key={apikey}&maxResults={limit}';

$ch = null;

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$userId = $modx->getOption('userId', $scriptProperties, '');
$apiKey = $modx->getOption('apiKey', $scriptProperties, '');
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, ''));
$feeds = explode(',', $modx->getOption('cacheName', $scriptProperties, 'default'));
$cacheTime = $modx->getOption('cacheTime', $scriptProperties, 43200);

$rawFeedData = array();
$output = '';

foreach ($feeds as $feed) {
	$cacheId = 'googleplusfeed-'.$cacheName;

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

	$i = 0;

	foreach ($feed->items as $message) {

		$rawFeedData[$i] = array(
			'avatar' => $message->actor->image->url,
			'displayName' => $message->actor->displayName,
			'profileUrl' => $message->actor->url,
			'postId' => $message->id,
			'postDate' => strtotime($message->published),
			'text' => $message->title,
			'html' => $message->object->content,
			'url' => $message->url,
			'attachmentUrl' => $message->object->attachments->url,
			'repliesCount' => $message->object->replies->totalItems,
			'plusCount' => $message->object->plusoners->totalItems,
			'resharesCount' => $message->object->resharers->totalItems,
		);

		$i++;

	}
}

if ($ch !== null) {
	curl_close($ch);
}

foreach ($rawFeedData as $message) {
	$output .= $modx->getChunk($tpl, $message);
}

return $output;