=== Google Analytics Dashboard for WP ===
Contributors: deconf
Donate link: http://deconf.com/donate/
Tags: google,analytics,google analytics,dashboard,analytics dashboard,google analytics dashboard,google analytics widget,tracking,realtime,wpmu,multisite
Requires at least: 2.8
Tested up to: 3.9.1
Stable tag: 4.2.20
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays Google Analytics Reports and Real-Time Statistics in your Dashboard. Automatically inserts the tracking code in every page of your website.

== Description ==
Using a widget, [Google Analytics Dashboard](http://deconf.com/google-analytics-dashboard-wordpress/) displays detailed analytics info and statistics about: number of visits, number of visitors, bounce rates, organic searches, pages per visit directly on your Administration Dashboard.

Authorized users can also view statistics like Views, UniqueViews and top searches, on frontend, at the end of each article.

Using this plugin, your analytics data is collected in a fast and secure manner because Google Analytics Dashboard uses OAuth2 protocol and Google Analytics API.

= Google Analytics Dashboard =

- you can access all websites statistics in a single widget (websites within same Google Account)
- real-time feature, displays real-time visitors, real-time sources and per page real-time traffic details
- option to choose a default color for your charts, graphs and maps
- option to display top 24 pages, referrers and searches (sortable by columns)
- option to display Visitors by Country on Geo Map
- local websites and business have an option to display cities, instead of countries, on a regional map
- option to display Traffic Overview in Pie Charts
- user access level settings for Backend statistics and analytics reports
- user access level settings for Frontend analytics data and reports
- option to display Google Analytics statistics on frontend, at the end of each article
- frontend widget with website's analytics stats and analytics data anonymization feature
- has multilingual support, a POT file is available for translations. If you have a complete translation, send me the translation file or upload it to our forum and will be included in next release.

= Google Analytics Tracking =

- enable/disable google analytics tracking code
- switch between universal analytics and classic analytics tracking methods
- supports analytics.js tracking for comaptibility with Universal Analytics web property  
- supports ga.js tracking for comaptibility with Classic Analytics web property
- automatically generates the tracking code; the user can also define a custom code
- IP address anonymization feature
- enhanced link attribution feature
- remarketing, demographics and interests tracking
- track events feature: track downloads, emails and outbound links (supported for both tracking methods: classic tracking and universal tracking)
- exclude traffic based on user level access
 
= Further reading and info =

* My [Google Analytics Dashboard](http://deconf.com/google-analytics-dashboard-wordpress/) homepage
* Other [WordPress Plugins](http://deconf.com/wordpress/) by same author
* [Google Analytics Dashboard](http://deconf.com/ask/) translations and support 

== Installation ==

1. Upload the full directory into your wp-content/plugins directory
2. Activate the plugin at the plugin administration page
3. Open the plugin configuration page, which is located under "Google Analytics" menu (optionally enter your API Key, Client Secret and Client ID).
4. Authorize the plugin using the 'Authorize Plugin' button
5. Go back to the plugin configuration page, which is located under "Google Analytics" menu to update/set your settings.
6. Use Google Analytics Tracking options to configure/enable/disable tracking

A tutorial and a short demo is available here: [Google Analytics Dashboard video tutorial](http://deconf.com/google-analytics-dashboard-wordpress/)

== Frequently Asked Questions == 

= I have several wordpress websites do I need an API Project for each one? =

No, you don't. You can use the same API Project (same API Key, Client Secret and Client ID) for all your websites.

= Some settings are missing in your video tutorial ... =

We are constantly improving our plugin, sometimes the video tutorial may be a little outdated.

= More Questions? =

A dedicated section for Wordpress Plugins is available here: [Wordpress Plugins Support](http://deconf.com/ask/)

== Screenshots ==

1. Google Analytics Dashboard Blue Color
2. Google Analytics Dashboard Real-Time
3. Google Analytics Dashboard Settings
4. Google Analytics Dashboard Geo Map
5. Google Analytics Dashboard Top Pages, Top Referrers and Top Searches
6. Google Analytics Dashboard Traffic Overview
7. Google Analytics Dashboard statistics per page on Frontend
8. Google Analytics Dashboard cities on region map
9. Google Analytics Dashboard Widget

== License ==

This plugin it's released under the GPLv2, you can use it free of charge on your personal or commercial website.

== Changelog ==

= 31.05.2014 - v4.2.20 =
- bugfix: russian country map is not working
- bugfix: only administrator can see stats while using a cache plugin
- bugfix: division by zero on frontend widget
- added german translation
- added spanish translation

= 28.04.2014 - v4.2.19 =
- added portuguese translation
- frontend widget CSS fix
- added remarketing, demographics and interests tracking support
- universal analytics is now the default tracking method
- CSS fix for dashboard widgets

= 04.04.2014 - v4.2.18 =
- translations bugfix
- menu display tweaks
- removed debugging log file
- permissions fix for WPMU
- URI fix for frontend filters (top pages and top searches)
- exclude frontend stats in preview mode
- updated download filters
- by default administrators are not excluded from tracking
- bugfix for refresh_profiles() method 

= 01.04.2014 - v4.2.17 =
- fixed on/off toggle bug for frontend settings

= 30.03.2014 - v4.2.16 =
- properly nonce verification

= 28.03.2014 - v4.2.15 =
- force token reset procedure when failing to authenticate
- deleting refresh token transient on uninstall
- trying to catch all possible exceptions in gapi
- no token reset on network connection errors
- fixed screen options bug
- added capability to select each role for access levels and exclude tracking
- added links to top pages table
- added links to top referrers table
- added option to display Chart&Totals/Chart/Totals to frontend widget
- retrieving realtime analytics with wp ajax
- switching to default jquery-ui-tooltip wordpress library
- fixed settings link not displayed in plugins page

= 23.03.2014 - v4.2.14 =
- bugfix for error reporting
- custom API credential are now saved before starting the authorization procedure
- hiding additional info in log data

= 23.03.2014 - v4.2.13 =
- bugfix for I18n
- implemented a basic debugging log
- CURL required error messages
- option to hide all other properties/views from Select Domain list
- added periodical _transient_timeout cleanup
- fixed bug in property refresh method
- disable hide option when none or a single property is available
- better handling errors when a user authorizes without actually having a Google Analytics account
- fixed bug in token revoke method
- fixed bug in token refresh method
- additional validations on frontend features


= 10.03.2014 - v4.2.12 =
- refreshing charts when the time interval changes
- saving last selection
- minimizing requests by using same query serial for frontend and backend queries
- fixed bug in dashboard's switch options for non-admins
- fixed Notice: Undefined index: ga_dash_frontend_stats for new installs
- no more queries if there is no token
 
= 09.03.2014 - v4.2.11 =
- added support for enhanced link attribution
- bugfix on classic tracking code

= 08.03.2014 - v4.2.10 =
- using predefined color for pie charts 

= 24.02.2014 - v4.2.9b =
- refresh token handles additional uncaught exceptions
- partially resolved conflicts with other analytics plugins

= 23.02.2014 - v4.2.8b =
- checkboxes replaced with switch on/off buttons
- multiple bug fixes

= 16.02.2014 - v4.2.7b =
- plugin code rewritten from scratch
- new enhanced, user friendly interface
- added custom tracking code
- added a new frontend widget
- cache improvements, loading speeds optimization, less GAPI queries
- responsive design

= 20.01.2014 - v4.2.6 =
- google analytics api token refresh bugfix

= 16.01.2014 - v4.2.5 =
- corrected wrong analytics stats reporting

= 02.01.2014 - v4.2.4 =
- css fixes
- clear cache fixes

= 11.11.2013 - v4.2.3 =
- time zone fixes
- hourly reports for yesterday and today
- small css fix on frontend

= 06.11.2013 - v4.2.2 =
- small fixes and update

= 12.10.2013 - v4.2.1 =
- fixed Domain and Subdomains tracking code for Universal Analytics 

= 21.09.2013 - v4.2 =
- added google analytics real-time support
- new date ranges: Today, Yesterday, Last 30 Days and Last 90 Days 

= 15.09.2013 - v4.1.5 =
- fixed "lightblack" color issue, on geomap, on light theme
- added cursor:pointer property to class .gabutton

= 09.09.2013 - v4.1.4 =
- added access level option to Additional Backend Settings section 
- added access level option to Additional Frontend Settings section
- new feature for Geo Map allowing local websites to display cities, instead of countries, on a regional map
- fixed colors for Geo Chart containing world visits by country

= 16.08.2013 - v4.1.3 =
- solved WooCommerce conflict using .note class
- added traffic exclusion based on user level access

= 29.07.2013 - v4.1.1 =
- added missing files
- other minor fixes

= 27.07.2013 - v4.1 =
- added event tracking feature: track downloads, track emails, track outbound links
- remove trailing comma for IE8 compatibility

= 14.07.2013 - v4.0.4 =
- a better way to retrieve domains and subdomains from profiles
- remove escaping slashes generating errors on table display

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
* resolved some Google Analytics Dashboard conflicts

= 13.03.2013 - v1.2.1 =

* minor fixes on google analytics api
* added video tutorials

= 12.03.2013 - v1.2 =

* minor fixes

= 11.03.2013 - v1.0 =

* first release
