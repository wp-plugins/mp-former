<?php

/*
  Plugin Name: MP Former
  Description: Graphic UI for Custom Post Type building
  Version: 0.4.2
  Author: Vasyl Martyniuk  <admin@whimba.com>, Benjamin Petersen
  Author URI: http://www.whimba.com
 */

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
 * Include MAIN config file
 */
//require(__DIR__ . '/mp_config.php');
require(dirname(__FILE__) . '/mp_config.php');

class mb_Former {
    /*
     * Control Node
     * 
     * @var object <mp_control>
     * @access private
     */

    private $controlNode;


    /*
     * Constructor
     */

    function __construct() {
        $this->controlNode = mp_create_object('mp_Control');
    }

    /*
     * Setup plugin
     * 
     */

    static public function activate() {
        global $wpdb, $wp_version;

        /* Version check */
        $exit_msg = 'Plugin requires WordPress 3.1.3 or newer. '
                . '<a href="' . WP_UPGRADE_URL . '">Update now!</a>';

        if (version_compare($wp_version, '3.1.3', '<')) {
            exit($exit_msg);
        }

        //setup necessary DB tables
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $query = 'CREATE TABLE IF NOT EXISTS ' . MP_DB_FIELD_REPOSITORY . ' (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `type` varchar(20) NOT NULL,
                    `config` text NOT NULL,
                    PRIMARY KEY (`id`)
                ) ' . $charset_collate;
        $wpdb->query($query);
    }

    /*
     * Uninstall plugin
     */

    static public function deactivate() {
        global $wpdb;

        //TODO : FUTURE - implement backup functionality
        
        /*
         * Get all custom post types and delete them
         * Firstly get post_type = object
         * 
         */
        $ptype = mp_Control::get_mainID();
        $query = "SELECT ID FROM {$wpdb->posts} WHERE post_type='{$ptype}'";
        $oList = $wpdb->get_results($query);
        if (is_array($oList) && count($oList)) {
            //delete all Custom post Types
        }
        
        //delete field repository table
        $query = 'DROP TABLE ' . MP_DB_FIELD_REPOSITORY;
        $wpdb->query($query);
    }

}

register_activation_hook(__FILE__, array('mb_Former', 'activate'));
register_deactivation_hook(__FILE__, array('mb_Former', 'deactivate'));
add_action('init', 'mp_init');

?>