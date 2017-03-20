=== Stop Spammers Spam Prevention ===
Tags: spam,  antispam, anti-spam, spam blocker, block spam, signup spam, comment spam, spam filter, registration spam, spammer, spammers, spamming, xss. malware, virus, captcha, comment, comments, contact, contact form, contact forms, form, forms, login, multisite, protection, register, registration, security, signup, trackback, trackbacks, user registration spam, widget, wonderful spam, lovely spam, wonderful spam
Tested up to: 4.5-alpha
Contributors: Keith Graham
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Aggressive anti-spam plugin that eliminates comment spam, trackback spam, contact form spam and registration spam. Protects against malicious attacks.

== Description == 
Stop Spammers is an aggressive website spam defence against comment spam and login attempts. It is capable of performing more than 20 different checks for spam and malicious events and can block spam from over 100 different countries. 

There are 12 pages of options that can be used to configure the plugin to your needs.

In cases where spam is detected, users are offered a second chance to post their comments or login. Denied requests are presented with a captcha screen in order to prevent users from being blocked. The captcha can be configures as OpenCaptcha, Google reCaptcha, or SolveMedia Captcha. The Captcha will only appear when a user is denied access as a spammer.

The plugin is designed to work with other plugins like Gravity Forms. It looks at any FORM POST such as BBPress or other addons that use access controls. THe plugin implements a fuzzy search for email and user ids in order to check for spam.

There are free add-ons available that check some special cases.

The Stop Spammers Plugin has been under development since 2010.

 
== Installation ==
1. Install the plugin using "add new" from the plugin's menu item on the WordPress control panel. Search for Stop Spammers and install.
OR
1. Download the plugin.
2. Upload the plugin to your wp-content/plugins directory.
THEN
3. Activate the plugin.
4. Under the settings, review options that are enabled. The plugin will operate very well without changing any settings. You may wish to update Web Services APIs for reporting spam and change the captcha settings from the default OpenCapture.  

== Changelog ==

= 6.15 =
* Committed changes again.

= 6.14 =
* fixed typo in last commit

= 6.13 =
* Added IP address to debug info.
* Added some extra checks on the invalid ip routine to avoid throwing an error on really broken IP addresses.
* Added new PayPal allow IP ranges.
* Added ClickBank IP addresses to Misc Allow List
* Preserved 404 status return on malicious 404 checks. Tell spammers nothing to see here, move along.
* Added a second security check to Ajax functions.
* Added an additional spam check on logins and registrations in case theme or plugin hides login event. (This is a big deal).
* Fixed bug in Hosting check module. Was reporting false positives. Fixed same bug in three other modules.
* fixed bug from 4.3 (or 4.4) that caused the plugin to send out an extra email to the users on registration.
* Disabled Botscout support. The service lowered their daily limits making it dangerous to rely on.
* Fixed bug in ChkValid that allowed some ips to pass early.
* stopped showing passwords on failed login attempts. Users complained. Might be a security risk.
* removed admin registration link from email routine. WP kept breaking my code. I will put it back some day.
* experimental add-on install seems to work. Still testing.
* I did not update the country ips or the deny lists. I may make these add-ons in the future.
* I did not get around to adding threat scan exceptions for WP 4.3 and 4.4. Will add in next release. Threat scan will thow a bunch of false positives.
* fixed issue with plugin getting confused between Great Britain and United Kingdom. Not the same thing.
* Removed OpenCaptcha - gives me a 500 error all the time. Need to use google or dumb or nothing (looking for an open captcha alternative).

= 6.12 =
* Removed a pregreplace backdoor signature from threat scan. Securi thinks that my search for the string is the actual string, so it reported the plugin as malware. I will release immediately.

= 6.11 =
* Fix Akismet conflict with white list. Akismet positives should be checked against the white list before reporting.
* Fixed another bug in Threat Scan where the file open failed trying to read a file with bad permissions.
* Added additional checks to threat scan based on an articles at: https://blog.sucuri.net
* Added a more complex exclude list to threat scan.
* Fixed OpenCaptcha so that it can display the HTTP image on HTTPS sites without a warning. Catchas require the host to enable curl libraries.
* This plugin and WP Jetpack plugin Login Protection clash. You get a blank screen if you use both. The plugin disables itself if JetPack Login Protection is installed.
* Rebuilt all spammer by country modules. Deleted Africa. Now African countries are reported by lacnic.net, so my programs to extract CIDRS from Stop Forum Spam lists works for Africa now. New Countries added. This fixed a bug where I spelled Africa wrong.
* Admin checks at login are for any user containing the word 'admin' anywhere in login id. Changed from lower case "admin" only. 
* I now show failed password because I think it is important to see the dictionary attacks with many passwords. I may make an option for this in case some admins suffer from "fat fingers" and mistype their passwords frequently.
* Fixed an error in options. The "Check credentials on all login attempts" and "Deny login attempts using 'admin' userid" were switched. The first one checks to the credentials of all login attempts. The second denies users who try to login with ids with the string 'admin', but the id doesn't exist.
* Fixed range check in invalid IP check. Was returning false positives.
* Conflict with eMember plugin. Stop Spammers disables itself (for login checks) if eMember is installed.


