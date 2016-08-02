<?php
//-----------------------------------------------------------
//  Package info
//-----------------------------------------------------------
/*
 *  @author: Phil Steer
 *  @package: JSONDerulo
 *  @site: GitHub source: https://github.com/pdincubus/JSONDerulo
 *  @site: MODX Extra: http://modx.com/extras/package/jsonderulo
 *  @version: 2.5.7
 *  @description: Fetches social feeds in JSON format
*/

//-----------------------------------------------------------
//  Current feeds supported:
//-----------------------------------------------------------
/*
 *  App.net public posts
 *  Eventbrite user events [requires Single user oAuth token - see 'Personal Tokens' on the Authentication page: http://developer.eventbrite.com/docs/auth/]
 *  Flickr recent photos [requires API key - http://www.flickr.com/services/apps/create/apply]
 *  Google Calendar (API v3) public events [requires API key - https://console.developers.google.com]
 *  Google+ public posts [requires API key - https://console.developers.google.com]
 *  LastFM loved tunes [requires API Key - http://www.last.fm/api/account]
 *  LastFM recent listens [requires API Key - http://www.last.fm/api/account]
 *  Tumblr posts
 *  Twitter timeline [requires keys and secrets, set up an app - https://dev.twitter.com/apps]
 *  Twitter favourites [requires keys and secrets as above]
 *  Vimeo recent likes
 *  YouTube (API v2) uploaded videos
 *  YouTube (API v2) favourites
 *  YouTube (API v3) public playlist videos [requires API key - https://code.google.com/apis/console/]
 */

//-----------------------------------------------------------
//  Generic options and var init
//-----------------------------------------------------------

$feed = $modx->getOption('feed', $scriptProperties, '');
$cacheName = $modx->getOption('cacheName', $scriptProperties, '');
$cacheTime = $modx->getOption('cacheTime', $scriptProperties, 43200);
$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$ch = null;
$rawFeedData = array();
$cacheName = str_replace(" ", "-", $cacheName);

