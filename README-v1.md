# JSONDerulo - A JSON feed fetcher for MODX Revolution CMS

## Available snippets

### App.net

You need your user id to get this working, not your username. You can find it on your profile page. [Hat tip to "man"](https://alpha.app.net/man/post/20858).

```
<ul>
  [[!AppDotNetFeed? &tpl=`AppDotNetFeedItem` &userId=`USERID` &limit=`LIMIT` &cacheTime=`CACHE_TIME_IN_SECONDS` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE`]]
</ul>
```

### Delicious

```
<ul>
  [[!DeliciousFeed? &tpl=`DeliciousFeedItem` &users=`USERNAME` &limit=`LIMIT` &cacheTime=`CACHE_TIME_IN_SECONDS` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE`]]
</ul>
```

### Flickr

Requires API key, get one here: [Flickr API Key](http://www.flickr.com/services/apps/create/apply)

```
<ul>
    [[!FlickrFeed? &tpl=`FlickrFeedItem` &limit=`LIMIT` &users=`FLICKR USER ID` &apiKey=`API_KEY` &userName=`USERNAME` &cacheTime=`CACHE_TIME_IN_SECONDS` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE`]]
</ul>
```

### Google+

Requires API key, get one here: [Google API key](https://code.google.com/apis/console/)

```
<ul>
    [[!GooglePlusFeed? &tpl=`GooglePlusFeedItem` &limit=`LIMIT` &userId=`USER_ID` &apiKey=`API_KEY` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

### Google calendar

You'll need to find the calendar's public feed URL. Don't panic, [read the instructions further down the page](#google-calendar-and-public-feed-urls)...

```
<ul>
    [[!GoogleCalendarFeed? &feedLocation=`FEED LOCATION` &limit=`LIMIT` &tpl=`GoogleCalendarFeedItem` &cacheTime=`CACHE_TIME_IN_SECONDS` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE`]]
</ul>
```

### LastFM

Requires api key, get one here: [LastFM API Key](http://www.last.fm/api/account)

```
<ul>
    [[!LastFmFeed? &tpl=`LastFmFeedItem` &limit=`LIMIT` &users=`USERNAME` &apiKey=`API KEY` &cacheTime=`CACHE_TIME_IN_SECONDS` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE`]]
</ul>
```

```
<ul>
    [[!LastFmListensFeed? &tpl=`LastFmFeedItem` &limit=`LIMIT` &users=`USERNAME` &apiKey=`API KEY` &cacheTime=`CACHE_TIME_IN_SECONDS` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE`]]
</ul>
```

### Picasa

```
<ul>
    [[!PicasaFeed? &tpl=`PicasaFeedItem` &limit=`LIMIT` &users=`USERID` &albumId=`ALBUMID` &albumName=`ALBUMNAME` &cacheTime=`CACHE_TIME_IN_SECONDS` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE`]]
</ul>
```

### Tumblr

The &postType option is optional (if not set, feed will return all post types), but can be set to ```audio```, ```video```, ```photo```, ```link```, ```text```

You can only set ONE postType.

```
<ul>
    [[!TumblrFeed? &tpl=`TumblrFeedItem` &limit=`LIMIT` &tag=`TAG TO FILTER BY` &postType=`POST TYPE TO FETCH` &notesInfo=`TRUE or FALSE` &blogUrl=`YOUR TUMBLR URL` &apiKey=`API KEY` &cacheTime=`CACHE_TIME_IN_SECONDS` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE`]]
</ul>
```

### Twitter (New API version)

You need to set up a Twitter "App" to make this work - [from here](https://dev.twitter.com/apps). From March 2013, this is the ONLY way.

The cacheName option is for users who may want to use the snippet more than once on a site for different users' tweets. Setting this appends the text to the cache filename so multiple feeds can be cached. I've added a string replacement to swap spaces for hyphens in there too.

The screenName option is *optional*. It will allow you to fetch another user's timeline. If this is not provided, it will default to the user whose consumer key, etc, that you are using.

```
<ul>
    [[!TwitterFeedNew? &tpl=`TwitterFeedItemNew` &limit=`LIMIT` &cacheName=`UNIQUE_NAME_TO_APPEND_TO_CACHE_FILE` &screenName=`USER_SCREEN_NAME_TO_FETCH_TIMELINE_FOR` &includeRTs=`1 or 0` &consumerKey=`YOUR_CONSUMER_KEY` &consumerSecret=`YOUR_CONSUMER_SECRET` &accessToken=`YOUR_ACCESS_TOKEN` &accessTokenSecret=`YOUR_ACCESS_TOKEN_SECRET` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

### Twitter (New API version for multiple timelines)

As above, you'll need to have an app set up to use the new Twitter API. This version **does not** have the cacheName option, and the screenName is **required**. This is not needed for all timeline types available, but it is used by the snippet for generating a cache filename for the feed. Pass multiple screenNames separated by commas to get more than one timeline. E.g: &screenName=`twitter,twitterapi`

For &timelineType, there are a few options:

* user_timeline - the default option. Your tweets.
* home_timeline - your tweets and tweets from those you follow
* mentions - Ronseal.
* retweets_of_me - Ronseal.
* any other timeline type listed in the [Twitter API docs](https://dev.twitter.com/docs/api/1.1). The four above are likely the most useful!


```
<ul>
    [[!TwitterFeedNewMultipleFeeds? &tpl=`TwitterFeedItemNew` &limit=`LIMIT` &screenName=`COMMAS_SEPARATED_LIST_OF_SCREEN_NAMES_TO_FETCH_TIMELINE_FOR` &includeRTs=`1 or 0` &consumerKey=`YOUR_CONSUMER_KEY` &consumerSecret=`YOUR_CONSUMER_SECRET` &accessToken=`YOUR_ACCESS_TOKEN` &accessTokenSecret=`YOUR_ACCESS_TOKEN_SECRET` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

### Vimeo

```
<ul>
    [[!VimeoFeed? &tpl=`VimeoFeedItem` &users=`USERNAME` &limit=`LIMIT` &cacheTime=`CACHE_TIME_IN_SECONDS` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE`]]
</ul>
```

### YouTube

```
<ul>
    [[!YouTubeFeed? &tpl=`YouTubeFeedItem` &limit=`LIMIT` &users=`USERNAME` &cacheTime=`CACHE_TIME_IN_SECONDS` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE`]]
</ul>
```

```
<ul>
    [[!YouTubeFeedUploads? &tpl=`YouTubeFeedItem` &limit=`LIMIT` &users=`USERNAME` &cacheTime=`CACHE_TIME_IN_SECONDS` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE`]]
</ul>
```

```
<ul>
    [[!YouTubeFeedV3PublicPlaylist? &tpl=`YouTubeFeedItem` &limit=`LIMIT` &playlistId=`YOUR_PLAYLIST_ID` &cacheTime=`CACHE_TIME_IN_SECONDS` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE` &apiKey=`YOUR_V3_API_KEY`]]
</ul>
```

Grab an API key for v3 from [Google API Console](https://code.google.com/apis/console/). Ensure you switch API v3 access on!

### ZooTool

Requires API key, get one here: [ZooTool API Key](http://zootool.com/api/keys)

```
<ul>
    [[!ZooToolFeed? &tpl=`ZooToolFeedItem` &limit=`LIMIT` &users=`USERNAME` &apiKey=`API KEY` &cacheTime=`CACHE_TIME_IN_SECONDS` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE`]]
</ul>
```
