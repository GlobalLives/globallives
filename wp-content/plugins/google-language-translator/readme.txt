=== Google Language Translator ===
Contributors: Rob Myrick
Donate link: http://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=47LFA33AC89S6
Plugin link: http://wp-studio.net/how-it-works
Tags: language translator, google translator, language translate, translate wordpress, google language translator, translation, translate, multi language
Requires at least: 2.9
Tested up to: 4.6
Stable tag: 5.0.29

Welcome to Google Language Translator! This plugin allows you to insert the Google Language Translator tool anywhere on your website using shortcode.

== Description ==

Settings include: inline or vertical layout, show/hide specific languages, hide/show Google toolbar, and hide/show Google branding. Add the shortcode to pages, posts, and widgets.

== Installation ==

1. Download the zip folder named google-language-translator.zip
2. Unzip the folder and put it in the plugins directory of your wordpress installation. (wp-content/plugins).
3. Activate the plugin through the plugin window in the admin panel.
4. Go to Settings > Google Language Translator, enable the plugin, and then choose your settings.
5. Copy the shortcode and paste it into a page, post or widget.
6. Do not use the shortcode twice on a single page - it will not work.

== Frequently Asked Questions ==

Q: What should I do if the translate widget does not show on my website?

1. Make sure that the plugin is installed and activated.
2. Verify that a check mark is placed in the activation setting located at "Settings > Google Language Translator".
3. Verify that the native Wordpress function, wp_footer(), is included in your theme's footer file.
4. Verify that "Show Language Box?" setting is enabled at "Settings > Google Language Translator".
5. Use your browser's web tools to ensure that CSS styles are not hiding the translation widget.
6. Contact support at http://www.wp-studio.net/submit-ticket.

Q: What should I do if there are no languages being displayed in the language box?

1. Make sure that the plugin is installed and activated.
2. Verify that a check mark is placed in the activation setting located at "Settings > Google Language Translator".
3. Verify that Adobe Flash is installed in your web browser. Google uses Flash in order to display their language box.
4. Contact support at http://www.wp-studio.net/submit-ticket.

Q: Can I exclude certain areas of my website from being translated?

A: Yes! Add the "notranslate" class to the HTML element containing your text. For example, the following text will be excluded from translation: <span class="notranslate">Hello World!</span>

== Changelog ==

5.0.29
- Fixed CSS display issues with the floating widget.

5.0.28
- Fixed CSS display issues with the floating widget.

5.0.27
- Removed toolbar.js and flags.js and combined to existing files (to improve efficiency of page load and http requests).
- Added new setting to allow or prevent floating widget text to translate.

5.0.26
- Fixed a small error in adding the setting for Floating Widget text.

5.0.25
- Added new setting for custom text in the Floating Widget.
- Removed "notranslate" class from the Floating Widget text to allow for translation.

5.0.24
- Improved functionality for flags. Users are now returned to original language when the flag is displayed. The Google Toolbar will be hidden once returning back to the original language. The Google Toolbar will appear again when another translation is made.
- Fixed the issue with flags not functioning with SIMPLE layout.
- Removed SIMPLE layout option (which was not working properly) for browsers not using English browsers. The coding challenge for implementing this option is difficult and must be postponed until we find a practical solution.

5.0.23
- Reverted back to an older version of flags.js. We still have some bugs to work out before releasing the updated version. We apologize for the inconvenience.

5.0.22
- Changed a line of text on the settings page.
- Removed a line of redundant javascript to reduce unnecessary page load.
- Fixed an HTML attribute issue displaying in the menu shortcode.
- Improved functionality for flags. The flag for default language will now be disabled once users click it's flag. Flag will be re-enabled once user makes another translation.

5.0.21
- Added 6 new languages with their associated flags: Hawaiian, Kurdish, Kyrgyz, Luxembourgish, Pashto, and Shona. You can now use these langauges using the menu shortcode, as well. 
- Added a "Select All / Clear All" feature to the settings page. No more finger cramps!

5.0.20
- Added Corsican as a new language with its associated flag.

5.0.19
- Added Frisian as a new language with its associated flag.

5.0.18
- Added Sindhi as a new language with its associated flag.

5.0.17
- Added Samoan as a new language with its associated flag.
- Added mobile-responsive CSS to the GLT settings page.

5.0.16
- Added Xhosa as a new language with its associated flag.

5.0.15
- Added Amharic as a new language with its associated flag.

