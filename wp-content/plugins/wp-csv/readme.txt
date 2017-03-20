=== WP CSV ===
Contributors: cpkwebsolutions
Donate link: http://cpkwebsolutions.com/donate
Tags: wp, csv, import, export, excel, taxonomy, tag, category, bulk, easy, all, importer, exporter, posts, pages, tags, custom, images
Requires at least: 3.5
Tested up to: 4.3.1
Stable tag: 1.8.0.0

A powerful, yet simple, CSV importer and exporter for Wordpress posts, pages, and custom post types. 

== Description ==
Most WP features are fully supported:


* More than 50000 lines can be imported/exported (the only limit is your server)
* Posts, pages, and custom post types
* Tags, categories, and custom taxonomies  
* Custom fields (simple and complex)
* Thumbnails
* Flexible filter system to easily control which fields export
* Simple User Interface (if you know Excel or another spreadsheet program, you will find this plugin quite easy)
The plugin should now be usable with most plugins that are fully Wordpress compliant.
* Shortcode to allow frontend download by your visitors if you choose

Learn more <a href='http://cpkwebsolutions.com/wp-csv'>here</a> and read the full documentation including a <a href='http://cpkwebsolutions.com/plugins/wp-csv/quick-start-guide/'>quick start guide</a> and a description of all the kinds of fields you'll see.

SUPPORT

We no longer provide any free support for the following <a href='http://cpkwebsolutions.com/plugins/wp-csv/support-and-donations'>reasons</a>.

If you see value in the plugin, <a href='http://cpkwebsolutions.com/donate'>support</a> it!

== Installation ==

Refer to the <a href='http://cpkwebsolutions.com/wp-csv/quick-start-guide'>Quick Start Guide</a>.

== Frequently Asked Questions ==

<a href='http://cpkwebsolutions.com/wp-csv/faq'>Frequently Asked Questions</a> are stored on our main website.

== Screenshots ==

No screenshots available.

