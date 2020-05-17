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
It imports categories, pages, images, comments and meta fields (if ACF plugin is installed).

Warning!
Please use new WordPress installation for importing and do not try to do it on your production site directly!

Steps to import the data from your MaxSite CMS site:
* Install "Export API" plugin on the MaxSite CMS site - https://github.com/zahardoc/export_api.
  > Please, make sure that plugin folder name is "export_api", not "export_api-master"
* Install this plugin on your WordPress site.
* Install Advanced Custom Fields plugin (it is needed to import meta fields).
* Go to the "Importer From MaxSite" page.
* Provide your MaxSite url and click "Import Content" button.
* Don't forget to disable "Export API" plugin on your MaxSite right after the importing!

Feel free to submit questions, suggestions, bug reports, concerns, etc. to me.


== Plugin Requirements ==
PHP version : 5.6 and latest
WordPress   : Wordpress 4.8 and latest


== Frequently Asked Questions ==
= Plugin doesn't work =
Please check following possible reasons:
1. The "Export API" plugin is not installed on MaxSite.
2. The "Export API" plugin folder name is not "export_api".
3. MaxSite site doesn't work or returns errors. Please check this url- https://your-site/export_api/v1/categories.
   It should return valid JSON.

= What if I don't need to import meta fields? =
Just don't install Advanced Custom Fields plugin - meta fields importing will be skipped.

= I need more functionality, plugin doesn't work properly =
Just write me and we'll think what to do :) I speak Russian :)

== Screenshots ==
1. Screenshot 'screenshot-1.png' shows how to start import.
2. Screenshot 'screenshot-2.png' shows importing process.
3. Screenshot 'screenshot-3.png' shows importing results.



== Upgrade Notice ==
= 1.5 =
* Importing comments
* Updated readme

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
