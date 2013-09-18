<?php
/*
* @author Phil Steer
* @package JSONDerulo
* @site GitHub source: https://github.com/pdincubus/JSONDerulo
* @site MODX Extra: http://modx.com/extras/package/jsonderulo
* Fetches YouTube public playlist feed from the new API v3
*/


$feedUrl = 'https://www.googleapis.com/youtube/v3/playlistItems?part=id%2C+snippet%2CcontentDetails%2Cstatus&playlistId={playlistid}&fields=etag%2Citems%2CpageInfo&key={apikey}&maxResults={limit}';

$ch = null;

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$playlist = $modx->getOption('playlistId', $scriptProperties, '');
$cacheName = $modx->getOption('cacheName', $scriptProperties, '');
$cacheTime = $modx->getOption('cacheTime', $scriptProperties, 43200);
$apiKey = $modx->getOption('apiKey', $scriptProperties, '');

$rawFeedData = array();
$cacheName = str_replace(" ", "-", $cacheName);

$cacheId = 'youtubefeedv3publicplaylist-'.$cacheName.'-'.$playlist;

if (($json = $modx->cacheManager->get($cacheId)) === null) {
    if ($ch === null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    }

    curl_setopt_array($ch, array(
        CURLOPT_URL => str_replace(array('{playlistid}', '{limit}', '{apikey}'), array($playlist, $limit, $apiKey), $feedUrl),
        ));


    $json = curl_exec($ch);
    if (empty($json)) {
        continue;
    }

    $modx->cacheManager->set($cacheId, $json, $cacheTime);
}

$feed = json_decode($json);

if ($feed === null) {
    $image['title'] = 'No videos returned.';
    $output = $modx->getChunk($tpl, $image);
} else {

    foreach ($feed->items as $video) {
        $rawFeedData[] = array(
            'published' => strtotime($video->snippet->publishedAt),
            'picture' => $video->snippet->thumbnails->high->url,
            'title' => $video->snippet->title,
            'description' => $video->snippet->description,
            'ytlink' => 'http://www.youtube.com/watch?v=' . $video->snippet->resourceId->videoId,
            'embedlink' => 'https://www.youtube.com/v/' . $video->snippet->resourceId->videoId,
            'author' => $video->author[0]->name->{'$t'},
            );
    }


    if ($ch !== null) {
        curl_close($ch);
    }

    $output = '';

    foreach ($rawFeedData as $image) {
        $output .= $modx->getChunk($tpl, $image);
    }

}

return $output;
