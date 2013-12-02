=== Insert JavaScript and CSS ===
Contributors: RyanNutt
Donate link: http://www.nutt.net/donate/
Tags: post, page, javascript, css
Requires at least: 3.0
Tested up to: 3.4.1
Stable tag: 0.2
License: GPLv2

Adds fields to the post and page edit pages that allow you to insert custom JavaScript or CSS for that
post or page. 

== Description ==
Easily add custom JavaScript or CSS to a single post or page.

When activated, a new icon is added to the post edit page near the icon you use 
to add media to a post. Clicking the new icon brings up a screen where you can 
insert JavaScript and CSS that will be included with the current post. 

Anything entered into the text field on the JavaScript tab will be inserted into 
the &lt;head&gt; section of your web page. Same is true of the CSS tab. This allows 
you to insert arbitrary JavaScript and CSS into any post or page you would 
like without having to resort to loading it on all pages.

== Installation ==

1. Upload the `insert-javascript-css` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

OR

1. Use the plugin installer inside of WordPress

== Frequently Asked Questions ==

= Do I need to wrap in script or style tags? =
Does't matter. If you do, they're left in place. If you don't, they'll be added.

= What about archive pages? =
Unless you specifically tell it not to, this plugin will include any JavaScript or CSS 
included in a post on archive pages as well.

This can lead to conflicts. Take for example two posts, one with `body { background: blue; }`
and the other with `body { background: green; }`. Whichever post comes last will take priority,
but that's probably not what you want. 

If you add a custom field named `ijsc_single_only` to your post the JavaScript or CSS will
only add when `is_single()` is true. It doesn't matter what value you put in `ijsc_single_only`, only
that there is a value. 

= Who can insert JavaScript or CSS =
By default users with the capability upload_files are allowed to insert JavaScript
or CSS into posts. This seemed to be a logical choice as you need to have a certain level of trust 
for users to upload files. 

If you would like to change what capability is required you can edit the `IJC_CAPABILITY_REQUIRED` 
constant defined in `insert-javascript-css.php` inside the plugin folder. 


== Screenshots ==

1. Icon added to the post media area
2. Form to enter JavaScript
3. Form to enter CSS

== Changelog ==

= 0.2 =
Fix so CSS and JS will show up in post types other than post.

= 0.1 =
Initial release

== Upgrade Notice ==
Fix so that JS and CSS will load in post types other than post.