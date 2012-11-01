# JSONDerulo - A JSON feed fetcher for MODX Revolution CMS

Snippets and chunks to pull in various social JSON feeds.
Results are cached for 12 hours by the snippet. Make sure you call the snippet uncached from your templates!
Most snippets allow you to specify multiple usernames, however any feeds which require an API key will not yet work with multiple accounts.
The following feeds are already set up:

* Delicious - most recent bookmarks
* Flickr - most recent photographs in your photostream
* LastFM - recent "loved" tracks or recent "listens"
* Picasa - Photos from a named album
* Twitter - most recent tweets
* Vimeo - most recent "likes"
* YouTube - Most recent additions to "favourites" playlist or specific user's uploads
* ZooTool - Most recent items (pages or images)

## Thanks!

Plenty of thouroughly helpful help from [Mister John Noel](https://github.com/johnnoel) and his code on which these snippets are based.

## Requirements/Prerequisites

* Tested on MODx 2.2.0 / 2.2.1
* PHPThumbOf
* API Keys for certain feeds
* That's it!

## Still to do

~~Having added option to include retweets in the Twitter feed, probably needs some work on placeholders available specific to retweets.~~
Done. See placeholders further down...


## Available snippets

### Delicious

```
<ul>
  [[!DeliciousFeed? &tpl=`DeliciousFeedItem` &users=`{USERNAME}` &limit=`{LIMIT}`]]
</ul>
```

### Flickr

Requires API key, get one here: [Flickr API Key](http://www.flickr.com/services/apps/create/apply)

```
<ul>
	[[!FlickrFeed? &tpl=`FlickrFeedItem` &limit=`{LIMIT}` &users=`{FLICKR USER ID}` &apiKey=`{API KEY}` &userName=`{USERNAME}`]]
</ul>
```

### LastFM

Requires api key, get one here: [LastFM API Key](http://www.last.fm/api/account)

```
<ul>
	[[!LastFmFeed? &tpl=`LastFmFeedItem` &limit=`{LIMIT}` &users=`{USERNAME}` &apiKey=`{API KEY}`]]
</ul>
```

```
<ul>
	[[!LastFmListensFeed? &tpl=`LastFmFeedItem` &limit=`{LIMIT}` &users=`{USERNAME}` &apiKey=`{API KEY}`]]
</ul>
```

### Picasa

```
<ul>
	[[!PicasaFeed? &tpl=`PicasaFeedItem` &limit=`{LIMIT}` &users=`{USERID}` &albumId=`{ALBUMID}` &albumName=`{ALBUMNAME}`]]
</ul>
```

### Twitter

```
<ul>
	[[!TwitterFeed? &tpl=`TwitterFeedItem` &limit=`{LIMIT}` &users=`{USERNAME}` &includeRTs=`{1 or 0}`]]
</ul>
```

### Twitter (New API version)

You need to set up a Twitter "App" to make this work. From March 2013, this is the ONLY way. The method above will stop working altogether by this date. See more information in the Twitter section below!

```
<ul>
	[[!TwitterFeedNew? &tpl=`TwitterFeedItem` &limit=`{LIMIT}` &users=`{USERNAME}` &includeRTs=`{1 or 0}` &consumerKey=`{YOUR_CONSUMER_KEY}` &consumerSecret=`{YOUR_CONSUMER_SECRET}` &accessToken=`{YOUR_ACCESS_TOKEN}` &accessTokenSecret=`{YOUR_ACCESS_TOKEN_SECRET}`]]
</ul>
```

### Vimeo

```
<ul>
	[[!VimeoFeed? &tpl=`VimeoFeedItem` &users=`{USERNAME}` &limit=`{LIMIT}`]]
</ul>
```

### YouTube

```
<ul>
	[[!YouTubeFeed? &tpl=`YouTubeFeedItem` &limit=`{LIMIT}` &users=`{USERNAME}`]]
</ul>
```

```
<ul>
	[[!YouTubeFeedUploads? &tpl=`YouTubeFeedItem` &limit=`{LIMIT}` &users=`{USERNAME}`]]
</ul>
```

### ZooTool

Requires API key, get one here: [ZooTool API Key](http://zootool.com/api/keys)

```
<ul>
	[[!ZooToolFeed? &tpl=`ZooToolFeedItem` &limit=`{LIMIT}` &users=`{USERNAME}` &apiKey=`{API KEY}`]]
</ul>
```

## Chunks

I've provided basic chunks to get you started. Any feed which returns thumbnails/imagery I've used PHPThumbOf, if you use these chunks make sure you have it installed too!

You can also use the MODx's output filters to provide fallback should the feed you request be empty. E.g. - such as a new twitter account with no tweets yet:

```
[[!TwitterFeed:default=`<li>No tweets</li>`? &tpl=`TwitterFeedItem` &limit=`10` &users=`{USERNAME}`]]
```

The basic Twitter chunk also shows you how to use the isRetweet option to switch out your details for the author of the tweet you retweeted. The example isn't the best, but it'll give you the right idea, and will also allow you to integrate a retweet icon if you wish.

### Chunk placeholders

Currently only the basics have placeholders provided. The YouTube feed, for example, has more options that you may ever need. If I find I ever use them, I will add them in.

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
* Picasa Thumbnails: ```*.googleusercontent.com```


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