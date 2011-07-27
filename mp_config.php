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
define('T_OBJECT_ARGS_PUBLICLY_QUERYABLE', 'Whether post_type queries can be performed from the front page.');
define('T_OBJECT_ARGS_LABELS_ADD_NEW', 'The add new text. The default is Add New for both hierarchical and non-hierarchical types.');
define('T_OBJECT_ARGS_LABELS_ADD_NEW_ITEM', 'The add new item text. Default is Add New Post/Add New Page');
define('T_OBJECT_ARGS_LABELS_EDIT_ITEM', 'The edit item text. Default is Edit Post/Edit Page');
define('T_OBJECT_ARGS_LABELS_NEW_ITEM', 'The new item text. Default is New Post/New Page ');
define('T_OBJECT_ARGS_LABELS_VIEW_ITEM', 'The view item text. Default is View Post/View Page');
define('T_OBJECT_ARGS_LABELS_SEARCH_ITEMS', 'The search items text. Default is Search Posts/Search Pages ');
define('T_OBJECT_ARGS_LABELS_NOT_FOUND', 'The not found text. Default is No posts found/No pages found ');
define('T_OBJECT_ARGS_LABELS_NOT_FOUND_IN_TRASH', 'The not found in trash text. Default is No posts found in Trash/No pages found in Trash ');
define('T_OBJECT_ARGS_LABELS_PARENT_ITEM_COLON', 'The parent text. This string isn\'t used on non-hierarchical types. In hierarchical ones the default is Parent Page ');
define('T_OBJECT_ARGS_EXCLUDE_FROM_SEARCH', 'Whether to exclude posts with this post type from search results. Default: value of the opposite of the public argument ');
define('T_OBJECT_ARGS_MAP_META_CAP', 'Whether to use the internal default meta capability handling. Default: false');
define('T_OBJECT_ARGS_HIERARCHICAL', 'Whether the post type is hierarchical. Allows Parent to be specified. Default: false');
define('T_OBJECT_ARGS_PERMALINK_EPMASK', 'The default rewrite endpoint bitmasks. For more info see Trac Ticket 12605. Default: EP_PERMALINK ');
define('T_OBJECT_ARGS_HAS_ARCHIVE', 'Enables post type archives. Will use string as archive slug. Will generate the proper rewrite rules if rewrite is enabled. Default: false');
define('T_OBJECT_ARGS_REWRITE', 'Rewrite permalinks with this format. False to prevent rewrite. Default: true and use post type as slug ');
define('T_OBJECT_ARGS_QUERY_VAR', 'False to prevent queries, or string value of the query var to use for this post type. Default: true - set to $post_type ');
define('T_OBJECT_STATS_TITLE', 'Status title to show on backend');
define('T_OBJECT_STATS_LABEL_COUNT_SINGULAR', 'Label for status if there is only one post. Default: Status title %s');
define('T_OBJECT_STATS_LABEL_COUNT_PLURAL', 'Label for status if there are more than one post. Default: Status titles %s');
//define('t_object_stats_show_in_admin_all', 'Whether to include posts in the edit listing for their post type');
define('T_OBJECT_STATS_SHOW_IN_ADMIN_ALL_LIST', 'Whether to include posts in the edit listing for their post type');
define('T_OBJECT_STATS_SHOW_IN_ADMIN_STATUS_LIST', 'Show in the list of statuses with post counts at the top of the edit listings, e.g. All (12) | Published (9) | My Custom Status (2) ...');
define('T_OBJECT_STATS_EXCLUDE_FROM_SEARCH', 'Whether to exclude posts with this post status from search results. Defaults to false.');
define('T_OBJECT_STATS_VISIBILITY', 'Post type behaviour with current status');
define('T_OBJECT_TAXONS_TITLE', 'Name of the taxonomy shown in the menu. Usually plural. If not set, labels[\'name\'] will be used.');
define('T_OBJECT_TAXONS_HIERARCHICAL', 'Has some defined purpose at other parts of the API and is a boolean value.');
define('T_OBJECT_TAXONS_REWRITE', 'False to prevent rewrite, or array(\'slug\'=>$slug) to customize permastruct; default will use $taxonomy as slug.');
define('T_OBJECT_TAXONS_QUERY_VAR', 'False to prevent queries, or string to customize query var (?$query_var=$term); default will use $taxonomy as query var.');
define('T_OBJECT_TAXONS_SHOW_UI', 'If the WordPress UI admin tags UI should apply to this taxonomy; defaults to public.');
define('T_OBJECT_TAXONS_SHOW_IN_NAV_MENUS', 'True makes this taxonomy available for selection in navigation menus. Defaults to public.');
define('T_OBJECT_TAXONS_SHOW_TAGCLOUD', 'False to prevent the taxonomy being listed in the Tag Cloud Widget; defaults to show_ui which defalts to public.');
define('T_OBJECT_TAXONS_LABELS_SINGULAR_NAME', 'The single name text');
define('T_OBJECT_TAXONS_LABELS_SEARCH_ITEMS', 'The search items text');
define('T_OBJECT_TAXONS_LABELS_ALL_ITEMS', 'The All items text');
define('T_OBJECT_TAXONS_LABELS_POPULAR_ITEMS', 'The polupar items text');
define('T_OBJECT_TAXONS_LABELS_PARENT_ITEM', 'The parent item text');
define('T_OBJECT_TAXONS_LABELS_EDIT_ITEM', 'The edit item text');
define('T_OBJECT_TAXONS_LABELS_UPDATE_ITEM', 'The update item text');
define('T_OBJECT_TAXONS_LABELS_ADD_NEW_ITEM', 'The add new item text');
define('T_OBJECT_TAXONS_LABELS_NEW_ITEM_NAME', 'The new item name text');
define('T_OBJECT_TAXONS_LABELS_SEPARATE_ITEMS_WITH_COMMAS', 'The separate items with commas text');
define('T_OBJECT_TAXONS_LABELS_ADD_OR_REMOVE_ITEMS', 'The add or remove items text');
define('T_OBJECT_TAXONS_LABELS_CHOOSE_FROM_MOST_USED', 'The choose from most used text');

/*
 * Error constants
 */

define('MP_ERROR_TEMPLATE', __('Template does not exist or empty', MP_PLUGIN_NAME));

define('MP_NOTICE_EMPTYLIST', __('List is Empty. Please click on Add New button to add Element', MP_PLUGIN_NAME));
?>