//-----------------------------------------------------------
//  App.net user posts feed
//-----------------------------------------------------------
if( $feed == 'appnet' ) {
    $feedUrl = 'https://alpha-api.app.net/stream/0/users/{userId}/posts?count={limit}';
    $excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'text'));
    $feeds = explode(',', $modx->getOption('userId', $scriptProperties, ''));

    foreach ($feeds as $user) {
        $cacheId = 'jsonderulo-appdotnetfeed-'.$cacheName.'-'.$user;

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

            $input = $message->text;
            // Convert URLs into hyperlinks
            $input= preg_replace("/(http:\/\/)(.*?)\/([\w\.\/\&\=\?\-\,\:\;\#\_\~\%\+]*)/", "<a href=\"\\0\">\\0</a>", $input);
            // Convert usernames (@) into links
            $input= preg_replace("(@([a-zA-Z0-9\_]+))", "<a href=\"https://alpha.app.net/\\1\">\\0</a>", $input);
            // Convert hash tags (#) to links
            $input= preg_replace('/(^|\s)#(\w+)/', '\1<a href="https://alpha.app.net/hashtags/\2">#\2</a>', $input);

            $rawFeedData[$i] = array(
                'id' => $message->id,
                'text' => $input,
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

    foreach ($rawFeedData as $message) {
        $output .= $modx->getChunk($tpl, $message);
    }

//-----------------------------------------------------------
//  Eventbrite user events
//-----------------------------------------------------------
} elseif( $feed == 'eventbrite' ) {
    $feedUrl = 'https://www.eventbriteapi.com/v3/users/me/owned_events/?status={status}&order_by={orderby}&token={token}&expand=event,venue,ticket_classes';

    $excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'url'));
    $status = $modx->getOption('status', $scriptProperties, 'live');
    $orderBy = $modx->getOption('orderBy', $scriptProperties, 'start_asc');
    $limit = $modx->getOption('limit', $scriptProperties, 3);

    $cacheId = 'jsonderulo-eventbritefeed-'.$cacheName;

    if (($json = $modx->cacheManager->get($cacheId)) === null) {
        if ($ch === null) {
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_USERAGENT => 'php',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_FOLLOWLOCATION => true,
            ));
        }

        $eventbriteUrl = str_replace(array('{status}', '{orderby}', '{token}'), array($status, $orderBy, $token), $feedUrl);

        curl_setopt_array($ch, array(
          CURLOPT_URL => $eventbriteUrl,
        ));

        $json = curl_exec($ch);

        if (empty($json)) {
            return 'No events returned';
        }

        $modx->cacheManager->set($cacheId, $json, $cacheTime);
    }

    $feed = json_decode($json);

    if ($feed === null) {
        return;
    }

    for ($i = 0; $i <= $limit-1; $i++) {
        foreach ($excludeEmpty as $k) {
            if ($feed->events[$i]->$k == '') {
                continue 2;
            }
        }

        $rawFeedData[$i] = array(
            'title' => $feed->events[$i]->name->text,
            'textDescription' => $feed->events[$i]->description->text,
            'htmlDescription' => $feed->events[$i]->description->html,
            'organiserName' => $feed->events[$i]->organizer->name,
            'organiserId' => $feed->events[$i]->organizer->id,
            'venueName' => $feed->events[$i]->venue->name,
            'venueAddress1' => $feed->events[$i]->venue->address->address_1,
            'venueAddress2' => $feed->events[$i]->venue->address->address_2,
            'venueCity' => $feed->events[$i]->venue->address->city,
            'venueRegion' => $feed->events[$i]->venue->address->region,
            'venueCountryName' => $feed->events[$i]->venue->address->country_name,
            'venueCountry' => $feed->events[$i]->venue->address->country,
            'venueLatitude' => $feed->events[$i]->venue->latitude,
            'venueLongitude' => $feed->events[$i]->venue->longitude,
            'url' => $feed->events[$i]->url,
            'eventStart' => $feed->events[$i]->start->utc,
            'eventEnd' => $feed->events[$i]->end->utc,
            'eventCapacity' => $feed->events[$i]->capacity,
            'eventFormat' => $feed->events[$i]->format->name,
            'eventId' => $feed->events[$i]->id,
        );

        $j = 1;
        foreach ($feed->events[$i]->ticket_classes as $ticket_class) {
            $rawFeedData[$i]['ticketType'.$j] = $ticket_class->name;
            $rawFeedData[$i]['ticketCost'.$j] = $ticket_class->cost->value;
            $rawFeedData[$i]['ticketFee'.$j] = $ticket_class->fee->value;
            $rawFeedData[$i]['ticketFree'.$j] = $ticket_class->free;
            $rawFeedData[$i]['ticketTypeQuantity'.$j] = $ticket_class->quantity_total;
            $rawFeedData[$i]['ticketTypeSold'.$j] = $ticket_class->quantity_sold;

            $j++;
        }
    }

    foreach ($rawFeedData as $item) {
        $output .= $modx->getChunk($tpl, $item);
    }


//-----------------------------------------------------------
//  Flickr user's photos
//-----------------------------------------------------------
} elseif( $feed == 'flickr' ) {
    $feedUrl = 'https://api.flickr.com/services/rest/?method=flickr.photos.search&format=json&nojsoncallback=1&api_key={apikey}&user_id={userid}&per_page={limit}&extras=url_m,url_l,date_upload';

    $excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'url_m'));
    $feeds = explode(',', $modx->getOption('users', $scriptProperties, '3'));
    $apiKey = $modx->getOption('apiKey', $scriptProperties, '');
    $userName = $modx->getOption('userName', $scriptProperties, '');

    foreach ($feeds as $userId) {
        $cacheId = 'jsonderulo-flickrfeed-'.$cacheName.'-'.$userId;

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

        foreach ($feed->photos->photo as $photo) {
            foreach ($excludeEmpty as $k) {
                if ($photo->$k == '') {
                    continue 2;
                }
            }

            $rawFeedData[] = array(
                'id' => $photo->id,
                'created' => $photo->dateupload,
                'picture' => $photo->url_m,
                'picturelarge' => $photo->url_l,
                'title' => $photo->title,
                'username' => $userName,
            );
        }
    }

    foreach ($rawFeedData as $photo) {
        $output .= $modx->getChunk($tpl, $photo);
    }