== Changelog ==
= 1.8.0.0 =
* Fixed custom field/json bug.
= 1.7.9.0 =
* Fix PHP notices in strict mode.
* Fix layout bug on export page.
* Remove CSV Export Vulnerability.
* Improve handling of blank custom fields.
* Fixed exclude none bug.
= 1.7.8.0 =
* Adding tabbing and split the settings screen into two tabs
* Improved the type and status filtering to give users full control over what is exported
= 1.7.7.0 =
* Shortcode added to optionally allow download by visitors, with date range selection on modified field.
* Fixed bug causing incorrect interpretation of '0' in custom fields.
* Improved code that tests for the new field naming convention.
* Moved menus and reworked all screens
* Added file cleanup function (remove files after 1 day)
* Added export id to files and throughout code to handle concurrency
* Replaced download code with new class
= 1.7.6.0 =
* Post type filtering bug fixed.
= 1.7.5.0 =
* Fixed mark_done bug.
* Added option to export/import attachment post types
* Added check to make sure the uploaded file is smaller than post_max and upload_max PHP ini settings
* Added modified_date and post_mime_type to exported field list
* Fixed a small bug with future posts incorrectly being marked as 'Missed Schedule'
* Improved AJAX error reporting
= 1.7.4.0 =
* Fixed bug causing hidden fields to always export.
* Errors generated during AJAX calls will now be displayed
= 1.7.3.0 =
* Fixed bug causing fields to not line up correctly sometimes.
= 1.7.2.0 =
* Fixed bug with hidden custom fields not exporting.
* Added errors for when an old style import/export file is used.
= 1.7.1.0 =
* New version to prevent the 'invalid header' error that some users were getting on install/upgrade
= 1.7.0.0 =
* Added a debug setting to trace export operations (import to be added in future)
* Overhaul of export code to improve handling of fields with the same name (ie a custom field called 'post_title')
* All fields will now be exported with prefixes (ie wp_ID, wp_post_title, cf_this_is_custom_field, tx_post_tag)
* Modified import to allow import of the new prefixed field names
* Added code to prevent deprecated warning for inconv_set_encoding call in PHP > 5.6
* By request, posts in 'trash' can now be exported
= 1.6.6.0 =
* Minor bug fixes related to the 'no posts found' problem.  (Thank you napcok and mcdorf!)
= 1.6.5.0 =
* Fixed bug in download_view
* Eliminated several more error notices
* mysqli_real_escape_string warnings addressed
= 1.6.4.0 =
* Fixed bug with permissions setting
* Changed function name in download_view to prevent a reported conflict.
* Added a PHP version check before calling of the deprecated iconv_set_encoding function
* Fixed various PHP Notice errors
* Changed mysql to mysqli
= 1.6.3.0 =
* Export should now work for those who have specified a db port in wp-config.php
= 1.6.2.0 =
* Empty exports will hopefully no longer happen.  Bug affecting users with more recent versions of PHP.
= 1.6.1.0 =
* Improved logic for finding/creating a usable CSV folder, and also now provide better feedback when there are problems.
= 1.6.0.0 =
* Added fix for security vulnerability
= 1.5.9.0 =
* Added post status filter (sponsored feature addition)
* Fixed an incompatibility bug reported for WP 3.9
* Protocol independent urls for better compatibility when served over HTTPS
= 1.5.8.0 =
* Enhancement: URL decode taxonomy items before import/export.
= 1.5.7.0 =
* Fixed bug with taxonomy import causing duplicates under some circumstances
* Added dropdown to settings to make it possible for non-administrators to access
= 1.5.6.0 =
* Fixed bug relating to new posts always being 'published'.  Now you can set to 'draft' etc for newly created posts if you wish.
= 1.5.5.0 =
* Fixed bug relating to export of custom post types.
= 1.5.4.0 =
* Fixed bug with import and export when the separator characters are different to defaults.
* Fixed bug that was causing settings to be wiped under certain circumstances.
= 1.5.3.0 =
* Fixed small javascript error that was preventing import and export working for some users
= 1.5.2.0 = 
* Testing of 50000+ records has been done, with some small optimizations.
* Added report messages to give feedback about memory usage, etc.  
* Plugin now ready to be internationalized (POT file in 'lang' sub-folder, please send me MO files in your language).
* Several more minor enhancements and bug fixes, based on feedback.  
= 1.5.1.0 =
* Will now export posts with 'pending' status
* Misc bugfixes and tidy up.
= 1.5.0.0 =
* Compatible with WP 3.8
* Improved look and feel
* Improved memory management greatly, should now be able to process much larger numbers of posts and adapt better to available resources
* More helpful error reporting
= 1.4.5.0 =
* Improved error handling and user feedback for badly formatted taxonomy terms.
= 1.4.4.0 =
* Added row limit and row offset as a work around for when memory limit/timeouts are being hit
* Added post and page to the post type filter, for greater control over what exports
= 1.4.3.0 =
* Enabled export of 'hidden' post meta fields
* Added include/exclude filtering for fields
* Convert complex (serialized) custom fields to JSON and back
= 1.4.2.0 =
* Code cleanup
* Fixed post_author bug (non-existant user ids will now export blank)
= 1.4.1.0 =
* Fixed minor export bug
= 1.4.0.0 =
* Added support for custom taxonomies (NOTE: Old export files are not compatible since the column heading names have changed)
* Added a check for iconv support
* Tweak to reduce memory footprint (experimental)
= 1.3.8.0 =
* Added a custom post type filter for export (thanks to Phillip Temple for the idea and for submitting the code)
= 1.3.7.0 =
* Added error checking and helpful messages when the wrong data is put into the Author field.
* Improved validation of comma separated category lists
= 1.3.6.0 =
* Added support for post_author field.
= 1.3.5.0 =
* Fixed: Error 'creating default object from empty value'.
= 1.3.4.0 =
* Enhancement: Plugin will now automatically create a backup folder in one of 4 locations (in order of preference) and add an .htaccess file to prevent unauthorized download.
= 1.3.3.0 =
* Fixed: Another session bug
= 1.3.2.0 =
* Fixed: Session bug preventing download of CSVs
* Fixed: Version string not being updated
* Added: Automatic search and/or creation of a safe download folder
= 1.3.1.0 =
* Fixed: mysqli_real_escape_string issue
= 1.3.0.0 =
* Fixed: minor incompatibility with WP 3.5
= 1.2.0.0 =
* Fixed: minor incompatibility with PHP 5.4
* Fixed: small improvement to the download mechanism
= 1.1.0.0 =
* Made csv file path configurable
= 1.0.0.0 =
* Initial upload

== Upgrade Notice ==

= 1.8.0.0 =
* Fixed custom field/json bug.
= 1.7.9.0 =
* Fix PHP notices in strict mode.
* Fix layout bug on export page.
* Remove CSV Export Vulnerability.
* Improve handling of blank custom fields.
* Fixed exclude none bug.
