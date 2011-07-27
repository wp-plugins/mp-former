<?php

/*
  Copyright 2011

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/*
 * Define main constants
 */
define('MP_PREFIX', 'mp_');
define('MP_PLUGIN_NAME', MP_PREFIX . 'former');
//define('MP_BASE_DIR', __DIR__ . '/');
define('MP_BASE_DIR', dirname(__FILE__) . '/');
define('MP_BASE_URL', WP_PLUGIN_URL . '/' . MP_PLUGIN_NAME . '/');
define('MP_BACKUPS_DIR', MP_BASE_DIR . 'backups');
define('MP_HTML_DIR', MP_BASE_DIR . 'view/html/');
define('MP_JS_DIR', MP_BASE_URL . 'view/js/');
define('MP_CSS_DIR', MP_BASE_URL . 'view/css/');

define('WP_UPGRADE_URL', 'http://codex.wordpress.org/Upgrading_WordPress');

/* DB Tables */
global $wpdb;

define('MP_DB_FIELD_REPOSITORY', $wpdb->prefix . 'field_repository');

require(MP_BASE_DIR . 'mp_functions.php');

//include external phpQuery library
if (is_admin()) {
    require(MP_BASE_DIR . 'view/phpQuery/phpQuery.php');
}

/*
 * Define labels and tooltips
 */

define('T_OBJECT_ARGS_PUBLIC', 'Whether posts of this type should be shown in the admin UI. Defaults to false');
define('T_OBJECT_ARGS_SHOW_UI', 'Whether to generate a default UI for managing this post type. Defaults to true if the type is public, false if the type is not public.');
define('T_OBJECT_ARGS_SHOW_IN_MENU', 'Where to show the post type in the admin menu. True for a top level menu,  false for no menu, or can be a top level page like "tools.php" or "edit.php?post_type=page". show_ui must be true');
define('T_OBJECT_ARGS_DESCRIPTION', 'A short descriptive summary of what the post type is. Defaults to blank.');
define('T_OBJECT_ARGS_LABELS_MENU_NAME', 'The menu name text. This string is the name to give menu items. Defaults to value of object title');
define('T_OBJECT_ARGS_MENU_POSITION', 'The position in the menu order the post type should appear. Default: null - defaults to below Comments.');
define('T_OBJECT_ARGS_CAPABILITY_TYPE', 'The string to use to build the read, edit, and delete capabilities. By default the capability_type is used as a base to construct capabilities. Default: "post"');
define('T_OBJECT_ARGS_SUPPORTS', 'An alias for calling add_post_type_support() directly.');
define('T_OBJECT_ARGS_CAN_EXPORT', 'True allows this post type to be exported.');

/*
 * Error constants
 */

define('MP_ERROR_TEMPLATE', __('Template does not exist or empty', MP_PLUGIN_NAME));

define('MP_NOTICE_EMPTYLIST', __('List is Empty. Please click on Add New button to add Element', MP_PLUGIN_NAME));
?>
