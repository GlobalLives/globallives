=== Google sitemap plugin ===
Contributors: bestwebsoft
Donate link: http://bestwebsoft.com/
Tags: sitemap, google sitemap, google api, google webmaster tools, stmap, gogle sitemap, sitemp, google api sitemap, api sitemap, webmaster sitemap, webmaster tols, google stmp
Requires at least: 2.9
Tested up to: 3.5.1
Stable tag: 2.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to add a Sitemap file to Google Webmaster Tools.

== Description ==

With the Google Sitemap Plugin you can create and add a Sitemap file to Google Webmaster Tools, and get the info about your site in Google Webmaster Tools.

<a href="http://wordpress.org/extend/plugins/google-sitemap-plugin/faq/" target="_blank">FAQ</a>
<a href="http://support.bestwebsoft.com" target="_blank">Support</a>

= Translation =

* Arabic (ar_AR) (thanks to Albayan Design, hani aladoli)
* French (fr_FR) (thanks to <a href="mailto:paillat.jeff@gmail.com">Jeff</a>)
* Russian (ru_RU)
* Serbian (sr_RS) (thanks to <a href="mailto:diana@wpdiscounts.com">Diana</a>, www.wpdiscounts.com)
* Spanish (es_ES) (thanks to <a href="mailto:mrjosefernando@gmail.com">Fernando De León</a>)

If you create your own language pack or update the existing one, you can send <a href="http://codex.wordpress.org/Translating_WordPress" target="_blank">the text in PO and MO files</a> for <a href="http://support.bestwebsoft.com" target="_blank">BWS</a> and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO files <a href="http://www.poedit.net/download.php" target="_blank">Poedit</a>.

= Technical support =

Dear users, our plugins are available for free download. If you have any questions or recommendations regarding the functionality of our plugins (existing options, new options, current issues), please feel free to contact us. Please read the documentation and information on our Support Forum carefully before contacting us. Please note that we accept requests in English only. All messages in another languages won't be accepted.

If you notice any bugs in the plugins, you can notify us about it and we'll investigate and fix the issue then. Your request should contain URL of the website, issues description and WordPress admin panel credentials.
Moreover we can customize the plugin according to your requirements. It's a paid service (as a rule it costs $40, but the price can vary depending on the amount of the necessary changes and their complexity). Please note that we could also include this or that feature (developed for you) in the next release and share with the other users then. 
We can fix some things for free for the users who provide translation of our plugin into their native language (this should be a new translation of a certain plugin, you can check available translations on the official plugin page).

== Installation ==

1. Upload the folder `google sitemap` to the directory `/wp-content/plugins/`.
2. Activate the plugin via the 'Plugins' menu in WordPress.
3. The site settings are available in "Settings"->"Sitemap".

== Frequently Asked Questions ==

= How can I use this plugin? =

After opening the plugin settings page your Sitemap file will be created automatically. If you already have a Sitemap file and do not want to change it, just do not check off the field "I want to create a new sitemap file".
If you do not want a Sitemap file to be added to Google Webmaster Tools automatically, just follow the brief instruction. In another case you should enter your login and password and choose the necessary action.
In order to add a path to your sitemap file in robots.txt you do not need to enter login and password, you should just select the necessary field and click "Update button". If you're using multisiting, the plugin does not allow to add a sitemap to robots.txt

= How to create sitemap.xml file? =

After opening the Settings page the sitemap.xml file will be created automatically in the site root.

= How to replace the existing sitemap.xml file? =

Select "I want to create a new sitemap file" and click "Update", the sitemap file will be recreated then.

= How to add a site to Google Webmaster Tools? =

Select "I want to add this site to Google Webmaster Tools" and click "Update". Your site will be added to Google Webmaster Tools and verified, afterwards your sitemap file will be added.

= How can I remove a site from Google Webmaster Tools? =

Select "I want to delete this site from Google Webmaster Tools" and click "Update". 

= How can I get information about my site in Google Webmaster Tools? =

