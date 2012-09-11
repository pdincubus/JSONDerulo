<?php
/*
* @author Phil Steer
* @package JSONDerulo
* @site https://github.com/pdincubus/JSONDerulo
* Fetches Twitter feed in JSON format and allows templating via chunk
*/

$cacheTime = 43200; // 12 hours
$feedUrl = 'https://api.twitter.com/1/statuses/user_timeline.json?screen_name={username}&count={limit}&include_rts={includeRTs}';

$ch = null;

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$includeRTs = $modx->getOption('includeRTs', $scriptProperties, 1);
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'text'));
$feeds = explode(',', $modx->getOption('users', $scriptProperties, 'twitter'));

$rawFeedData = array();

foreach ($feeds as $username) {
	$cacheId = 'twitterfeed-'.$username;
  
	if (($json = $modx->cacheManager->get($cacheId)) === null) {
		if ($ch === null) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
		}

		curl_setopt_array($ch, array(
			CURLOPT_URL => str_replace(array('{username}', '{limit}', '{includeRTs}'), array($username, $limit, $includeRTs), $feedUrl),
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

	foreach ($feed as $message) {
		foreach ($excludeEmpty as $k) {
			if ($message->$k == '') {
				continue 2;
			}
		}

		$rawFeedData[] = array(
			'id' => $message->id_str,
			'message' => $message->text,
			'created' => strtotime($message->created_at),
			'picture' => $message->user->profile_image_url,
			'title' => $message->user->name,
			'username' => $message->user->screen_name,
		);
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