=== Importer From MaxSite ===
Contributors: zahardoc
Tags: maxsite, import, wordpress
Requires at least: 4.8
Tested up to: 4.9.5
Requires PHP: 5.4
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== Description ==
Plugin Importer From MaxSite provides easy and fast way to move your data from MaxSite CMS to the WordPress.
For now, it imports pages, categories, meta fields and images.

Warning!
Please use new WordPress installation for importing and do not try to do it on your production site directly!

Steps to import the data from your MaxSite CMS site:
* Install "Export API" plugin on the MaxSite CMS site - https://github.com/zahardoc/export_api.
* Install this plugin on your WordPress site.
* Install Advanced Custom Fields plugin (it is needed to import meta fields).
* Go to the "Importer From MaxSite" page.
* Provide your MaxSite url and click "Import Content" button.

Feel free to submit questions, suggestions, bug reports, concerns, etc. to me.


== Plugin Requirements ==
PHP version : 5.4 and latest
WordPress   : Wordpress 4.8 and latest


== Frequently Asked Questions ==
= What if I don't need to import meta fields? =
Just don't install Advanced Custom Fields plugin - meta fields importing will be skipped.

= I need more functionality, plugin doesn't work properly =
Just write me and we'll think what to do :) I speak Russian :)

== Screenshots ==
1. Screenshot 'screenshot-1.png' shows how to start import.
2. Screenshot 'screenshot-2.png' shows importing process.
3. Screenshot 'screenshot-3.png' shows importing results.



== Upgrade Notice ==
= 1.4 =
* Code refactoring
* Used native php function to get pages and images
* Added screenshots
* Importing fields bugs fixed

= 1.3 =
* Importing fields realized (using ACF plugin)
* Disable submit button after click to prevent multiple submissions.
* Added Russian translation.
* Errors handling improved.
* Styles improved.

= 1.2 =
* Message to install Export API plugin added
* Added exceptions and messages when endpoint can not be reached
* Closed template file from direct access

= 1.1 =
* Importing images for posts realized
* Added README.md file


== Changelog ==
= Version 1.4 =
* Code refactoring
* Used native php function to get pages and images
* Added screenshots
* Importing fields bugs fixed

= Version 1.3 =
* Importing fields realized (using ACF plugin)
* Disable submit button after click to prevent multiple submissions.
* Added Russian translation.
* Errors handling improved.
* Styles improved.

= Version 1.2 =
* Message to install Export API plugin added
* Added exceptions and messages when endpoint can not be reached
* Closed template file from direct access

= Version 1.1 =
* Importing images for posts realized
* Added README.md file