5.0.14
- Fixed a file naming error in google-language-translator.php. This caused flags not to display - we apologize for the inconvenience.

5.0.13
- Renamed some of the wp_enqueue_script calls to include more unique file names, thus avoiding conflict with other plugins overriding these files.
- Corrected some file paths to be more accurate/secure.
- Removed WP Helpdesk logo from the settings page. We are no longer offering these services officially.

5.0.12
- Revert malicious changes made in 5.0.10

5.0.11
- Fixed incorrect link

5.0.10
- Tested up to 4.6

5.0.09
- Turned off error reporting. I apologize for the inconvenience.

5.0.08
- Added a new popup-style layout". You can find this new layout in the settings page located in "Settings > Google Language Translator".

5.0.07
- Changed the flag for Chinese(Traditional) to the Taiwanese flag.  Requested long ago by a few users - thanks for your months of patience!

5.0.06
- Fixed a XSS Cross Scripting Vulnerability in the plugin, as requested by Wordpress.org. Unnecessary code (during testing) was being posted to the settings page, so the code was removed.

5.0.05
- Added 3 new options to the settings page: English, Spanish, and Portuguese flag image variations.
- Fixed an error with the Floating Widget: order of flags was not being honored in certain cases.

5.0.04
- Corrected the text on the settings page to reflect 91 total flags, instead of 81 flags.

5.0.03
- Added 10 new languages and associated flags: Chichewa, Kazakh, Malagasy, Malayalam, Myanmar(Burmese), Sesotho, Sinhala, Sundanese, Tajik, and Uzbek.

5.0.02
- Updated the Tamil flag to use the Indian flag, instead of Tamil Tigers flag.

5.0.01
- Updated style.css to reflect the syntax error connecting to the Chinese flag.

5.0.0
- Wordpress security updates added to the settings page [wp_nonce_field()].
- Removed 3 outside Javascript files - these files are now called upon directly from Wordpress CMS.
- Unpacked flags.js p,a,c,k,e,r code. Unknowingly, this method of coding violated Wordpress plugin policy.
- Updated pricing display for GLT Premium. It was displaying $15 previously, but the price had increased since the last update.

4.0.9
- Replaced: incorrect Catalonian flag image, with the correct image. I apologize for any inconvenience.
- Fixed: Floating Widget issue - previously it loaded 2 times when shortcode was added, which caused it not to work.

4.0.8
- Fixed the small syntax issue related to the Google Analytics tracking number - it was another cause of the language box not displaying.

4.0.7
- Fixed a CSS error in the settings panel display.
- Fixed the coding issue when "Specific Languages" option is chosen - the shortcode was not displaying the language dropdown.

4.0.6

- Removed: "onclick" events from diplaying directly inside HTML. Converted those events to jQuery.
- Fixed the shortcode that allows adding single languages to Wordpress menus. (New example is shown on settings page.)
- Consolidated all flag images into image sprites!
- Re-designed 10 flag images to match the quality of the other flags.
- Fixed the incorrect "alt" tags associated with flag images. The "alt" tag now displays the language name.
- Modified text on the settings page - also added some lightbox pop-ups to help explain settings.
- New updates have also been provided for our Premium version (currently version 4.0.1) located at http://www.wp-studio.net/

4.0.5

- Fixed: Display bug when using single language shortcode.
- Added: New link on the Plugins menu page, which links directly to Google Language Translator settings.


4.0.4

- Added NEW shortcode!  Allows placement of single languages into the navigation menu, pages, and posts. See settings panel for usage details.
- Re-factored code in googlelanguagetranslator.php which reduced code to around 950 lines.
- Removed the "de-activation" hook, which previously deleted options when plugin de-activated.  Added "uninstall" hook instead, so that settings will be preserved only when user deletes the plugin completely.
- Updated CSS styles for the flags area to prevent themes from overriding layouts.

4.0.3

- Adjusted CSS styles for the flag display.

4.0.2

