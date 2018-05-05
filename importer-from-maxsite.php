<?php
/**
 * @package Importer_From_Maxsite
 */
/*
Plugin Name: Importer From MaxSite
Plugin URI: https://github.com/
Description: Plugin Importer From MaxSite provides easy and fast way to move your data from MaxSite CMS to the WordPress.
Version: 1.0
Author: Sergey Zaharchenko <zaharchenko.dev@gmail.com>
Author URI: https://github.com/zahardoc
License: GPLv3
Text Domain: importer-from-maxsite
*/

/*
*** GPL3 ***
This plugin is free software:
you can redistribute it and/or modify it under the terms of the
GNU General Public License as published by the Free Software Foundation,
either version 3 of the License, or (at your option) any later version.
It is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with "Importer From MaxSite".
If not, see <http://www.gnu.org/licenses/>.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    exit;
}

define( 'IFM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IFM_PLUGIN_BASENAME', plugin_basename(__FILE__));
define( 'IFM_PLUGIN_URL', plugins_url('', __FILE__));
define( 'IFM_TEXT_DOMAIN', 'importer-from-maxsite');
define( 'IFM_ASSETS_VERSION', 1.1);

require_once __DIR__ . '/app/class-page-controller.php';
require_once __DIR__ . '/app/class-importer.php';

\Importer_From_Maxsite\Page_Controller::instance()->init();
\Importer_From_Maxsite\Importer::instance()->init();
