=== Google Analytics ===
Contributors: ShareThis
Tags: analytics, dashboard, google, google analytics, google analytics plugin, javascript, marketing, pageviews, statistics, stats, tracking, visits, web stats, widget, analytics dashboard, google analytics dashboard, google analytics widget, google analytics dashboard
Requires at least: 3.8
Tested up to: 4.7.3
Stable tag: 2.1.3

Use Google Analytics on your Wordpress site without touching any code, and view visitor reports right in your Wordpress admin dashboard!

== Description ==

Google Analytics plugin from ShareThis is the best way to add GA tracking code to your website without modifying any files. Just log in with Google right from your WP admin dashboard and choose which website you want to link. Then you can disable GA tracking of specific users, so that when you are browsing your own site it won't affect your analytics.

Also, you will be able to see Google Analytics reports in the same interface you already use every day to write and manage your posts - in your WordPress dashboard. Now you can stay informed on how your website is doing without having to log into a separate tool.

This Google Analytics plugin has a unique feature called Trending Content. It learns about your traffic patterns and shows you a list of content that is performing significantly better than average, so that you know what resonates with your audience the most. You can even sign up to receive alerts via email or Slack when content is taking off!

One more thing - unlike other plugins, Google Analytics for WordPress has no monthly fees, and no paid upgrades. All the features are totally free.

= Features: =
* Simple setup - Adds latest version of Google Analytics javascript to every page
* Account linking - no need to copy and paste any code or know your GA ID, just log in with Google, select the required website and it will automatically include the appropriate code
* Visitor trends - Shows a summary dashboard with page views, users, pages per session and bounce rate for the past 7 days as compared to the previous 7 days
* Traffic sources - Shows top 5 traffic sources so you know where your visitors are coming from
* Trending Content - Shows a history of content that is performing better than average, so at any given time you know what content resonates with your audience the most
* Alerts - Sign up for alerts via email or Slack when your content is taking off
* Only track real visitors - Allows you to disable tracking for any role like Admins, or Editors so your analytics represent real visitors
* Mobile - Fully optimized for mobile, so you can view your dashboards on any device
* More updates coming - Continually updated and supported by a team of top Wordpress developers

If you don't have a Google Analytics account, you can sign up for free here: https://www.google.com/analytics/

By downloading and installing this plugin you are agreeing to the <a href="http://www.sharethis.com/privacy/" target="_blank">Privacy Policy</a> and <a href="http://www.sharethis.com/publisher-terms-of-use/" target="_blank">Terms of Service</a>.

= Support =
If you have any questions please let us know directly at support@googleanalytics.zendesk.com or create a new topic within our support portal here: <a href=”https://googleanalytics.zendesk.com/hc/en-us/community/posts/new> https://googleanalytics.zendesk.com/hc/en-us/community/posts/new</a>

== Installation ==

1. Install Google Analytics either via WordPress.org plugin repository or directly by uploading the files to your server
2. Activate the plugin through the Plugins menu in your WordPress dashboard
3. Navigate to Google Analytics in the WordPress sidebar
4. Authenticate via Google, copy and paste the access code and choose your property from the dropdown. You can also add the web property ID from Google Analytics manually but dashboards won't show up in this case.
5. When any of your content takes off you will see the URLs inside the Trending Content section

== Frequently Asked Questions ==
= Do I need to touch any code to add Google Analytics? =
Nope, just sign in with google, choose your website, and our plugin will automatically add Google analytics code to all pages.

= How do I make sure Google Analytics is properly installed on all pages? =
If you signed it with google and selected your website (or manually added the property ID) the Google Analytics javascript will be added to all pages. To check what UA code it is adding, just open any page of your website in Chrome, right click to select Inspect, navigate to Network tab, reload the page and search for googleanalytics, you will see the google code with your UA ID. <a href=”https://cl.ly/1q3o2q26261V/[e5b08a5ae1c09684a56ba14c36e6fa5c]_Screen%2520Shot%25202017-02-06%2520at%25201.57.34%2520PM.png” title=”Google Analytics code on the page example”>See example here.</a>

= I see broken formatting inside the plugin, for example some buttons are not aligned? =
This is likely caused by AdBlocker that is blocking anything related to "google analytics". Please disable AdBlocker for your own website or add it to exceptions if you are using Opera.

= How does that cool "Trending Content" feature work? =
It learns about your traffic patterns to spot "spikes" of visitors and then sends an alert. If your website doesn't have good amount of visitors you might not see any Trending Content Alerts because the algorithm needs more data to see "trends".

= I have other questions, where I can get support or provide feedback? =
If you have any questions please let us know directly at support@googleanalytics.zendesk.com or create a new topic within our support portal here: <a href=”https://googleanalytics.zendesk.com/hc/en-us/community/posts/new> https://googleanalytics.zendesk.com/hc/en-us/community/posts/new</a>
We are always happy to help.

== Screenshots ==

1. Overall site performance - the past 7 days vs previous 7 days
2. The top 5 traffic sources for the past 7 days
3. Directly authenticate Google Analytics, and exclude sets of logged in users
4. Just click to authenticate, then copy the API key and add it to the plugin
5. View different time ranges and key metrics in the Wordpress Google Analytics widget
6. Trending Content shows a list of alerts, article URLs, pageviews and time notified

== Changelog ==

= 2.1.2 =
* Fixed authentication error issue experienced by some users.
* Added re-authentication button for easier changing or relinking of Google Analytics accounts.
* Added “Send Debug” button for faster technical troubleshooting.
* Added refresh button for Google Analytics within dashboard.
* Included new alert for missing Google Analytics account.
* Included new alert for unsupported PHP version.

= 2.1.1 =
* Reduced requests to Google API to help with Google Analytics quotas

= 2.1 =
* NEW: Trending Content - trending content shows you a list of content that is performing better than average
* NEW: Alerts - option to sign up for alerts via email or Slack when your content is taking off
* Additional caching to always show Google Analytics dashboards
* User interface improvements

= 2.0.5 =
* Better compatibility with the Google API quotas
* Undefined variable fix, thanks to charlesstpierre

= 2.0.4 =
* Replaced Bootstrap with own scripts

= 2.0.3 =
* Reliability improvements for Google Analytics access
* Better connection to Google Analytics API
* Fixed the save settings issue, thanks @biologix @tanshaydar
* Minor bug fixes

= 2.0.2 =
* Fixed issues related to older versions of PHP
* Fixed terms of service notice
* Added better support for HTTP proxy, thanks @usrlocaldick for the suggestion
* Added better support when WP_PLUGIN_DIR are already set, thanks @heiglandreas for the tip
* Added support for PHP version 5.2.17

= 2.0.1 =
* Fix for old versions of PHP

= 2.0.0 =
* Completely redesigned with new features!
* Updated with the latest Google Analytics code
* No need to find your GA property ID and copy it over, just sign in with Google and choose your site
* See analytics right inside the plugin, the past 7 days vs your previous 7 days
* Shows pageviews, users, pages per session and bounce rate + top 5 traffic referrals
* Wordpress Dashboard widget for 7, 30 or 90 days graph and top site usage stats
* Disable tracking for logged in users like admins or editors for more reliable analytics

= 1.0.7 =
* Added ability to include Google Analytics tracking code on every WordPress page