= 6.10 =
* Fixed bug in check multi hits option.
* Fixed problem with server_addr variable in checking of allow lists.
* Johan Schiff sent me some nice improvements to the TLD check which I included. It supports complex sub-domains now in addition to simple TLDs.
* Another fix to threat scan trying to follow symbolic links.
* Fixed captcha processing on sites that cannot use URL open functions.
* Checks for WP eMember login in order to prevent conflict on logins. 

= 6.09 =
* IIs 7 and IIs 6 and some hosts fixes for SERVER_ADDR not found
* Fix for Manage Plugin Options to prevent transient checks. (I may restore the transient checks in a future version.)
* Add WorldPay to misc allow list.
* Updated Country spam list and Generated Allow List.
* Fixed bug in finding values in POST. Sometimes returned an array.
* Removed Stripe from Donation page. 
* TLD now looks at all post fields. If author, url, subject or comment ends in dot-tld it is denied. Woo forms sometimes confuses what is the email, so this will test more things for email. It is better though to try *@*.xxx in the deny list, than trying to use TLDs when a plugin uses non standard form field names.

= 6.08 =
* Responded to complaints about admin menu - now it is boring.
* Fixed issue in Threat Scan for unexpected directories or symlinks that threw errors in opendir();
* Added keyword SPAM to plugin name. It was not coming up in plugin searches.
* Added a month's worth of Spammers from the Stop Forum Spam lists. Regenerated all countries spammer lists.
* Fixed bug in IP wildcard checks.

= 6.07 =
* Fixed a bug in white listing
* Fixed a bug in checking ip address
* restored automatic cloudflare ip updating


= 6.06 =
* Fixed a mistake that caused the plugin to stop checking some post variables
* Fixed bug in diagnostics when phpinfo is not allowed
* added a function deny or allow userids. This is dangerous and not very useful, but can be done. A user requested the feature.
* removed cloudflare warning message for now, since the plugin mirrors the CF plugin.

= 6.05 =
* Bad mistake in cloudflare module fixed. Breaks on IPv6 checks
* Added Easter egg to summary screen to change the total count and date.

= 6.04 =
* Removed goto in cloudflare check. It was a wonderful dream that turned into a nightmare when it turns out 5.2 PHP doesn't support the goto statement. It was the first goto that I've coded in high level language in 25 years and I wanted it to work.

= 6.03 =
* Added robust full wild card search for lists using * and ?
* Restored link in registration email
* Restored use of WP_Http for all web service file reads
* Added PHPInfo to Diagnostics
* Added delete transients option to Other WP Options
* Changed from Ugly image to a more conventional one on admin panel
* Fixed bug in link for SFS api checks.
* Forced CloudFlare IP fixing if CloudFlare plugin not found. 
It is still better to install CloudFlare plugin to get most recent IP list, but at least this way the plugin can check for bad ips.



= 6.02 =
* fix link typo in summary.
* fix conflict with Woo Commerce.


= 6.01 =
* Total Rewrite of all code. The plugin uses modular approach so that programmers can add new modules to detect spam. 
* added Diagnostic checks.
* added the ability to use a simple API so that plugin authors can hook into the Stop Spammers' processing to add new detection methods.
* added the ability to block spammers by country.
* added better proxy and firewall detection.
* added multiple allow lists to help prevent false positives.
* improved the plugin interface.
* added the ability to scan the WordPress installation for malicious code.
* added the ability to view and maintain all options, including those from other plugins.
* added second chance captcha options including OpenCaptcha, Google reCaptcha or SolveMedia captcha.



== Frequently Asked Questions ==