- Eliminated all (or most) HTML validation errors. Big improvement!
- Re-factored more code to increase efficiency.
- Added de-activation hook to reset all plugin settings when plugin is de-activated. (CSS Overrides and Google Analytics ID setting will remain in place and won't be deleted.)
- Fixed the issue with flag language checkboxes. Users can remove English flag if so desired. Previously, English flag was alway required to stay checked, which was not most user-friendly.

4.0.1

- Fixed PHP errors that were neglected in upgrade to 4.0.
- Added conditionals to prevent scripts from loading when the floating widget is turned off.

4.0

- Added 2 new features: 1) Drag/drop flags to re-arrange their order, and 2) Custom flag sizes (16px to 24px).
- Re-factored code in google-language-translator.php. Languages are now loaded dynamically and are not hard-coded.
- GLT Premium is now released: Updates include multiple flags for English, Spanish, and Portuguese languages; customized URLs with 'lang' attribute; drag/drop flags to re-arrnage their order

3.0.9

- Added a title field to the Google Language Translator widget.
- Removed "unexpected text characters" error upon activation (due to error in activation hook).

3.0.8

- Added 9 new languages into the plugin (Hausa, Igbo, Maori, Mongolian, Nepali, Punjabi, Somali, Yoruba, Zulu).
- Corrected an "undefined variable" error that was being generated in Vertical and Horizontal layouts.
- Re-structured coding once again into an Object-Oriented approach.
- Moved all functions of the base class into 'googlelanguagetranslator.php' and the widget into 'widget.php'.
- Moved all javascript files into it's own JS folder.
- Fixed an display issue with "Edit Translations" - they were being hidden when "No branding" option was chosen.
- Corrected various "comma" errors in the string that outputs the script for loading the translator.
- Changed Changelog in readme.txt to show most recent changes first, instead of last.

3.0.7

- Removed an unnecessary CSS file, left over from development. Sorry for any inconvenience if you received display errors.

3.0.6

- Corrected a small display error in displaying the floating widget correctly.

3.0.5

- Added new Floating Widget (see settings page). The Floating Widget is simply another way for allowing website visitors to translate languages.  The functionality is built-in with the existing flag preferences, and can be turned on or off at the administrator's preference. The floating widget can also function in full with both the language box and/or flags showing OR hiding, so the administrator has full control of how it displays. The floating widget is placed at bottom right of the website in the free version, but can be placed in other locations by changing CSS styles associated with the box. The premium version will allow more options as to changing the Floating Widget location.
- Fixed the issue with Dashboard styles loading on the wrong pages. This was causing some annoying display issues on the Wordpress Dashboard.

3.0.4

- Re-factored/re-arranged more code in google languagetransltor.php by placing them into separate files.
- Fixed the issue of Custom CSS box not displaying it's styles to the website. This was only missed in this last update, due to re-arrangement of the files. Sorry for any inconvenience.
- Removed style2.php file, which is unnecessary and was being used in testing.

3.0.3

- Re-factored/re-arranged some of the code in googlelanguagetranslator.php by placing them into separate files.
- Fixed a minor coding issue in glt_widget.php - this was generating an error in Wordpress when debugging.
- Moved all CSS code into a single file.  The result is nice, clean inline CSS code that is now called only once.
- Fixed some additional CSS display issues.

3.0.2

- Adjusted additional minor invalid HTML issues on the settings page, and also in the front-end plugin display.

3.0.1

- Changed the url request to Google to allow both unsecured and secured page translations. Previously, some users experienced errors when trying to use the translator on "https://" (secured) pages.
- Adjusted some minor spacing issues in the settings page HTML (caused some annoying red HTML errors when using "View Source" in right-click menu).
- Removed old CSS styles that were added in the previous 3.0 update - the styles were added when Google servers were being updated, and were producing major translation dislay issues until their update was complete.  Now the styles I added are no longer needed.

3.0

- Correct a small CSS error that affected the showing/hiding of the Google toolbar.

2.9

***IMPORTANT: Google's most recent server update is producing display issues for website translation tool. There are major display issues with the translation toolbar and also the translations editing interface. Version 2.9 temporarily hides the edit translation functionality until Google decides to fix this issue, although you can still edit translations directly through your Google account at translate.google.com. Please direct any support requests through Wordpress.org and we will be happy to assist you.

- Fixed Google Translation toolbar display issue
- Fixed the Edit Translation interface by hiding it temporarily until Google fixes this
- Removed some unneeded styles from the style sheet.
- Fixed some CSS issues for the Google Branding display, which was affected by Google's most recent update

2.8

