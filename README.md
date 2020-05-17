# Importer From MaxSite

Plugin Importer From MaxSite provides easy and fast way to move your data from MaxSite CMS to the WordPress.

Feel free to submit questions, suggestions, bug reports, concerns, etc. to me.

Steps to import the data from your MaxSite CMS site:
* Install [Export API](https://github.com/zahardoc/export_api/) plugin on the MaxSite CMS site
  > Please, make sure that plugin folder name is "export_api", not "export_api-master"

* Install [Importer From MaxSite](https://github.com/zahardoc/importer-from-maxsite/) plugin on your WordPress site.
* Install [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/) plugin (it is needed to import meta fields).
* Go to the "Importer From MaxSite" page.
* Provide your MaxSite url and click "Import Content" button.


## Frequently Asked Questions
#### Plugin doesn't work
    Please check following possible reasons:
    1. The "Export API" plugin is not installed on MaxSite.
    2. The "Export API" plugin folder name is not "export_api".
    3. MaxSite site doesn't work or returns errors. Please check this url- https://your-site/export_api/v1/categories.
       It should return valid JSON.

#### What if I don't need to import meta fields?
    Just don't install Advanced Custom Fields plugin - meta fields importing will be skipped.

#### I need more functionality, plugin doesn't work properly =
    Just write me and we'll think what to do :) I speak Russian :)
