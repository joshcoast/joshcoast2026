<?php
/*
 * Plugin Name: IvyForms - The innovative Contact Form Builder
 * Plugin URI: https://ivyforms.com
 * Description: Transform your WordPress site with IvyForms: a powerful, user-friendly plugin for creating and managing customizable forms effortlessly.
 * Version: 0.8.2
 * Requires PHP: 7.4
 * Author: Melograno Ventures
 * Author URI: https://melograno.io/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ivyforms
 * Domain Path: /languages
 *
 * IvyForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * IvyForms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with IvyForms. If not, see {URI to Plugin License}.
 */
namespace IvyForms;

use Exception;
use IvyForms\Plugin\Plugin;

if ( ! defined( 'ABSPATH' ) )
{
    exit; // Exit if accessed directly.
}

if (!defined('IVYFORMS_PATH'))
{
    define('IVYFORMS_PATH', __DIR__);
}

if (!defined('IVYFORMS_URL'))
{
    define('IVYFORMS_URL', plugin_dir_url(__FILE__));
}

if (!defined('IVYFORMS_TEMPLATES_IMAGES_URL'))
{
    define('IVYFORMS_TEMPLATES_IMAGES_URL', plugin_dir_url(__FILE__) . 'frontend/src/assets/images/templates/');
}

if (!defined('IVYFORMS_DEV'))
{
    define('IVYFORMS_DEV', false);
}

// Const for plugin slug
if (!defined('IVYFORMS_PLUGIN_SLUG'))
{
    define('IVYFORMS_PLUGIN_SLUG', plugin_basename(__FILE__));
}

if (!defined('IVYFORMS_FILE'))
{
    define('IVYFORMS_FILE', __FILE__);
}
// Const for site URL
if (!defined('IVYFORMS_SITE_URL')) {
    define('IVYFORMS_SITE_URL', get_site_url());
}

if (!defined('IVYFORMS_VERSION')) {
    define('IVYFORMS_VERSION', '0.8.2');
}


require_once IVYFORMS_PATH . '/backend/scope-vendor/autoload.php';
require_once IVYFORMS_PATH . '/backend/vendor/autoload.php';

try {
    Plugin::getInstance();
} catch (Exception $e) {
    echo 'ERROR: ' . esc_html($e->getMessage());
}