- Added an option to allow users to manage their own translations directly through their Google Translate account (free). When activated, users can hover over the text of their website, and edit the translations from the webpage directly.  Google will remember these translations, and then serve them to users once the edits are made. Users must install the Google Translate Customization meta tag provided through Google Translate here: translate.google.com/manager/website/settings. To obtain this meta tag, users need to configure the Google Translate tool directly from this website (although they will not use this because the plugin provides it), then the user can obtain the meta tag on the "Get Code" screen, which is displayed after configuring the Google Translate tool on this webpage.
- Added an option to allow users to turn on/off Google's multilanguagePage option, that when activated, the original website content will be a forced translation, instead of original content (but only after a translation is made.)
- Added more flexible styles to the settings page, so that left and right panels display nicely to the user.

2.7

- Added Google Analytics tracking capability to the plugin.

- Added a "CSS Styles" box in the settings panel.

- Changed the Catalonian flag to its correct flag image.

- Fixed coding issues that previously updated options incorrectly, which is why many users experienced display issues.  All options are now initialized upon plugin activation, which should fix this issue permanently.

- Fixed a glitch in our usage of the translate API.  Previously, when the user clicked the default language, it would toggle back and forth between the default language and "Afrikaans" language. Now, users will see the correct language displayed at all times, no matter how many times it is clicked.

2.6

- Added defaults to all options to ensure there are no more issues with the translator displaying upon installation. Again, sorry for any inconvenience.

2.5

- Eliminated an internal Wordpress error being generated from a coding mistake.

- Added a default option for the Translator alingment. Previously, this was causing the plugin to disapppear.

2.4

- Found a couple of small display errors in the settings page after uploading version 2.3. Sorry for any inconvenience!

2.3

- Added a "Preview" area on the settings page that allows you to see your settings in action.

- Added custom flag support for all languages (custom flags available ONLY when selecting the "ALL Languages" setting.

- Added an option that allows left/right alignment of the translation tool.

- Added the "Google Language Translator" widget.

- Updated googlelanguagetranslator.php to properly register setting in the admin settings panel.

2.2

- Added language "Portuguese" and "German" to the Original Language drop-down option on the settings page.

- Changed flag image for the English language (changed United States flag to the United Kingdom flag).

- Added link in the settings panel that points to Google's Attribution Policy.

2.1

- Added language "Dutch" to the Original Language drop-down option on the settings page.

- Added a new CSS class that more accurately hides the "Powered by" text when hiding Google's branding. In previous version, the "Powered by" text was actually disguised by setting it's color to "transparent", but now we have set it's font-size to 0px instead.

2.0 Corrected some immediate errors in the 1.9 update.

1.9

- Added 7 flag image choices that, when clicked by website visitors, will change the language displayed, both on the website, AND in the drop-down box (flag language choices are limited to those provided in this plugin).

- Added 6 additional languages to the translator, as provided in Google's most recent updates ( new languages include Bosnian, Cebuano, Khmer, Marathi, Hmong, Javanese ).

- Corrected a minor technical issue where the Czech option (on the backend) was incorrectly displaying the Croatian language on the front end.

- Added jQuery functionality to the settings panel to improve the user experience.

- Added an option for users to display/hide the flag images.

- Added an option for users to display/hide the translate box when flags are displayed.

- Removed the settings.css file - I found a better way of displaying the options without CSS.

1.8 Modified google-language-translator.php to display the correct output to the browser when horizontal layout is selected.  Previously, it was not displaying at all.

1.7 Modified google-language-translator.php so that jQuery and CSS styles were enqueued properly onto the settings page only. Previously, jQuery functionality and CSS styles were being added to all pages of the Wordpresss Dashboard, which was causing functionality and display issues for some users.

1.6 Added "Specific Language" support to the plugin settings, which allows the user to choose specific languages that are displayed to website visitors.

1.5 Added "Original Language" support to the plugin settings, which allows the user to choose the original language of their website, which ultimately removes the original language as a choice in the language drop-down presented to website visitors.

1.4 Corrected display problems associated with CSS styles not being placed correctly in wp_head.

1.3 HTML display problem in the sidebar area now fixed. Previously, inserting the [google-translator] plugin into a text widget caused it to display above the widget, instead of inside of it.

1.2 Shortcode support is now available for adding [google-translator] to text widgets.  I apologize for any inconvenience this may have caused.

1.1 The shortcode supplied on the settings page was updated to display '[google-translator]'.

== Screenshots ==

1. Settings include: inline or vertical layout, hide/show Google toolbar, display specific languages, and show/hide Google branding. Add the shortcode to pages, posts, and widgets.