//-----------------------------------------------------------
//  Google calendar public events
//-----------------------------------------------------------
} elseif( $feed == 'googlecalendar' ) {
    $feedUrl = 'https://www.googleapis.com/calendar/v3/calendars/{feedlocation}/events?key={apiKey}&orderBy=startTime&singleEvents=true&maxResults={limit}&timeMin={timeMin}';

    $apiKey = $modx->getOption('apiKey', $scriptProperties, '');
    $timeMin = urlencode($modx->getOption('timeMin', $scriptProperties, date("Y-m-d\TH:i:sP")));
    $excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'htmlLink'));
    $feeds = explode(',', $modx->getOption('feedLocation', $scriptProperties, ''));

    foreach ($feeds as $username) {
        $cacheId = 'jsonderulo-googlecalendarfeed-'.$cacheName.'-'.$username;

        if (($json = $modx->cacheManager->get($cacheId)) === null) {
            if ($ch === null) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            }

            curl_setopt_array($ch, array(
              CURLOPT_URL => str_replace(array('{apiKey}', '{feedlocation}', '{limit}', '{timeMin}'), array($apiKey, $username, $limit, $timeMin), $feedUrl),
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

        $feeditems = $feed->items;

        if($feeditems) {

            $timezone = $feed->timeZone;
            $calendarName = $feed->summary;

            foreach ($feeditems as $event) {
                foreach ($excludeEmpty as $k) {
                    if ($event->$k == '') {
                        continue 2;
                    }
                }

                if(empty($event->start->dateTime))
                {
                    $eventStart = $event->start->date;
                    $eventEnd = $event->end->date;
                    $allDayEvent = true;
                }
                else
                {
                    $eventStart = $event->start->dateTime;
                    $eventEnd = $event->end->dateTime;
                    $allDayEvent = false;
                }

                $rawFeedData[] = array(
                    'published' => strtotime($event->created),
                    'timezone' => $timezone,
                    'title' => $event->summary,
                    'content' => $event->description,
                    'link' => $event->htmlLink,
                    'calendarName' => $calendarName,
                    'eventEnd' => strtotime($eventEnd),
                    'eventStart' => strtotime($eventStart),
                    'location' => $event->location,
                    'allDayEvent' => $allDayEvent
                );

            }

            foreach ($rawFeedData as $item) {
                $output .= $modx->getChunk($tpl, $item);
            }

        } else {
            $item['title'] = 'No events.';
            $output = $modx->getChunk($tpl, $item);
        }
    }

//-----------------------------------------------------------
//  Google+ public posts
//-----------------------------------------------------------
} elseif( $feed == 'googleplus' ) {
    $feedUrl = 'https://www.googleapis.com/plus/v1/people/{userid}/activities/public?alt=json&pp=1&key={apikey}&maxResults={limit}';

    $userId = $modx->getOption('userId', $scriptProperties, '');
    $apiKey = $modx->getOption('apiKey', $scriptProperties, '');
    $excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, ''));
    $feeds = explode(',', $modx->getOption('cacheName', $scriptProperties, 'default'));

    foreach ($feeds as $feed) {
        $cacheId = 'jsonderulo-googleplusfeed-'.$cacheName.'-'.$userId;

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
                'fullImage' => $message->object->attachments[0]->fullImage->url,
            );

            $i++;
        }
    }

    foreach ($rawFeedData as $message) {
        $output .= $modx->getChunk($tpl, $message);
    }

