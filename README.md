# JSONDerulo - A JSON feed fetcher for MODX Revolution CMS

Snippets and chunks to pull in various social JSON feeds. [Available on the MODX Extras repo](http://modx.com/extras/package/jsonderulo23).

Results are cached for 12 hours by default, but you can specify your own time limit in seconds using &cacheTime. Make sure you call the snippet uncached from your templates!
Most snippets allow you to specify multiple usernames, however any feeds which require an API key will not yet work with multiple accounts.
The following feeds are already set up:

* App.net - most recent posts
* Delicious - most recent bookmarks
* Flickr - most recent photographs in your photostream
* Google+ - most recent public posts
* LastFM - recent "loved" tracks or recent "listens"
* Picasa - Photos from a named album
* Twitter - most recent tweets
* Vimeo - most recent "likes"
* YouTube - Most recent additions to "favourites" playlist or specific user's uploads
* ZooTool - Most recent items (pages or images)

## Thanks!

Plenty of thouroughly helpful help from [Mister John Noel](https://github.com/johnnoel) and his code on which these snippets are based.

Also, hat tip to [basvaneijk](https://github.com/basvaneijk) for the preg_replace stuff to auto link @, # and URLs within the [[+message]]

## Requirements/Prerequisites

* Tested on MODx 2.2.x
* PHPThumbOf
* API Keys/app secrets, etc for certain feeds
* That's it!


## Available snippets

### App.net

You need your user id to get this working, not your username. You can find it on your profile page. [Hat tip to "man"](https://alpha.app.net/man/post/20858).

```
<ul>
  [[!AppDotNetFeed? &tpl=`AppDotNetFeedItem` &userId=`USERID` &limit=`LIMIT` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

### Delicious

```
<ul>
  [[!DeliciousFeed? &tpl=`DeliciousFeedItem` &users=`USERNAME` &limit=`LIMIT` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

### Flickr

Requires API key, get one here: [Flickr API Key](http://www.flickr.com/services/apps/create/apply)

```
<ul>
	[[!FlickrFeed? &tpl=`FlickrFeedItem` &limit=`LIMIT` &users=`FLICKR USER ID` &apiKey=`API_KEY` &userName=`USERNAME` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

### Google+

Requires API key, get one here: [Google API key](https://code.google.com/apis/console/)

```
<ul>
    [[!GooglePlusFeed? &tpl=`GooglePlusFeedItem` &limit=`LIMIT` &userId=`USER_ID` &apiKey=`API_KEY` &cacheName=`UNIQUE_NAME_FOR_CACHE_FILE` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

### LastFM

Requires api key, get one here: [LastFM API Key](http://www.last.fm/api/account)

```
<ul>
	[[!LastFmFeed? &tpl=`LastFmFeedItem` &limit=`LIMIT` &users=`USERNAME` &apiKey=`API KEY` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

```
<ul>
	[[!LastFmListensFeed? &tpl=`LastFmFeedItem` &limit=`LIMIT` &users=`USERNAME` &apiKey=`API KEY` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

### Picasa

```
<ul>
	[[!PicasaFeed? &tpl=`PicasaFeedItem` &limit=`LIMIT` &users=`USERID` &albumId=`ALBUMID` &albumName=`ALBUMNAME` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

### Twitter

```
<ul>
	[[!TwitterFeed? &tpl=`TwitterFeedItem` &limit=`LIMIT` &users=`USERNAME` &includeRTs=`1 or 0` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

### Twitter (New API version)

You need to set up a Twitter "App" to make this work. From March 2013, this is the ONLY way. The method above will stop working altogether by this date. See more information in the Twitter section below!

The cacheName option is for users who may want to use the snippet more than once on a site for different users' tweets. Setting this appends the text to the cache filename so multiple feeds can be cached. I've added a string replacement to swap spaces for hyphens in there too.

The screenName option is *optional*. It will allow you to fetch another user's timeline. If this is not provided, it will default to the user whose consumer key, etc, that you are using.

```
<ul>
	[[!TwitterFeedNew? &tpl=`TwitterFeedItemNew` &limit=`LIMIT` &cacheName=`UNIQUE_NAME_TO_APPEND_TO_CACHE_FILE` &screenName=`USER_SCREEN_NAME_TO_FETCH_TIMELINE_FOR` &includeRTs=`1 or 0` &consumerKey=`YOUR_CONSUMER_KEY` &consumerSecret=`YOUR_CONSUMER_SECRET` &accessToken=`YOUR_ACCESS_TOKEN` &accessTokenSecret=`YOUR_ACCESS_TOKEN_SECRET` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

### Twitter (New API version for multiple timelines)

As above, you'll need to have an app set up to use the new Twitter API. This version **does not** have the cacheName option, and the screenName is **required**. This is not needed for all timeline types available, but it is used by the snippet for generating a cache filename for the feed. Pass multiple screenNames separated by commas to get more than one timeline. E.g: &screenName=`twitter,twitterapi`

Combined timelines will be output in the order specified in the call. If you want to randomise the output then you're probably going to need a bit of javascript to shuffle the items after the DOM has finished loading. Maybe like this handy bit of script I found a while back:

```javascript
/*------------------------------------------------------------------------------------
 * Shuffle function
 *----------------------------------------------------------------------------------*/
	
(function($){
    $.fn.shuffle = function() {
 
        var allElems = this.get(),
            getRandom = function(max) {
                return Math.floor(Math.random() * max);
            },
            shuffled = $.map(allElems, function(){
                var random = getRandom(allElems.length),
                    randEl = $(allElems[random]).clone(true)[0];
                allElems.splice(random, 1);
                return randEl;
           });
 
        this.each(function(i){
            $(this).replaceWith($(shuffled[i]));
        });
 
        return $(shuffled);
    };
})(jQuery);
```

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
	[[!VimeoFeed? &tpl=`VimeoFeedItem` &users=`USERNAME` &limit=`LIMIT` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

### YouTube

```
<ul>
	[[!YouTubeFeed? &tpl=`YouTubeFeedItem` &limit=`LIMIT` &users=`USERNAME` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

```
<ul>
	[[!YouTubeFeedUploads? &tpl=`YouTubeFeedItem` &limit=`LIMIT` &users=`USERNAME` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

### ZooTool

Requires API key, get one here: [ZooTool API Key](http://zootool.com/api/keys)

```
<ul>
	[[!ZooToolFeed? &tpl=`ZooToolFeedItem` &limit=`LIMIT` &users=`USERNAME` &apiKey=`API KEY` &cacheTime=`CACHE_TIME_IN_SECONDS`]]
</ul>
```

## Chunks

I've provided basic chunks to get you started. Any feed which returns thumbnails/imagery I've used PHPThumbOf, if you use these chunks make sure you have it installed too!

You can also use the MODx's output filters to provide fallback should the feed you request be empty. E.g. - such as a new twitter account with no tweets yet:

```
[[!TwitterFeed:default=`<li>No tweets</li>`? &tpl=`TwitterFeedItem` &limit=`10` &users=`USERNAME`]]
```

The basic Twitter chunk also shows you how to use the isRetweet option to switch out your details for the author of the tweet you retweeted. The example isn't the best, but it'll give you the right idea, and will also allow you to integrate a retweet icon if you wish.

### Chunk placeholders

Currently only the basics have placeholders provided. The YouTube feed, for example, has more options that you may ever need. If I find I ever use them, I will add them in.


#### App.net:

The App.net feed is very pleasant and gives you the option of either "text" or "html" versions of a post. I've included placeholders for both. The basic chunk I've provided only uses [[+text]].

```
[[+id]]
[[+text]]
[[+html]]
[[+created]]
[[+picture]]
[[+title]]
[[+username]]
[[+profile]]
[[+postUrl]]
```

#### Delicious:

```
[[+title]]
[[+description]]
[[+link]]
[[+date]]
[[+username]]
```

#### Flickr:

```
[[+id]]
[[+created]]
[[+picture]]
[[+picturelarge]]
[[+title]]
[[+username]]
```

### Google+:

```
[[+text]]
[[+html]]
[[+postId]]
[[+attachmentUrl]]
[[+repliesCount]]
[[+plusCount]]
[[+resharesCount]]
[[+postUrl]]
[[+postDate]]
[[+profileUrl]]
[[+avatar]]
[[+displayName]]

```

#### LastFM:

```
[[+track]]
[[+artist]]
[[+link]]
[[+picture]]
[[+date]]
[[+username]]
```

### Picasa:

```
[[+link]]
[[+albumid]]
[[+created]]
[[+picture]]
[[+title]]
[[+userid]]
[[+albumname]]
```

#### Twitter:

```
[[+id]]
[[+message]]
[[+created]]
[[+picture]]
[[+title]]
[[+username]]
[[+retweetCount]]
[[+isRetweet]]
[[+originalAuthorPicture]]
[[+originalAuthor]]
[[+originalUsername]]
[[+originalId]]
```
#### Vimeo:

```
[[+id]]
[[+url]]
[[+created]]
[[+picture]]
[[+title]]
[[+username]]
```

#### YouTube:

```
[[+published]]
[[+picture]]
[[+title]]
[[+ytlink]]
[[+embedlink]]
[[+author]]
```

#### ZooTool:

```
[[+date]]
[[+picture]]
[[+title]]
[[+username]]
[[+referrer]]
[[+permalink]]
```

### CSS

I've used the clearfix from the [HTML5 Boilerplate normalize.css](http://www.html5boilerplate.com) on ```<li>``` items to clear floats inside them:

```css
.cf:before, .cf:after { content: ""; display: table; }
.cf:after { clear: both; }
.cf { *zoom: 1; }
```


### PHPThumbOf settings

Ensure you have the following in the "phpthumb_nohotlink_valid_domains" section:

* Flickr: ```*.flickr.com, *.staticflickr.com```
* ZooTool: ```s3.amazonaws.com```
* YouTube Thumbnails: ```.ytimg.com```
* LastFM Album art: ```userserve-ak.last.fm```
* Vimeo Thumbnails: ```*.vimeocdn.com```
* Twitter profile avatars: ```*.twimg.com```
* Picasa/Google+ Thumbnails: ```*.googleusercontent.com```
* App.net avatars: ```*.cloudfront.net```


## Twitter feed and the new API

Much publicised changes to the Twitter API means that you have to authenticate to get public feed data now. I will keep the original, easy way to get a Twitter feed until the date they switch this off, but you're probably best going through the pain of getting onto the new API stuff now.

This uses the excellent (and thoroughly easy to use) [TwitterOAuth](https://github.com/abraham/twitteroauth/) by [Abraham Williams](https://github.com/abraham) (check out his incredible beard!). If you're adding the JSONDerulo snippets and chunks manually, you're going to need to upload a copy of the TwitterOAuth stuff to {core_path}/components/jsonderulo/

The transport package will do this automatically for you. (Hopefully).

To set up a Twitter "App", go to the [Twitter dev site](https://dev.twitter.com/apps/) and choose "Create a new application". Fill in the form and you'll end up with a new "app" which will let you know the consumer secret, consumer key, access token, and access token secret.

Hat tip also to [Stewart Orr](http://www.qodo.co.uk/blog/twitterx-a-new-modx-extra-for-pulling-in-twitter-feeds-using-api-1.1/), whose TwitterX addon reminded me to get my backside in gear and fix this package up!

## Twitter display "requirements"

I've included a new chunk for displaying tweets. This includes several changes which *should* make the output acceptable based on the [Developer display requirements documentation](https://dev.twitter.com/terms/display-requirements). You will need to include the Twitter widgets.js somewhere on the page you're displaying tweets on. You only need this included ONCE on the page, not for each tweet.

```
<script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>
```