Select "I want to get info about this site in Google Webmaster Tools" and click "Update". 

= How can I deactivate the plugin? =

In the WordPress admin panel please go to "Plugins", find the Google Sitemap Plugin and click "Deactivate".

== Screenshots ==

1. Google sitemap Settings page.
2. Google sitemap Settings page on the hosting which doesn't support cURL.

== Changelog ==

= V2.8 - 03.06.2013 =
* Update : BWS plugins section is updated.

= V2.7 - 18.04.2013 =
* Update : The English language is updated in the plugin.

= V2.6 - 29.03.2013 =
* NEW : The Serbian language file is added to the plugin.

= V2.5 - 21.03.2013 =
* New: Added ability to create sitemap.xml for multi-sites.
* Update : We updated plugin for custom WP configuration.

= V2.4 - 20.02.2013 =
* NEW : The Spanish language file is added to the plugin.

= V2.3 - 31.01.2013 =
* Bugfix : Bugs in admin menu were fixed.

= V2.2 - 29.01.2013 =
* Bugfix : Update option database request bug was fixed.

= V2.1 - 29.01.2013 =
* NEW: The French language file is added to the plugin.
* Update : We updated all functionality for wordpress 3.5.1.

= V2 - 25.01.2013 =
* New: The automatic update of sitemap after a post or page is trashed or published is added.
* Update : We updated all functionality for wordpress 3.5.

= V1.10 - 24.07.2012 =
* Bugfix : Cross Site Request Forgery bug was fixed. 
* Update : We updated all functionality for wordpress 3.4.1.

= V1.09 - 27.06.2012 =
* New: Added the Arabic language file for plugin.
* Bugfix: Create new sitemap file and Add sitemap file path in robots.txt errors were fixed.
* Update : We updated all functionality for wordpress 3.4.

= V1.08 - 03.04.2012 =
* NEW: Added a possibility to include links on the selected post types to the sitemap.

= V1.07 - 02.04.2012 =
* Bugfix: CURL and save setting errors were fixed.

= V1.06 - 26.03.2012 =
* New: Added language files for plugin.

= 1.05 =
* New: Added sitemap.xsl stylesheet.

= 1.04 =
* New: Added ability to add sitemap.xml path in robots.txt.

= 1.03 =
* New: Added ability to get info about site in google webmaster tools.

= 1.02 =
* New: Added ability to delete site from google webmaster tools.

= 1.01 =
* New: Added ability to add site in google webmaster tools, verificate it and add sitemap file.

== Upgrade Notice ==

= V2.8 =
BWS plugins section is updated.

= V2.7 =
The English language is updated in the plugin.

= V2.6 =
The Serbian language file is added to the plugin.

= V2.5 =
Added ability to create sitemap.xml for multi-sites. We updated plugin for custom WP configuration.

= V2.4 =
The Spanish language file is added to the plugin.

= V2.3 =
Bugs in admin menu were fixed.

= V2.2 =
Update option database request bug was fixed.

= V2.1 =
The French language file is added to the plugin. We updated all functionality for wordpress 3.5.1.

= V2 =
The automatic update of sitemap after a post or page is trashed or published is added. We updated all functionality for wordpress 3.5.

= V1.10 =
Cross Site Request Forgery bug was fixed. We updated all functionality for wordpress 3.4.1.

= V1.09 =
Added the Arabic language file for plugin. Create new sitemap file and Add sitemap file path in robots.txt errors were fixed. We updated all functionality for wordpress 3.4.

= V1.08 =
Added a possibility to include links on the selected post types to the sitemap.

= 1.07 =
CURL and save setting errors were fixed.

= 1.06 =
Added language files for plugin.

= 1.05 =
Added sitemap.xsl stylesheet.

= 1.04 =
Added ability to add sitemap.xml path in robots.txt.

= 1.03 =
Added ability to get info about site in google webmaster tools.

= 1.02 =
Added ability to delete site from google webmaster tools.

= 1.01 =
Added ability to add site in google webmaster tools, verificate it and add sitemap file.
