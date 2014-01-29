=== Really Simple Twitter Feed Widget ===
Contributors: whiletrue
Donate link: http://www.whiletrue.it/
Tags: twitter, twitter sidebar, sidebar, social sidebar, widget, plugin, post, posts, links, twitter widget, twitter feed, simple twitter, twitter api 1.1, api 1.1, oauth, twitter oauth
Requires at least: 2.9+
Tested up to: 3.8
Stable tag: 2.4.11

Shows the latest tweets from a Twitter account in a sidebar widget. Twitter API 1.1 ready.

== Description ==
This plugin displays the latest posts from a Twitter account in a sidebar widget. 
Twitter API 1.1 ready, with easy customization of number of posts shown and replies detection.


The plugin is based on Twitter API version 1.1. 
In order to use it, you have to create a personal Twitter Application on the [dev.twitter.com](https://dev.twitter.com/apps "dev.twitter.com") website.
Within your Application, Twitter provides two strings: the Consumer Key and the Consumer Secret.
You also need two other strings, the Access Token and the Access Token Secret, that you can get
following [this guide](https://dev.twitter.com/docs/auth/tokens-devtwittercom "this guide").
Finally, enter all the Authorization string in the widget options box, along with your favorite display settings: your Twitter Widget is now ready and active!

You can use the same Authorization strings for several widgets and multiple website. 
Just remember to store them in a safe place!

You also need CURL and OPENSSL extensions enabled in your PHP environment.

= Shortcode =

If you want to put your recent tweets other than in a widget, you can use the [really_simple_twitter] shortcode. 
The shortcode support is experimental. 

At the moment at least the twitter username and the 4 authentication attributes are mandatory. The shortcode minimal configuration is (with all fields filled):

[really_simple_twitter username="" consumer_key="" consumer_secret="" access_token="" access_token_secret=""]

You can specify other optional attributes, e.g.:

* num (number of tweets to show, e.g. num="10")
* skip_retweets (if set to true, retweets are skipped, e.g. skip_retweets="true")

The full list of available options is available in the plugin FAQ.

= Reference =

For more informations: [www.whiletrue.it](http://www.whiletrue.it/really-simple-twitter-feed-widget-for-wordpress/ "www.whiletrue.it")

Do you like this plugin? Give a chance to our other works:

* [Really Simple Facebook Twitter Share Buttons](http://www.whiletrue.it/really-simple-facebook-twitter-share-buttons-for-wordpress/ "Really Simple Facebook Twitter Share Buttons")
* [Most and Least Read Posts](http://www.whiletrue.it/most-and-least-read-posts-widget-for-wordpress/ "Most and Least Read Posts")
* [Tilted Tag Cloud Widget](http://www.whiletrue.it/tilted-tag-cloud-widget-per-wordpress/ "Tilted Tag Cloud Widget")
* [Reading Time](http://www.whiletrue.it/reading-time-for-wordpress/ "Reading Time")

= Credits =

The initial release of the plugin was based on previous work of Max Steel (Web Design Company, Pro Web Design Studios), which was based on Pownce for Wordpress widget.

The 1.2.3 release is based on the work of Frank Gregor.

The 1.3.5 and 1.3.7 releases are based on the work of Jim Durand.

Starting from the 2.0 release, the Codebird library by J.M. ( me@mynetx.net - https://github.com/mynetx/codebird-php ) is used for Twitter OAuth Authentication.

= Translators =

* Branco, Slovak translation (WebHostingGeeks.com)
* WhileTrue, Italian translation (www.whiletrue.it)
* Inspirats, French translation (rysk-x.com)
* Aleksandra Czuba, Polish translation (www.iwasindira.com)
* Alexandre Janini, Brazilian Portuguese translation (www.asterisko.com.br)


== Installation ==
Best is to install directly from WordPress. If manual installation is required, please make sure to put all of the plugin files in a folder named `really-simple-twitter-widget` (not two nested folders) in the plugin directory, then activate the plugin through the `Plugins` menu in WordPress.

== Frequently Asked Questions == 

= Does the widget show my tweets in real time? =
Yes they're shown in real time, although you have to refresh the page for them to appear.

= How can I modify the styles? =

The plugin follows the standard rules for "ul" and "li" elements in the sidebar. You can set your own style modifying or overriding these rules:
.really_simple_twitter_widget { /* your stuff */ }
.really_simple_twitter_widget li { /* your stuff */ }

As for the linked username on the bottom (if enabled), you can customize it this way:
div.rstw_link_user { /* your stuff */ }

= I've enable user thumbnails. How can I make them look better? =

You can use some CSS rules like these:
`.really_simple_twitter_widget     { margin-left:0; }`
`.really_simple_twitter_widget li  { margin-bottom:6px; clear:both; list-style:none;   }`
`.really_simple_twitter_widget img { margin-right :6px; float:left; border-radius:4px; }`

= What options are available for the shortcode? =

This is the complete option list. The boolean options can be set writing "true" or "false" as values.

*TWITTER AUTHENTICATION* 

*consumer_key*	: Consumer Key

*consumer_secret*	: Consumer Secret

*access_token*	: Access Token

*access_token_secret*	: Access Token Secret

*TWITTER DATA* 

*username*	: Twitter Username

*num*	: Show # of Tweets

*skip_text*	: Skip tweets containing this text

*skip_replies*	: Skip replies (value: true or false)

*skip_retweets*	: Skip retweets (value: true or false)

*WIDGET TITLE*

*title*	: Title

*title_icon*	: Show Twitter icon on title (value: true or false)

*title_thumbnail*	: Show account thumbnail on title (value: true or false)

*link_title*	: Link above Title with Twitter user (value: true or false)

*WIDGET FOOTER*

*link_user*	: Show a link to the Twitter user profile (value: true or false)

*link_user_text*	: Link text

*button_follow*	: Show a Twitter Follow Me button (value: true or false)

*button_follow_text*	: Button text

*ITEMS AND LINKS*

*linked*	: Show this linked text at the end of each Tweet

*update*	: Show timestamps (value: true or false)

*date_format*	: Timestamp format (e.g. M j ) ?

*thumbnail*	: Include thumbnail before tweets (value: true or false)

*thumbnail_retweets* : Use author thumb for retweets (value: true or false)

*hyperlinks*	: Find and show hyperlinks (value: true or false)

*replace_link_text*	: Replace hyperlinks text with fixed text (e.g. "-–>")

*twitter_users*	: Find Replies in Tweets (value: true or false)

*link_target_blank*	: Create links on new window / tab (value: true or false)

*DEBUG*

*debug* :	Show debug info (value: true or false)

*erase_cached_data*	: Erase cached data (value: true or false)

*encode_utf8*	: Force UTF8 Encode (value: true or false)

== Screenshots ==
1. Sample content, using default options (e.g. no active links)  
2. Options available in the Settings menu 

== Changelog ==

= 2.4.11 =
* Added: Shortcode options list

= 2.4.10 =
* Added: Donate link

= 2.4.9 =
* Fixed: Layout cleaning

= 2.4.8 =
* Fixed: Increased number of retrieved posts when the "skip text" option is enabled

= 2.4.7 =
* Added: Timestamp format option

= 2.4.6 =
* Fixed: Secret fields masked

= 2.4.5 =
* Changed: Readme cleaning

= 2.4.4 =
* Changed: readme cleaning

= 2.4.3 =
* Changed: code cleaning

= 2.4.2 =
* Added: account thumbnail option
* Changed: code cleaning

= 2.4.1 =
* Added: "really_simple_twitter" shortcode

= 2.4 =
* Added: Twitter Follow @user button with text customization
* Changed: more compact and clean settings ui

= 2.3.99 =
* Fixed: revert to older Codebird version (PHP < 5.3 compatible)

= 2.3.1.2 =
* Fixed: previous Codebird version now available when running PHP < 5.3 

= 2.3.1.1 =
* Fixed: class_exists check is now namespace safe

= 2.3.1 =
* Changed: updated Codebird library
* Changed: better error handling 

= 2.3 =
* Changed: updated timestamp function, following the current Twitter guidelines 
* Changed: updated Italian translation

= 2.2 =
* Added: Skip retweets option
* Added: Use author thumb for retweets option
* Changed: Retrieve and use original text for retweets (avoids truncated messages)

= 2.1.1 =
* Added: Replace links with fixed text option
* Added: Brazilian Portuguese translation by Alexandre Janini (www.asterisko.com.br)

= 2.1 =
* Added: Show thumbnail option
* Added: Limit to 200 tweets as stated in Twitter API
* Added: Skip replies option, active by default for new widgets
* Changed: Cache timeout lowered from 30 to 10 minutes

= 2.0.4 =
* Added: Erase cached data option (to be used only for a few minutes, when having issues)
* Changed: More reasonable options display
* Changed: Only show Twitter API debug status when getting actual Twitter data
* Changed: If widget title is not set, don't show the title box
* Fixed: Twitter object format causing PHP warnings
* Fixed: if Twitter data is empty, don't cache it
* Fixed: minor warning on empty Twitter data
* Fixed: minor notice on Twitter status timeout

= 2.0 =
* Added: Twitter API 1.1 support
* Changed: Brand new options box
* Changed: Brand new debug system

= 1.3.17 =
* Added: Polish translation by Aleksandra Czuba (www.iwasindira.com)
* Added: French translation by Inspirats (rysk-x.com)
* Added: Slovak translation by Branco (WebHostingGeeks.com)
* Added: show debug info option
* Added: optional Twitter icon near the widget title
* Added: span wrapper element around the comma before the timestamp (class: rstw_comma), for easier design customization
* Added: option to customize text on the user link below the list of tweets
* Changed: more feed error catching 
* Changed: screenshots moved outside, reducing the size of the plugin and allowing for faster updates
* Changed: cleaner options UI
* Changed: now the "Create links on new window/tab" option affects all kinds of link
* Changed: different regular expression for hashtag recognition
* Changed: better caching control, allowing two widget with same username and different number of messages
* Changed: simpler widget name
* Fixed: widget_title filter
* Fixed: storing feed error 
* Fixed: better caching errors control (work by Jim Durand)
* Fixed: avoid php warning on preg_replace function (php 5.3.5 bug)
* Fixed: error checking in the json request
* Fixed: now checks for errors when retrieving data from the Twitter API

= 1.3.1 =
* Fixed: now retrieve data in JSON format for better storage with transient API and faster data update

= 1.3.0 =
* Changed: use Transient API to cache Twitter results, in order to reduce direct requests to the Twitter API
* Added: option to add a link to the Twitter user page below the posts (CSS customizable via the "rstw_link_user" class)
* Added: option to create all the links on new window / tab
* Added: Italian translation

= 1.2.3 =
* Changed: a bit of UI (work by Frank Gregor)
* Added: switch for setting on/off a link of the title to the twitter user (work by Frank Gregor)
* Added: German translation (work by Frank Gregor)
* Fixed: Better hashtag handling

= 1.2.0 =
* Changed: FB Widget API adoption (carries multiple Widgets support)

= 1.1.1 =
* Changed: direct to the twitter.com search link

= 1.1.0 =
* Changed: Use the new Twitter REST API
* Changed: Error handling cleaning

= 1.0.2 =
* Changed: Feed cache lifetime shortening to 30 minutes (default is 12 hours)

= 1.0.1 =
* Changed: Some more code cleaning and security option control

= 1.0.0 =
* Added: Option to skip tweets containing certain text
* Changed: New Wordpress Feed API adoption
* Changed: Code cleaning


== Upgrade Notice ==

= 2.3.99 =
Revert to old Codebird version, users running PHP < 5.3 MUST upgrade (we apologize)

= 2.3.1.2 =
Previous Codebird version now available, users running PHP < 5.3 should upgrade 

= 2.3.1.1 =
The class_exists check is now namespace safe, users with multiple Codebird instances should upgrade

= 2.3.1 =
When using this release, PHP >= 5.3 is needed (required by the latest Codebird Twitter API), users running PHP < 5.3 should upgrade at least to the 2.3.1.2 plugin release.

= 2.0 =
This plugin is based on Twitter API version 1, that will be deleted on March 2013. 
The upcoming 2.0 plugin release, based on the new Twitter API version 1.1, requires you
to create a personal Twitter Application on the [dev.twitter.com](https://dev.twitter.com/apps "dev.twitter.com") website.
Within your Application, Twitter provides two strings: the Consumer Key and the Consumer Secret.
You also need two other strings, the Access Token and the Access Token Secret, that you can get
following [this guide](https://dev.twitter.com/docs/auth/tokens-devtwittercom "this guide").
Finally, enter all the Authorization string in the widget options box, along with your favorite display settings: your Twitter Widget is now ready and active!
You can use the same Authorization strings for several widgets and multiple website. 
Just remember to store them in a safe place!
You also need to enable the CURL and OPENSSL extensions in your PHP environment.

= 1.3.13 =
A blocking bug appeared in the 1.3.12 release is fixed

= 1.3.9.1 =
A "saturday" bug appeared in the 1.3.9 release is fixed (the Twitter icon wasn't properly shown)

= 1.3.1 =
A blocking bug appeared in the 1.3.0 release is fixed

= 1.2.0 =
Due to the FB Widget API adoption, existing widgets need to be recreated

= 1.0.0 =
Initial release
