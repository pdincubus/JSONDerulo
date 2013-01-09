<?php
/*
* @author Phil Steer
* @package JSONDerulo
* @site https://github.com/pdincubus/JSONDerulo
* Fetches Twitter feed in JSON format and allows templating via chunk
* Updated to work with the new API awfulness
*/

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$screenName = explode(',', $modx->getOption('screenName', $scriptProperties, ''));
$includeRTs = $modx->getOption('includeRTs', $scriptProperties, 1);
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'text'));
$consumerKey = $modx->getOption('consumerKey', $scriptProperties, '');
$consumerSecret =	$modx->getOption('consumerSecret', $scriptProperties, '');
$accessToken =	$modx->getOption('accessToken', $scriptProperties, '');
$accessTokenSecret =	$modx->getOption('accessTokenSecret', $scriptProperties, '');
$cacheTime =	$modx->getOption('cacheTime', $scriptProperties, 43200);

$rawFeedData = array();
$output = '';

foreach ($screenName as $user) {

    $cacheId = 'twitterfeednew-'.$user;
    
    if (($json = $modx->cacheManager->get($cacheId)) === null) {
		require_once $modx->getOption('core_path').'components/jsonderulo/twitteroauth/twitteroauth.php';
		$fetch = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
		$fetch->format = 'json';
		$fetch->decode_json = FALSE;
		$fetch->ssl_verifypeer = FALSE;
		$json = $fetch->get('statuses/user_timeline', array('include_rts' => $includeRTs, 'count' => $limit, 'screen_name' => $user));
		
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

		$input = $message->text;
		// Convert URLs into hyperlinks
		$input= preg_replace("/(http:\/\/)(.*?)\/([\w\.\/\&\=\?\-\,\:\;\#\_\~\%\+]*)/", "<a href=\"\\0\">\\0</a>", $input);
		// Convert usernames (@) into links
		$input= preg_replace("(@([a-zA-Z0-9\_]+))", "<a href=\"http://www.twitter.com/\\1\">\\0</a>", $input);
		// Convert hash tags (#) to links
		$input= preg_replace('/(^|\s)#(\w+)/', '\1<a href="http://search.twitter.com/search?q=%23\2">#\2</a>', $input);

		$rawFeedData[$i] = array(
			'id' => $message->id_str,
			'message' => $input,
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
    
    foreach ($rawFeedData as $item) {
        $output .= $modx->getChunk($tpl, $item);
    }
}

return $output;