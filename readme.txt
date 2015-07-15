=== WP BoardDocs XML ===
Contributors: cgrymala
Tags: boarddocs, policies, feed
Donate link: http://giving.umw.edu/
Requires at least: 3.5
Tested up to: 4.3
Stable tag: 0.1
License: GPLv2 or later

Allows WordPress to consume and display information from BoardDocs XML feeds.

== Description ==
Implements a new shortcode and widget that allows WordPress to consume information from a BoardDocs XML feed and display it within the WordPress site.

= Usage =

The shortcode implemented by this plugin is `[boarddocs-feed]`. It accepts the following arguments:

* `type` - the type of feed being consumed. The ActivePolicies feed is the default feed used by the shortcode. The available feeds are:
    * ActivePolicies
    * Board
    * Events
    * General
    * Goals
    * ActiveMeetings
    * CurrentMeetings
    * PoliciesUnderConsideration
    * Minutes
* `feed` - the URL to the feed being consumed. If no value is specified, the value of the XML prefix setting will be used
* `show_what` - if a specific book, section, member, category, event, item or goal is supposed to be shown, that can be specified with this parameter

== Installation ==
1. Upload the `wp-boarddocs-xml` folder to wp-content/plugins
1. Activate the plugin
1. Go to Settings -> General and configure the settings for the plugin
1. Use the widget or shortcode where appropriate

== Changelog ==

= 0.3 =
* Fix errors that show up when plugin is active on non-multinetwork install