# JSONDerulo - A JSON feed fetcher for MODX Revolution CMS

Snippets and chunks to pull in various social JSON feeds. [Available on the MODX Extras repo](http://modx.com/extras/package/jsonderulo).

Results are cached for 12 hours by default, but you can specify your own time limit in seconds using &cacheTime. Make sure you call the snippet uncached from your templates!
Most snippets allow you to specify multiple usernames, however any feeds which require an API key will not yet work with multiple accounts.
The following feeds are already set up:

* App.net - most recent posts
* Flickr - most recent photographs in your photostream
* Google+ - most recent public posts
* Google calendar - upcoming public events
* LastFM - recent "loved" tracks or recent "listens"
* Tumblr - most recent posts (several post type options)
* Twitter - most recent tweets, or a user's favourites
* Vimeo - most recent "likes"
* YouTube - Most recent additions to "favourites" playlist or specific user's uploads
* YouTube - (For API v.3 - fetch a public playlist)

## Thanks!

Plenty of thouroughly helpful help from [Mister John Noel](https://github.com/johnnoel) and his code on which these snippets are based.

Also, hat tip to [basvaneijk](https://github.com/basvaneijk) for the preg_replace stuff to auto link @, # and URLs within the [[+message]]

## Requirements/Prerequisites

* Tested on MODx 2.2.x
* PHPThumbOf / pThumb
* API Keys/app secrets, etc for certain feeds
* That's it!

#Specific details

[JSONDerulo v1](https://github.com/pdincubus/JSONDerulo/blob/master/README-v1.md) | [JSONDerulo v2](https://github.com/pdincubus/JSONDerulo/blob/master/README-v2.md)

#General details (applicable to v1 and v2)

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

#### Eventbrite

```
[[+title]]
[[+textDescription]]
[[+htmlDescription]]
[[+organiserName]]
[[+organiserId]]
[[+venueName]]
[[+venueAddress1]]
[[+venueAddress2]]
[[+venueCity]]
[[+venueRegion]]
[[+venueCountryName]]
[[+venueCountry]]
[[+venueLatitude]]
[[+venueLongitude]]
[[+url]]
[[+eventStart]]
[[+eventEnd]]
[[+eventCapacity]]
[[+eventFormat]]
[[+eventId]]
```

There are also some placeholders generated for ticket types (if you add these to your event). They're looped through with the index tagged on to the name. So if your event has only one type of ticket, you'd get these placeholders made available to you:

```
[[+ticketType1]]
[[+ticketCost1]]
[[+ticketFee1]]
[[+ticketFree1]]
[[+ticketTypeQuantity1]]
[[+ticketTypeSold1]]
```

The basic chunk provided has placeholders set for up to three ticket classes - if you have more, just add more!


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
[[+fullImage]]

```

### Google Calendar:

```
[[+timezone]]
[[+published]]
[[+title]]
[[+content]]
[[+link]]
[[+calendarName]]
[[+eventStart]]
[[+eventEnd]]
[[+location]]
[[+allDayEvent]]
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


### Tumblr:

Default placeholders:

```
[[+blogUrl]]
[[+blogName]]
[[+blogDescription]]
[[+post]]
[[+postType]]
[[+postUrl]]
[[+created]]
[[+createdDate]]
[[+id]]
[[+shortUrl]]
```

For video posts:

```
[[+caption]]
[[+videoPermalink]]
[[+thumbnail]]
[[+player250]]
[[+player400]]
[[+player500]]
```

For link posts:

```
[[+title]]
[[+linkUrl]]
[[+linkDescription]]
```

For text posts:

```
[[+title]]
[[+content]]
```

For audio posts:

```
[[+audioSourceUrl]]
[[+audioSourceTitle]]
[[+artist]]
[[+album]]
[[+trackName]]
[[+player]]
[[+audioUrl]]
```

For image posts:

```
[[+caption]]
[[+imagePermalink]]
[[+image]]
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
[[+favouriteCount]]
[[+inReplyToStatusId]]
[[+inReplyToScreenName]]
[[+isRetweet]]
[[+originalAuthorPicture]]
[[+originalAuthor]]
[[+originalUsername]]
[[+originalId]]
[[+mediaThumb]]
[[+mediaSmall]]
[[+mediaMedium]]
[[+mediaLarge]]
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
[[+videoId]]
[[+author]]
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

## PLEASE NOTE - Twitter feeds and count limit

The [Twitter API documentation](https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline) states under retweets that: "When set to false, the timeline will strip any native retweets (though they will still count toward both the maximal length of the timeline and the slice selected by the count parameter)". This means that if you set retweets OFF and the limit to return 5 tweets, and 4 of your most recent tweets are retweets the feed will only return ONE item. This is NOT a bug!


## Google calendar and public feed URLs

Click the arrow beside the name of the calendar, then go to settings.

![Calendar Settings](http://pdincubus.github.com/JSONDerulo/img/cal-settings.png)

Scroll down to find your Calendar ID and use it as the feedLocation parameter - it should look like this: ```str1ng0fr4nd0mch4r5@group.calendar.google.com```
