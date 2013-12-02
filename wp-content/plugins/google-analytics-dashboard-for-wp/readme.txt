=== Google Analytics Dashboard for WP ===
Contributors: deconf
Tags: google analytics dashboard, analytics dashboard, google, dashboard, google analytics widget, google analytics, tracking, analytics
Requires at least: 2.8
Tested up to: 3.5.1
Stable tag: 4.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin will display Google Analytics data and statistics inside your WordPress Blog.

== Description ==
Using a widget, Google Analytics Dashboard for WP displays detailed info and statistics about: number of visits, number of visitors, bounce rates, organic searches, pages per visit directly on your Admin Dashboard.

Authorized users can also view statistics like Views, UniqueViews and top searches, on frontend, at the end of each article.

Using this plugin, your data is collected in a fast and secure manner because Google Analytics Dashboard for WP uses OAuth2 protocol and Google Analytics API.

Main benefits:

- you can access all websites statistics in a single widget (websites within same Google Account)
- cache feature, this improves loading speed up to 7 times and avoids dailyLimitExceeded, usageLimits.userRateLimitExceededUnreg, userRateLimitExceeded errors from Google Analytics API
- two themes: Blue Theme and Light Theme
- access level settings and lock profile feature
- option to display top 24 pages, referrers and searches (sortable by columns)
- option to display Visitors by Country on Geo Map
- option to display Traffic Overview in Pie Charts
- option to display Google Analytics statistics on frontend, at the end of each article
- simple Authorization process
- has multilingual support, a POT file is available for translations. If you have a complete translation, send me the translation file or upload it to our forum and will be included in next release.

This plugin suports Google Analytics tracking. Main tracking options and features:

- enable/disable google analytics tracking code
- switch between universal analytics and classic analytics tracking methods
- supports analytics.js tracking for comaptibility with Universal Analytics web property  
- supports ga.js tracking for comaptibility with Classic Analytics web property
- track single domain, domain and all subdomains, multiple TLD domains
- IP address anonymization feature
 
Related Links:

* <a href="http://forum.deconf.com/en/wordpress-plugins-f182/google-analytics-dashboard-for-wp-translations-t532.html" target="_blank">Support and Google Analytics Dashboard translations</a>

* <a href="http://www.deconf.com/en/projects/google-analytics-dashboard-for-wordpress/" title="Google Analytics Dashboard for WP Plugin"  target="_blank">Google Analytics Dashboard Plugin Homepage</a>

== Installation ==

1. Upload the full directory into your wp-content/plugins directory
2. Activate the plugin at the plugin administration page
3. Open the plugin configuration page, which is located under Settings -> GA Dashboard and enter your API Key, Client Secret and Client ID.
4. Authorize the application using the 'Authorize Application' button
5. Go back to the plugin configuration page, which is located under Settings -> GA Dashboard to update the final settings.

A step by step tutorial is available here: [Google Analytics Dashboard for WP video tutorial](http://www.deconf.com/en/projects/google-analytics-dashboard-for-wordpress/)

== Frequently Asked Questions == 

= Where can I find my Google API Key, Client Secret, Client ID? =

Follow this step by step video tutorial: [Google Analytics Dashboard for WP](http://www.deconf.com/en/projects/google-analytics-dashboard-for-wordpress/)

= I have several wordpress websites do I need an API Project for each one? =

No, you don't. You can use the same API Project (same API Key, Client Secret and Client ID) for all your websites.

= Some settings are missing from your video tutorial ... =

We are constantly improving our plugin, sometimes the video tutorial may be a little outdated.

= More Questions? =

A dedicated section for Wordpress Plugins is available here: [Wordpress Plugins Support](http://forum.deconf.com/en/wordpress-plugins-f182/)

== Screenshots ==

1. Google Analytics Dashboard Blue Theme
2. Google Analytics Dashboard Light Theme
3. Google Analytics Dashboard Settings
4. Google Analytics Dashboard Geo Map
5. Google Analytics Dashboard Top Pages, Top Referrers and Top Searches
6. Google Analytics Dashboard Traffic Overview
7. Google Analytics Dashboard statistics per page on Frontend

== License ==

This plugin it's released under the GPLv2, you can use it free of charge on your personal or commercial website.

== Changelog ==

= 21.06.2013 - v4.0.3 =
- improvements on tracking code
- redundant variable for default domain name
- fix for "cannot redeclare class URI_Template_Parser" error
- added Settings to plugins page
- modified Google Profiles timeouts

= 29.05.2013 - v4.0.2 =
- minimize Google Analytics API requests
- new warnings available on Admin Option Page
- avoid any unnecessary profile list update
- avoid errors output for regular users while adding the tracking code

= 29.05.2013 - v4.0.1 =
- fixed some 'Undefined index' notices
- cache fix to decrease number of API requests

= 03.05.2013 - v4.0 =

* simplified authorization process for beginners
* advanced users can use their own API Project

= 30.04.2013 - v3.5.3 =

* translation fix, textdomain ga-dash everywhere

= 25.04.2013 - v3.5.2 =

* some small javascript fixes for google tracking code

= 19.04.2013 - v3.5.1 =

* renamed function get_main_domain() to ga_dash_get_main_domain

= 19.04.2013 - v3.5 =

* small bug fix for multiple TLD domains tracking and domain with subdomains tracking
* added universal analytics support (you can track visits using analytics.js or using ga.js)

= 17.04.2013 - v3.4.1 =

* switch to domain names instead of profile names on select lists
* added is_front_page() check to avoid problems in Woocommerce

= 13.04.2013 - v3.4 =

* i8n improvements
* RTL improvements
* usability and accessibility improvements
* added google analytics tracking features

= 10.04.2013 - v3.3.3 =

* a better way to determine temp dir for google api cache

= 09.04.2013 - v3.3.3 =

* added error handles 
* added quick support buttons
* added Sticky Notes
* switched from Visits to Views vs UniqueViews on frontpage
* fixed select lists issues after implementing translation, fixed frontend default google analytics profile
* added frontpage per article statistics

= 25.03.2013 - v3.2 =

* added multilingual support
* small bug fix when locking admins to a single google analytics profile

= 25.03.2013 - v3.1 =

* added Traffic Overview in Pie Charts
* added lock google analytics profile feature for Admins
* code optimization

= 25.03.2013 - v3.0 =

* added Geo Map, sortable tables
* minor fixes

= 22.03.2013 - v2.5 =

* added cache feature
* simplifying google analytics api authorizing process

= 21.03.2013 - v2.0 =

* added light theme
* added top pages tab
* added top searches tab
* added top referrers tab
* added display settings

= 20.03.2013 - v1.6 =

* admins can jail access level to a single google analytics profile

= 20.03.2013 - v1.5 =

* added multi-website support
* table ids and profile names are now automatically retrived from google analytics

= 17.03.2013 - v1.4 =

* added View access levels (be caution, ex: if level is set to "Authors" than all editors and authors will have view access)
* fixed menu display issue

= 15.03.2013 - v1.3 =

* switch to Google API PHP Client 0.6.1
* resolved some Google Adsense Dashboard conflicts

= 13.03.2013 - v1.2.1 =

* minor fixes on google analytics api
* added video tutorials

= 12.03.2013 - v1.2 =

* minor fixes

= 11.03.2013 - v1.0 =

* first release