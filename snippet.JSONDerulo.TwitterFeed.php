<?php
$cacheTime = 43200; // 12 hours
$feedUrl = 'https://api.twitter.com/1/statuses/user_timeline.json?screen_name={username}&count={limit}';

$ch = null;

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'text'));
$feeds = explode(',', $modx->getOption('users', $scriptProperties, 'twitter'));

$rawFeedData = array();

foreach ($feeds as $username) {
	$cacheId = 'twitterfeed-'.$username;
  
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