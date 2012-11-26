<?php
/*
* @author Phil Steer
* @package JSONDerulo
* @site https://github.com/pdincubus/JSONDerulo
* Fetches Twitter feed in JSON format and allows templating via chunk
* Updated to work with the new API awfulness
*/

$cacheTime = 43200; // 12 hours

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$includeRTs = $modx->getOption('includeRTs', $scriptProperties, 1);
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'text'));
$feeds = $modx->getOption('users', $scriptProperties, 'twitter');
$consumerKey = $modx->getOption('consumerKey', $scriptProperties, '');
$consumerSecret =	$modx->getOption('consumerSecret', $scriptProperties, '');
$accessToken =	$modx->getOption('accessToken', $scriptProperties, '');
$accessTokenSecret =	$modx->getOption('accessTokenSecret', $scriptProperties, '');

$rawFeedData = array();

$cacheId = 'twitterfeednew-'.$feeds;

if (($json = $modx->cacheManager->get($cacheId)) === null) {
		require_once $modx->getOption('core_path').'components/jsonderulo/twitteroauth/twitteroauth.php';
		$fetch = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
		$fetch->format = 'json';
		$fetch->decode_json = FALSE;
		$fetch->ssl_verifypeer = FALSE;
		$json = $fetch->get('statuses/user_timeline', array('include_rts' => $includeRTs, 'count' => $limit));
		
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

foreach ($feed as $message) {
		foreach ($excludeEmpty as $k) {
				if ($message->$k == '') {
						continue 2;
				}
		}
		
		$rawFeedData[$i] = array(
				'id' => $message->id_str,
				'message' => $message->text,
				'created' => strtotime($message->created_at),
				'picture' => $message->user->profile_image_url,
				'title' => $message->user->name,
				'username' => $message->user->screen_name,
				'retweetCount' => $message->retweet_count,
				'isRetweet' => '0',
		);
		
		if(isset($message->retweeted_status)){
				$rawFeedData[$i]['originalAuthorPicture'] = $message->retweeted_status->user->profile_image_url;
				$rawFeedData[$i]['originalAuthor'] = $message->retweeted_status->user->name;
				$rawFeedData[$i]['originalUsername'] = $message->retweeted_status->user->screen_name;
				$rawFeedData[$i]['isRetweet'] = '1';
				$rawFeedData[$i]['originalId'] = $message->retweeted_status->id;
		}
		
		$i++;
}

$output = '';
foreach ($rawFeedData as $message) {
		$output .= $modx->getChunk($tpl, $message);
}

return $output;