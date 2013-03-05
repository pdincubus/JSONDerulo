<?php
/*
* @author Phil Steer
* @package JSONDerulo
* @site https://github.com/pdincubus/JSONDerulo
* @site MODX Exta: http://modx.com/extras/package/jsonderulo
* Fetches Tumblr feed in JSON format and allows templating via chunk
*/

$feedUrl = 'http://api.tumblr.com/v2/blog/{blogurl}/posts{posttype}?notes_info={notesinfo}&tag={tag}&limit={limit}&api_key={apikey}';

$ch = null;

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$feeds = explode(',', $modx->getOption('blogUrl', $scriptProperties, ''));
$postType = $modx->getOption('postType', $scriptProperties, '');
$tag = $modx->getOption('tag', $scriptProperties, '');
$notesInfo = $modx->getOption('notesInfo', $scriptProperties, 'false');
$apiKey = $modx->getOption('apiKey', $scriptProperties, '');
$cacheTime = $modx->getOption('cacheTime', $scriptProperties, 43200);

$rawFeedData = array();

foreach ($feeds as $tumblr) {
    $cacheId = 'tumblrfeed-'.$tumblr;

    if (($json = $modx->cacheManager->get($cacheId)) === null) {
        if ($ch === null) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        }

        curl_setopt_array($ch, array(
            CURLOPT_URL => str_replace(array('{apikey}', '{posttype}', '{limit}', '{tag}', '{notesinfo}', '{blogurl}'), array($apiKey, '/'.$postType, $limit, $tag, $notesInfo, $blogUrl), $feedUrl),
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

    $feeditems = $feed->response->posts;

    $blogName = $feed->response->blog->title;
    $blogUrl = $feed->response->blog->url;
    $blogDescription = $feed->response->blog->description;

    $i = 0;

    foreach ($feeditems as $post) {

        $rawFeedData[$i] = array(
            'id' => $post->id,
            'post' => $post->text,
            'created' => strtotime($post->timestamp),
            'createdDate' => $post->date,
            'blogName' => $blogName,
            'blogUrl' => $blogUrl,
            'blogDescription' => $blogDescription,
            'postUrl' => $post->post_url,
            'postType' => $post->type,
            'shortUrl' => $post->short_url,
            );

        if($post->type=='video'){
            $rawFeedData[$i]['caption'] = $post->caption;
            $rawFeedData[$i]['videoPermalink'] = $post->permalink_url;
            $rawFeedData[$i]['thumbnail'] = $post->thumbnail_url;
            $rawFeedData[$i]['player250'] = $post->player[0]->embed_code;
            $rawFeedData[$i]['player400'] = $post->player[1]->embed_code;
            $rawFeedData[$i]['player500'] = $post->player[2]->embed_code;
        }

        if($post->type=='link'){
            $rawFeedData[$i]['title'] = $post->title;
            $rawFeedData[$i]['linkUrl'] = $post->url;
            $rawFeedData[$i]['linkDescription'] = $post->description;
        }

        if($post->type=='text'){
            $rawFeedData[$i]['title'] = $post->title;
            $rawFeedData[$i]['content'] = $post->body;
        }

        if($post->type=='audio'){
            $rawFeedData[$i]['audioSourceUrl'] = $post->caption;
            $rawFeedData[$i]['audioSourceTitle'] = $post->source_title;
            $rawFeedData[$i]['artist'] = $post->artist;
            $rawFeedData[$i]['album'] = $post->album;
            $rawFeedData[$i]['trackName'] = $post->track_name;
            $rawFeedData[$i]['player'] = $post->player;
            $rawFeedData[$i]['audioUrl'] = $post->audio_url;
        }

        if($post->type=='photo'){
            $rawFeedData[$i]['caption'] = $post->caption;
            $rawFeedData[$i]['imagePermalink'] = $post->image_permalink;
            $rawFeedData[$i]['image'] = $post->photos->original_size->url;
        }

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