//-----------------------------------------------------------
//  Last.fm user loved tunes
//-----------------------------------------------------------
} elseif( $feed == 'lastfm' ) {
    $feedUrl = 'http://ws.audioscrobbler.com/2.0/?method=user.getlovedtracks&user={username}&api_key={apikey}&format=json&limit={limit}';

    $excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'name'));
    $feeds = explode(',', $modx->getOption('users', $scriptProperties, ''));
    $apiKey = $modx->getOption('apiKey', $scriptProperties, '');

    foreach ($feeds as $username) {
        $cacheId = 'jsonderulo-lastfmfeed-'.$cacheName.'-'.$username;

        if (($json = $modx->cacheManager->get($cacheId)) === null) {
            if ($ch === null) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            }

            curl_setopt_array($ch, array(
              CURLOPT_URL => str_replace(array('{apikey}', '{username}', '{limit}'), array($apiKey, $username, $limit), $feedUrl),
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

      $feedtracks = $feed->lovedtracks;

        foreach ($feedtracks->track as $item) {
            foreach ($excludeEmpty as $k) {
                if ($item->$k == '') {
                    continue 2;
                }
            }

            $rawFeedData[] = array(
                'track' => $item->name,
                'artist' => $item->artist->name,
                'link' => $item->url,
                'picture' => $item->image[3]->{'#text'},
                'date' => $item->date->uts,
                'username' => $username,
            );
        }
    }

    foreach ($rawFeedData as $item) {
        $output .= $modx->getChunk($tpl, $item);
    }

//-----------------------------------------------------------
//  Last.fm user listened tunes
//-----------------------------------------------------------
} elseif( $feed == 'lastfmlistens' ) {
    $feedUrl = 'http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user={username}&api_key={apikey}&format=json&limit={limit}';

    $excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'name'));
    $feeds = explode(',', $modx->getOption('users', $scriptProperties, ''));
    $apiKey = $modx->getOption('apiKey', $scriptProperties, '');

    foreach ($feeds as $username) {
        $cacheId = 'jsonderulo-lastfmlistensfeed-'.$cacheName.'-'.$username;

        if (($json = $modx->cacheManager->get($cacheId)) === null) {
            if ($ch === null) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            }

            curl_setopt_array($ch, array(
              CURLOPT_URL => str_replace(array('{apikey}', '{username}', '{limit}'), array($apiKey, $username, $limit), $feedUrl),
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

      $feedtracks = $feed->recenttracks;

        foreach ($feedtracks->track as $item) {
            foreach ($excludeEmpty as $k) {
                if ($item->$k == '') {
                    continue 2;
                }
            }

            $rawFeedData[] = array(
                'track' => $item->name,
                'artist' => $item->artist->name,
                'link' => $item->url,
                'picture' => $item->image[3]->{'#text'},
                'date' => $item->date->uts,
                'username' => $username,
            );
        }
    }

    foreach ($rawFeedData as $item) {
        $output .= $modx->getChunk($tpl, $item);
    }

//-----------------------------------------------------------
//  Tumblr posts
//-----------------------------------------------------------
} elseif( $feed == 'tumblr' ) {
    $feedUrl = 'http://api.tumblr.com/v2/blog/{blogurl}/posts{posttype}?notes_info={notesinfo}&tag={tag}&limit={limit}&api_key={apikey}';

    $feeds = explode(',', $modx->getOption('blogUrl', $scriptProperties, ''));
    $postType = $modx->getOption('postType', $scriptProperties, '');
    $tag = $modx->getOption('tag', $scriptProperties, '');
    $notesInfo = $modx->getOption('notesInfo', $scriptProperties, 'false');
    $apiKey = $modx->getOption('apiKey', $scriptProperties, '');

    foreach ($feeds as $tumblr) {
        $cacheId = 'jsonderulo-tumblrfeed-'.$cacheName.'-'.$tumblr;

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
                $rawFeedData[$i]['image'] = $post->photos[0]->original_size->url;
            }

            $i++;
        }
    }

    foreach ($rawFeedData as $message) {
        $output .= $modx->getChunk($tpl, $message);
    }

