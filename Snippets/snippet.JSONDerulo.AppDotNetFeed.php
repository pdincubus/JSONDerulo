<?php
/*
* @author Phil Steer
* @package JSONDerulo
* @site GitHub source: https://github.com/pdincubus/JSONDerulo
* @site MODX Exta: http://modx.com/extras/package/jsonderulo
* Fetches App.net feed in JSON format and allows templating via chunk
*/

$feedUrl = 'https://alpha-api.app.net/stream/0/users/{userId}/posts?count={limit}';

$ch = null;

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'text'));
$feeds = explode(',', $modx->getOption('userId', $scriptProperties, '19445'));
$cacheTime = $modx->getOption('cacheTime', $scriptProperties, 43200);

$rawFeedData = array();

foreach ($feeds as $user) {
	$cacheId = 'appdotnetfeed-'.$user;

	if (($json = $modx->cacheManager->get($cacheId)) === null) {
		if ($ch === null) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
		}

		curl_setopt_array($ch, array(
			CURLOPT_URL => str_replace(array('{userId}', '{limit}'), array($user, $limit), $feedUrl),
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

	$feeditems = $feed->data;

	$i = 0;

	foreach ($feeditems as $message) {
		foreach ($excludeEmpty as $k) {
			if ($message->$k == '') {
				continue 2;
			}
		}

		$rawFeedData[$i] = array(
			'id' => $message->id,
			'text' => $message->text,
			'html' => $message->html,
			'created' => strtotime($message->created_at),
			'picture' => $message->user->avatar_image->url,
			'title' => $message->user->name,
			'username' => $message->user->username,
			'profile' => $message->user->canonical_url,
			'postUrl' => $message->canonical_url,
		);

		$i++;
	}
}

if ($ch !== null) {
	curl_close($ch);
}

$output = '';
foreach ($rawFeedData as $message) {
	$output .= $modx->getChunk($tpl, $message);
}

return $output;