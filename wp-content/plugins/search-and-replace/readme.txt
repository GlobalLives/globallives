=== Search & Replace ===
Contributors: inpsyde, Bueltge, derpixler, ChriCo, s-hinse, Giede
Tags: search, replace, backup, import, sql, migrate, multisite
Requires at least: 4.0
Tested up to: 4.7
Stable tag: 3.1.1

Search & Replace data in your database with WordPress admin, replace domains/URLs of your WordPress installation.

== Description ==
With Search & Replace you can search for everything and replace this with everything **but before** you do this you can easily **create** a simple **database backup** and restore it.

**We have implements special features!**
The first one is "Replace a Domain / Url" that is useful for a quick and simple transfer or a migration of an WordPress.
The second is a full support for serialized data but there are a lot more features - find them!

Our goal with this plugin is to give you a good solution for both Developers and Users of WordPress.

> **Note:** This plugin requires PHP 5.4 or higher to be activated.

[**Checkout our GitHub Repository**](https://github.com/inpsyde/search-and-replace)

= Features =
- Search & replace data in your WordPress database
- Change domain/URL of WordPress installation
- Handle serialized data
- Choose dry run or download SQL file
- Change table prefix
- Backup & restore your database
- WordPress Multisite support
- Localized and ready for your own language

= Crafted by Inpsyde =
The team at [Inpsyde](http://inpsyde.com) is engineering the web and WordPress since 2006.

= Donation? =
You want to donate - we prefer a [positive review](https://wordpress.org/support/view/plugin-reviews/search-and-replace?rate=5#postform), not more.

== Installation ==
= Requirements =
- WordPress 4.0 (Single and Multisite)
- PHP 5.4, newer PHP versions will work faster.

== Screenshots ==
1. Search and Replace
2. Replace Domain/URL
3. Restore Database
4. Backup Database
5. Result screen after search or search and replace

== Changelog ==

= v3.1.1 (2016-12-31) =
* hotfix: prevent declaration error with Requisite

= v3.1.1 (2016-12-24) =
* Refactor Plugin loading [#67](https://github.com/inpsyde/search-and-replace/issues/67)
* Display error notice is the search value the current domain and save changes to Database selected

= v3.1.0 (2016-02-07) =
* Improve codquality
* Prepared for localization (GlotPress)
* Prevent doing idle prozesses if search & replace pattern the same
* Implement better BigData handling.
* Implement better tab and adminpage handling [#33](https://github.com/inpsyde/search-and-replace/issues/33)
* Prepare the Plugin for localization, change Text-Domain.[#47](https://github.com/inpsyde/search-and-replace/issues/47)
* Remove difference in wordings for buttons between descriptions.[#46](https://github.com/inpsyde/search-and-replace/issues/46)

= v3.0.1 (2016-02-09) =
* Add support for Searchpattern with quotes. [#40](https://github.com/inpsyde/search-and-replace/issues/40)
* Basic travis support for travis was added. [#38](https://github.com/inpsyde/search-and-replace/issues/38)
* Fix Unittest [#37](https://github.com/inpsyde/search-and-replace/issues/37)

= v3.0.0 (2016-01-29) =
* Refactor the plugin, new requirements, goal and result.
* *Thanks to [Sven Hinse](https://github.com/s-hinse/) for help to maintain the plugin*
* Changeable table prefix on replace site URL tab enhancement
* Implement database backup & import tab
* Implement dry Run: Keep for search and replace
* Prevent self destroy
* Multisite basic support - show only tables of current site
* Add special tab for replace the URL
* Supports serialized data
* Refactor the whole codebase

= v2.7.1 (2015-05-28) =
* Fix for changes on database collate since WordPress version 4.2
* Fix to reduce backslashes in search and replace string

= v2.7.0 (2014-09-14) =
* Exclude serialized data from replace function (maybe we reduce the support)
* Add hint, if is serialized data on the result table
* Fix to see also the result case sensitive

= v2.6.6 (09/05/2014) =
* *Thanks to [Ron Guerin](http://wordpress.org/support/profile/rong) for help to maintain the plugin*
* Fix to use $wpdb object for all database access
* Fix inability to search and replace quoted strings
* Output changes to clarify when searching vs. searching and replacing
* Some changes to English strings and string identifiers

= v2.6.5 =
* Fix for change User-ID, add table `comments`

= v2.6.4 =
* Fix capability check, if the constant `DISALLOW_FILE_EDIT` ist defined

= v2.6.3 (10/10/2011) =
* filter for return values, html-filter
* add belarussian language
* add romanian language files

= v2.6.2 (09/11/2011) =
* change right object for use the plugin also on WP smaller 3.0, include 2.9
* add function search and replace in all tables of the database - special care!

= v2.6.1 (01/25/2011) =
* Feature: Add Signups-Table for WP MU
* Maintenance: check for tables, PHP Warning fix

= v2.6.0 (01/03/2011) =
* Feature: add an new search for find strings (maybe a new way for search strings)
* Maintenance: small changes on source

= v2.5.1 (07/07/2010) =
* small changes for use in WP 3.0
Status API Training Shop Blog About Pricing