//-----------------------------------------------------------
//  Twitter timeline
//-----------------------------------------------------------
} elseif( $feed == 'twitter' ) {
    $screenNames = explode(',', $modx->getOption('screenName', $scriptProperties, ''));
    $includeRTs = $modx->getOption('includeRTs', $scriptProperties, 1);
    $limit = $modx->getOption('limit', $scriptProperties, 5);
    $timelineType = $modx->getOption('timelineType', $scriptProperties, 'user_timeline');
    $excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'text'));
    $consumerKey = $modx->getOption('consumerKey', $scriptProperties, '');
    $consumerSecret = $modx->getOption('consumerSecret', $scriptProperties, '');
    $accessToken = $modx->getOption('accessToken', $scriptProperties, '');
    $accessTokenSecret = $modx->getOption('accessTokenSecret', $scriptProperties, '');
    $sortDir = $modx->getOption('sortDir', $scriptProperties, 'ASC');

    $feeds = array();
    $tweets = array();

    foreach( $screenNames as $i => $screenName) {
        if ($screenName != '') {
            $cacheId = 'jsonderulo-twitterfeed-'.$screenName.'-'.$cacheName;
        }else{
            $cacheId = 'jsonderulo-twitterfeed-'.$cacheName;
        }

        if (($json = $modx->cacheManager->get($cacheId)) === null) {
            require_once $modx->getOption('core_path').'components/jsonderulo/twitteroauth/twitteroauth.php';
            $fetch = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
            $fetch->host = "https://api.twitter.com/1.1/";
            $fetch->format = 'json';
            $fetch->decode_json = FALSE;
            $fetch->ssl_verifypeer = FALSE;
            $json = $fetch->get('statuses/'.$timelineType, array('include_rts' => $includeRTs, 'count' => $limit, 'screen_name' => $screenName));

            if(!empty($json)) {
                $modx->cacheManager->set($cacheId, $json, $cacheTime);
            }
        }

        $feeds[$i] = json_decode($json, true);
    }

    foreach($feeds as $feed) {
        $tweets = array_merge($tweets, $feed);
    }

    function sksort( &$array, $subkey, $sort_ascending ) {
        if ( count($array) ) {
            $temp_array[key($array)] = array_shift($array);
        }

        foreach ( $array as $key => $val ) {
            $offset = 0;
            $found = false;

            foreach ( $temp_array as $tmp_key => $tmp_val ) {
                if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey])) {
                    $temp_array = array_merge( (array)array_slice($temp_array,0,$offset),
                        array($key => $val),
                        array_slice($temp_array,$offset)
                        );
                    $found = true;
                }
                $offset++;
            }

            if ( !$found ) {
                $temp_array = array_merge($temp_array, array($key => $val));
            }
        }

        if ($sort_ascending == true) {
            $array = array_reverse($temp_array);
        } else {
            $array = $temp_array;
        }
    }

    foreach( $tweets as $i => $t ) {
        $tweets[$i]['created_at_timestamp'] = strtotime($t['created_at']);
    }

    if ( $sortDir == 'ASC' ) {
        sksort($tweets, 'created_at_timestamp', true);
    } else {
        sksort($tweets, 'created_at_timestamp', false);
    }

    array_splice($tweets, $limit);
    $tweets = json_decode(json_encode($tweets));

    $feed = $tweets;

    if ($feed === null) {
        $message['message'] = 'No tweets returned.';
        $output = $modx->getChunk($tpl, $message);
    } else {
        $i = 0;

        foreach ($feed as $message) {
            foreach ($excludeEmpty as $k) {
                if ($message->$k == '') {
                    continue 2;
                }
            }

            $input = $message->text;
            // Convert URLs into hyperlinks
            $input = preg_replace("/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", "<a href=\"\\0\">\\0</a>", $input);
            // Convert usernames (@) into links
            $input= preg_replace("(@([a-zA-Z0-9\_]+))", "<a href=\"https://twitter.com/\\1\">\\0</a>", $input);
            // Convert hash tags (#) to links
            $input= preg_replace('/(^|\s)#(\w+)/u', '\1<a href="https://twitter.com/search?q=%23\2&src=hash">#\2</a>', $input);

            $rawFeedData[$i] = array(
                'id' => $message->id_str,
                'message' => $input,
                'created' => strtotime($message->created_at),
                'picture' => $message->user->profile_image_url,
                'title' => $message->user->name,
                'username' => $message->user->screen_name,
                'retweetCount' => $message->retweet_count,
                'favouriteCount' => $message->favorite_count,
                'inReplyToStatusId' => $message->in_reply_to_status_id_str,
                'inReplyToScreenName' => $message->in_reply_to_screen_name,
                'isRetweet' => '0',
                'mediaThumb' =>$message->entities->media[0]->media_url.':thumb',
                'mediaSmall' =>$message->entities->media[0]->media_url.':small',
                'mediaMedium' =>$message->entities->media[0]->media_url.':medium',
                'mediaLarge' =>$message->entities->media[0]->media_url.':large',
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

        foreach ($rawFeedData as $message) {
            $output .= $modx->getChunk($tpl, $message);
        }
    }

//-----------------------------------------------------------
//  Twitter user favourites
//-----------------------------------------------------------
} elseif( $feed == 'twitterFaves' ) {
    $screenName = $modx->getOption('screenName', $scriptProperties, '');
    $limit = $modx->getOption('limit', $scriptProperties, 5);
    $excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'text'));
    $consumerKey = $modx->getOption('consumerKey', $scriptProperties, '');
    $consumerSecret = $modx->getOption('consumerSecret', $scriptProperties, '');
    $accessToken = $modx->getOption('accessToken', $scriptProperties, '');
    $accessTokenSecret = $modx->getOption('accessTokenSecret', $scriptProperties, '');

    if ($screenName != '') {
        $cacheId = 'jsonderulo-twitterfavesfeed-'.$screenName.'-'.$cacheName;
    }else{
        $cacheId = 'jsonderulo-twitterfavesfeed-'.$cacheName;
    }

    if (($json = $modx->cacheManager->get($cacheId)) === null) {
        require_once $modx->getOption('core_path').'components/jsonderulo/twitteroauth/twitteroauth.php';
        $fetch = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
        $fetch->host = "https://api.twitter.com/1.1/";
        $fetch->format = 'json';
        $fetch->decode_json = FALSE;
        $fetch->ssl_verifypeer = FALSE;
        $json = $fetch->get('favorites/list', array('count' => $limit, 'screen_name' => $screenName));

        if(!empty($json)) {
            $modx->cacheManager->set($cacheId, $json, $cacheTime);
        }
    }

    $feed = json_decode($json);

    if ($feed === null) {
        $message['message'] = 'No tweets returned.';
        $output = $modx->getChunk($tpl, $message);
    } else {
        $i = 0;

        foreach ($feed as $message) {
            foreach ($excludeEmpty as $k) {
                if ($message->$k == '') {
                    continue 2;
                }
            }

            $input = $message->text;
            // Convert URLs into hyperlinks
            $input = preg_replace("/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", "<a href=\"\\0\">\\0</a>", $input);
            // Convert usernames (@) into links
            $input= preg_replace("(@([a-zA-Z0-9\_]+))", "<a href=\"https://twitter.com/\\1\">\\0</a>", $input);
            // Convert hash tags (#) to links
            $input= preg_replace('/(^|\s)#(\w+)/', '\1<a href="https://twitter.com/search?q=%23\2&src=hash">#\2</a>', $input);

            $rawFeedData[$i] = array(
                'id' => $message->id_str,
                'message' => $input,
                'created' => strtotime($message->created_at),
                'picture' => $message->user->profile_image_url,
                'title' => $message->user->name,
                'username' => $message->user->screen_name,
                'retweetCount' => $message->retweet_count,
                'favouriteCount' => $message->favorite_count,
                'inReplyToStatusId' => $message->in_reply_to_status_id_str,
                'inReplyToScreenName' => $message->in_reply_to_screen_name,
                'isRetweet' => '0',
                'mediaThumb' =>$message->entities->media[0]->media_url.':thumb',
                'mediaSmall' =>$message->entities->media[0]->media_url.':small',
                'mediaMedium' =>$message->entities->media[0]->media_url.':medium',
                'mediaLarge' =>$message->entities->media[0]->media_url.':large',
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

        foreach ($rawFeedData as $message) {
            $output .= $modx->getChunk($tpl, $message);
        }
    }

//-----------------------------------------------------------
//  Vimeo likes
//-----------------------------------------------------------
} elseif( $feed == 'vimeo' ) {
    $feedUrl = 'http://vimeo.com/api/v2/{username}/likes.json';

    $excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'title'));
    $feeds = explode(',', $modx->getOption('users', $scriptProperties, ''));

    foreach ($feeds as $username) {
        $cacheId = 'jsonderulo-vimeofeed-'.$cacheName.'-'.$username;

        if (($json = $modx->cacheManager->get($cacheId)) === null) {
            if ($ch === null) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            }

            curl_setopt_array($ch, array(
              CURLOPT_URL => str_replace(array('{username}'), array($username), $feedUrl),
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

        foreach ($feed as $video) {
            $counter++;

            if($counter>$limit){
                  break;
            }

            foreach ($excludeEmpty as $k) {
                if ($video->$k == '') {
                    continue 2;
                }
            }

            $rawFeedData[] = array(
                'id' => $video->id,
                'url' => $video->url,
                'created' => strtotime($video->upload_date),
                'picture' => $video->thumbnail_large,
                'title' => $video->title,
                'username' => $userName,
            );
        }
    }

    foreach ($rawFeedData as $video) {
        $output .= $modx->getChunk($tpl, $video);
    }

//-----------------------------------------------------------
//  YouTube API V2 favourites
//-----------------------------------------------------------
} elseif( $feed == 'youtubev2' ) {
    $feedUrl = 'http://gdata.youtube.com/feeds/api/users/{username}/favorites?max-results={limit}&start-index={offset}&alt=json';

    $startIndex = $modx->getOption('startIndex', $scriptProperties, 1);
    $videoParams = $modx->getOption('videoParams', $scriptProperties, '?fs=1');
    $excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'link'));
    $feeds = explode(',', $modx->getOption('users', $scriptProperties, ''));

    foreach ($feeds as $username) {
        $cacheId = 'jsonderulo-youtubefeed-'.$cacheName.'-'.$username;

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
                'embedlink' => 'https://www.youtube.com/embed/' .$videoId. $videoParams,
                'videoId' => $videoId,
                'author' => $video->author[0]->name->{'$t'},
            );
        }
    }

    foreach ($rawFeedData as $image) {
        $output .= $modx->getChunk($tpl, $image);
    }

