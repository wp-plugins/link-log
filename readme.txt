=== link-log ===
Contributors: smartware.cc
Donate link:http://smartware.cc/make-a-donation/
Tags: log, click, click counting, link analytics, tracking, visitor tracking, external links
Requires at least: 3.0
Tested up to: 4.0
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Find out where your visitors leave to. Track clicks on external links. 

== Description ==

> This Plugin allows you to track which external links your visitors click on.

To achieve this the link-log Plugin changes all your links to external sites. For example **http://www.google.com** is changed to **http://www.example.com/?goto=http://www.google.com**. The link change takes place when a post or page is displayed. Internal links to pages on your domain are not changed, also URLs not starting with "http" or "https" are not changed. Also attributes (like class or target) are not touched.

There is **no need to change anything**. All Links in all posts (even custom post types) and pages are changed automatically in front end. When editing a post or page in back end all links appear unchanged.

= New in Version 1.2 =

It is now possible to omit search engines and other bots.

= Settings (optional) =

In 'Settings' -> 'link-log' you can set
* the name of the parameter (default ?goto)
* the duration of the IP-lock (default none)
* whether to omit search engines or not

= Theme functions =

There are two functions you can use in your theme files:

**`get_linklog_url( $url )`** to get the tracking URL, 
e.g. `<?php $google = get_linklog_url( 'http://www.google.com' ); ?>`

**`the_linklog_url( $url )`** to echo the tracking URL, 
e.g. `<a href="<?php the_linklog_url( 'http://www.google.com' ); ?>" target"=_blank">Google</a>`

= More Information =

Visit the [Plugin Homepage](http://smartware.cc/wp-link-log)

= Do you like the link-log Plugin? =

Thanks, I appreciate that. You don’t need to make a donation. No money, no beer, no coffee. Please, just [tell the world that you like what I’m doing](http://smartware.cc/make-a-donation/)! And that’s all.

== Installation ==

= From your WordPress dashboard =

1. Visit 'Plugins' -> 'Add New'
1. Search for 'link-log'
1. Activate the plugin through the 'Plugins' menu in WordPress

= Manually from wordpress.org =

1. Download link-log from wordpress.org and unzip the archive
1. Upload the `link-log` folder to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Do I have to change the links?  =

No, all your links to external sites are changed automatically.

= Will the changed links appear in the Editor? =

No, the links are changed every time when a post or page is displayed - the original links are left unchanged.

= Will this work with a Caching Plugin?  =

Yes, but if you change the parameter name you have to clear the cache.

= Will the plugin work with my existing Theme?  =

Yes, the plugin works with all Themes.

= Will the plugin work on WordPress Multisite?  =

Yes, in a Multisite installation the plugin stores the link clicks per blog.

== Screenshots ==

1. Settings
2. Click Stats

== Changelog ==

= 1.2 (2014-09-19) =
* Omit search engines and other bots

= 1.1 (2014-06-25) =
* Omit multiple clicks from same IP

= 1.0 (2014-02-20) =
* Initial Release