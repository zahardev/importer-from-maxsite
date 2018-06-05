=== Importer From MaxSite ===
Contributors: Sergey Zaharchenko
Tags: maxsite, import, wordpress
Requires at least: 4.8
Tested up to: 4.9.5
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Plugin Importer From MaxSite provides easy and fast way to move your data from MaxSite CMS to the WordPress.

Feel free to submit questions, suggestions, bug reports, concerns, etc. to me.

Steps to import the data from your MaxSite CMS site:
* Install "Export API" plugin on the MaxSite CMS site - https://github.com/zahardoc/export_api.
* Install this plugin on your WordPress site.
* Install Advanced Custom Fields plugin (it is needed to import meta fields).
* Go to the "Importer From MaxSite" page.
* Provide your MaxSite url and click "Import Content" button.


== Plugin Requirements ==
PHP version : 5.4 and latest
WordPress   : Wordpress 4.8 and latest


== Screenshots ==
1. Screenshot 'screenshot-1.png' shows how to start import.
2. Screenshot 'screenshot-2.png' shows importing process.
3. Screenshot 'screenshot-3.png' shows importing results.


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
