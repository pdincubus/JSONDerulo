# JSONDerulo

Snippets and chunks to pull in various social JSON feeds.
Results are cached for 12 hours by the snippet. Make sure you call the snippet uncached from your templates!
Most snippets allow you to specify multiple usernames, however any feeds which require an API key will not yet work with multiple accounts.
The following feeds are already set up:

* Delicious - most recent bookmarks
```http://feeds.delicious.com/v2/json/{username}```
* Flickr - most recent photographs in your photostream
```http://api.flickr.com/services/rest/?method=flickr.photos.search&format=json&nojsoncallback=1&api_key={apikey}&user_id={userid}&per_page={limit}&extras=url_m,date_upload```
* LastFM - recent "loved" tracks or recent "listens"
```http://ws.audioscrobbler.com/2.0/?method=user.getlovedtracks&user={username}&api_key={apikey}&format=json&limit={limit}
http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user={username}&api_key={apikey}&format=json&limit={limit}```
* Twitter - most recent tweets
```https://api.twitter.com/1/statuses/user_timeline.json?screen_name={username}&count={limit}```
* Vimeo - most recent "likes"
```http://vimeo.com/api/v2/{username}/likes.json```
* YouTube - Most recent additions to "favourites" playlist
```http://gdata.youtube.com/feeds/api/users/{username}/favorites?max-results={limit}&alt=json```
* ZooTool - Most recent items (pages or images)
```http://zootool.com/api/users/items/?username={username}&apikey={apikey}&limit={limit}```

## PHPThumbOf settings

Ensure you have the following in the "phpthumb_nohotlink_valid_domains" section:
```
{http_host},*.flickr.com,*.staticflickr.com,s3.amazonaws.com,*.ytimg.com,userserve-ak.last.fm,*.vimeocdn.com,*.twimg.com
```
Flickr: ```*.flickr.com, *.staticflickr.com```
ZooTool: ```s3.amazonaws.com```
YouTube Thumbnails: ```.ytimg.com```
LastFM Album art: ```userserve-ak.last.fm```
Vimeo Thumbnails: ```*.vimeocdn.com```
Twitter profile avatars: ```*.twimg.com```

## Available snippets

### Delicious
```html
<ul>
	[[!DeliciousFeed? &tpl=`DeliciousFeedItem` &users=`{USERNAME}` &limit=`{LIMIT}`]]
</ul>
```

### Flickr
Requires API key, get one here:
[Flickr API Key](http://www.flickr.com/services/apps/create/apply)
```html
<ul>
	[[!FlickrFeed? &tpl=`FlickrFeedItem` &limit=`{LIMIT}` &users=`{FLICKR USER ID}` &apiKey=`{API KEY}` &userName=`{USERNAME}`]]
</ul>
```
### LastFM
Requires api key, get one here:
[LastFM API Key](http://www.last.fm/api/account)
```html
<ul>
	[[!LastFmFeed? &tpl=`LastFmFeedItem` &limit=`{LIMIT}` &users=`{USERNAME}` &apiKey=`{API KEY}`]]
</ul>
```
### Twitter
```html
<ul>
	[[!TwitterFeed? &tpl=`TwitterFeedItem` &limit=`{LIMIT}` &users=`{USERNAME}`]]
</ul>
```
### Vimeo
```html
<ul>
	[[!VimeoFeed? &tpl=`VimeoFeedItem` &users=`{USERNAME}`]]
</ul>
```
### YouTube
```html
<ul>
	[[!YouTubeFeed? &tpl=`YouTubeFeedItem` &limit=`{LIMIT}` &users=`{USERNAME}`]]
</ul>
```
### ZooTool
Requires API key, get one here:
[ZooTool API Key](http://zootool.com/api/keys)
```html
<ul>
	[[!ZooToolFeed? &tpl=`ZooToolFeedItem` &limit=`{LIMIT}` &users=`{USERNAME}` &apiKey=`{API KEY}`]]
</ul>
```
## Chunk placeholders

Currently only the basics have placeholders provided. The YouTube feed, for example, has more options that you may ever need. If I find I ever use them, I will add them in.

### Delicious:
```
[[+title]]
[[+link]]
[[+date]]
[[+username]]
```
### Flickr:
```
[[+id]]
[[+created]]
[[+picture]]
[[+title]]
[[+username]]
```
### LastFM:
```
[[+track]]
[[+artist]]
[[+link]]
[[+picture]]
[[+date]]
[[+username]]
```
### Twitter:
```
[[+id]]
[[+message]]
[[+created]]
[[+picture]]
[[+title]]
[[+username]]
```
### Vimeo:
```
[[+id]]
[[+url]]
[[+created]]
[[+picture]]
[[+title]]
[[+username]]
```
### YouTube:
```
[[+published]]
[[+picture]]
[[+title]]
[[+ytlink]]
[[+embedlink]]
[[+author]]
```
### ZooTool:
```
[[+date]]
[[+picture]]
[[+title]]
[[+username]]
[[+referrer]]
[[+permalink]]
```