//-----------------------------------------------------------
//  YouTube API V2 uploads
//-----------------------------------------------------------
} elseif( $feed == 'youtubev2uploads' ) {
    $feedUrl = 'http://gdata.youtube.com/feeds/api/users/{username}/uploads?max-results={limit}&start-index={offset}&alt=json';

    $startIndex = $modx->getOption('startIndex', $scriptProperties, 1);
    $videoParams = $modx->getOption('videoParams', $scriptProperties, '?fs=1');
    $excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'link'));
    $feeds = explode(',', $modx->getOption('users', $scriptProperties, ''));

    foreach ($feeds as $username) {
        $cacheId = 'jsonderulo-youtubeuploadsfeed-'.$cacheName.'-'.$username;

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
                'embedlink' => 'https://www.youtube.com/embed/' .$videoId. $videoParams,
                'videoId' => $videoId,
                'author' => $video->author[0]->name->{'$t'},
            );
        }
    }

    foreach ($rawFeedData as $image) {
        $output .= $modx->getChunk($tpl, $image);
    }

//-----------------------------------------------------------
//  YouTube API V3 public playlist
//-----------------------------------------------------------
} elseif( $feed == 'youtubev3playlist' ) {
    $feedUrl = 'https://www.googleapis.com/youtube/v3/playlistItems?part=id%2C+snippet%2CcontentDetails%2Cstatus&playlistId={playlistid}&fields=etag%2Citems%2CpageInfo&key={apikey}&maxResults={limit}';

    $playlist = $modx->getOption('playlistId', $scriptProperties, '');
    $apiKey = $modx->getOption('apiKey', $scriptProperties, '');

    $cacheId = 'jsonderulo-youtubefeedv3publicplaylist-'.$cacheName.'-'.$playlist;

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
            return;
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
                'embedlink' => 'https://www.youtube.com/embed/' . $video->snippet->resourceId->videoId,
                'videoId' => $video->snippet->resourceId->videoId,
                'author' => $video->author[0]->name->{'$t'},
                );
        }

        foreach ($rawFeedData as $image) {
            $output .= $modx->getChunk($tpl, $image);
        }
    }
}//close if

//-----------------------------------------------------------
//  close curl connection, return data from snippet
//-----------------------------------------------------------

if ($ch !== null) {
    curl_close($ch);
}

return $output;
