<?php
/*
* @author Phil Steer
* @package JSONDerulo
* @site https://github.com/pdincubus/JSONDerulo
* @site MODX Extra: http://modx.com/extras/package/jsonderulo
* Fetches public Google calendar events in JSON format and allows templating via chunk
*/

$feedUrl = 'http://www.google.com/calendar/feeds/{feedlocation}/public/full?alt=json&orderby=starttime&max-results={limit}&singleevents=true&sortorder=ascending&futureevents=true
';

$ch = null;

$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, 2);
$excludeEmpty = explode(',', $modx->getOption('excludeEmpty', $scriptProperties, 'link'));
$feeds = explode(',', $modx->getOption('feedLocation', $scriptProperties, ''));
$cacheName = $modx->getOption('cacheName', $scriptProperties, '');
$cacheTime =    $modx->getOption('cacheTime', $scriptProperties, 43200);

$rawFeedData = array();
$cacheName = str_replace(" ", "-", $cacheName);

foreach ($feeds as $username) {
    $cacheId = 'googlecalendarfeed-'.$cacheName.'-'.$username;

    if (($json = $modx->cacheManager->get($cacheId)) === null) {
        if ($ch === null) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }

        curl_setopt_array($ch, array(
          CURLOPT_URL => str_replace(array('{feedlocation}', '{limit}'), array($username, $limit), $feedUrl),
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

    $timezone = $feeditems->{'gCal$timezone'}->value;

    foreach ($feeditems->entry as $event) {

        foreach ($excludeEmpty as $k) {
            if ($event->$k == '') {
                continue 2;
            }
        }

        $videoId = substr($event->id->{'$t'},42);

        $rawFeedData[] = array(
            'published' => strtotime($event->published->{'$t'}),
            'timezone' => $timezone,
            'title' => $event->title->{'$t'},
            'content' => $event->content->{'$t'},
            'link' => $event->link[0]->href,
            'calendarName' => $event->title->{'$t'},
            'eventEnd' => strtotime($event->{'gd$when'}[0]->endTime),
            'eventStart' => strtotime($event->{'gd$when'}[0]->startTime),
            'location' => $event->{'gd$where'}[0]->valueString,
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