= All spammers have the same IP address =
This is the most comment problem that I see. If you see in your log that all users have the same IP address it is possible that your site is behind a firewall of proxy. The IP address that the plugin sees is the IP address of the Proxy or Firewall. You need to configure the proxy to pass the user's original source IP to you. CloudFlare will use its IP address if it is acting as a proxy for your site. You MUST install the CloudFlare plugin in this case. Stop Spammers can do little without a reliable IP address.

= Help, I'm locked out of my Website =
Not everyone who is marked as a spammer is actually a spammer. It is quite possible that you have been marked as a spammer on one of the spammer databases. There is no "back door", because spammers could use it.
Rename stop-spammer-registrations.php to stop-spammer-registrations.xxx and then login. Rename it back and check the history logs for the reason why your were denied access. Was your email or IP address marked as spam in one of the databases? If so, contact the website that maintains the database and ask them to remove you. 
Check off the box, "Automatically add admins to Allow List" in the spammer options settings. Then save your settings. This puts your IP address into the Allow List. You should be able to logout and then log back in.
Use the button on the Stop Spammer settings page to see if you pass. You may have to uncheck some options in order to pass. 
Users in some countried often have to use Proxy servers or VPNs in order to access the site. Often the proxy servers are marked as a source of spam. You should find the IP addresses of the proxies that you use and add add those IP addresses to the Allow List.
You can possibly find out why you were locked out by using the form on the Diagnostics page.
Avoid lockouts my making sure that the second chance captcha is turned on.


= I have found a bug =

Please report it NOW. I fill try to fix it and incorporate the fix into the next release. I try to respond quickly to bugs that are possible to fix (all others take a few days). 
If you are adventurous you can download the latest versions of some of my plugins before I release them.

= I used an older version of the plugin and it worked, but the latest version breaks my site =
You can download previous versions of the plugin at: http://wordpress.org/extend/plugins/stop-spammer-registrations-plugin/developers/
Don't forget to report to me what the problem is so I can try to fix it.

= All spammers have the same IP =
I am finding more and more plugin users on hosts that do some kind of Network Address Translation (NAT) or are behind a firewall, router, or proxy that does not pass the original IP address to the web server. If the proxy does not support X-FORWARDED-FOR (XFF) type headers then there is little that you can do. You must uncheck the "Check IP" box and rely on the plugin to use the passive methods to eliminate spammers. These are good methods and will stop most spammers, but you cannot report spam without reporting yourself, and you cannot cache bad IP addresses.

= I can't log into WordPress from my Android/iPhone app. =
Check your log files to find out exactly why the app was rejected. It usually is often the HTTP_REFERER header was not sent correctly. This is one sign of badly written spam software. It is also, unfortunately, a sign of badly written login software. Uncheck the box on the Stop Spammer settings page "Block with missing or invalid HTTP_REFERER". I Allow List iPhones and iPads using Safari on some checks because of bugs in the headers it sends.

= I see errors in the error listing below the cache listing =
It could be that there is something in your system that is causing errors. Copy the errors and email them to me, or paste them into a comment on the WordPress plugin page. I will investigate and try to fix these errors.

= You plugin is stopping new spam registrations, but how do I clean up existing spam registrations? =
Unfortunately, WordPress did not record the IP address of User registrations prior to version 5.0. This is a design flaw in WordPress. They do record the IP of comments. I cannot run a check against logins without their IP address, so you have to remove users the old fashioned way, one at a time. 
You might try listing the emails of all registered users, and then deleting them. You can then ask all users to re-register, but that would probably annoy your legitimate users.

= I have a cool idea for a feature for Stop-Spammer-Registrations-Plugin. =
I am a full time programmer and have little time to work on my own projects. I will certainly make note of your suggestion, but I may never get to it.

= I would like to support your programming efforts =
I am slowing down maintenance on this plugin. I don't have time to work on it. Don't send me money unless you have a corporate credit card and your bosses can afford it. There is a plugin menu item to contribute. It has links for contributions and buying my books. The best way to support me is to buy me a beer at the local Blues Jam and don't laugh when I play harmonica.

== Support ==

2/21/2015: I found that I cannot handle support other than try to fix problems when pointed out. If you are locked out of your website, delete the plugin and don't use it again. If you find it is too aggressive then start un-checking boxes in the configuration until it works. My sites are hosted on SiteGround.com. I pay for this service, and the plugin works perfectly. I can recommend www.SiteGround.com wholeheartedly. If you self-host or you are on a free or cheap hosting company that uses a proxy server or does not implement basic PHP functions then you cannot use this plugin.


