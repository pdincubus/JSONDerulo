# JSONDerulo

Snippets and chunks to pull in various social JSON feeds.
Results are cached for 12 hours by the snippet. Make sure you call the snippet uncached from your templates!
Most snippets allow you to specify multiple usernames, however any feeds which require an API key will not yet work with multiple accounts.
The following feeds are already set up:

* Delicious - most recent bookmarks
* Flickr - most recent photographs in your photostream
* LastFM - recent "loved" tracks or recent "listens"
* Twitter - most recent tweets
* Vimeo - most recent "likes"
* YouTube - Most recent additions to "favourites" playlist
* ZooTool - Most recent items (pages or images)

## Thanks!

Plenty of thouroughly helpful help from [Mister John Noel](https://github.com/johnnoel) and his code on which these snippets are based.

## Requirements/Prerequisites

* Tested on MODx 2.2.0
* PHPThumbOf
* API Keys for certain feeds
* That's it!

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

### Twitter

```
<ul>
	[[!TwitterFeed? &tpl=`TwitterFeedItem` &limit=`{LIMIT}` &users=`{USERNAME}`]]
</ul>
```

### Vimeo

```
<ul>
	[[!VimeoFeed? &tpl=`VimeoFeedItem` &users=`{USERNAME}`]]
</ul>
```

### YouTube

```
<ul>
	[[!YouTubeFeed? &tpl=`YouTubeFeedItem` &limit=`{LIMIT}` &users=`{USERNAME}`]]
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

### PHPThumbOf settings

Ensure you have the following in the "phpthumb_nohotlink_valid_domains" section:

```{http_host},*.flickr.com,*.staticflickr.com,s3.amazonaws.com,*.ytimg.com,userserve-ak.last.fm,*.vimeocdn.com,*.twimg.com```

* Flickr: ```*.flickr.com, *.staticflickr.com```
* ZooTool: ```s3.amazonaws.com```
* YouTube Thumbnails: ```.ytimg.com```
* LastFM Album art: ```userserve-ak.last.fm```
* Vimeo Thumbnails: ```*.vimeocdn.com```
* Twitter profile avatars: ```*.twimg.com```

### Chunk placeholders

Currently only the basics have placeholders provided. The YouTube feed, for example, has more options that you may ever need. If I find I ever use them, I will add them in.

#### Delicious:

```
[[+title]]
[[+link]]
[[+date]]
[[+username]]
```
#### Flickr:

```
[[+id]]
[[+created]]
[[+picture]]
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
#### Twitter:

```
[[+id]]
[[+message]]
[[+created]]
[[+picture]]
[[+title]]
[[+username]]
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